<?php
declare(strict_types=1);

require_once 'Book.php';
require_once 'BookValidator.php';
require_once 'BookRepository.php';
require 'vendor/autoload.php';

$validator = new BookValidator();
$repository = new BookRepository(__DIR__ . '/data/books.json');

// === ЛОГИКА (КОНТРОЛЛЕР) ===
$errors = [];
$success = false;
$books = $repository->getAll();

// Определяем текущий движок
$engine = $_GET['engine'] ?? 'php';

// 1. Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = $validator->validate($_POST);
    if (empty($errors)) {
        $book = new Book($_POST);
        $repository->save($book->toArray());
        // ВАЖНО: Сохраняем параметр engine при редиректе, чтобы пользователь остался в том же виде
        header('Location: index.php?status=success&engine=' . $engine);
        exit;
    }
}

if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $success = true;
    $books = $repository->getAll();
}

// 2. Сортировка
$sortField = $_GET['sort'] ?? 'created_at';
$sortOrder = $_GET['order'] ?? 'desc';
$allowedSorts = ['title', 'author', 'publication_date', 'price', 'created_at'];

if (in_array($sortField, $allowedSorts)) {
    usort($books, function ($a, $b) use ($sortField, $sortOrder) {
        $valA = $a[$sortField] ?? '';
        $valB = $b[$sortField] ?? '';
        if ($sortField === 'price') return $sortOrder === 'asc' ? ((float)$valA <=> (float)$valB) : ((float)$valB <=> (float)$valA);
        if ($sortField === 'publication_date' || $sortField === 'created_at') {
            $valA = strtotime($valA) ?: 0; $valB = strtotime($valB) ?: 0;
            return $sortOrder === 'asc' ? ($valA <=> $valB) : ($valB <=> $valA);
        }
        return $sortOrder === 'asc' ? strcmp($valA, $valB) : strcmp($valB, $valA);
    });
}

// Данные для передачи в оба шаблона
$viewData = [
    'books' => $books,
    'errors' => $errors,
    'success' => $success,
    'sortField' => $sortField,
    'sortOrder' => $sortOrder,
    'engine' => $engine // <-- Передаем engine в шаблоны
];

// === ВЫБОР ШАБЛОНИЗАТОРА ===
if ($engine === 'twig') {
    // --- ВАРИАНТ 1: TWIG ---
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/views');
    $twig = new \Twig\Environment($loader, ['cache' => false, 'debug' => true]);

    // Кастомный фильтр
    $formatPriceFilter = new \Twig\TwigFilter('format_price', function ($price) {
        return number_format((float)$price, 2, '.', ' ') . ' ₽';
    });
    $twig->addFilter($formatPriceFilter);

    echo $twig->render('index_twig.html.twig', $viewData);
} else {
    // --- ВАРИАНТ 2: НАТИВНЫЙ PHP ---
    // Распаковываем массив в переменные
    extract($viewData);
    
    // Подключаем PHP-файл
    include __DIR__ . '/views/index_native.php';
}