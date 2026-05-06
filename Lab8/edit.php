<?php
declare(strict_types=1);
session_start();
require_once 'src/functions.php';
require_once 'vendor/autoload.php';

$engine = $_GET['engine'] ?? 'twig';
$id = (int)($_GET['id'] ?? 0);
$book = getRecordById($id);

if (!$book) {
    http_response_code(404);
    die('Книга не найдена');
}

// Получаем имя автора для поля ввода
$pdo = Database::getConnection();
$stmt = $pdo->prepare("SELECT name FROM authors WHERE id = :id");
$stmt->execute([':id' => $book['author_id']]);
$book['author_name'] = $stmt->fetchColumn();

$errors = [];
$old = $book;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Ошибка безопасности (CSRF).');
    }

    $errors = validateBookData($_POST);
    $old = $_POST;

    if (empty($errors)) {
        if (updateRecord($id, $_POST)) {
            header("Location: index.php?engine=$engine");
            exit;
        } else {
            $errors[] = 'Ошибка базы данных при обновлении.';
        }
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$data = ['errors' => $errors, 'old' => $old, 'engine' => $engine, 'csrf_token' => $_SESSION['csrf_token'], 'action' => 'edit', 'id' => $id];

if ($engine === 'twig') {
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/views');
    $twig = new \Twig\Environment($loader, ['cache' => false]);
    echo $twig->render('form_twig.html.twig', $data);
} else {
    extract($data);
    include __DIR__ . '/views/form_native.php';
}