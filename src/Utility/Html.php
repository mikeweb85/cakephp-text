<?php declare(strict_types=1);

namespace MikeWeb\CakeText\Utility;

use Cake\Core\Configure;
use Html2Text\Html2Text;
use HTMLPurifier;
use HTMLPurifier_Config;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use tidy;

class Html
{
    private static function getHtmlPurifier(): HTMLPurifier|tidy|null
    {
        $hasPurifier = Text::getRegistry()
            ->has('html_purifier');

        if (!$hasPurifier) {
            $config = HTMLPurifier_Config::create(
                Configure::readOrFail('HtmlPurifier')
            );

            Text::getRegistry()
                ->add('html_purifier', HTMLPurifier::class)
                ->addArgument($config);
        }

        try {
            return Text::getRegistry()
                ->get('html_purifier');

        } catch (ContainerExceptionInterface|NotFoundExceptionInterface $e) {
            return null;
        }
    }

    public static function clean(string $html): string
    {
        $html = static::getHtmlPurifier()
            ?->purify($html);

        return trim((string)$html);
    }

    public static function toText(string $html): string
    {
        $text = (new Html2Text($html))
            ->getText();

        return trim((string)$text);
    }
}