<?php
declare(strict_types=1);
session_start();
require_once 'src/functions.php';
require_once 'vendor/autoload.php';

$engine = $_GET['engine'] ?? 'twig';
$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Ошибка безопасности (CSRF).');
    }

    $errors = validateBookData($_POST);
    $old = $_POST;

    if (empty($errors)) {
        if (createRecord($_POST)) {
            header("Location: index.php?engine=$engine");
            exit;
        } else {
            $errors[] = 'Ошибка базы данных при создании.';
        }
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Новый токен

$data = ['errors' => $errors, 'old' => $old, 'engine' => $engine, 'csrf_token' => $_SESSION['csrf_token'], 'action' => 'create'];

if ($engine === 'twig') {
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/views');
    $twig = new \Twig\Environment($loader, ['cache' => false]);
    echo $twig->render('form_twig.html.twig', $data);
} else {
    extract($data);
    include __DIR__ . '/views/form_native.php';
}