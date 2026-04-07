<?php
/**
 * Класс BookValidator отвечает за серверную валидацию данных формы книги.
 */
class BookValidator {
    /**
     * Выполняет проверку переданных данных и возвращает массив ошибок.
     *
     * @param array<string, mixed> $data Данные из $_POST
     * @return array<string> Массив строк с сообщениями об ошибках. Пустой массив при успешной проверке.
     */
    public function validate(array $data): array {
        $errors = [];

        
        $title = trim($data['title'] ?? '');
        if (empty($title)) {
            $errors[] = 'Поле "Название" обязательно для заполнения.';
        } else {
            if (strlen($title) < 2) {
                $errors[] = 'Название должно содержать минимум 2 символа.';
            }
            
            if (preg_match('/\d/', $title)) {
                $errors[] = 'Название книги не должно содержать цифр.';
            }
        }

        
        $author = trim($data['author'] ?? '');
        if (empty($author)) {
            $errors[] = 'Поле "Автор" обязательно для заполнения.';
        } else {
            if (strlen($author) < 2) {
                $errors[] = 'Имя автора должно содержать минимум 2 символа.';
            }
            
            if (preg_match('/\d/', $author)) {
                $errors[] = 'Имя автора не должно содержать цифр.';
            }
        }

        
        $isbn = trim($data['isbn'] ?? '');
        if (!empty($isbn) && !preg_match('/^(97(8|9))?\d{9}(\d|X)?$/i', $isbn)) {
            $errors[] = 'Неверный формат ISBN. Допустимо 10 или 13 цифр.';
        }

        
        $date = $data['publication_date'] ?? '';
        if (empty($date)) {
            $errors[] = 'Укажите дату публикации.';
        } elseif (!DateTime::createFromFormat('Y-m-d', $date)) {
            $errors[] = 'Некорректный формат даты. Используйте ГГГГ-ММ-ДД.';
        }

        
        $description = trim($data['description'] ?? '');
        if (empty($description)) {
            $errors[] = 'Поле "Описание" обязательно для заполнения.';
        } else {
            if (strlen($description) < 50) {
                $errors[] = 'Описание должно содержать минимум 50 символов.';
            }
            
            
            $words = preg_split('/\s+/', $description);
            $validWords = array_filter($words, function($word) {
                return strlen($word) >= 2 && preg_match('/[а-яА-Яa-zA-Z]/u', $word);
            });
            if (count($validWords) < 5) {
                $errors[] = 'Описание должно содержать хотя бы 5 осмысленных слов.';
            }
        }

        
        $price = $data['price'] ?? '';
        if ($price === '' || !is_numeric($price)) {
            $errors[] = 'Укажите корректную цену (число).';
        } elseif ((float)$price < 0) {
            $errors[] = 'Цена не может быть отрицательной.';
        }

        return $errors;
    }
}