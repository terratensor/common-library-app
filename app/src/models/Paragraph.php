<?php

declare(strict_types=1);

namespace src\models;

use yii\base\Model;

class Paragraph extends Model
{
    public string $uuid;
    public string $book_id;
    public string $book_name;
    public string $text;
    public string $position;
    public string $length;
    public array $highlight;
    private int $id;

    public static function create(
        string $uuid,
        string $book_id,
        string $book_name,
        string $text,
        string $position,
        string $length,
        array $highlight,
    ): self {
        $paragraph = new static();

        $paragraph->uuid = $uuid;
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

    public function getId(): int
    {
        return $this->id;
    }
}
