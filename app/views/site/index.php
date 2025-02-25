<?php

use app\widgets\ScrollWidget;
use app\widgets\SearchResultsSummary;
use src\forms\SearchForm;
use src\models\Paragraph;
use src\repositories\ParagraphDataProvider;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;
use yii\data\Pagination;

/** @var yii\web\View $this */
/** @var ParagraphDataProvider $results */
/** @var Pagination $pages */
/** @var SearchForm $model */
/** @var string $errorQueryMessage */

$this->title = Yii::$app->name;
// $this->params['breadcrumbs'][] = Yii::$app->name;

$this->params['meta_description'] = 'Цитаты из 11 тысяч томов преимущественно русскоязычных авторов, в которых широко раскрыты большинство исторических событий — это документальная, научная, историческая литература, а также воспоминания, мемуары, дневники и письма, издававшиеся в форме собраний сочинений и художественной литературы';

if ($results) {
    $this->registerMetaTag(['name' => 'robots', 'content' => 'noindex, nofollow']);
} else {
    $this->registerLinkTag(['rel' => 'canonical', 'href' => Yii::$app->params['frontendHostInfo']]);
    $this->registerMetaTag(['name' => 'robots', 'content' => 'index, nofollow']);
}

/** Quote form block  */

echo Html::beginForm(['/site/quote'], 'post', ['name' => 'QuoteForm',  'target' => "print_blank" ]);
echo Html::hiddenInput('uuid', '', ['id' => 'quote-form-uuid']);
echo Html::endForm();

/** Search settings form block */
echo Html::beginForm(['/site/search-settings'], 'post', ['name' => 'searchSettingsForm', 'class' => 'd-flex']);
echo Html::hiddenInput('value', 'toggle');
echo Html::endForm();
$searchIcon = '<svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path></svg>';
$inputTemplate = '<div class="input-group mb-2">
          {input}
          <button class="btn btn-primary" type="submit" id="button-search">' . $searchIcon . '</button>
          <button class="btn btn-outline-secondary ' .
    (Yii::$app->session->get('show_search_settings') ? 'active' : "") . '" id="button-search-settings">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3h9.05zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8h2.05zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1h9.05z"/>
            </svg>
          </button>
          </div>';

?>
  <div class="site-index">
    <div class="search-block">
      <div class="container-fluid">

          <?php $form = ActiveForm::begin(
              [
                  'method' => 'GET',
                  'action' => ['site/index'],
                  'options' => ['class' => 'pb-1 mb-2 pt-3', 'autocomplete' => 'off'],
              ]
          ); ?>
        <div class="d-flex align-items-center">
            <?= $form->field($model, 'query', [
                'inputTemplate' => $inputTemplate,
                'options' => [
                    'class' => 'w-100', 'role' => 'search'
                ]
            ])->textInput(
                [
                    'type' => 'search',
                    'class' => 'form-control form-control-lg',
                    'placeholder' => "Поиск",
                    'autocomplete' => 'off',
                ]
            )->label(false); ?>
        </div>
        <div id="search-setting-panel"
             class="search-setting-panel <?= Yii::$app->session->get('show_search_settings') ? 'show-search-settings' : '' ?>">
        <div>
            <?= $form->field($model, 'matching', ['inline' => true, 'options' => ['class' => 'pb-2']])
                ->radioList($model->getMatching(), ['class' => 'form-check-inline'])
                ->label(false); ?>
                </div>                  
        <div>
            <?= $form->field($model, 'fuzzy', ['options' => ['class' => 'pb-2']])
              ->checkbox()
              ->label('Нечёткий поиск'); ?>
         </div>
        </div>
          <?php ActiveForm::end(); ?>
      </div>
    </div>
    <div class="container-fluid search-results">
        <?php if (!$results): ?>
            <?php if ($errorQueryMessage): ?>
            <div class="card border-danger mb-3">
              <div class="card-body"><?= $errorQueryMessage; ?></div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($results): ?>
        <?php
        // Property totalCount пусто пока не вызваны данные модели getModels(),
        // сначала получаем массив моделей, потом получаем общее их количество
        /** @var Paragraph[] $paragraphs */
        $paragraphs = $results->getModels();
        $pagination = new Pagination(
            [
                'totalCount' => $results->getTotalCount(),
                'defaultPageSize' => Yii::$app->params['searchResults']['pageSize'],
            ]
        );
        ?>
      <div class="row">
        <div class="col-md-12">
            <?php if ($pagination->totalCount === 0): ?>
              <h5>По вашему запросу ничего не найдено</h5>
            <?php else: ?>
              <div class="row">
                <div class="col-md-8 d-flex align-items-center">
                    <?= SearchResultsSummary::widget(['pagination' => $pagination]); ?>
                </div>
              </div>

              <div class="card pt-3">
                <div class="card-body">
                    <?php foreach ($paragraphs as $paragraph): ?>
                      <div class="px-xl-5 px-lg-5 px-md-5 px-sm-3 paragraph" data-entity-id="<?= $paragraph->uuid; ?>">
                        <div class="paragraph-header">
                          <div class="d-flex justify-content-between">
                            <div>

                            </div>
                            <div class="paragraph-context">
                                <?= Html::button('контекст', [
                                        'class' => 'btn btn-link btn-context paragraph-context',
                                        'data-uuid' => $paragraph->uuid,
                                ]); ?>
                            </div>
                          </div>
                        </div>
                        <div>
                          <div class="paragraph-text">
                              <?php if (!$paragraph->highlight['text'] || !$paragraph->highlight['text'][0]): ?>
                                  <?php Yii::$app->formatter->asRaw(htmlspecialchars($paragraph->text)); ?>
                                  <?php echo \yii\helpers\HtmlPurifier::process(htmlspecialchars($paragraph->text), function ($config) {
                                      /** @var $config HTMLPurifier_Config */
                                      $config->getHTMLDefinition(true)
                                          ->addElement('mark', 'Block', 'Flow', 'Common');
                                  }); ?>
                              <?php else: ?>
                                  <?php echo \yii\helpers\HtmlPurifier::process($paragraph->highlight['text'][0], function ($config) {
                                      /** @var $config HTMLPurifier_Config */
                                      $config->getHTMLDefinition(true)
                                          ->addElement('mark', 'Block', 'Flow', 'Common');
                                  }); ?>
                              <?php endif; ?>
                          </div>
                        </div>
                        <div class="d-flex justify-content-start book-name">
                          <div><strong><i><?=$paragraph->book_name; ?></i></strong></div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                </div>
              </div>

            <?php endif; ?>

          <div class="container container-pagination">
            <div class="detachable fixed-bottom">
                <?php echo LinkPager::widget(
                    [
                        'pagination' => $pagination,
                        'firstPageLabel' => true,
                        'lastPageLabel' => false,
                        'maxButtonCount' => 3,
                        'options' => [
                            'class' => 'd-flex justify-content-center'
                        ],
                        'listOptions' => ['class' => 'pagination mb-0']
                    ]
                ); ?>
            </div>
          </div>

        </div>
      </div>
    </div>
      <?= ScrollWidget::widget(['data_entity_id' => isset($paragraph) ? $paragraph->uuid : 0]); ?>
      <?php else: ?>
<!--        <div class="card welcome-card">-->
<!--          <div class="card-body">-->
<!--          </div>-->
<!--        </div>-->
      <?php endif; ?>
  </div>
<?php $js = <<<JS
  let menu = $(".search-block");
var menuOffsetTop = menu.offset().top;
var menuHeight = menu.outerHeight();
var menuParent = menu.parent();
var menuParentPaddingTop = parseFloat(menuParent.css("padding-top"));
 
checkWidth();
 
function checkWidth() {
    if (menu.length !== 0) {
      $(window).scroll(onScroll);
    }
}
 
function onScroll() {
  if ($(window).scrollTop() > menuOffsetTop) {
    menu.addClass("shadow");
    menuParent.css({ "padding-top": menuParentPaddingTop });
  } else {
    menu.removeClass("shadow");
    menuParent.css({ "padding-top": menuParentPaddingTop });
  }
}

const btn = document.getElementById('button-search-settings');
btn.addEventListener('click', toggleSearchSettings, false)

function toggleSearchSettings(event) {
  event.preventDefault();
  btn.classList.toggle('active')
  document.getElementById('search-setting-panel').classList.toggle('show-search-settings')
  
  const formData = new FormData(document.forms.searchSettingsForm);
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "/site/search-settings");
  xhr.send(formData);
}
// Обработчик ссылок контекста
const contextButtons = document.querySelectorAll('button.btn-context')
contextButtons.forEach(function (element) {
  element.addEventListener('click', btnContextHandler, false)
})

function btnContextHandler(event) {
  const quoteForm = document.forms["QuoteForm"]
  const uuid = document.getElementById("quote-form-uuid")
  uuid.value = event.target.dataset.uuid
  quoteForm.submit();
}


$('input[type=radio]').on('change', function() {
    $(this).closest("form").submit();
});

JS;

$this->registerJs($js);
