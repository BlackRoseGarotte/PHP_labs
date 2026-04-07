<?php
/**
 * Класс BookRepository отвечает за чтение и сохранение данных книг в JSON-файл.
 */
class BookRepository {
    private string $filePath;

    /**
     * Конструктор репозитория. Инициализирует путь к файлу и создает его при отсутствии.
     *
     * @param string $filePath Абсолютный путь к файлу хранения данных
     */
    public function __construct(string $filePath) {
        $this->filePath = $filePath;
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode([]));
        }
    }

    /**
     * Сохраняет массив данных новой книги в конец файла.
     *
     * @param array<string, mixed> $bookData Массив данных книги
     * @return bool Успешность операции записи
     */
    public function save(array $bookData): bool {
        $books = $this->getAll();
        $books[] = $bookData;
        $json = json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($this->filePath, $json, LOCK_EX) !== false;
    }

    /**
     * Возвращает все сохраненные книги из файла.
     *
     * @return array<int, array<string, mixed>> Массив книг
     */
    public function getAll(): array {
        $content = file_get_contents($this->filePath);
        return $content !== false ? json_decode($content, true) : [];
    }
}