<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use app\widgets\ShortLinkModal;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100" data-bs-theme="dark">
<head>
    <?= $this->render('favicon'); ?>
    <title><?= Html::encode($this->title) ?></title>
    <script src="/js/color-mode-toggler.js"></script>
    <?php $this->head() ?>
    <?= $this->render('yandex_metrika'); ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>
<?= $this->render('red_header'); ?>

<main role="main" class="flex-shrink-0 mb-3">
  <div class="container-fluid pb-0">
  <?= Alert::widget() ?>
    <div class="d-flex justify-content-end align-items-baseline svodd-breadcrumb-deprecated">
        <?php Breadcrumbs::widget(
            [
                'links' => $this->params['breadcrumbs'] ?? [],
            ]
        ) ?>
        <?php if (Yii::$app->params['shortLinkEnable']): ?>
        <?= ShortLinkModal::widget(); ?>
        <?php endif; ?>
    </div>
     
  </div>

    <?= $content ?>

</main>

<?= $this->render('footer'); ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
