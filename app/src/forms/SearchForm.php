<?php

declare(strict_types=1);

namespace src\forms;

use yii\base\Model;

class SearchForm extends Model
{
    public string $query = '';
    public string $matching = 'query_string';

    public function rules(): array
    {
        return [
            ['query', 'string'],
            ['matching', 'in', 'range' => array_keys($this->getMatching())],
        ];
    }

    public function getMatching(): array
    {
        return [
            'query_string' => 'По умолчанию',
            'match_phrase' => 'По соответствию фразе',
            'match' => 'По совпадению слов',
        ];
    }

    public function formName(): string
    {
        return 'search';
    }
}
