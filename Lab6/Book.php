<?php
/**
 * Класс Book представляет модель данных для книги.
 */
class Book {
    public string $title;
    public string $author;
    public string $isbn;
    public string $publication_date;
    public string $description;
    public float $price;
    public bool $is_featured;
    public string $created_at;

    /**
     * Конструктор класса Book. Инициализирует свойства на основе переданного массива данных.
     *
     * @param array<string, mixed> $data Массив данных из формы
     */
    public function __construct(array $data) {
        $this->title = trim($data['title'] ?? '');
        $this->author = trim($data['author'] ?? '');
        $this->isbn = trim($data['isbn'] ?? '');
        $this->publication_date = $data['publication_date'] ?? '';
        $this->description = trim($data['description'] ?? '');
        $this->price = isset($data['price']) ? (float)$data['price'] : 0.0;
        $this->is_featured = isset($data['is_featured']);
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
    }

    /**
     * Преобразует объект книги в ассоциативный массив для сериализации.
     *
     * @return array<string, mixed> Массив с данными книги
     */
    public function toArray(): array {
        return [
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'publication_date' => $this->publication_date,
            'description' => $this->description,
            'price' => $this->price,
            'is_featured' => $this->is_featured,
            'created_at' => $this->created_at
        ];
    }
}