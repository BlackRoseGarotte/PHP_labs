<?php
declare(strict_types=1);

/**
 * Класс Transaction описывает одну банковскую транзакцию.
 * 
 * Использует инкапсуляцию: все свойства приватны, доступ осуществляется через публичные геттеры.
 * Содержит логику для вычисления времени, прошедшего с момента транзакции.
 */
class Transaction
{
    private int $id;
    private string $date;
    private float $amount;
    private string $description;
    private string $merchant;

    /**
     * Конструктор для инициализации свойств транзакции.
     *
     * @param int $id Уникальный идентификатор транзакции
     * @param string $date Дата совершения транзакции в формате YYYY-MM-DD
     * @param float $amount Сумма транзакции
     * @param string $description Описание назначения платежа
     * @param string $merchant Название организации-получателя
     */
    public function __construct(
        int $id,
        string $date,
        float $amount,
        string $description,
        string $merchant
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->amount = $amount;
        $this->description = $description;
        $this->merchant = $merchant;
    }

    /**
     * Возвращает уникальный идентификатор транзакции.
     *
     * @return int ID транзакции
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Возвращает дату транзакции.
     *
     * @return string Дата в формате YYYY-MM-DD
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * Возвращает сумму транзакции.
     *
     * @return float Сумма транзакции
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Возвращает описание транзакции.
     *
     * @return string Описание платежа
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Возвращает название получателя платежа.
     *
     * @return string Название организации
     */
    public function getMerchant(): string
    {
        return $this->merchant;
    }

    /**
     * Вычисляет количество дней, прошедших с момента транзакции до текущей даты.
     *
     * @return int Количество дней
     */
    public function getDaysSinceTransaction(): int
    {
        $transactionDate = new DateTime($this->date);
        $currentDate = new DateTime();
        $interval = $currentDate->diff($transactionDate);
        return (int)$interval->days;
    }
}


/**
 * Интерфейс TransactionStorageInterface определяет контракт для хранилищ транзакций.
 * 
 * Любое хранилище (в памяти, в базе данных, в файле), реализующее этот интерфейс,
 * обязано предоставить методы для добавления, удаления, получения списка и поиска транзакций.
 * Это позволяет использовать принцип инверсии зависимостей (Dependency Inversion Principle).
 */
interface TransactionStorageInterface
{
    /**
     * Добавляет новую транзакцию в хранилище.
     *
     * @param Transaction $transaction Объект транзакции для добавления
     * @return void
     */
    public function addTransaction(Transaction $transaction): void;

    /**
     * Удаляет транзакцию из хранилища по её уникальному идентификатору.
     *
     * @param int $id ID транзакции для удаления
     * @return void
     */
    public function removeTransactionById(int $id): void;

    /**
     * Возвращает массив всех транзакций, хранящихся в хранилище.
     *
     * @return array Массив объектов Transaction
     */
    public function getAllTransactions(): array;

    /**
     * Находит и возвращает транзакцию по её уникальному идентификатору.
     *
     * @param int $id ID искомой транзакции
     * @return Transaction|null Объект транзакции или null, если не найдена
     */
    public function findById(int $id): ?Transaction;
}

/**
 * Класс TransactionRepository реализует интерфейс TransactionStorageInterface.
 * 
 * Отвечает за хранение коллекции транзакций в оперативной памяти (массиве)
 * и предоставление базовых операций доступа к ним (CRUD).
 */
class TransactionRepository implements TransactionStorageInterface
{
    /**
     * @var Transaction[] Массив объектов транзакций
     */
    private array $transactions = [];

    /**
     * Добавляет новую транзакцию в массив.
     *
     * @param Transaction $transaction Объект транзакции
     * @return void
     */
    public function addTransaction(Transaction $transaction): void
    {
        $this->transactions[] = $transaction;
    }

    /**
     * Удаляет транзакцию по ID. После удаления массив переиндексируется.
     *
     * @param int $id Идентификатор удаляемой транзакции
     * @return void
     */
    public function removeTransactionById(int $id): void
    {
        foreach ($this->transactions as $key => $transaction) {
            if ($transaction->getId() === $id) {
                unset($this->transactions[$key]);
                // Переиндексация массива для сохранения последовательных числовых ключей
                $this->transactions = array_values($this->transactions);
                return;
            }
        }
    }

    /**
     * Возвращает полный массив всех сохраненных транзакций.
     *
     * @return array Массив объектов Transaction
     */
    public function getAllTransactions(): array
    {
        return $this->transactions;
    }

    /**
     * Осуществляет линейный поиск транзакции по ID.
     *
     * @param int $id Идентификатор искомой транзакции
     * @return Transaction|null Найденный объект или null
     */
    public function findById(int $id): ?Transaction
    {
        foreach ($this->transactions as $transaction) {
            if ($transaction->getId() === $id) {
                return $transaction;
            }
        }
        return null;
    }
}

/**
 * Класс TransactionManager содержит бизнес-логику приложения.
 * 
 * Он не хранит данные самостоятельно, а делегирует операции хранения объекту,
 * реализующему интерфейс TransactionStorageInterface (внедрение зависимостей).
 */
class TransactionManager
{
    /**
     * @var TransactionStorageInterface Репозиторий для работы с данными
     */
    private TransactionStorageInterface $repository;

    /**
     * Конструктор принимает зависимость через интерфейс.
     *
     * @param TransactionStorageInterface $repository Реализация хранилища
     */
    public function __construct(TransactionStorageInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Вычисляет общую сумму всех транзакций в хранилище.
     *
     * @return float Общая сумма
     */
    public function calculateTotalAmount(): float
    {
        $total = 0.0;
        foreach ($this->repository->getAllTransactions() as $transaction) {
            $total += $transaction->getAmount();
        }
        return $total;
    }

    /**
     * Вычисляет сумму транзакций за указанный период дат.
     *
     * @param string $startDate Начало периода (YYYY-MM-DD)
     * @param string $endDate Конец периода (YYYY-MM-DD)
     * @return float Сумма транзакций за период
     */
    public function calculateTotalAmountByDateRange(string $startDate, string $endDate): float
    {
        $total = 0.0;
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);

        foreach ($this->repository->getAllTransactions() as $transaction) {
            $tDate = new DateTime($transaction->getDate());
            if ($tDate >= $start && $tDate <= $end) {
                $total += $transaction->getAmount();
            }
        }
        return $total;
    }

    /**
     * Подсчитывает количество транзакций у конкретного получателя (частичное совпадение).
     *
     * @param string $merchant Часть названия получателя
     * @return int Количество найденных транзакций
     */
    public function countTransactionsByMerchant(string $merchant): int
    {
        $count = 0;
        foreach ($this->repository->getAllTransactions() as $transaction) {
            if (stripos($transaction->getMerchant(), $merchant) !== false) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Возвращает массив транзакций, отсортированный по дате (возрастание).
     *
     * @return array Отсортированный массив объектов Transaction
     */
    public function sortTransactionsByDate(): array
    {
        $transactions = $this->repository->getAllTransactions();
        usort($transactions, function (Transaction $a, Transaction $b) {
            return new DateTime($a->getDate()) <=> new DateTime($b->getDate());
        });
        return $transactions;
    }

    /**
     * Возвращает массив транзакций, отсортированный по сумме (убывание).
     *
     * @return array Отсортированный массив объектов Transaction
     */
    public function sortTransactionsByAmountDesc(): array
    {
        $transactions = $this->repository->getAllTransactions();
        usort($transactions, function (Transaction $a, Transaction $b) {
            return $b->getAmount() <=> $a->getAmount();
        });
        return $transactions;
    }
}

/**
 * Класс TransactionTableRenderer отвечает исключительно за представление данных.
 * 
 * Генерирует HTML-код таблицы на основе переданного массива объектов транзакций.
 * Объявлен как final, так как наследование не предполагается.
 */
final class TransactionTableRenderer
{
    /**
     * Генерирует HTML-разметку таблицы со списком транзакций.
     *
     * @param array $transactions Массив объектов Transaction
     * @return string HTML-код таблицы
     */
    public function render(array $transactions): string
    {
        $html = "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;'>";
        $html .= "<thead><tr style='background-color: #f2f2f2;'>";
        $html .= "<th>ID</th><th>Дата</th><th>Сумма (MDL)</th><th>Описание</th>";
        $html .= "<th>Получатель</th><th>Категория</th><th>Дней назад</th>";
        $html .= "</tr></thead><tbody>";

        if (empty($transactions)) {
            $html .= "<tr><td colspan='7' style='text-align:center;'>Нет данных</td></tr>";
        } else {
            foreach ($transactions as $t) {
                $category = $this->getCategory($t->getMerchant());
                $html .= "<tr>";
                $html .= "<td>{$t->getId()}</td>";
                $html .= "<td>{$t->getDate()}</td>";
                $html .= "<td style='text-align:right;'>" . number_format($t->getAmount(), 2, ',', ' ') . "</td>";
                $html .= "<td>" . htmlspecialchars($t->getDescription()) . "</td>";
                $html .= "<td>" . htmlspecialchars($t->getMerchant()) . "</td>";
                $html .= "<td>{$category}</td>";
                $html .= "<td style='text-align:center;'>" . $t->getDaysSinceTransaction() . "</td>";
                $html .= "</tr>";
            }
        }
        $html .= "</tbody></table>";
        return $html;
    }

    /**
     * Вспомогательный метод для определения категории получателя на основе названия.
     *
     * @param string $merchant Название получателя
     * @return string Название категории
     */
    private function getCategory(string $merchant): string
    {
        $m = strtolower($merchant);
        if (str_contains($m, 'linella') || str_contains($m, 'market')) return 'Продукты';
        if (str_contains($m, 'telecom')) return 'Связь';
        if (str_contains($m, 'petrol')) return 'Авто';
        if (str_contains($m, 'pizza')) return 'Еда';
        return 'Разное';
    }
}

$repository = new TransactionRepository();

$manager = new TransactionManager($repository);
$renderer = new TransactionTableRenderer();

$data = [
    [1, '2026-01-10', 1500.50, 'Продукты', 'Linella'],
    [2, '2026-01-15', 200.00, 'Интернет', 'Moldtelecom'],
    [3, '2026-02-05', 450.75, 'Бензин', 'Rompetrol'],
    [4, '2026-02-10', 5000.00, 'Аренда', 'Балев В.'],
    [5, '2026-02-20', 1350.00, 'Одежда', 'New Yorker'],
    [6, '2026-03-01', 213.00, 'Обед', 'Andys Pizza'],
    [7, '2026-03-05', 3200.00, 'Телефон', 'Enter'],
    [8, '2026-03-10', 40.00, 'Кофе', 'Skull Coffee'],
    [9, '2026-03-12', 300.00, 'Такси', 'Yandex Go'],
    [10, '2026-03-15', 270.30, 'Аптека', 'Felicia'],
];

foreach ($data as $item) {
    $repository->addTransaction(new Transaction($item[0], $item[1], $item[2], $item[3], $item[4]));
}

$total = $manager->calculateTotalAmount();
$rangeTotal = $manager->calculateTotalAmountByDateRange('2026-02-01', '2026-03-31');
$countLinella = $manager->countTransactionsByMerchant('Linella');
$sortedByDate = $manager->sortTransactionsByDate();
$sortedByAmount = $manager->sortTransactionsByAmountDesc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Транзакции ООП (с Интерфейсом)</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f9; padding: 20px; color: #333; }
        h1, h2 { color: #2c3e50; }
        .stats { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .stat-row { margin: 8px 0; font-size: 1.1em; }
        .val { color: #27ae60; font-weight: bold; }
        table { background: #fff; width: 100%; border-collapse: collapse; margin-top: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th { background: #3498db; color: #fff; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f9f9f9; }
    </style>
</head>
<body>

<h1>Система управления транзакциями (ООП + Интерфейсы)</h1>

<div class="stats">
    <h2>Статистика</h2>
    <div class="stat-row">Общая сумма: <span class="val"><?= number_format($total, 2, ',', ' ') ?> MDL</span></div>
    <div class="stat-row">Сумма (Февраль-Март): <span class="val"><?= number_format($rangeTotal, 2, ',', ' ') ?> MDL</span></div>
    <div class="stat-row">Транзакций в Linella: <span class="val"><?= $countLinella ?></span></div>
    <div class="stat-row">Всего записей: <span class="val"><?= count($sortedByDate) ?></span></div>
</div>

<h2>Все транзакции (по дате)</h2>
<?= $renderer->render($sortedByDate) ?>

<h2>Топ расходов (по сумме)</h2>
<?= $renderer->render($sortedByAmount) ?>

</body>
</html>