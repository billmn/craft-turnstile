<?php

namespace billmn\turnstile\services;

use billmn\turnstile\Turnstile;
use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use craft\helpers\Template;
use craft\web\View;
use Twig\Markup;
use yii\base\Component;

/**
 * Widget service
 */
class Widget extends Component
{
    protected array $config = [];
    protected array $attributes = [];

    /**
     * Get widget configuration.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set widget configuration.
     */
    public function setConfig(array $config): static
    {
        $this->config = ArrayHelper::merge([
            'scriptAttr' => [],
        ], $config);

        return $this;
    }

    /**
     * Get widget attributes.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set widget attributes.
     */
    public function setAttributes(array $attributes): static
    {
        $randomId = StringHelper::randomString(5);
        $settings = Turnstile::getInstance()->getSettings();

        $this->attributes = ArrayHelper::merge([
            'id' => "turnstile-{$randomId}",
            'class' => 'cf-turnstile',
            'data' => [
                'sitekey' => $settings->getSiteKey(),
                'turnstile' => 1,
            ],
        ], $attributes);

        return $this;
    }

    /**
     * Get Turnstile script url.
     */
    public function getScriptUrl(): string
    {
        return 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=onTurnstileLoaded';
    }

    /**
     * Get Turnstile script attributes.
     */
    public function getScriptAttributes(): array
    {
        return ArrayHelper::merge([
            'async' => true,
            'defer' => true,
            'position' => Craft::$app->view::POS_HEAD,
        ], $this->config['scriptAttr']);
    }

    /**
     * Render widget.
     */
    public function render(): Markup
    {
        $view = Craft::$app->view;
        $registerJs = $this->config['registerJs'] ?? true;

        if ($registerJs) {
            $view->registerJsFile($this->getScriptUrl(), $this->getScriptAttributes());
            $view->registerJs($this->getInitScript(), $view::POS_END);
        }

        $template = Craft::$app->getView()->renderTemplate('turnstile/_widget', [
            'attributes' => $this->attributes,
        ], View::TEMPLATE_MODE_CP);

        return Template::raw($template);
    }

    /**
     * Get Turnstile initialization script.
     */
    public function getInitScript(): string
    {
        $settings = Turnstile::getInstance()->getSettings();

        return <<<JS
            window.onTurnstileLoaded = function () {
                document.querySelectorAll('[data-turnstile]').forEach(function (el) {
                    renderTurnstile(el.id);
                });
            }

            function renderTurnstile(id) {
                turnstile.render('#' + id, {
                    sitekey: '{$settings->getSiteKey()}'
                });
            }
        JS;
    }
}
