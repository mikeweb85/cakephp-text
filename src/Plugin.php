<?php declare(strict_types=1);

namespace MikeWeb\CakeText;

use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Core\PluginApplicationInterface;

/**
 * Plugin for CakeText
 */
class Plugin extends BasePlugin
{
    /**
     * Load all the plugin configuration and bootstrap logic.
     *
     * The host application is provided as an argument. This allows you to load
     * additional plugin dependencies, or attach events.
     *
     * @param PluginApplicationInterface $app The host application
     * @return void
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        if (!Configure::check('HtmlPurifier'))
        {
            Configure::write('HtmlPurifier', [
                'Cache.SerializerPath'                      => TMP,
                'Core.EscapeNonASCIICharacters'             => true,
                'AutoFormat.AutoParagraph'                  => true,
                'AutoFormat.RemoveEmpty'                    => true,
                'AutoFormat.RemoveSpansWithoutAttributes'   => true,
                'AutoFormat.RemoveEmpty.RemoveNbsp'         => true,
                'HTML.ForbiddenAttributes'                  => ['style', 'class', 'id', 'lang'],
                'HTML.AllowedElements'                      => ['br', 'p', 'i', 'em', 'strong', 'b', 'span', 'img', 'blockquote'],
                'HTML.ForbiddenElements'                    => ['style'],
            ]);
        }
    }
}
