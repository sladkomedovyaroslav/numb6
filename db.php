<?php

function connectDB() {

    static $pdo = null;

    if ($pdo === null) {

        $host = 'localhost';
        $db   = 'u82683';
        $user = 'u82683';
        $pass = '1511698';

        try {

            $pdo = new PDO(
                "mysql:host=$host;dbname=$db;charset=utf8mb4",
                $user,
                $pass
            );

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {

            die("Ошибка подключения: " . $e->getMessage());
        }
    }

    return $pdo;
}
?>