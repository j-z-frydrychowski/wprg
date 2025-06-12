<?php
// admin/add_nuty.php

$page_title = 'Panel - Dodaj Nuty';
$page_alerts = [];

// Odczytanie ewentualnych komunikatów z GET (jeśli były przekazane)
if (isset($_GET['status']) && isset($_GET['message'])) {
    $page_alerts[] = [
        'type' => htmlspecialchars($_GET['status']),
        'message' => htmlspecialchars($_GET['message'])
    ];
}

// Dołączenie globalnego nagłówka (sesja, config, funkcje, autoryzacja podstawowa, początek HTML)
require_once '../inc/templates/header.php';

// Autoryzacja specyficzna dla tej strony (admin, bibliotekarz, zarzad)
$can_access_page = false;
$db_kategorie = []; // Inicjalizacja tablicy kategorii
$db_error_kategorie = null; // Błąd ładowania kategorii

if (isset($user_role) && in_array($user_role, ['admin', 'bibliotekarz', 'zarzad'])) {
    $can_access_page = true;

    //pobieranie kategorii z bazy danych
    try {
        $pdo = getPdoConnection();
        $stmt_kategorie = $pdo->query("SELECT id, nazwa FROM kategorie ORDER BY nazwa ASC");
        $db_kategorie = $stmt_kategorie->fetchAll(PDO::FETCH_ASSOC);

        if (empty($db_kategorie)) {
            $page_alerts[] = ['type' => 'info', 'message' => 'Nie zdefiniowano jeszcze żadnych kategorii. Dodaj je w panelu zarządzania nutami/kategoriami.'];
        }

    } catch (PDOException $e) {
        $db_error_kategorie = "Wystąpił problem podczas ładowania kategorii.";
        error_log("Błąd PDO w admin/add_nuty.php (kategorie): " . $e->getMessage());
        $page_alerts[] = ['type' => 'error', 'message' => $db_error_kategorie];
        $can_access_page = false;
    }

} else {
    $page_alerts[] = ['type' => 'error', 'message' => 'Nie masz uprawnień do dodawania nut.'];
}

?>

<?php if ($can_access_page): // Wyświetlaj treść tylko dla uprawnionych i jeśli nie było błędu ładowania kategorii ?>
    <p><a href="manage_nuty.php">Powrót do zarządzania nutami</a></p>

    <h2>Dodaj Nową Nutę</h2>
    <form action="process_add_nuty.php" method="post" enctype="multipart/form-data">
        <div>
            <label for="autor">Autor:</label><br>
            <input type="text" id="autor" name="autor">
        </div>
        <br>
        <div>
            <label for="tytul">Tytuł:</label><br>
            <input type="text" id="tytul" name="tytul" required>
        </div>
        <br>
        <div>
            <label for="kategoria">Kategoria:</label><br>
            <select id="kategoria" name="kategoria" required <?php if (empty($db_kategorie)) echo 'disabled'; /* Wyłącz select, jeśli nie ma kategorii */ ?>>
                <option value="">Wybierz kategorię</option>
                <?php //Pętla po kategoriach z bazy danych >> ?>
                <?php foreach ($db_kategorie as $kategoria_item): ?>
                    <option value="<?php echo htmlspecialchars($kategoria_item['nazwa']); ?>">
                        <?php echo htmlspecialchars($kategoria_item['nazwa']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (empty($db_kategorie) && !$db_error_kategorie): ?>
                <small>Brak dostępnych kategorii. Dodaj je w panelu zarządzania.</small>
            <?php endif; ?>
        </div>
        <br>
        <div>
            <label for="plik">Plik PDF z nutami:</label><br>
            <input type="file" id="plik" name="plik" accept=".pdf" required>
            <small>Dozwolone tylko pliki PDF.</small>
        </div>
        <br>
        <button type="submit" name="submit_nuty" <?php if (empty($db_kategorie)) echo 'disabled'; /* Wyłącz przycisk, jeśli nie ma kategorii */ ?>>Dodaj Pozycje</button>
    </form>
<?php endif;?>

<?php
// Dołączenie globalnej stopki
require_once '../inc/templates/footer.php';
?>