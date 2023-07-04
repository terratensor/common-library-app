<?php

declare(strict_types=1);

namespace src\models;

use yii\base\Model;

class Paragraph extends Model
{
    public $book_id;
    public $book_name;
    public $text;
    public $position;
    public $length;
    public $highlight;
    private $id;

    public static function create(
        string $book_id,
        string $book_name,
        string $text,
        string $position,
        string $length,
        ?string $highlight,
    ): self {
        $paragraph = new static();

        $paragraph->book_id = $book_id;
        $paragraph->book_name = $book_name;
        $paragraph->text = $text;
        $paragraph->position = $position;
        $paragraph->length = $length;
        $paragraph->highlight = $highlight;

        return $paragraph;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }
}
