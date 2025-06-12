<?php

session_start();

require_once '../inc/config.php'; // Dla BASE_PATH i stałych DB
require_once '../inc/functions.php'; // Dla getPdoConnection()

$redirect_status = 'error'; // Domyślnie błąd
$redirect_message = 'Wystąpił nieoczekiwany błąd podczas próby usunięcia kategorii.';

// Autoryzacja
$can_delete_category = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'bibliotekarz', 'zarzad'])) {
    $can_delete_category = true;
}

if (!$can_delete_category) {
    $redirect_message = 'Nie masz uprawnień do wykonania tej akcji.';
    header("Location: " . BASE_PATH . "/admin/manage_nuty.php?category_action_status=" . $redirect_status . "&category_action_message=" . urlencode($redirect_message));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_kategoria_id'])) {
        $kategoria_id = filter_input(INPUT_POST, 'delete_kategoria_id', FILTER_SANITIZE_NUMBER_INT);

        if ($kategoria_id !== false && $kategoria_id !== null && $kategoria_id > 0) {
            try {
                $pdo = getPdoConnection();

                // 1. Pobieramy nazwe kategorii do usunięcia
                $stmt_get_name = $pdo->prepare("SELECT nazwa FROM kategorie WHERE id = :id");
                $stmt_get_name->bindParam(':id', $kategoria_id, PDO::PARAM_INT);
                $stmt_get_name->execute();
                $kategoria_data = $stmt_get_name->fetch(PDO::FETCH_ASSOC);

                if ($kategoria_data) {
                    $nazwa_kategorii_do_usuniecia = $kategoria_data['nazwa'];

                    // 2. Sprawdź, czy kategoria jest używana w tabeli 'nuty'
                    $stmt_check_usage = $pdo->prepare("SELECT COUNT(*) FROM nuty WHERE kategoria = :nazwa_kategorii");
                    $stmt_check_usage->bindParam(':nazwa_kategorii', $nazwa_kategorii_do_usuniecia, PDO::PARAM_STR);
                    $stmt_check_usage->execute();

                    if ($stmt_check_usage->fetchColumn() > 0) {
                        // Kategoria jest w użyciu, nie można usunąć
                        $redirect_status = 'error';
                        $redirect_message = "Nie można usunąć kategorii '" . htmlspecialchars($nazwa_kategorii_do_usuniecia) . "', ponieważ jest przypisana do istniejących nut. Najpierw zmień kategorię tych nut.";
                    } else {
                        // Kategoria nie jest używana, można usunąć
                        $stmt_delete = $pdo->prepare("DELETE FROM kategorie WHERE id = :id");
                        $stmt_delete->bindParam(':id', $kategoria_id, PDO::PARAM_INT);

                        if ($stmt_delete->execute() && $stmt_delete->rowCount() > 0) {
                            $redirect_status = 'success';
                            $redirect_message = "Kategoria " . htmlspecialchars($nazwa_kategorii_do_usuniecia) . " została pomyślnie usunięta.";
                        } else {
                            $redirect_status = 'error';
                            $redirect_message = "Nie udało się usunąć kategorii (być może została już usunięta lub ID jest nieprawidłowe).";
                        }
                    }
                } else {
                    $redirect_status = 'error';
                    $redirect_message = "Nie znaleziono kategorii o podanym ID.";
                }
            } catch (PDOException $e) {
                error_log("Błąd PDO w process_delete_kategoria.php (ID: $kategoria_id): " . $e->getMessage());
                $redirect_message = "Wystąpił błąd serwera podczas usuwania kategorii.";
            }
        } else {
            $redirect_message = "Nieprawidłowe ID kategorii.";
        }
    } else {
        $redirect_message = "Nie podano ID kategorii do usunięcia.";
    }
} else {
    $redirect_message = "Nieprawidłowe żądanie.";
}

header("Location: " . BASE_PATH . "/admin/manage_nuty.php?category_action_status=" . $redirect_status . "&category_action_message=" . urlencode($redirect_message));
exit();
?>