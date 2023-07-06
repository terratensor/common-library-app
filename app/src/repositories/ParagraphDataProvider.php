<?php

declare(strict_types=1);

namespace src\repositories;


use Manticoresearch\Search;
use src\models\Paragraph;
use yii\data\BaseDataProvider;

class ParagraphDataProvider extends BaseDataProvider
{
    /**
     * @var string|callable Имя столбца с ключом или callback-функция, возвращающие его
     */
    public $key;
    /**
     * @var Search
     */
    public Search $query;

    protected function prepareModels(): array
    {
        $models = [];
        $pagination = $this->getPagination();
        $sort = $this->getSort();

        foreach ($sort->getOrders() as $attribute => $value) {
            $direction = $value === SORT_ASC ? 'asc' : 'desc';
            $this->query->sort($attribute, $direction);
        }

        if ($pagination === false) {
            // в случае отсутствия разбивки на страницы - прочитать все строки
            foreach ($this->query->get() as $hit) {
                $models[] = new Paragraph($hit->getData());
            }
        } else {
            // в случае, если разбивка на страницы есть - прочитать только одну страницу
            $pagination->totalCount = $this->getTotalCount();

            $this->query->limit($pagination->pageSize);
            $this->query->offset($pagination->getOffset());

            $limit = $pagination->getLimit();

            $data = $this->query->get();

            // Если количество записей меньше чем лимит,
            // то переписываем лимит, чтобы избежать ошибки Undefined array key при вызове $data->current()
            if ($data->count() < $limit) {
                $limit = $data->count();
            }

            for ($count = 0; $count < $limit; ++$count) {
                $model = new Paragraph( $data->current()->getData());
                $model->setId((int)$data->current()->getId());
                try {
                    $model->highlight = $data->current()->getHighlight();
                } catch (\Exception $e) {
                    $model->highlight = [];
                }
                $models[] = $model;
                $data->next();
            }
        }

        return $models;
    }

    protected function prepareKeys($models): array
    {
        if ($this->key !== null) {
            $keys = [];

            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }

            return $keys;
        } else {
            return array_keys($models);
        }
    }

    protected function prepareTotalCount()
    {
        $max_matches = (int)\Yii::$app->params['manticore']['max_matches'];
        $count = $this->query->get()->getTotal();
        /**
         * https://manual.manticoresearch.com/Searching/Options#max_matches
         * Если ограничение max_matches установлено, то устанавливаем счетчик кол-во совпадений равный параметру max_matches
         * Иначе устанавливаем значение запроса max_matches равное кол-во существующих результатов, т.е. выдача без ограничения
         */
        if ($max_matches !== 0 && $count > $max_matches) {
            $count = $max_matches;
        }
        $this->query->maxMatches($count);
        return $count;
    }
}
