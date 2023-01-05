<?php

namespace billmn\turnstile\variables;

use billmn\turnstile\Turnstile;
use craft\helpers\ArrayHelper;
use Twig\Markup;

class TurnstileVariable
{
    /**
     * Render widget.
     *
     * @param array $options
     * @return Markup
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
     *
     * @return string
     */
    public function getSiteKey(): string
    {
        $settings = Turnstile::getInstance()->getSettings();

        return $settings->getSiteKey();
    }
}
