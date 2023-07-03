<?php

namespace billmn\turnstile\variables;

use billmn\turnstile\Turnstile;
use craft\helpers\ArrayHelper;
use Twig\Markup;

class TurnstileVariable
{
    /**
     * Render widget.
     */
    public function getWidget(array $options = []): Markup
    {
        $widget = Turnstile::getInstance()->widget;

        $config = $options['config'] ?? [];
        $attributes = ArrayHelper::without($options, 'config');

        return $widget
            ->setConfig($config)
            ->setAttributes($attributes)
            ->render();
    }

    /**
     * Get site key.
     */
    public function getSiteKey(): string
    {
        $settings = Turnstile::getInstance()->getSettings();

        return $settings->getSiteKey();
    }

    /**
     * Script url.
     */
    public function getScriptUrl(): string
    {
        $widget = Turnstile::getInstance()->widget;

        return $widget->getScriptUrl();
    }

    /**
     * Initialization script.
     */
    public function getInitScript(): string
    {
        $widget = Turnstile::getInstance()->widget;

        return $widget->getInitScript();
    }
}
