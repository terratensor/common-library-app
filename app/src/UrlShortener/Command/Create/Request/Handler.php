<?php

declare(strict_types=1);

namespace src\UrlShortener\Command\Create\Request;

use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\Exception;

class Handler
{
    private Client $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function handle(Command $command): \yii\httpclient\Response|string
    {
        $origin = $command->origin;

        $host = \Yii::$app->params['urlShortenerHost'];

        $response = $this->client
            ->createRequest()
            ->setFormat(Client::FORMAT_JSON)
            ->setMethod('post')
            ->setUrl("$host/create")
            ->setData(['origin' => $origin])
            ->send();

        if ($response->isOk) {
            return $response->content;
        }

        return "";
    }
}
