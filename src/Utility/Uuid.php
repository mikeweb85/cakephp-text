<?php declare(strict_types=1);

namespace MikeWeb\CakeText\Utility;

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\Utility\Security;
use DateTimeInterface;
use MikeWeb\CakeText\Exception\UuidParsableException;
use Ramsey\Uuid\Provider\Node\RandomNodeProvider;
use Ramsey\Uuid\Provider\NodeProviderInterface;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Integer;
use Ramsey\Uuid\Uuid as UuidClass;
use Ramsey\Uuid\UuidInterface;

class Uuid
{
    const VERSION_4_SSL = 0;
    const VERSION_4_CAKE = -1;
    const VERSION_1 = UuidClass::UUID_TYPE_TIME;
    const VERSION_2 = UuidClass::UUID_TYPE_DCE_SECURITY;
    const VERSION_3 = UuidClass::UUID_TYPE_HASH_MD5;
    const VERSION_4 = UuidClass::UUID_TYPE_RANDOM;
    const VERSION_5 = UuidClass::UUID_TYPE_HASH_SHA1;
    const VERSION_6 = UuidClass::UUID_TYPE_REORDERED_TIME;
    const VERSION_7 = UuidClass::UUID_TYPE_UNIX_TIME;
    const VERSION_8 = UuidClass::UUID_TYPE_CUSTOM;

    static string $namespace = '';

    static ?NodeProviderInterface $provider = null;

    /**
     * @return string
     */
    private static function getNamespaceUuid(): string
    {
        if (!static::$namespace) {
            static::$namespace = Configure::read(
                'Security.Uuid.namespace',
                UuidClass::uuid1()
                    ->toString()
            );
        }

        return static::$namespace;
    }

    /**
     * @return NodeProviderInterface
     */
    protected static function getNodeProvider(): NodeProviderInterface
    {
        if (!static::$provider) {
            static::$provider = new RandomNodeProvider();
        }

        return static::$provider;
    }

    /**
     * @param array<string, mixed> $options
     * @return UuidInterface|string
     */
    public static function generate(array $options=[]): UuidInterface|string
    {
        $options += [
            'version'   => Configure::read('Security.Uuid.version', self::VERSION_7),
            'case'      => Configure::read('Security.Uuid.case', Text::CASE_LOWER),
            'name'      => Security::randomString(64),
            'time'      => new FrozenTime(),
            'return'    => 'string',
        ];

        switch ($options['version']) {
            case static::VERSION_1:
                $uuid = UuidClass::uuid1(
                    static::getNodeProvider()
                        ->getNode()
                );
                break;

            case static::VERSION_2:
                $uuid = UuidClass::uuid2(
                    UuidClass::DCE_DOMAIN_PERSON,
                    new Integer(microtime()),
                    static::getNodeProvider()
                        ->getNode()
                );
                break;

            case static::VERSION_3:
                $uuid = UuidClass::uuid3(
                    static::getNamespaceUuid(),
                    $options['name']
                );
                break;

            case static::VERSION_4:
                $uuid = UuidClass::uuid4();
                break;

            case static::VERSION_5:
                $uuid = UuidClass::uuid5(
                    static::getNamespaceUuid(),
                    $options['name']
                );
                break;

            case static::VERSION_6:
                $uuid = UuidClass::uuid6(
                    static::getNodeProvider()
                        ->getNode()
                );
                break;

            case static::VERSION_7:
                $uuid = UuidClass::uuid7(
                    $options['time']
                );
                break;

            case static::VERSION_4_SSL:
                $raw = openssl_random_pseudo_bytes(16);
                $raw[8] = chr(ord($raw[8]) & 0x39 | 0x80);  // set variant
                $raw[6] = chr(ord($raw[6]) & 0xf | 0x40);   // set version
                $uuid = UuidClass::fromString(
                    preg_replace(
                        '/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/',
                        '$1-$2-$3-$4-$5',
                        bin2hex($raw)
                    )
                );
                break;

            default:
                $uuid = UuidClass::fromString(Text::uuid());
        }

        return ($options['return'] == 'string') ?
            mb_convert_case($uuid->toString(), $options['case'],  'utf-8') :
            $uuid;
    }

    /**
     * @param string $uuid
     * @return bool
     */
    public static function valid(string $uuid): bool
    {
        return UuidClass::isValid($uuid);
    }

    /**
     * @param DateTimeInterface|string|int $uuid
     * @throws UuidParsableException
     * @return UuidInterface
     */
    public static function parse(DateTimeInterface|string|int $uuid): UuidInterface
    {
        return match (true) {
            ($uuid instanceof DateTimeInterface)
                => UuidClass::fromDateTime($uuid),

            (str_starts_with(strtolower($uuid), '0x'))
                => UuidClass::fromHexadecimal( new Hexadecimal($uuid) ),

            (false !== filter_var($uuid, FILTER_VALIDATE_INT))
                => UuidClass::fromInteger((string)$uuid),

            (strlen($uuid) == 16)
                => UuidClass::fromBytes($uuid),

            (strlen($uuid) == 36 && UuidClass::isValid($uuid))
                => UuidClass::fromString($uuid),

            default => throw new UuidParsableException(),
        };
    }
}
