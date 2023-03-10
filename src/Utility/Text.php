<?php declare(strict_types=1);

namespace MikeWeb\CakeText\Utility;

use Cake\Core\Configure;
use Cake\Routing\RouteBuilder;
use Cake\Utility\Inflector;
use Cake\Utility\Text as BaseText;
use Html2Text\Html2Text;
use HTMLPurifier;
use HTMLPurifier_Config;
use voku\helper\StopWords;
use voku\helper\StopWordsLanguageNotExists;

class Text extends BaseText {

    const CASE_LOWER = MB_CASE_LOWER;

    const CASE_UPPER = MB_CASE_UPPER;

    protected static ?HTMLPurifier $_purifier;

    protected static ?StopWords $_stopWords;

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
    public static function uuid(array|int|bool $options = []): string
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
    public static function slug(string $string, $options = []): string
    {
        $string = preg_replace_callback(
            '/(?<=\s|^|\W)[A-Z]{2,}s?/',
            function ($match) {
                return ucfirst(mb_strtolower((string)$match[0]));
            },
            $string
        );

        return mb_strtolower(
            parent::slug(
                Inflector::underscore(
                    trim($string, "_- \t\n\r\0\x0B")
                )
            ),
            '8bit'
        );
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
     * @return HTMLPurifier
     */
    protected static function getHtmlPurifier(): HTMLPurifier
    {
        if ( !isset(self::$_purifier) || ! self::$_purifier instanceof HTMLPurifier ) {
            $config = Configure::readOrFail('HtmlPurifier');
            self::$_purifier = new HTMLPurifier(HTMLPurifier_Config::create($config));
        }

        return self::$_purifier;
    }

    /**
     * @param string $language
     * @return array
     */
    protected static function getStopWordsFromLanguage(string $language='en'): array
    {
        if ( !isset(self::$_stopWords) || !self::$_stopWords instanceof StopWords ) {
            self::$_stopWords = new StopWords();
        }

        try {
            return self::$_stopWords
                ->getStopWordsFromLanguage($language);

        } catch (StopWordsLanguageNotExists $e) {
            return [];
        }
    }

    /**
     * @param string $data
     * @return Html2Text
     */
    protected static function _getHtmlTextObject(string $data): Html2Text
    {
        return new Html2Text($data);
    }

    /**
     * @param string $html
     * @return string
     */
    public static function htmlToText(string $html): string
    {
        return trim(
            self::_getHtmlTextObject($html)
                ->getText()
        );
    }

    /**
     * @param string $html
     * @return string
     */
    public static function purifyHtml(string $html): string
    {
        return self::getHtmlPurifier()
            ->purify($html);
    }
}