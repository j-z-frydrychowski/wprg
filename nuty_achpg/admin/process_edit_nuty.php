<?php
// admin/process_edit_nuty.php
session_start();

require_once '../inc/config.php'; // Dla BASE_PATH i stałych DB
require_once '../inc/functions.php'; // Dla getPdoConnection()

// Domyślne wartości dla przekierowania
$redirect_page = 'manage_nuty.php'; // Domyślnie wracamy do listy nut
$redirect_params = ['status' => 'error', 'message' => 'Wystąpił nieoczekiwany błąd.'];

// Autoryzacja
$can_edit_nuty = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'bibliotekarz', 'zarzad'])) {
    $can_edit_nuty = true;
}

if (!$can_edit_nuty) {
    $redirect_params['message'] = 'Nie masz uprawnień do wykonania tej akcji.';
    header("Location: " . BASE_PATH . "/admin/" . $redirect_page . "?" . http_build_query($redirect_params));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Walidacja i pobranie ID nut
    if (!isset($_POST['nuta_id']) || !is_numeric($_POST['nuta_id'])) {
        $redirect_params['message'] = 'Brakujące lub nieprawidłowe ID nuty.';
        header("Location: " . BASE_PATH . "/admin/" . $redirect_page . "?" . http_build_query($redirect_params));
        exit();
    }
    $nuta_id = (int)$_POST['nuta_id'];
    $redirect_page = "edit_nuta.php"; // W razie błędu, wracamy do formularza edycji
    $redirect_params['id'] = $nuta_id; // Dodajemy ID do parametrów przekierowania

    // Pobranie i walidacja pozostałych danych
    $tytul = isset($_POST['tytul']) ? trim($_POST['tytul']) : '';
    $autor = isset($_POST['autor']) ? trim($_POST['autor']) : ''; // Autor może być pusty
    $kategoria_nazwa = isset($_POST['kategoria']) ? trim($_POST['kategoria']) : '';

    $errors = [];

    if (empty($tytul)) {
        $errors[] = "Tytuł jest wymagany.";
    } elseif (strlen($tytul) > 255) {
        $errors[] = "Tytuł jest zbyt długi.";
    }

    if (strlen($autor) > 255) {
        $errors[] = "Nazwa autora jest zbyt długa.";
    }

    if (empty($kategoria_nazwa)) {
        $errors[] = "Kategoria jest wymagana.";
    } else {
        // Walidacja, czy wybrana kategoria istnieje w bazie
        try {
            $pdo_check_cat = getPdoConnection();
            $stmt_check_cat = $pdo_check_cat->prepare("SELECT COUNT(*) FROM kategorie WHERE nazwa = :nazwa");
            $stmt_check_cat->bindParam(':nazwa', $kategoria_nazwa, PDO::PARAM_STR);
            $stmt_check_cat->execute();
            if ($stmt_check_cat->fetchColumn() == 0) {
                $errors[] = "Wybrana kategoria jest nieprawidłowa lub nie istnieje.";
            }
        } catch (PDOException $e) {
            error_log("Błąd PDO przy sprawdzaniu kategorii w process_edit_nuta.php: " . $e->getMessage());
            $errors[] = "Błąd serwera podczas weryfikacji kategorii.";
        }
    }

    $nowa_sciezka_pliku_db = null; // Scieżka nowego pliku gdy wybrany
    $stara_sciezka_pliku_fizyczna = null; // Ścieżka do starego pliku na serwerze, do usunięcia

    // Obsługa opcjonalnej podmiany pliku PDF
    if (isset($_FILES['plik_nowy']) && $_FILES['plik_nowy']['error'] === UPLOAD_ERR_OK) {
        $plik_tmp_nazwa = $_FILES['plik_nowy']['tmp_name'];
        $plik_oryginalna_nazwa = $_FILES['plik_nowy']['name'];
        $plik_rozmiar = $_FILES['plik_nowy']['size'];
        $plik_rozszerzenie = strtolower(pathinfo($plik_oryginalna_nazwa, PATHINFO_EXTENSION));

        $dozwolone_rozszerzenia = ['pdf'];
        $maksymalny_rozmiar_pliku = 10 * 1024 * 1024; // 10 MB

        if (!in_array($plik_rozszerzenie, $dozwolone_rozszerzenia)) {
            $errors[] = "Niedozwolone rozszerzenie pliku. Akceptowane są tylko pliki PDF.";
        }
        if ($plik_rozmiar > $maksymalny_rozmiar_pliku) {
            $errors[] = "Plik jest zbyt duży. Maksymalny rozmiar to 10MB.";
        }

        if (empty($errors)) { // Jeśli nie ma błędów walidacji pliku
            try {
                //Pobranie scieżki starego pliku do usunięcia
                $pdo_get_old_file = getPdoConnection();
                $stmt_old_file = $pdo_get_old_file->prepare("SELECT plik FROM nuty WHERE id = :id");
                $stmt_old_file->bindParam(':id', $nuta_id, PDO::PARAM_INT);
                $stmt_old_file->execute();
                $old_file_result = $stmt_old_file->fetch(PDO::FETCH_ASSOC);
                if ($old_file_result && !empty($old_file_result['plik'])) {
                    $stara_sciezka_pliku_fizyczna = '../public/' . $old_file_result['plik'];
                }
            } catch (PDOException $e) {
                error_log("Błąd PDO przy pobieraniu starej ścieżki pliku w process_edit_nuta.php: " . $e->getMessage());
            }

            // Generuj nową unikalną nazwę i ścieżki
            $nazwa_pliku_serwer = 'nuty_' . time() . '_' . uniqid('', true) . '.' . $plik_rozszerzenie;
            $sciezka_docelowa_fizyczna = '../public/nuty_pdf/' . $nazwa_pliku_serwer;
            $nowa_sciezka_pliku_db = 'nuty_pdf/' . $nazwa_pliku_serwer; // Ścieżka do zapisu w bazie

            if (!move_uploaded_file($plik_tmp_nazwa, $sciezka_docelowa_fizyczna)) {
                $errors[] = "Wystąpił problem z zapisaniem nowego pliku na serwerze.";
                $nowa_sciezka_pliku_db = null; // Nie udało się przenieść, więc nie aktualizuj ścieżki w DB
            }
        }
    } elseif (isset($_FILES['plik_nowy']) && $_FILES['plik_nowy']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Inny błąd uploadu niż "brak pliku"
        $errors[] = "Wystąpił błąd podczas przesyłania pliku. Kod błędu: " . $_FILES['plik_nowy']['error'];
    }


    if (empty($errors)) {
        try {
            $pdo = getPdoConnection();

            $sql_update_parts = [];
            $params_to_bind = [];

            $sql_update_parts[] = "tytul = :tytul";
            $params_to_bind[':tytul'] = $tytul;

            $sql_update_parts[] = "autor = :autor";
            $params_to_bind[':autor'] = $autor;

            $sql_update_parts[] = "kategoria = :kategoria";
            $params_to_bind[':kategoria'] = $kategoria_nazwa;

            if ($nowa_sciezka_pliku_db !== null) { // Jeśli nowy plik został pomyślnie przesłany
                $sql_update_parts[] = "plik = :plik";
                $params_to_bind[':plik'] = $nowa_sciezka_pliku_db;
            }

            $params_to_bind[':id'] = $nuta_id;

            $sql = "UPDATE nuty SET " . implode(", ", $sql_update_parts) . " WHERE id = :id";
            $stmt_update = $pdo->prepare($sql);

            if ($stmt_update->execute($params_to_bind)) {
                if ($nowa_sciezka_pliku_db !== null && $stara_sciezka_pliku_fizyczna !== null && file_exists($stara_sciezka_pliku_fizyczna)) {
                    if (!unlink($stara_sciezka_pliku_fizyczna)) {
                        error_log("Nie udało się usunąć starego pliku PDF: " . $stara_sciezka_pliku_fizyczna . " po aktualizacji nuty ID: " . $nuta_id);
                    }
                }
                $redirect_params['status'] = 'success';
                $redirect_params['message'] = 'Dane nuty zostały pomyślnie zaktualizowane.';
                $redirect_page = 'manage_nuty.php'; // Po sukcesie wracamy do listy
                unset($redirect_params['id']); // Nie potrzebujemy ID w URL listy
            } else {
                $redirect_params['message'] = 'Nie udało się zaktualizować danych nuty w bazie.';
            }

        } catch (PDOException $e) {
            error_log("Błąd PDO w process_edit_nuta.php (aktualizacja): " . $e->getMessage());
            $redirect_params['message'] = 'Wystąpił błąd serwera podczas aktualizacji danych.';
        }
    } else {
        // Jeśli były błędy walidacji
        $redirect_params['message'] = implode(" ", $errors);
    }

} else {
    $redirect_params['message'] = 'Nieprawidłowe żądanie.';
    $redirect_page = 'manage_nuty.php'; // Jeśli nie POST, wracamy do listy
    unset($redirect_params['id']);
}

header("Location: " . BASE_PATH . "/admin/" . $redirect_page . "?" . http_build_query($redirect_params));
exit();
?>