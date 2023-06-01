<?php

namespace guimauve\crafttwigviewcomposers;

use Craft;
use craft\base\Plugin;
use yii\base\Event;
use craft\web\View;
use craft\events\TemplateEvent;
use craft\helpers\StringHelper;

/**
 * Twig View Composers plugin
 *
 * @method static TwigViewComposers getInstance()
 */
class TwigViewComposers extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = false;

    public function init()
    {
        parent::init();
        $this->attachEventHandlers();
    }

    private function attachEventHandlers(): void
    {
        // Add View Composers functionality similar to Laravel
        Event::on(View::class, View::EVENT_BEFORE_RENDER_TEMPLATE, function (
            TemplateEvent $event
        ) {
            $template = $event->template;
            // Namespace could be customizable through config
            $composerNamespace = "guimauve\\composers\\";
            $composerClassName = $this->viewComposerClassFromTemplate(
                $template
            );
            $composerClassFull = $composerNamespace . $composerClassName;

            if (class_exists($composerClassFull)) {
                $composerClassFull::compose($event);
            }
        });
    }

    private function viewComposerClassFromTemplate(string $template): string
    {
        return str_replace("/", "\\", StringHelper::camelCase($template));
    }
}
