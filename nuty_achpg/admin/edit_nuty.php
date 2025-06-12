<?php
// admin/edit_nuta.php

$page_title = 'Panel - Edytuj Nuty';
$page_alerts = []; // Inicjalizacja alertów

require_once '../inc/templates/header.php'; // Nagłówek (sesja, config, funkcje, podstawowa autoryzacja)

// Zmienne inicjalizacyjne
$nuta_data = null;
$db_kategorie = [];
$can_access_page = false;
$form_visible = false; // Czy formularz powinien być widoczny

// Autoryzacja specyficzna dla tej strony
if (isset($user_role) && in_array($user_role, ['admin', 'bibliotekarz', 'zarzad'])) {
    $can_access_page = true;
} else {
    $page_alerts[] = ['type' => 'error', 'message' => 'Nie masz uprawnień do edycji nut.'];
}

if ($can_access_page) {
    // Sprawdzenie ID nut z GET
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $nuta_id = (int)$_GET['id'];

        try {
            $pdo = getPdoConnection();

            // 1. Pobierz dane edytowanych nut
            $stmt_nuta = $pdo->prepare("SELECT id, tytul, autor, kategoria, plik FROM nuty WHERE id = :id");
            $stmt_nuta->bindParam(':id', $nuta_id, PDO::PARAM_INT);
            $stmt_nuta->execute();
            $nuta_data = $stmt_nuta->fetch(PDO::FETCH_ASSOC);

            if ($nuta_data) {
                // 2. Pobierz listę kategorii dla dropdownu
                $stmt_kategorie = $pdo->query("SELECT id, nazwa FROM kategorie ORDER BY nazwa ASC");
                $db_kategorie = $stmt_kategorie->fetchAll(PDO::FETCH_ASSOC);
                $form_visible = true; // Mamy dane nut i kategorie, pokaż formularz
            } else {
                $page_alerts[] = ['type' => 'error', 'message' => 'Nie znaleziono nuty o podanym ID.'];
            }

        } catch (PDOException $e) {
            error_log("Błąd PDO w admin/edit_nuty.php (pobieranie danych): " . $e->getMessage());
            $page_alerts[] = ['type' => 'error', 'message' => 'Wystąpił błąd serwera podczas ładowania danych nuty.'];
        }
    } else {
        $page_alerts[] = ['type' => 'error', 'message' => 'Nieprawidłowe lub brakujące ID nuty do edycji.'];
    }
}

?>

<?php if ($form_visible && $can_access_page): // Wyświetl formularz tylko jeśli mamy dane i uprawnienia ?>
    <p><a href="manage_nuty.php">Powrót do zarządzania nutami</a></p>

    <h2>Edytuj Nutę: <?php echo htmlspecialchars($nuta_data['tytul']); ?></h2>
    <form action="process_edit_nuty.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="nuta_id" value="<?php echo htmlspecialchars($nuta_data['id']); ?>">

        <div>
            <label for="tytul">Tytuł:</label><br>
            <input type="text" id="tytul" name="tytul" value="<?php echo htmlspecialchars($nuta_data['tytul']); ?>" required>
        </div>
        <br>
        <div>
            <label for="autor">Autor:</label><br>
            <input type="text" id="autor" name="autor" value="<?php echo htmlspecialchars($nuta_data['autor']); ?>">
        </div>
        <br>
        <div>
            <label for="kategoria">Kategoria:</label><br>
            <select id="kategoria" name="kategoria" required>
                <option value="">Wybierz kategorię</option>
                <?php foreach ($db_kategorie as $kategoria_item): ?>
                    <option value="<?php echo htmlspecialchars($kategoria_item['nazwa']); ?>"
                        <?php if ($nuta_data['kategoria'] === $kategoria_item['nazwa']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($kategoria_item['nazwa']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <br>
        <div>
            <p>Obecny plik:
                <a href="<?php echo BASE_PATH . '/' . htmlspecialchars($nuta_data['plik']); ?>" target="_blank">
                    <?php echo htmlspecialchars(basename($nuta_data['plik'])); // Pokaż tylko nazwę pliku ?>
                </a>
            </p>
            <label for="plik_nowy">Zastąp plik PDF (opcjonalnie):</label><br>
            <input type="file" id="plik_nowy" name="plik_nowy" accept=".pdf">
            <small>Pozostaw puste, jeśli nie chcesz zmieniać obecnego pliku. Dozwolone tylko pliki PDF.</small>
        </div>
        <br>
        <button type="submit" name="submit_edit_nuty">Zapisz Zmiany</button>
    </form>
<?php else: // Jeśli $form_visible jest false (np. błąd, brak ID, brak uprawnień) ?>
    <?php if ($can_access_page): // Jeśli błąd dotyczył danych, a nie uprawnień ?>
        <p><a href="manage_nuty.php">Powrót do zarządzania nutami</a></p>
    <?php endif; ?>
    <?php // Komunikaty o błędach ($page_alerts) są już wyświetlane przez header.php ?>
<?php endif; ?>

<?php
require_once '../inc/templates/footer.php'; // Dołączenie globalnej stopki
?>