<?php
//Dane do połączenia się z bazą danych
define('DB_HOST', 'localhost:8081'); // Adres serwera bazy danych
define('DB_NAME', 'nuty_achpg'); // Nazwa bazy danych
define('DB_USER', 'root'); // Nazwa użytkownika
define('DB_PASSWORD', ''); // Hasło

define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Włączenie raportowania błędów w formie wyjątków
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Ustawienie trybu domyślnego pobierania danych na tablice asocjacyjne
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci" // Ustawienie kododwania na UTF-8
]);

define('BASE_PATH', ''); //Definicja podstawowej ścieżki do folderu projektu w htdocs
?>