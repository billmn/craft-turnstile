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
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set widget configuration.
     *
     * @param array $config
     * @return static
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
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set widget attributes.
     *
     * @param array $attributes
     * @return static
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
     *
     * @return string
     */
    public function getScriptUrl(): string
    {
        return 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=onTurnstileLoaded';
    }

    /**
     * Get Turnstile script attributes.
     *
     * @return array
     */
    public function getScriptAttributes(): array
    {
        return ArrayHelper::merge([
            'async' => true,
            'defer' => true,
        ], $this->config['scriptAttr']);
    }

    /**
     * Render widget.
     *
     * @return Markup
     */
    public function render(): Markup
    {
        $view = Craft::$app->view;

        $view->registerJsFile($this->getScriptUrl(), $this->getScriptAttributes());
        $view->registerJs($this->getInitScript(), $view::POS_END);

        $template = Craft::$app->getView()->renderTemplate('turnstile/_widget', [
            'attributes' => $this->attributes,
        ], View::TEMPLATE_MODE_CP);

        return Template::raw($template);
    }

    /**
     * Get Turnstile initialization script.
     *
     * @return string
     */
    protected function getInitScript(): string
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
