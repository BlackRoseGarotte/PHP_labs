<?php
declare(strict_types=1);
session_start();
require_once 'src/functions.php';
require_once 'vendor/autoload.php';

$engine = $_GET['engine'] ?? 'twig';
$sortField = $_GET['sort'] ?? 'created_at';
$sortOrder = $_GET['order'] ?? 'desc';

// Генерация/Проверка CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Обработка удаления (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_delete'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Ошибка безопасности (CSRF). Обновите страницу.');
    }
    
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        deleteRecord($id);
    }
    header("Location: index.php?engine=$engine");
    exit;
}

$books = getAllRecords($sortField, $sortOrder);

$data = [
    'books' => $books,
    'sortField' => $sortField,
    'sortOrder' => $sortOrder,
    'engine' => $engine,
    'csrf_token' => $_SESSION['csrf_token']
];

if ($engine === 'twig') {
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/views');
    $twig = new \Twig\Environment($loader, ['cache' => false]);
    $twig->addFilter(new \Twig\TwigFilter('price_fmt', fn($p) => number_format((float)$p, 2, '.', ' ') . ' MDL'));
    echo $twig->render('index_twig.html.twig', $data);
} else {
    extract($data);
    include __DIR__ . '/views/index_native.php';
}