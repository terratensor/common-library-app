<?php

declare(strict_types=1);

namespace src\repositories;

use Manticoresearch\Client;
use Manticoresearch\Index;
use Manticoresearch\Query\BoolQuery;
use Manticoresearch\Query\Equals;
use Manticoresearch\Query\In;
use Manticoresearch\Query\MatchPhrase;
use Manticoresearch\Query\MatchQuery;
use Manticoresearch\Query\QueryString;
use Manticoresearch\Search;
use src\forms\SearchForm;
use src\helpers\SearchHelper;
use src\models\Paragraph;

class ParagraphRepository
{
    private Client $client;
    public Index $index;
    private Search $search;

    private string $indexName = 'vpsssr_library';
    public int $pageSize = 20;

    public function __construct(Client $client, $pageSize)
    {
        $this->client = $client;
        $this->setIndexName(\Yii::$app->params['indexes']['common']);
        $this->setIndex($this->client->index($this->indexName));
        $this->search = new Search($this->client);
        $this->pageSize = $pageSize;
    }

    /**
     * @param string $queryString
     * @param string|null $indexName
     * @param SearchForm|null $form
     * @return Search
     * "query_string" accepts an input string as a full-text query in MATCH() syntax
     */
    public function findByQueryStringNew(
        string $queryString,
        ?string $indexName = null,
        ?SearchForm $form = null
    ): Search {
        $this->search->reset();
        if ($indexName) {
            $this->setIndex($this->client->index($indexName));
        }

        $queryString = SearchHelper::escapingCharacters($queryString);

        // Запрос переделан под фильтр
        $query = new BoolQuery();

        if ($form->query) {
            $query->must(new QueryString($queryString));
        }

        // Выполняем поиск если установлен фильтр или установлен строка поиска
        if ($form->query) {
            $search = $this->index->search($query);
//            $search->facet('book_id');
//            var_dump($search->facet('book_id'));
        } else {
            throw new \DomainException('Задан пустой поисковый запрос');
        }

        // Включаем нечёткий поиск, если строка не пустая или не содержит символы, используемые в полнотекстовом поиске
        // и не сдержит hash автварки пользователя
        if ($form->fuzzy && $this->validateQueryString($queryString)) {
            \Yii::$app->session->setFlash('success', "Включена опция «Нечёткий поиск». Для выключения уберите флажок в настройках поиска.");
            static::applyFuzzy($search, true);
        }
        // Если нет совпадений no_match_size возвращает пустое поле для подсветки
        $search->highlight(
            ['text'],
            [
                'limit' => 0,
                'no_match_size' => 0,
                'pre_tags' => '<mark>',
                'post_tags' => '</mark>'
            ],
        );
        return $search;
    }

    /**
     * @param string $queryString
     * @param string|null $indexName
     * @param SearchForm|null $form
     * @return Search
     * "match" is a simple query that matches the specified keywords in the specified fields.
     */
    public function findByQueryStringMatch(
        string $queryString,
        ?string $indexName = null,
        ?SearchForm $form = null
    ): Search {
        $this->search->reset();
        if ($indexName) {
            $this->setIndex($this->client->index($indexName));
        }

        // Запрос переделан под фильтр
        $query = new BoolQuery();

        if ($form->query) {
            $query->must(new MatchQuery($queryString, 'text'));
        }

        // Выполняем поиск если установлен фильтр или установлен строка поиска
        if ($form->query) {
            $search = $this->index->search($query);
        } else {
            throw new \DomainException('Задан пустой поисковый запрос');
        }

        $search->highlight(
            ['text'],
            [
                'limit' => 0,
                'no_match_size' => 0,
                'pre_tags' => '<mark>',
                'post_tags' => '</mark>'
            ]
        );

        return $search;
    }

    /**
     * @param string $queryString
     * @param string|null $indexName
     * @return Search
     * "match_phrase" is a query that matches the entire phrase. It is similar to a phrase operator in SQL.
     */
    public function findByMatchPhrase(
        string $queryString,
        ?string $indexName = null,
        ?SearchForm $form = null
    ): Search
    {
        $this->search->reset();
        if ($indexName) {
            $this->setIndex($this->client->index($indexName));
        }

        // Запрос переделан под фильтр
        $query = new BoolQuery();

        if ($form->query) {
            $query->must(new MatchPhrase($queryString, 'text'));
        }


        // Выполняем поиск если установлен фильтр или установлен строка поиска
        if ($form->query) {
            $search = $this->index->search($query);
        } else {
            throw new \DomainException('Задан пустой поисковый запрос');
        }

        $search->highlight(
            ['text'],
            [
                'limit' => 0,
                'no_match_size' => 0,
                'pre_tags' => '<mark>',
                'post_tags' => '</mark>'
            ]
        );

        return $search;
    }

    /**
     * @param $queryString String Число или строка чисел через запятую
     * @param string|null $indexName
     * @return Search
     * Поиск по data_id, вопрос или комментарий, число или массив data_id
     */
    public function findByParagraphId(
        string $queryString,
        ?string $indexName = null,
        ?SearchForm $form = null
    ): Search
    {
        $this->search->reset();
        if ($indexName) {
            $this->setIndex($this->client->index($indexName));
        }

        $result = explode(',', $queryString);

        foreach ($result as $key => $item) {
            $item = (int)$item;
            if ($item == 0) {
                unset($result[$key]);
                continue;
            }
            $result[$key] = $item;
        }
        // Запрос переделан под фильтр
        $query = new BoolQuery();

        if (!empty($result)) {
            $query->must(new In('id', array_values($result)));
        } else {
            throw new \DomainException('Неправильный запрос, при поиске по номеру(ам) надо указать номер параграфа, или перечислить номера через запятую');
        }

        // Выполняем поиск если установлен фильтр или установлен строка поиска
        if ($form->query) {
            $search = $this->index->search($query);
        } else {
            throw new \DomainException('Задан пустой поисковый запрос');
        }

        $search->highlight(
            ['text'],
            [
                'limit' => 0,
                'no_match_size' => 0,
                'pre_tags' => '<mark>',
                'post_tags' => '</mark>'
            ]
        );
        $search->sort('id', 'asc');

        return $search;
    }

    /**
     * @param Index $index
     */
    public function setIndex(Index $index): void
    {
        $this->index = $index;
    }

    public function findBookById(int $id): \Manticoresearch\ResultSet
    {
        $this->search->reset();

        $search = $this->search->setIndex($this->indexName);

        $search->search('');
        $search->filter('book_id', $id);
        $search->limit(1);

        return $search->get();
    }

    public function findParagraphsByBookId(int $id): Search
    {
        $this->search->reset();

        $search = $this->search->setIndex($this->indexName);


        $search->filter('book_id', 'in', $id);

        // Запрос переделан под фильтр
        $query = new BoolQuery();


        $query->must(new In('id', $id));
        $this->index->search($query);

        $search->highlight(
            ['text'],
            [
                'limit' => 0,
                'no_match_size' => 0,
                'pre_tags' => '<mark>',
                'post_tags' => '</mark>'
            ]
        );
        return $search;
    }

    /**
     * @param string $indexName
     */
    public function setIndexName(string $indexName): void
    {
        $this->indexName = $indexName;
    }

    /**
     * Возвращает paragraph по uuid
     * @param string $uuid
     * @return Paragraph
     */
    public function findByParagraphUuid(string $uuid): Paragraph
    {

        $this->search->reset();

        $query = new BoolQuery();

        $query->must(new Equals('uuid', $uuid));

        $search = $this->index->search($query);

        $current = $search->get()->current();
        $paragraph = new Paragraph($current->getData());
        $paragraph->setId((int)$current->getId());
        return $paragraph;
    }

    /**
     * @param Search $search
     * @param bool $enable_layouts
     * @return void
     */
    protected static function applyFuzzy(Search $search, bool $enable_layouts = false): void
    {
        $search->option('fuzzy', 1);
        $layouts = [];
        if ($enable_layouts) {
            $layouts = ['ru', 'us'];
        }
        $search->option('layouts', $layouts);
        $search->option('distance', 2);
    }

    
    private function validateQueryString($queryString): bool
    {
        // Если строка пустая или содержит символы, используемые в полнотекстовом поиске, то возвращаем false
        if (empty($queryString) || SearchHelper::containsSpecialChars($queryString)) {
            return false;
        }

        // В противном случае возвращаем true
        return true;
    }
}
