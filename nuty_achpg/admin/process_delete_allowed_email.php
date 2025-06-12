<?php
// admin/process_delete_allowed_email.php
session_start();

require_once '../inc/config.php'; // Dla BASE_PATH i stałych DB
require_once '../inc/functions.php'; // Dla getPdoConnection()

// Domyślne wartości dla przekierowania
$redirect_status = 'error';
$redirect_message = 'Wystąpił nieoczekiwany błąd podczas próby usunięcia adresu e-mail.';

// Autoryzacja
$can_manage_emails = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'zarzad'])) {
    $can_manage_emails = true;
}

if (!$can_manage_emails) {
    $redirect_message = 'Nie masz uprawnień do wykonania tej akcji.';
    header("Location: " . BASE_PATH . "/admin/manage_allowed_emails.php?email_action_status=" . $redirect_status . "&email_action_message=" . urlencode($redirect_message));
    exit();
}

// Sprawdzenie, czy żądanie jest typu POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_email_id'])) {
        $email_id_to_delete = filter_input(INPUT_POST, 'delete_email_id', FILTER_SANITIZE_NUMBER_INT);

        if ($email_id_to_delete !== false && $email_id_to_delete !== null && $email_id_to_delete > 0) {
            try {
                $pdo = getPdoConnection();

                // Sprawdzenie czy adres email z ID istnieje
                $stmt_fetch = $pdo->prepare("SELECT email FROM allowed_emails WHERE id = :id");
                $stmt_fetch->bindParam(':id', $email_id_to_delete, PDO::PARAM_INT);
                $stmt_fetch->execute();
                $email_data = $stmt_fetch->fetch(PDO::FETCH_ASSOC);

                if ($email_data) {
                    $email_address = $email_data['email'];
                    // Usuń adres e-mail z bazy
                    $stmt_delete = $pdo->prepare("DELETE FROM allowed_emails WHERE id = :id");
                    $stmt_delete->bindParam(':id', $email_id_to_delete, PDO::PARAM_INT);

                    if ($stmt_delete->execute()) {
                        if ($stmt_delete->rowCount() > 0) {
                            $redirect_status = 'success';
                            $redirect_message = "Adres e-mail " . htmlspecialchars($email_address) . " został pomyślnie usunięty z listy dozwolonych.";
                        } else {
                            $redirect_status = 'error';
                            $redirect_message = "Nie znaleziono adresu e-mail o podanym ID (" . htmlspecialchars($email_id_to_delete) . ") lub został on już wcześniej usunięty.";
                        }
                    } else {
                        $redirect_status = 'error';
                        $redirect_message = "Nie udało się usunąć adresu e-mail z bazy danych.";
                    }
                } else {
                    $redirect_status = 'error';
                    $redirect_message = "Nie znaleziono adresu e-mail o podanym ID (" . htmlspecialchars($email_id_to_delete) . ").";
                }

            } catch (PDOException $e) {
                error_log("Błąd PDO w process_delete_allowed_email.php (ID: $email_id_to_delete): " . $e->getMessage());
                $redirect_message = "Wystąpił błąd serwera podczas usuwania adresu e-mail.";
            }
        } else {
            $redirect_message = "Nieprawidłowe ID adresu e-mail.";
        }
    } else {
        $redirect_message = "Nie podano ID adresu e-mail do usunięcia.";
    }
} else {
    // Jeśli nie jest to żądanie POST
    $redirect_message = "Nieprawidłowe żądanie.";
}

// Przekierowanie z powrotem na stronę zarządzania e-mailami
header("Location: " . BASE_PATH . "/admin/manage_allowed_emails.php?email_action_status=" . $redirect_status . "&email_action_message=" . urlencode($redirect_message));
exit();
?>