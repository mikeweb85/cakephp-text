<?php declare(strict_types=1);

namespace MikeWeb\CakeText\Utility;

use Cake\Datasource\EntityInterface;
use Cake\Utility\Inflector;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use voku\helper\StopWordsLanguageNotExists;

class Slug
{
    const SEPARATOR = '-';

    /**
     * @throws StopWordsLanguageNotExists
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function generate(EntityInterface|string $source, array $options=[]): string
    {
        $options += [
            'case'          => Text::CASE_LOWER,
            'field'         => 'name',
            'strip'         => false,
            'prefix'        => null,
            'replacement'   => static::SEPARATOR,
        ];

        $string = ($source instanceof EntityInterface) ?
            $source->get($options['field']) :
            (string)$source;

        if (
            $source instanceof EntityInterface
            && false !== filter_var($source->get('id'), FILTER_VALIDATE_INT)
        ) {
            $options['prefix'] = $source->get('id');
        }

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

        $slug = \Cake\Utility\Text::slug(
            Inflector::underscore(
                trim($string, "_- \t\n\r\0\x0B")
            )
        );

        if ($options['prefix']) {
            $slug = (string)$options['prefix'] . $options['replacement'] . $slug;
        }

        return mb_convert_case($slug, $options['case'], '8bit');
    }
}
