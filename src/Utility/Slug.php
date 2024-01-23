<?php declare(strict_types=1);

namespace MikeWeb\CakeText\Utility;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\FactoryLocator;
use Cake\ORM\Locator\LocatorInterface;
use Cake\Utility\Inflector;
use voku\helper\StopWordsLanguageNotExists;

class Slug
{
    const SEPARATOR = '-';

    /**
     * @var LocatorInterface|null
     */
    static $_tableLocator;

    protected static function getTableLocator(): LocatorInterface
    {
        if (static::$_tableLocator === null) {
            /** @psalm-suppress InvalidPropertyAssignmentValue */
            static::$_tableLocator = FactoryLocator::get('Table');
        }

        return static::$_tableLocator;
    }

    public static function generate(EntityInterface|string $source, array $options=[]): string
    {
        $options += [
            'case'          => Text::CASE_LOWER,
            'field'         => 'name',
            'strip'         => false,
            'prefix'        => null,
            'replacement'   => static::SEPARATOR,
        ];

        $hydrated = ($source instanceof EntityInterface);

        $string = $hydrated ?
            $source->get($options['field']) :
            (string)$source;

        if ($options['prefix'] === true && $hydrated) {
            $pk = static::getTableLocator()
                ->get($source->getSource())
                ->getPrimaryKey();

            if ($source->has($pk)) {
                $prefix = $source->get('id');
                $options['prefix'] = !is_int($prefix) ?
                    crc32($prefix):
                    $prefix;
            }
        }

        if ($options['strip']) {
            try {
                $string = preg_replace(
                    sprintf('/\b(?:%s)\b/i', join('|', Text::getStopwords())),
                    ' ',
                    $string
                );
            } catch (StopWordsLanguageNotExists $e) {
                // do nothing
            }
        }

        $string = preg_replace_callback(
            '/(?<=\s|^|\W)[A-Z]{2,}s?/',
            function ($match) {
                return ucfirst(mb_strtolower((string)$match[0]));
            },
            $string
        );

        $slug = \Cake\Utility\Text::slug(
            Inflector::underscore(
                trim($string, "_- \t\n\r\0\x0B")
            ),
            $options
        );

        if ($options['prefix']) {
            $slug = (string)$options['prefix'] . $options['replacement'] . $slug;
        }

        return mb_convert_case($slug, $options['case'], '8bit');
    }
}
