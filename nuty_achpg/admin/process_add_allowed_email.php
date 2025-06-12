<?php
// admin/process_add_allowed_email.php
session_start();

require_once '../inc/config.php'; // Dla BASE_PATH i stałych DB
require_once '../inc/functions.php'; // Dla getPdoConnection()

// Domyślne wartości dla przekierowania
$redirect_status = 'error';
$redirect_message = 'Wystąpił nieoczekiwany błąd.';

// Autoryzacja
$can_manage_emails = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'zarzad'])) {
    $can_manage_emails = true;
}

if (!$can_manage_emails) {
    $redirect_message = 'Nie masz uprawnień do wykonania tej akcji.';
    // Przekierowanie do strony zarządzania, która wyświetli ten błąd przez header.php
    header("Location: " . BASE_PATH . "/admin/manage_allowed_emails.php?email_action_status=" . $redirect_status . "&email_action_message=" . urlencode($redirect_message));
    exit();
}

// Sprawdzenie, czy żądanie jest typu POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email_to_add'])) {
        $email_to_add = trim($_POST['email_to_add']);
        $errors = [];

        // 1. Walidacja adresu e-mail
        if (empty($email_to_add)) {
            $errors[] = "Adres e-mail nie może być pusty.";
        } elseif (!filter_var($email_to_add, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Podany adres e-mail ma nieprawidłowy format.";
        } elseif (strlen($email_to_add) > 255) {
            $errors[] = "Adres e-mail jest zbyt długi (maksymalnie 255 znaków).";
        }

        if (empty($errors)) {
            try {
                $pdo = getPdoConnection();

                // 2. Sprawdzenie, czy e-mail już istnieje w tabeli dozwolonych
                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM allowed_emails WHERE email = :email");
                $stmt_check->bindParam(':email', $email_to_add, PDO::PARAM_STR);
                $stmt_check->execute();

                if ($stmt_check->fetchColumn() > 0) {
                    $errors[] = "Podany adres e-mail ('" . htmlspecialchars($email_to_add) . "') już znajduje się na liście dozwolonych.";
                } else {
                    // 3. Dodanie nowego adresu e-mail do bazy
                    $stmt_insert = $pdo->prepare("INSERT INTO allowed_emails (email) VALUES (:email)");
                    $stmt_insert->bindParam(':email', $email_to_add, PDO::PARAM_STR);

                    if ($stmt_insert->execute()) {
                        $redirect_status = 'success';
                        $redirect_message = "Adres e-mail " . htmlspecialchars($email_to_add) . " został pomyślnie dodany do listy dozwolonych.";
                    } else {
                        $errors[] = "Nie udało się dodać adresu e-mail do bazy danych.";
                    }
                }
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') { // Kod dla Integrity constraint violation
                    $errors[] = "Adres e-mail ('" . htmlspecialchars($email_to_add) . "') już istnieje (błąd bazy danych).";
                } else {
                    error_log("Błąd PDO w process_add_allowed_email.php: " . $e->getMessage());
                    $errors[] = "Wystąpił błąd serwera podczas dodawania adresu e-mail.";
                }
            }
        }

        // Jeśli były błędy walidacji lub błąd zapisu
        if (!empty($errors)) {
            $redirect_status = 'error';
            $redirect_message = implode(" ", $errors);
        }

    } else {
        $redirect_message = "Nie podano adresu e-mail.";
    }
} else {
    // Jeśli nie jest to żądanie POST
    $redirect_message = "Nieprawidłowe żądanie.";
}

// Przekierowanie z powrotem na stronę zarządzania dozwolonymi e-mailami
header("Location: " . BASE_PATH . "/admin/manage_allowed_emails.php?email_action_status=" . $redirect_status . "&email_action_message=" . urlencode($redirect_message));
exit();
?>