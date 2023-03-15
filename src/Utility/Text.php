<?php declare(strict_types=1);

namespace MikeWeb\CakeText\Utility;

use Cake\Utility\Text as BaseText;
use League\Container\Container;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use voku\helper\StopWords;
use voku\helper\StopWordsLanguageNotExists;

class Text extends BaseText {

    const CASE_LOWER = MB_CASE_LOWER;
    const CASE_UPPER = MB_CASE_UPPER;

    protected static ?Container $registry = null;

    /**
     * @return Container
     */
    public static function getRegistry(): Container
    {
        if (!static::$registry) {
            static::$registry = new Container();
        }

        return static::$registry;
    }

    /**
     * @param string $lang
     * @return array<string>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws StopWordsLanguageNotExists
     */
    public static function getStopwords(string $lang='en'): array
    {
        $hasStopwords = static::getRegistry()
            ->has('stopwords');

        if (!$hasStopwords) {
            static::getRegistry()
                ->add('stopwords', StopWords::class);
        }

        return static::getRegistry()
            ->get('stopwords')
            ->getStopWordsFromLanguage($lang);
    }

    /**
     * Generate a random UUID version 4
     *
     * ### Options
     * case: desired case for string output. mb_string options are CASE_LOWER | CASE_UPPER
     * secure: FALSE uses the core Text::uuid(), TRUE uses openssl_random_pseudo_bytes
     * version: See Uuid::generate()
     *
     * @param int|bool|array<string, mixed> $options
     * @return string
     * @deprecated
     */
    public static function uuid(array|int|bool $options=[]): string
    {
        deprecationWarning('Use Uuid::generate() instead.');

        if (is_int($options)) {
            $options = ['case' => $options];
        } elseif (is_bool($options)) {
            $options = ['secure' => $options];
        }

        $options += [
            'case'      => self::CASE_LOWER,
            'secure'    => false,
            'version'   => Uuid::VERSION_4,
        ];

        return (string)Uuid::generate($options);
    }

    /** @inheritDoc */
    public static function slug(string $string, $options=[]): string
    {
        return Slug::generate($string, $options);
    }

    /**
     * @param string $string
     * @return bool
     * @deprecated
     */
    public static function isUuid(string $string): bool
    {
        deprecationWarning('Use Uuid::valid() instead.');
        return Uuid::valid($string);
    }

    /**
     * @param string $language
     * @return array<string>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws StopWordsLanguageNotExists
     */
    protected static function getStopWordsFromLanguage(string $language='en'): array
    {
        deprecationWarning('Use Text::getStopWords() instead.');
        return static::getStopwords($language);
    }

    /**
     * @param string $html
     * @return string
     * @deprecated
     */
    public static function htmlToText(string $html): string
    {
        deprecationWarning('Use Html::toText() instead.');
        return Html::toText($html);
    }

    /**
     * @param string $html
     * @return string
     * @deprecated
     */
    public static function purifyHtml(string $html): string
    {
        deprecationWarning('Use Html::clean() instead.');
        return Html::clean($html);
    }
}

