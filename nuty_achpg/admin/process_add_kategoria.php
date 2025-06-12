<?php

session_start();

// Dołączenie konfiguracji i funkcji
require_once '../inc/config.php'; // Dla BASE_PATH i stałych DB
require_once '../inc/functions.php'; // Dla getPdoConnection()

// Inicjalizacja zmiennych dla przekierowania
$redirect_status = 'error'; // Domyślnie błąd
$redirect_message = 'Wystąpił nieoczekiwany błąd.';

// Autoryzacja
$can_add_category = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'bibliotekarz', 'zarzad'])) {
    $can_add_category = true;
}

if (!$can_add_category) {
    $redirect_message = 'Nie masz uprawnień do wykonania tej akcji.';
    header("Location: " . BASE_PATH . "/admin/manage_nuty.php?category_action_status=" . $redirect_status . "&category_action_message=" . urlencode($redirect_message));
    exit();
}

// Sprawdzenie, czy żądanie jest typu POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nazwa'])) {
        $nazwa_kategorii = trim($_POST['nazwa']);
        $errors = [];

        // 1. Walidacja nazwy kategorii
        if (empty($nazwa_kategorii)) {
            $errors[] = "Nazwa kategorii nie może być pusta.";
        } elseif (strlen($nazwa_kategorii) > 100) { // Zgodnie z DB schema VARCHAR(100)
            $errors[] = "Nazwa kategorii jest zbyt długa (maksymalnie 100 znaków).";
        }

        if (empty($errors)) {
            try {
                $pdo = getPdoConnection();

                // 2. Sprawdzenie, czy kategoria o tej nazwie już istnieje
                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM kategorie WHERE nazwa = :nazwa");
                $stmt_check->bindParam(':nazwa', $nazwa_kategorii, PDO::PARAM_STR);
                $stmt_check->execute();

                if ($stmt_check->fetchColumn() > 0) {
                    $errors[] = "Kategoria o podanej nazwie już istnieje.";
                } else {
                    // 3. Dodanie nowej kategorii do bazy
                    $stmt_insert = $pdo->prepare("INSERT INTO kategorie (nazwa) VALUES (:nazwa)");
                    $stmt_insert->bindParam(':nazwa', $nazwa_kategorii, PDO::PARAM_STR);

                    if ($stmt_insert->execute()) {
                        $redirect_status = 'success';
                        $redirect_message = "Kategoria '" . htmlspecialchars($nazwa_kategorii) . "' została pomyślnie dodana.";
                    } else {
                        $errors[] = "Nie udało się dodać kategorii do bazy danych.";
                    }
                }
            } catch (PDOException $e) {
                error_log("Błąd PDO w process_add_kategoria.php: " . $e->getMessage());
                $errors[] = "Wystąpił błąd serwera podczas dodawania kategorii.";
            }
        }

        // Jeśli były błędy walidacji lub błąd zapisu
        if (!empty($errors)) {
            $redirect_status = 'error';
            $redirect_message = implode(" ", $errors);
        }

    } else {
        $redirect_message = "Nie podano nazwy kategorii.";
    }
} else {
    // Jeśli nie jest to żądanie POST, przekieruj
    $redirect_message = "Nieprawidłowe żądanie.";
}

// Przekierowanie z powrotem na stronę zarządzania nutami
header("Location: " . BASE_PATH . "/admin/manage_nuty.php?category_action_status=" . $redirect_status . "&category_action_message=" . urlencode($redirect_message));
exit();
?>