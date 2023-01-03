<?php

namespace billmn\turnstile\services;

use billmn\turnstile\Turnstile;
use Craft;
use craft\helpers\Json;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\ResponseInterface;
use yii\base\Component;

/**
 * Validator service
 */
class Validator extends Component
{
    /**
     * Get HTTP Client.
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return new Client([
            'base_uri' => 'https://challenges.cloudflare.com/turnstile/v0/',
        ]);
    }

    /**
     * Call endpoint to validate widget response.
     *
     * @return ReponseInterface
     */
    protected function sendRequest(): ResponseInterface
    {
        $request = Craft::$app->getRequest();
        $turnstileResponse = $request->getParam('cf-turnstile-response');

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
     * Verifiy if request is valid.
     *
     * @return boolean|ConnectException
     */
    public function verify(): bool|ConnectException
    {
        try {
            $response = $this->sendRequest();

            if ($response->getStatusCode() === 200) {
                $body = Json::decodeIfJson($response->getBody());

                if ($body['success'] ?? false) {
                    return true;
                }

                Craft::error(Json::encode($body), __METHOD__);

                return false;
            }
        } catch (ConnectException $e) {
            Craft::error($e->getMessage(), __METHOD__);

            if (Craft::$app->config->general->devMode) {
                throw $e;
            }
        }

        return false;
    }
}
