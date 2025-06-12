<?php

session_start();
require_once '../inc/config.php'; // Dla BASE_PATH
require_once '../inc/functions.php'; // Dla getPdoConnection()

// Autoryzacja
$can_delete = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'zarzad', 'bibliotekarz'])) {
    $can_delete = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_nuta_id']) && $can_delete) {
    $nuta_id_do_usuniecia = filter_input(INPUT_POST, 'delete_nuta_id', FILTER_SANITIZE_NUMBER_INT);

    $delete_status = 'error'; // Domyślny status
    $delete_message = 'Wystąpił nieoczekiwany błąd.'; // Domyślny komunikat

    if ($nuta_id_do_usuniecia !== false && $nuta_id_do_usuniecia !== null) {
        try {
            $pdo = getPdoConnection();

            // Pobierz ścieżkę pliku PDF przed usunięciem rekordu
            $stmt_select = $pdo->prepare("SELECT plik FROM nuty WHERE id = :id");
            $stmt_select->bindParam(':id', $nuta_id_do_usuniecia, PDO::PARAM_INT);
            $stmt_select->execute();
            $result = $stmt_select->fetch(PDO::FETCH_ASSOC);

            if ($result && !empty($result['plik'])) {
                $sciezka_fizyczna_pliku = '../public/' . $result['plik'];

                // Usuń rekord z bazy danych
                $stmt_delete = $pdo->prepare("DELETE FROM nuty WHERE id = :id");
                $stmt_delete->bindParam(':id', $nuta_id_do_usuniecia, PDO::PARAM_INT);
                $stmt_delete->execute();

                if ($stmt_delete->rowCount() > 0) {
                    if (file_exists($sciezka_fizyczna_pliku)) {
                        if (unlink($sciezka_fizyczna_pliku)) {
                            $delete_status = 'success';
                            $delete_message = "Nuta została pomyślnie usunięta.";
                        } else {
                            $delete_status = 'warning';
                            $delete_message = "Nuta została usunięta z bazy, ale wystąpił problem z usunięciem pliku PDF. Sprawdź uprawnienia.";
                            error_log("Nie udało się usunąć pliku: " . $sciezka_fizyczna_pliku . " dla nuty ID: " . $nuta_id_do_usuniecia);
                        }
                    } else {
                        $delete_status = 'warning';
                        $delete_message = "Nuta została usunięta z bazy, ale plik PDF ('" . htmlspecialchars($result['plik']) . "') nie został znaleziony na serwerze.";
                        error_log("Plik PDF do usunięcia nie istnieje: " . $sciezka_fizyczna_pliku . " dla nuty ID: " . $nuta_id_do_usuniecia);
                    }
                } else {
                    $delete_status = 'error';
                    $delete_message = "Nie udało się usunąć nuty z bazy danych (rekord mógł już nie istnieć lub ID było nieprawidłowe).";
                }
            } else {
                $delete_status = 'error';
                $delete_message = "Nie znaleziono nuty o podanym ID (" . htmlspecialchars($nuta_id_do_usuniecia) . ") lub brak informacji o pliku.";
            }
        } catch (PDOException $e) {
            $delete_status = 'error';
            $delete_message = "Wystąpił problem z bazą danych podczas usuwania nuty.";
            error_log("Błąd PDO przy usuwaniu nuty (ID: " . $nuta_id_do_usuniecia . "): " . $e->getMessage());
        }
    } else {
        $delete_status = 'error';
        $delete_message = "Nieprawidłowe lub brakujące ID nuty do usunięcia.";
    }

    header("Location: manage_nuty.php?delete_status=" . $delete_status . "&delete_message=" . urlencode($delete_message));
    exit();

} elseif (!$can_delete && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Próba usunięcia bez uprawnień
    header("Location: index.php?error_message=" . urlencode("Brak uprawnień do wykonania tej akcji."));
    exit();
} else {
    // Bezpośredni dostęp GET lub inne nieprawidłowe żądanie
    header("Location: index.php");
    exit();
}
?>