<?php declare(strict_types=1);

namespace MikeWeb\CakeText\Utility;

use Cake\Utility\Text;
use Cake\Utility\Inflector;

class Slug
{
    public static function generate(string $string, array $options=[]): string
    {
        $string = preg_replace_callback(
            '/(?<=\s|^|\W)[A-Z]{2,}s?/',
            function ($match) {
                return ucfirst(mb_strtolower((string)$match[0]));
            },
            $string
        );

        return mb_strtolower(
            Text::slug(
                Inflector::underscore(
                    trim($string, "_- \t\n\r\0\x0B")
                )
            ),
            '8bit'
        );
    }
}
