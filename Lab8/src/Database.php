<?php
class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config.php';
            
            // DSN для PostgreSQL
            $dsn = sprintf(
                "pgsql:host=%s;dbname=%s",
                $config['db']['host'],
                $config['db']['dbname']
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Для PG эмуляция подготовленных выражений часто вызывает проблемы с последним ID,
                // поэтому лучше оставить false, но если будут ошибки - попробуйте true.
                PDO::ATTR_EMULATE_PREPARES   => false, 
            ];

            try {
                self::$instance = new PDO($dsn, $config['db']['user'], $config['db']['password'], $options);
                // Установка кодировки клиента явно (опционально, но полезно)
                self::$instance->exec("SET NAMES 'UTF8'");
            } catch (PDOException $e) {
                die("Ошибка подключения к БД (PostgreSQL): " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}