<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../inc/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $autor = trim($_POST['autor']);
    $tytul = trim($_POST['tytul']);
    $kategoria = trim($_POST['kategoria']);

    // Obsługa przesłanego pliku
    if (isset($_FILES['plik']) && $_FILES['plik']['error'] === UPLOAD_ERR_OK) {
        $plik_nazwa = $_FILES['plik']['name'];
        $plik_tmp_nazwa = $_FILES['plik']['tmp_name'];
        $plik_rozszerzenie = strtolower(pathinfo($plik_nazwa, PATHINFO_EXTENSION));

        $dozwolone_rozszerzenia = ['pdf'];
        $maksymalny_rozmiar = 10 * 1024 * 1024; // 10 MB

        if (in_array($plik_rozszerzenie, $dozwolone_rozszerzenia)) {
            if ($_FILES['plik']['size'] <= $maksymalny_rozmiar) {
                $nazwa_pliku_serwer = 'nuty_' . time() . '_' . uniqid() . '.' . $plik_rozszerzenie;
                $sciezka_docelowa = '../public/nuty_pdf/' . $nazwa_pliku_serwer;
                $sciezka_bazy_danych = 'nuty_pdf/' . $nazwa_pliku_serwer; // Ścieżka do zapisania w bazie danych

                if (!move_uploaded_file($plik_tmp_nazwa, $sciezka_docelowa)) {
                    error_log("Błąd podczas przenoszenia pliku (wersja oryginalna): " . print_r(error_get_last(), true), 0);
                    header("Location: add_nuty.php?error=" . urlencode("Wystąpił problem z zapisaniem pliku na serwerze (wersja oryginalna). Sprawdź logi serwera."));
                    exit();
                }
            } else {
                header("Location: add_nuty.php?error=" . urlencode("Plik jest zbyt duży. Maksymalny rozmiar to 10 MB."));
                exit();
            }
        } else {
            header("Location: add_nuty.php?error=" . urlencode("Dozwolone są tylko pliki PDF."));
            exit();
        }
    } else {
        header("Location: add_nuty.php?error=" . urlencode("Nie przesłano pliku lub wystąpił błąd podczas przesyłania. Kod błędu: " . $_FILES['plik']['error']));
        exit();
    }

    if (empty($tytul) || empty($kategoria) || empty($sciezka_bazy_danych)) {
        header("Location: add_nuty.php?error=" . urlencode("Tytuł, kategoria i plik są wymagane."));
        exit();
    }

    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASSWORD, DB_OPTIONS);
        $stmt = $pdo->prepare("INSERT INTO nuty (autor, tytul, kategoria, plik) VALUES (:autor, :tytul, :kategoria, :plik)");
        $stmt->bindParam(':autor', $autor, PDO::PARAM_STR);
        $stmt->bindParam(':tytul', $tytul, PDO::PARAM_STR);
        $stmt->bindParam(':kategoria', $kategoria, PDO::PARAM_STR);
        $stmt->bindParam(':plik', $sciezka_bazy_danych, PDO::PARAM_STR);
        $stmt->execute();

        header("Location: index.php?nuty_added=1");
        exit();

    } catch (PDOException $e) {
        header("Location: add_nuty.php?error=" . urlencode("Wystąpił problem z dodaniem nuty do bazy danych: " . $e->getMessage()));
        exit();
    }

} else {
    header("Location: add_nuty.php");
    exit();
}
?>