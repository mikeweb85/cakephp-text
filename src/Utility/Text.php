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

    protected static ?HTMLPurifier $_purifier;

    protected static ?StopWords $_stopWords;

    /**
     * Generate a random UUID version 4
     *
     * Available $options are:
     * - case: desired case for string output. mb_string options are CASE_LOWER | CASE_UPPER
     * - secure: FALSE uses the core Text::uuid(), TRUE uses openssl_random_pseudo_bytes
     *
     * @param int|bool|array<string, mixed> $options
     * @return string
     */
    public static function uuid(array|int|bool $options = []): string
    {
        if (is_int($options)) {
            $options = ['case' => $options];
        } elseif (is_bool($options)) {
            $options = ['secure' => $options];
        }

        $options += [
            'case'      => MB_CASE_LOWER,
            'secure'    => false,
        ];

        if ($options['secure']) {
            $uuid = openssl_random_pseudo_bytes(16);
            // set variant
            $uuid[8] = chr(ord($uuid[8]) & 0x39 | 0x80);
            // set version
            $uuid[6] = chr(ord($uuid[6]) & 0xf | 0x40);

            $uuid = preg_replace(
                '/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/',
                '$1-$2-$3-$4-$5',
                bin2hex($uuid)
            );

        } else {
            $uuid = parent::uuid();
        }

        return mb_convert_case($uuid, $options['case'], 'utf-8');
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
     */
    public static function isUuid(string $string): bool
    {
        return (0 < (int)preg_match(sprintf('/^%s$/i', RouteBuilder::UUID), $string));
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