<?php

declare(strict_types=1);

namespace src\UrlShortener\Http\Action\V1\UrlShortener;

use src\UrlShortener\Command\Create\Request\Command;
use src\UrlShortener\Command\Create\Request\Handler;
use src\UrlShortener\Form\CreateLink\CreateForm;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\httpclient\Exception;

class ShortLinkAction extends Action
{
    private Handler $handler;

    public function __construct($id, $controller, Handler $handler, $config = [])
    {
        parent::__construct($id, $controller, $config);
        $this->handler = $handler;
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function run(): array|string|\yii\httpclient\Response
    {
        $form = new CreateForm();

        try {
            if ($form->load(Yii::$app->request->post()) && $form->validate()) {
                $command = new Command();
                $command->origin = $form->origin;
                return $this->handler->handle($command);
            }
        } catch (\DomainException $e) {
            Yii::$app->errorHandler->logException($e);
        }

        return [];
    }
}
