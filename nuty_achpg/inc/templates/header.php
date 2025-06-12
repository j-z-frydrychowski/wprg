<?php
// inc/templates/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Rozpocznij sesję, jeśli jeszcze nie została rozpoczęta
}

// Dołączenie konfiguracji i funkcji
// Użycie __DIR__ dla pewniejszych ścieżek względem aktualnego pliku
require_once __DIR__ . '/../config.php'; // BASE_PATH i stałe DB powinny być tutaj zdefiniowane
require_once __DIR__ . '/../functions.php'; // getPdoConnection()

// Domyślny tytuł strony, jeśli nie zostanie ustawiony przez stronę dołączającą ten nagłówek
$page_title = isset($page_title) ? $page_title : 'Biblioteka Nut Chóru';
// Inicjalizacja tablicy alertów, jeśli nie została przekazana
$page_alerts = isset($page_alerts) ? $page_alerts : [];

if (isset($_SESSION['page_alerts_flash']) && is_array($_SESSION['page_alerts_flash'])) {
    foreach ($_SESSION['page_alerts_flash'] as $flash_alert) {
        $page_alerts[] = $flash_alert; // Dodaj flash alert do bieżących alertów strony
    }
    unset($_SESSION['page_alerts_flash']); // Usuń flash alerty z sesji po ich odczytaniu
}

// Pobranie ID i roli użytkownika
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/public/css/style.css">

</head>
<body>
<div class="container">

    <header>
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <nav>
            <ul>
                <?php if ($user_id): ?>
                    <li><a href="<?php echo BASE_PATH; ?>/public/biblioteka.php">Biblioteka</a></li>
                    <li><a href="<?php echo BASE_PATH; ?>/public/profile.php">Mój Profil</a></li>

                    <?php if (in_array($user_role, ['bibliotekarz', 'zarzad', 'admin'])): ?>
                        <li><a href="<?php echo BASE_PATH; ?>/admin/add_nuty.php">Dodaj Nuty</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/admin/manage_nuty.php">Zarządzaj Nutami</a></li>
                    <?php endif; ?>

                    <?php if (in_array($user_role, ['zarzad', 'admin'])): ?>
                        <li><a href="<?php echo BASE_PATH; ?>/admin/manage_allowed_emails.php">Zarządzaj Dozwolonymi E-mailami</a></li>
                    <?php endif; ?>

                    <?php if ($user_role === 'admin'): ?>
                        <li><a href="<?php echo BASE_PATH; ?>/admin/index.php">Panel Administratora</a></li>
                    <?php endif; ?>

                    <li><a href="<?php echo BASE_PATH; ?>/public/logout.php">Wyloguj się</a></li>

                    <li><a href="<?php echo BASE_PATH; ?>/public/index.php">Strona Główna</a></li>
                    <li><a href="<?php echo BASE_PATH; ?>/public/login.php">Logowanie</a></li>
                    <li><a href="<?php echo BASE_PATH; ?>/public/rejestracja.php">Rejestracja</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <hr>
    </header>

    <main>
        <?php if (!empty($page_alerts)): ?>
            <?php foreach ($page_alerts as $alert): ?>
                <div class="alert alert-<?php echo htmlspecialchars($alert['type']); ?>">
                    <?php echo htmlspecialchars($alert['message']); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>