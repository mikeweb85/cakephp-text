<?php declare(strict_types=1);

namespace MikeWeb\CakeText\Utility;

use Cake\Utility\Inflector;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use voku\helper\StopWordsLanguageNotExists;

class Slug
{
    /**
     * @throws StopWordsLanguageNotExists
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function generate(string $string, array $options=[]): string
    {
        $options += [
            'case'      => Text::CASE_LOWER,
            'strip'     => false,
        ];

        if ($options['strip']) {
            $string = preg_replace(
              sprintf('/\b(?:%s)\b/i', join('|', Text::getStopwords())),
              ' ',
                $string
            );
        }

        $string = preg_replace_callback(
            '/(?<=\s|^|\W)[A-Z]{2,}s?/',
            function ($match) {
                return ucfirst(mb_strtolower((string)$match[0]));
            },
            $string
        );

        return mb_convert_case(
            \Cake\Utility\Text::slug(
                Inflector::underscore(
                    trim($string, "_- \t\n\r\0\x0B")
                )
            ),
            $options['case'],
            '8bit'
        );
    }
}
