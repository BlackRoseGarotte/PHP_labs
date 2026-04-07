<?php
declare(strict_types=1);

require_once 'Book.php';
require_once 'BookValidator.php';
require_once 'BookRepository.php';

$validator = new BookValidator();
$repository = new BookRepository(__DIR__ . '/data/books.json');

$errors = [];
$success = false;
$books = $repository->getAll();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = $validator->validate($_POST);

    if (empty($errors)) {
        $book = new Book($_POST);
        $repository->save($book->toArray());
        
        header('Location: index.php?status=success');
        exit;
    }
}


if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $success = true;
    $books = $repository->getAll();
}

$sortField = $_GET['sort'] ?? 'created_at';
$sortOrder = $_GET['order'] ?? 'desc';
$allowedSorts = ['title', 'author', 'publication_date', 'price', 'created_at'];

if (in_array($sortField, $allowedSorts)) {
    usort($books, function ($a, $b) use ($sortField, $sortOrder) {
        $valA = $a[$sortField] ?? '';
        $valB = $b[$sortField] ?? '';

        if ($sortField === 'price') {
            $valA = (float)$valA;
            $valB = (float)$valB;
            return $sortOrder === 'asc' ? ($valA <=> $valB) : ($valB <=> $valA);
        }

        if ($sortField === 'publication_date' || $sortField === 'created_at') {
            $valA = strtotime($valA) ?: 0;
            $valB = strtotime($valB) ?: 0;
            return $sortOrder === 'asc' ? ($valA <=> $valB) : ($valB <=> $valA);
        }

        $result = strcmp($valA, $valB);
        return $sortOrder === 'asc' ? $result : -$result;
    });
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Книжный магазин - Лабораторная работа 6</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Управление книгами в магазине</h1>
    </header>

    <main>
        <section class="form-section">
            <h2>Добавить новую книгу</h2>
            <?php if ($success): ?>
                <div class="alert success">Книга успешно добавлена!</div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <ul class="alert error">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="POST" action="index.php" novalidate>
                <div class="form-group">
                    <label for="title">Название книги *</label>
                    <input type="text" id="title" name="title" required minlength="2" maxlength="150" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="author">Автор *</label>
                    <input type="text" id="author" name="author" required minlength="2" maxlength="100" value="<?= htmlspecialchars($_POST['author'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="isbn">ISBN</label>
                    <input type="text" id="isbn" name="isbn" pattern="^(97(8|9))?\d{9}(\d|X)?$" maxlength="13" value="<?= htmlspecialchars($_POST['isbn'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="publication_date">Дата публикации *</label>
                    <input type="date" id="publication_date" name="publication_date" required value="<?= htmlspecialchars($_POST['publication_date'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="description">Описание (аннотация) *</label>
                    <textarea id="description" name="description" required minlength="50" maxlength="2000"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Цена *</label>
                    <input type="number" id="price" name="price" required step="0.01" min="0" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1" <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
                    <label for="is_featured">Рекомендуемое издание</label>
                </div>

                <button type="submit">Сохранить книгу</button>
            </form>
        </section>

        <section class="table-section">
            <h2>Список книг</h2>
            <?php if (empty($books)): ?>
                <p>В магазине пока нет книг.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th><a href="?sort=title&order=<?= $sortField === 'title' && $sortOrder === 'asc' ? 'desc' : 'asc' ?>">Название</a></th>
                            <th><a href="?sort=author&order=<?= $sortField === 'author' && $sortOrder === 'asc' ? 'desc' : 'asc' ?>">Автор</a></th>
                            <th>ISBN</th>
                            <th><a href="?sort=publication_date&order=<?= $sortField === 'publication_date' && $sortOrder === 'asc' ? 'desc' : 'asc' ?>">Дата публикации</a></th>
                            <th>Описание</th>
                            <th><a href="?sort=price&order=<?= $sortField === 'price' && $sortOrder === 'asc' ? 'desc' : 'asc' ?>">Цена</a></th>
                            <th>Рекомендация</th>
                            <th><a href="?sort=created_at&order=<?= $sortField === 'created_at' && $sortOrder === 'asc' ? 'desc' : 'asc' ?>">Добавлено</a></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td><?= htmlspecialchars($book['title'] ?? 'Без названия') ?></td>
                                <td><?= htmlspecialchars($book['author'] ?? 'Не указан') ?></td>
                                <td><?= htmlspecialchars($book['isbn'] ?? '') ?></td>
                                <td><?= htmlspecialchars($book['publication_date'] ?? 'Не указана') ?></td>
                                <td class="desc-cell">
                                    <?php 
                                    $descRaw = $book['description'] ?? '';
                                    $desc = htmlspecialchars($descRaw);
    
                                    $descOneLine = str_replace(["\n", "\r\n", "\r"], ' ', $descRaw);
                                    $descOneLine = preg_replace('/\s+/', ' ', $descOneLine);
    

                                    if (strlen($descOneLine) > 150) {
                                        $shortDesc = htmlspecialchars(substr($descOneLine, 0, 150));

                                        $shortDesc = preg_replace('/[\x00-\x1F\x7F-\xFF]|.$/u', '...', $shortDesc);
        
                                    if (!str_ends_with($shortDesc, '...')) {
                                        $shortDesc .= '...';
                                    }
                                    } else {
                                        $shortDesc = htmlspecialchars($descOneLine);
                                    }
                                    ?>
    <div class="description-content">
        <div class="desc-short">
            <?= $shortDesc ?>
        </div>
        <div class="desc-full">
            <?= nl2br($desc) ?>
        </div>
        <button type="button" class="show-more-btn" data-book-id="<?= $book['isbn'] ?? 'book' ?>">
            Показать полностью
        </button>
    </div>
</td>
                                <td><?= number_format((float)($book['price'] ?? 0), 2, '.', ' ') ?> MDL</td>
                                <td><?= !empty($book['is_featured']) ? 'Да' : 'Нет' ?></td>
                                <td><?= htmlspecialchars($book['created_at'] ?? 'Неизвестно') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const buttons = document.querySelectorAll('.show-more-btn');
    
    buttons.forEach(function(button) {
        button.addEventListener('click', function() {
            const contentDiv = this.closest('.description-content');
            const shortDiv = contentDiv.querySelector('.desc-short');
            const fullDiv = contentDiv.querySelector('.desc-full');
            
            if (fullDiv.style.display === 'none' || fullDiv.style.display === '') {
                shortDiv.style.display = 'none';
                fullDiv.style.display = 'block';
                this.textContent = 'Свернуть';
            } else {
                shortDiv.style.display = 'block';
                fullDiv.style.display = 'none';
                this.textContent = 'Показать полностью';
            }
        });
    });
});
</script>
</body>
</html>