<?php
require_once __DIR__ . '/Database.php';

/**
 * Валидация данных книги.
 * @param array $data Данные из формы
 * @return array Массив ошибок
 */
function validateBookData(array $data): array {
    $errors = [];

    // Автор
    if (empty(trim($data['author_name'] ?? ''))) {
        $errors[] = 'Имя автора обязательно.';
    } elseif (preg_match('/\d/', $data['author_name'])) {
        $errors[] = 'Имя автора не должно содержать цифр.';
    }

    // Название
    if (empty(trim($data['title'] ?? ''))) {
        $errors[] = 'Название книги обязательно.';
    } elseif (preg_match('/\d/', $data['title'])) {
        $errors[] = 'Название не должно содержать цифр.';
    }

    // ISBN (опционально, но если есть - проверяем формат)
    $isbn = trim($data['isbn'] ?? '');
    if (!empty($isbn) && !preg_match('/^(97(8|9))?\d{9}(\d|X)?$/i', $isbn)) {
        $errors[] = 'Неверный формат ISBN.';
    }

    // Дата
    $date = $data['publication_date'] ?? '';
    if (!empty($date) && !DateTime::createFromFormat('Y-m-d', $date)) {
        $errors[] = 'Некорректная дата публикации.';
    }

    // Описание
    $desc = trim($data['description'] ?? '');
    if (strlen($desc) < 50) {
        $errors[] = 'Описание должно быть не менее 50 символов.';
    }

    // Цена
    $price = $data['price'] ?? '';
    if ($price === '' || !is_numeric($price) || (float)$price < 0) {
        $errors[] = 'Укажите корректную цену.';
    }

    return $errors;
}

/**
 * Вспомогательная: Найти или создать автора.
 */
function getOrCreateAuthor(PDO $pdo, string $name): int {
    $name = trim($name);
    
    $stmt = $pdo->prepare("SELECT id FROM authors WHERE name = :name LIMIT 1");
    $stmt->execute([':name' => $name]);
    $author = $stmt->fetch();

    if ($author) {
        return (int)$author['id'];
    }

    $stmt = $pdo->prepare("INSERT INTO authors (name) VALUES (:name) RETURNING id");
    $stmt->execute([':name' => $name]);
    
    return (int)$stmt->fetchColumn();
}

/**
 * CREATE: Добавление новой записи.
 */
function createRecord(array $data): bool {
    $pdo = Database::getConnection();
    try {
        $pdo->beginTransaction();
        $authorId = getOrCreateAuthor($pdo, $data['author_name']);

        $sql = "INSERT INTO books (author_id, title, isbn, publication_date, description, price, is_featured) 
                VALUES (:aid, :title, :isbn, :pdate, :desc, :price, :feat)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':aid'   => $authorId,
            ':title' => $data['title'],
            ':isbn'  => $data['isbn'] ?? null,
            ':pdate' => $data['publication_date'] ?? null,
            ':desc'  => $data['description'],
            ':price' => $data['price'],
            ':feat'  => isset($data['is_featured']) ? 1 : 0
        ]);

        $pdo->commit();
        return true;
    } catch (\Exception $e) {
        $pdo->rollBack();
        error_log("Create Error: " . $e->getMessage());
        return false;
    }
}

/**
 * READ ALL: Получение всех записей с сортировкой.
 */
function getAllRecords(string $sortField = 'created_at', string $sortOrder = 'desc'): array {
    $pdo = Database::getConnection();
    
    $allowed = ['title', 'price', 'publication_date', 'created_at', 'author_name'];
    if (!in_array($sortField, $allowed)) $sortField = 'created_at';
    $sortOrder = ($sortOrder === 'asc') ? 'ASC' : 'DESC';

    // JOIN для получения имени автора
    $sql = "SELECT b.*, a.name as author_name 
            FROM books b 
            JOIN authors a ON b.author_id = a.id 
            ORDER BY $sortField $sortOrder";
            
    return $pdo->query($sql)->fetchAll();
}

/**
 * READ ONE: Получение одной записи по ID.
 */
function getRecordById(int $id) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

/**
 * UPDATE: Обновление существующей записи.
 */
function updateRecord(int $id, array $data): bool {
    $pdo = Database::getConnection();
    try {
        $pdo->beginTransaction();
        $authorId = getOrCreateAuthor($pdo, $data['author_name']);

        $sql = "UPDATE books SET 
                author_id = :aid, title = :title, isbn = :isbn, 
                publication_date = :pdate, description = :desc, 
                price = :price, is_featured = :feat 
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id'    => $id,
            ':aid'   => $authorId,
            ':title' => $data['title'],
            ':isbn'  => $data['isbn'] ?? null,
            ':pdate' => $data['publication_date'] ?? null,
            ':desc'  => $data['description'],
            ':price' => $data['price'],
            ':feat'  => isset($data['is_featured']) ? 1 : 0
        ]);

        $pdo->commit();
        return true;
    } catch (\Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

/**
 * DELETE: Удаление записи.
 */
function deleteRecord(int $id): bool {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = :id"); 
    return $stmt->execute([':id' => $id]);
}