<?php

namespace billmn\turnstile\services;

use billmn\turnstile\Turnstile;
use Craft;
use craft\helpers\Json;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use yii\base\Component;

/**
 * Validator service
 */
class Validator extends Component
{
    /**
     * HTTP Client.
     */
    public function getClient(): Client
    {
        return new Client([
            'base_uri' => 'https://challenges.cloudflare.com/turnstile/v0/',
        ]);
    }

    /**
     * Call endpoint to validate widget response.
     */
    protected function sendRequest(string|null $responseField = null): \Psr\Http\Message\ResponseInterface
    {
        $request = Craft::$app->getRequest();
        $turnstileResponse = $responseField ?: $request->getParam('cf-turnstile-response');

        $settings = Turnstile::getInstance()->getSettings();

        return $this->getClient()->post('siteverify', [
            'form_params' => [
                'secret' => $settings->getSecretKey(),
                'remoteip' => $request->getUserIP(),
                'response' => $turnstileResponse,
            ],
        ]);
    }

    /**
     * Verify if request is valid.
     */
    public function verify(string|null $responseField = null): bool
    {
        try {
            $response = $this->sendRequest($responseField);

            if ($response->getStatusCode() === 200) {
                $body = Json::decodeIfJson($response->getBody());

                if ($body['success'] ?? false) {
                    return true;
                }

                Craft::error(Json::encode($body), __METHOD__);

                return false;
            }
        } catch (ConnectException $e) {
            if (Craft::$app->config->general->devMode) {
                throw $e;
            }

            Craft::error($e->getMessage(), __METHOD__);
        }

        return false;
    }

    /**
     * Check if verification passes.
     */
    public function passes(...$args): bool
    {
        return $this->verify(...$args);
    }

    /**
     * Check if verification fails.
     */
    public function fails(...$args): bool
    {
        return $this->passes(...$args) === false;
    }
}
