<?php

namespace billmn\turnstile\models;

use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\helpers\App;

/**
 * Turnstile settings
 */
class Settings extends Model
{
    /**
     * The raw site key.
     *
     * @var string
     */
    public $siteKey;

    /**
     * The raw secret key.
     *
     * @var string
     */
    public $secretKey;

    /**
     * The parsed site key.
     *
     * @return string
     */
    public function getSiteKey(): string
    {
        return App::parseEnv($this->siteKey);
    }

    /**
     * The parsed secret key.
     *
     * @return string
     */
    public function getSecretKey(): string
    {
        return App::parseEnv($this->secretKey);
    }

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => ['siteKey', 'secretKey'],
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['siteKey', 'secretKey'], 'string'],
            [['siteKey', 'secretKey'], 'required'],
        ];
    }
}
