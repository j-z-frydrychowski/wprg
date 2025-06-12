<?php

$page_title = 'Panel - Zarządzaj Nutami i Kategoriami';
$page_alerts = []; // Inicjalizacja alertów

//Odczytanie komunikatów o statusie usuwania nut
if (isset($_GET['delete_status']) && isset($_GET['delete_message'])) {
    $status_type = 'info';
    if ($_GET['delete_status'] === 'success') {
        $status_type = 'success';
    } elseif ($_GET['delete_status'] === 'error') {
        $status_type = 'error';
    } elseif ($_GET['delete_status'] === 'warning') {
        $status_type = 'warning';
    }
    $page_alerts[] = [
        'type' => $status_type,
        'message' => htmlspecialchars($_GET['delete_message'])
    ];
}

if (isset($_GET['category_action_status']) && isset($_GET['category_action_message'])) {
    $page_alerts[] = [
        'type' => htmlspecialchars($_GET['category_action_status']),
        'message' => htmlspecialchars($_GET['category_action_message'])
    ];
}


require_once '../inc/templates/header.php';

// Autoryzacja
$can_access_page = authorize_user_access(['admin', 'bibliotekarz', 'zarzad']);

// Inicjalizacja zmiennych na dane z bazy
$nuty = [];
$kategorie = [];
$db_error = null; // Ogólna zmienna na błędy DB dla tej strony

if ($can_access_page) { // Pobieraj dane tylko jeśli użytkownik ma dostęp
    try {
        $pdo = getPdoConnection();

        // Pobierz listę nut
        $stmt_nuty = $pdo->query("SELECT id, tytul, autor, kategoria, plik FROM nuty ORDER BY tytul ASC");
        $nuty = $stmt_nuty->fetchAll(PDO::FETCH_ASSOC);

        // Pobierz listę kategorii
        $stmt_kategorie = $pdo->query("SELECT id, nazwa FROM kategorie ORDER BY nazwa ASC");
        $kategorie = $stmt_kategorie->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $db_error = "Wystąpił problem podczas ładowania danych.";
        error_log("Błąd PDO w admin/manage_nuty.php: " . $e->getMessage());
        $page_alerts[] = ['type' => 'error', 'message' => $db_error];
    }
}

?>

<?php if ($can_access_page): // Wyświetlaj treść tylko dla uprawnionych ?>

    <p><a href="add_nuty.php">Dodaj Nową Nutę</a></p>
    <hr>

    <?php // Sekcja zarządzania nutami ?>
    <div>
        <h2>Lista Nut</h2>
        <?php if ($db_error && empty($nuty)): // Pokaż błąd tylko jeśli wystąpił i nie ma danych ?>
            <p>Błąd ładowania listy nut.</p>
        <?php elseif (empty($nuty)): ?>
            <p>Brak nut w bibliotece.</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Tytuł</th>
                    <th>Autor</th>
                    <th>Kategoria</th>
                    <th>Plik</th>
                    <th>Akcje</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($nuty as $nuta_item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($nuta_item['id']); ?></td>
                        <td><?php echo htmlspecialchars($nuta_item['tytul']); ?></td>
                        <td><?php echo htmlspecialchars($nuta_item['autor']); ?></td>
                        <td><?php echo htmlspecialchars($nuta_item['kategoria']); ?></td>
                        <td><a href="<?php echo BASE_PATH . '/public/' . htmlspecialchars($nuta_item['plik']); ?>" target="_blank">Podgląd</a></td>
                        <td>
                            <a href="edit_nuty.php?id=<?php echo htmlspecialchars($nuta_item['id']); ?>" class="button">Edytuj</a>

                            <form method="post" action="process_delete_nuty.php" style="display:inline;" onsubmit="return confirm('Czy na pewno chcesz usunąć tę nutę?');">
                                <input type="hidden" name="delete_nuta_id" value="<?php echo $nuta_item['id']; ?>">
                                <button type="submit" class="button-danger">Usuń</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <hr>

    <?php // Sekcja zarządzania kategoriami ?>
    <div>
        <h2>Zarządzanie Kategoriami</h2>

        <div>
            <h3>Istniejące Kategorie</h3>
            <?php if ($db_error && empty($kategorie)): ?>
                <p>Błąd ładowania listy kategorii.</p>
            <?php elseif (empty($kategorie)): ?>
                <p>Brak zdefiniowanych kategorii.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($kategorie as $kategoria_item): ?>
                        <li>
                            <?php echo htmlspecialchars($kategoria_item['nazwa']); ?>
                            (ID: <?php echo htmlspecialchars($kategoria_item['id']); ?>)
                            <form method="post" action="process_delete_kategoria.php" style="display:inline; margin-left: 10px;" onsubmit="return confirm('Czy na pewno chcesz usunąć tę kategorię? UWAGA: Sprawdź, czy żadne nuty nie są do niej przypisane!');">
                                <input type="hidden" name="delete_kategoria_id" value="<?php echo $kategoria_item['id']; ?>">
                                <button type="submit" style="background:none; border:none; padding:0; color:red; text-decoration:underline; cursor:pointer;">Usuń</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div>
            <h3>Dodaj Nową Kategorię</h3>
            <form action="process_add_kategoria.php" method="post">
                <div>
                    <label for="nazwa_kategorii">Nazwa nowej kategorii:</label><br>
                    <input type="text" id="nazwa_kategorii" name="nazwa" required>
                </div>
                <br>
                <button type="submit">Dodaj Kategorię</button>
            </form>
        </div>
    </div>

<?php endif;?>

<?php
// Dołączenie globalnej stopki
require_once '../inc/templates/footer.php';
?>