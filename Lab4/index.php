<?php
declare(strict_types=1);

/**
 * Система управления банковскими транзакциями
 */

$transactions = [
    [
        'id' => 1,
        'date' => '2026-02-15',
        'amount' => 500.50,
        'description' => 'Оплата продуктов',
        'merchant' => 'Linella'
    ],
    [
        'id' => 2,
        'date' => '2026-02-20',
        'amount' => 200.00,
        'description' => 'Оплата интернета',
        'merchant' => 'Moldtelecom'
    ],
    [
        'id' => 3,
        'date' => '2026-02-25',
        'amount' => 450.75,
        'description' => 'Заправка автомобиля',
        'merchant' => 'Rompetrol'
    ],
    [
        'id' => 4,
        'date' => '2026-03-01',
        'amount' => 5000.00,
        'description' => 'Аренда квартиры',
        'merchant' => 'Балев В.'
    ]
];

/**
 * Вычисляет общую сумму всех транзакций
 * 
 * @param array $transactions Массив транзакций
 * @return float Общая сумма
 */
function calculateTotalAmount(array $transactions): float
{
    $total = 0.0;
    foreach ($transactions as $transaction) {
        $total += $transaction['amount'];
    }
    return $total;
}

/**
 * Ищет транзакцию по части описания
 * 
 * @param string $descriptionPart Часть описания
 * @return array|null Найденная транзакция
 */
function findTransactionByDescription(string $descriptionPart): ?array
{
    global $transactions;
    foreach ($transactions as $transaction) {
        if (stripos($transaction['description'], $descriptionPart) !== false) {
            return $transaction;
        }
    }
    return null;
}

/**
 * Ищет транзакцию по ID (через foreach)
 * 
 * @param int $id Идентификатор
 * @return array|null Найденная транзакция
 */
function findTransactionById(int $id): ?array
{
    global $transactions;
    foreach ($transactions as $transaction) {
        if ($transaction['id'] === $id) {
            return $transaction;
        }
    }
    return null;
}

/**
 * Ищет транзакцию по ID (через array_filter)
 * 
 * @param int $id Идентификатор
 * @return array|null Найденная транзакция
 */
function findTransactionByIdFilter(int $id): ?array
{
    global $transactions;
    
    $filtered = array_filter(
        $transactions,
        function ($transaction) use ($id) {
            return $transaction['id'] === $id;
        }
    );
    
    return !empty($filtered) ? array_shift($filtered) : null;
}

/**
 * Возвращает количество дней с даты транзакции
 * 
 * @param string $date Дата в формате Y-m-d
 * @return int Количество дней
 */
function daysSinceTransaction(string $date): int
{
    $transactionDate = new DateTime($date);
    $currentDate = new DateTime();
    $interval = $currentDate->diff($transactionDate);
    return (int)$interval->days;
}

/**
 * Добавляет новую транзакцию
 * 
 * @param int $id ID транзакции
 * @param string $date Дата
 * @param float $amount Сумма
 * @param string $description Описание
 * @param string $merchant Получатель
 */
function addTransaction(
    int $id,
    string $date,
    float $amount,
    string $description,
    string $merchant
): void {
    global $transactions;
    $transactions[] = [
        'id' => $id,
        'date' => $date,
        'amount' => $amount,
        'description' => $description,
        'merchant' => $merchant
    ];
}

/**
 * Удаляет транзакцию по ID
 * 
 * @param int $id ID транзакции
 * @return bool Успешность удаления
 */
function deleteTransaction(int $id): bool
{
    global $transactions;
    foreach ($transactions as $key => $transaction) {
        if ($transaction['id'] === $id) {
            unset($transactions[$key]);
            $transactions = array_values($transactions);
            return true;
        }
    }
    return false;
}

/**
 * Сортирует транзакции по сумме (только убывание)
 * 
 * @param array $transactions Массив транзакций
 */
function sortTransactionsByAmount(array &$transactions): void
{
    usort($transactions, function ($a, $b) {
        return $b['amount'] <=> $a['amount'];
    });
}

/**
 * Генерирует HTML таблицу транзакций
 * 
 * @param array $transactions Массив данных
 * @return string HTML код
 */
function generateTransactionsTable(array $transactions): string
{
    $html = "<table>\n";
    $html .= "<thead>\n";
    $html .= "    <tr>\n";
    $html .= "        <th>ID</th>\n";
    $html .= "        <th>Дата</th>\n";
    $html .= "        <th>Сумма (MDL)</th>\n";
    $html .= "        <th>Описание</th>\n";
    $html .= "        <th>Получатель</th>\n";
    $html .= "        <th>Дней назад</th>\n";
    $html .= "    </tr>\n";
    $html .= "</thead>\n";
    $html .= "<tbody>\n";
    
    foreach ($transactions as $transaction) {
        $days = daysSinceTransaction($transaction['date']);
        $amount = number_format($transaction['amount'], 2, ',', ' ');
        
        $html .= "    <tr>\n";
        $html .= "        <td>" . (string)$transaction['id'] . "</td>\n";
        $html .= "        <td>" . ($transaction['date']) . "</td>\n";
        $html .= "        <td>" . $amount . "</td>\n";
        $html .= "        <td>" . ($transaction['description']) . "</td>\n";
        $html .= "        <td>" . ($transaction['merchant']) . "</td>\n";
        $html .= "        <td>" . $days . "</td>\n";
        $html .= "    </tr>\n";
    }
    
    $html .= "</tbody>\n";
    $html .= "</table>\n";
    return $html;
}

/**
 * Получает изображения из директории
 * 
 * @param string $directory Путь к папке
 * @return array Массив путей
 */
function getImagesFromDirectory(string $directory): array
{
    $images = [];
    $jpgFiles = glob($directory . '/*.jpg');
    $jpegFiles = glob($directory . '/*.jpeg');
    
    if ($jpgFiles !== false) {
        $images = array_merge($images, $jpgFiles);
    }
    if ($jpegFiles !== false) {
        $images = array_merge($images, $jpegFiles);
    }
    
    return $images;
}

/**
 * Генерирует HTML галерею
 * 
 * @param array $images Массив изображений
 * @return string HTML код
 */
function generateImageGallery(array $images): string
{
    if (empty($images)) {
        return "<p>Изображения не найдены</p>";
    }
    
    $html = "<div class='gallery'>\n";
    foreach ($images as $image) {
        $imageName = basename($image);
        $html .= "    <div class='gallery-item'>\n";
        $html .= "        <img src='" . ($image) . "' alt='" . ($imageName) . "' />\n";
        $html .= "        <p class='image-name'>" . ($imageName) . "</p>\n";
        $html .= "    </div>\n";
    }
    $html .= "</div>\n";
    return $html;
}

/**
 * Генерирует шапку сайта
 * 
 * @return string HTML код
 */
function generateHeader(): string
{
    $html = "<header>\n";
    $html .= "    <h1>Система управления транзакциями</h1>\n";
    $html .= "    <p class='subtitle'>Банковские операции</p>\n";
    $html .= "</header>\n";
    return $html;
}

/**
 * Генерирует меню навигации
 * 
 * @return string HTML код
 */
function generateMenu(): string
{
    $html = "<nav>\n";
    $html .= "    <ul>\n";
    $html .= "        <li><a href='#transactions'>Транзакции</a></li>\n";
    $html .= "        <li><a href='#search'>Поиск</a></li>\n";
    $html .= "        <li><a href='#sort'>Сортировка</a></li>\n";
    $html .= "        <li><a href='#gallery'>Галерея</a></li>\n";
    $html .= "    </ul>\n";
    $html .= "</nav>\n";
    return $html;
}

/**
 * Генерирует подвал сайта
 * 
 * @return string HTML код
 */
function generateFooter(): string
{
    $html = "<footer>\n";
    $html .= "    <p>(с) 2026 Система управления транзакциями. Все права защищены.</p>\n";
    $html .= "    <p>PHP 8+</p>\n";
    $html .= "</footer>\n";
    return $html;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Транзакции</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php echo generateHeader(); ?>
<?php echo generateMenu(); ?>

<main>

<!-- Секция 1: Список транзакций -->
<section id="transactions" class="section">
    <h2>Список всех транзакций</h2>
    <?php echo generateTransactionsTable($transactions); ?>
    
    <div class="total">
        Общая сумма всех транзакций: 
        <?php echo number_format(calculateTotalAmount($transactions), 2, ',', ' '); ?> MDL
    </div>
    
    <div class="info">
        Всего транзакций: <?php echo count($transactions); ?>
    </div>
</section>

<!-- Секция 2: Поиск транзакций -->
<section id="search" class="section">
    <h2>Поиск транзакций</h2>
    
    <h3>Поиск по описанию "интернет":</h3>
    <?php
    $foundByDesc = findTransactionByDescription('интернет');
    if ($foundByDesc) {
        echo "<p class='success'>Найдена: ID=" . $foundByDesc['id'] . 
             ", Сумма=" . number_format($foundByDesc['amount'], 2, ',', ' ') . 
             " MDL, Получатель=" . ($foundByDesc['merchant']) . "</p>";
    } else {
        echo "<p>Не найдено</p>";
    }
    ?>
    
    <h3>Поиск по ID (foreach):</h3>
    <?php
    $foundByIdForeach = findTransactionById(3);
    if ($foundByIdForeach) {
        echo "<p class='success'>Найдена: " . ($foundByIdForeach['description']) . 
             " - " . number_format($foundByIdForeach['amount'], 2, ',', ' ') . " MDL</p>";
    }
    ?>
    
    <h3>Поиск по ID (array_filter):</h3>
    <?php
    $foundByIdFilter = findTransactionByIdFilter(2);
    if ($foundByIdFilter) {
        echo "<p class='success'>Найдена: " . ($foundByIdFilter['description']) . 
             " - " . number_format($foundByIdFilter['amount'], 2, ',', ' ') . " MDL</p>";
    }
    ?>
</section>

<!-- Секция 3: Сортировка -->
<section id="sort" class="section">
    <h2>Сортировка транзакций</h2>
    
    <?php
    $sorted = $transactions;
    sortTransactionsByAmount($sorted);
    echo "<h3>Отсортировано по сумме (убывание):</h3>";
    echo generateTransactionsTable($sorted);
    ?>
</section>

<!-- Секция 4: Добавление и удаление -->
<section class="section">
    <h2>Добавление и удаление транзакций</h2>
    
    <?php
    // Шаг 1: Добавление новой транзакции
    echo "<h3>Шаг 1: Добавление транзакции</h3>";
    addTransaction(5, '2026-03-10', 150.00, 'Покупка кофе', 'Skull Coffee');
    echo "<p class='success'>Добавлена: Покупка кофе, Skull Coffee, 150.00 MDL</p>";
    echo "<p>Всего транзакций: <strong>" . count($transactions) . "</strong></p>";
    
    // Шаг 2: Удаление транзакции
    echo "<h3>Шаг 2: Удаление транзакции</h3>";
    if (deleteTransaction(2)) {
        echo "<p class='success'>Удалена транзакция ID=2 (Оплата интернета)</p>";
    }
    echo "<p>Всего транзакций: <strong>" . count($transactions) . "</strong></p>";
    
    // Шаг 3: Показать итоговый список
    echo "<h3>Шаг 3: Итоговый список</h3>";
    echo generateTransactionsTable($transactions);
    ?>
</section>

<!-- Секция 5: Галерея изображений -->
<section id="gallery" class="section">
    <h2>Галерея изображений</h2>
    
    <?php
    $images = getImagesFromDirectory('image');
    
    if (!empty($images)) {
        echo "<p>Найдено изображений: <strong>" . count($images) . "</strong></p>";
        echo generateImageGallery($images);
    } else {
        echo "<div class='info'>";
        echo "<p>Изображения не найдены.</p>";
        echo "<p>Создайте директорию <code>image</code> и добавьте 20-30 файлов .jpg</p>";
        echo "</div>";
    }
    ?>
</section>

</main>

<?php echo generateFooter(); ?>

</body>
</html>