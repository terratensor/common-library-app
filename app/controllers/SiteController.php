<?php

namespace app\controllers;


use src\forms\SearchForm;
use src\Search\Http\Action\V1\SearchSettings\ToggleAction;
use src\services\EmptySearchRequestExceptions;
use src\services\ManticoreService;
use src\services\NeighboringService;
use src\UrlShortener\Http\Action\V1\UrlShortener\ShortLinkAction;
use Yii;
use yii\web\Controller;
use yii\widgets\ActiveForm;

class SiteController extends Controller
{
    private ManticoreService $service;
    private NeighboringService $neighboringService;

    public function __construct(
        $id,
        $module,
        ManticoreService $service,
        NeighboringService $neighboringService,
        $config = []
    )
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
        $this->neighboringService = $neighboringService;
    }

    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'search-settings' => [
                'class' => ToggleAction::class,
            ],
            'short-link' => [
                'class' => ShortLinkAction::Class,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $results = null;
        $form = new SearchForm();
        $errorQueryMessage = '';

        try {
            if ($form->load(Yii::$app->request->queryParams) && $form->validate()) {
                $results = $this->service->search($form);
            }
        } catch (\DomainException $e) {
            Yii::$app->errorHandler->logException($e);
            Yii::$app->session->setFlash('error', $e->getMessage());
        } catch (EmptySearchRequestExceptions $e) {
            $errorQueryMessage = $e->getMessage();
        }

        return $this->render('index', [
            'results' => $results ?? null,
            'model' => $form,
            'errorQueryMessage' => $errorQueryMessage,
        ]);
    }

    public function actionNeighboring($id, $num): \yii\web\Response
    {
        $form = $this->neighboringService->handle($id, $num);
        return $this->redirect(['site/index', 'search' => $form->toArray()]);
    }
}
