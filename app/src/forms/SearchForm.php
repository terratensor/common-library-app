<?php

declare(strict_types=1);

namespace src\forms;

use yii\base\Model;

class SearchForm extends Model
{
    public string $query = '';
    public string $matching = 'query_string';

    public bool $fuzzy = false;

    public function rules(): array
    {
        return [
            ['query', 'string'],
            ['matching', 'in', 'range' => array_keys($this->getMatching())],
            [['fuzzy'], 'boolean']
        ];
    }

    public function getMatching(): array
    {
        return [
            'query_string' => 'Обычный поиск',
            'match_phrase' => 'Точное соответствие',
            'match' => 'Любое слово',
        ];
    }

    public function formName(): string
    {
        return 'search';
    }
}
