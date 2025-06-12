<?php
// admin/manage_allowed_emails.php

$page_title = 'Panel - Zarządzaj Dozwolonymi E-mailami';
$page_alerts = []; // Inicjalizacja alertów dla tej strony

// Odczytanie komunikatów z GET (np. po dodaniu/usunięciu e-maila lub po imporcie)
if (isset($_GET['status']) && isset($_GET['message'])) {
    $page_alerts[] = [
        'type' => htmlspecialchars($_GET['status']), // Oczekiwane 'success', 'error', 'info'
        'message' => htmlspecialchars($_GET['message'])
    ];
}

require_once '../inc/templates/header.php'; // Dołączenie globalnego nagłówka

// Autoryzacja sprawdzenie czy admin lub zarzad
$can_access_page = false;
if (isset($user_role) && in_array($user_role, ['admin', 'zarzad'])) {
    $can_access_page = true;
} else {
    $page_alerts[] = ['type' => 'error', 'message' => 'Nie masz uprawnień dostępu do tej sekcji.'];
}

$allowed_emails_list = []; // Inicjalizacja listy
$db_fetch_error = null;

if ($can_access_page) {
    try {
        $pdo = getPdoConnection();
        $stmt = $pdo->query("SELECT id, email FROM allowed_emails ORDER BY email ASC");
        $allowed_emails_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $db_fetch_error = "Wystąpił problem podczas ładowania listy dozwolonych adresów e-mail.";
        error_log("Błąd PDO w admin/manage_allowed_emails.php: " . $e->getMessage());
        $page_alerts[] = ['type' => 'error', 'message' => $db_fetch_error];
    }
}

?>

<?php if ($can_access_page): ?>

    <p><a href="index.php">Powrót do panelu głównego administratora</a></p>
    <hr>

    <div>
        <h2>Dodaj Pojedynczy Adres E-mail</h2>
        <form action="process_add_allowed_email.php" method="post">
            <div>
                <label for="new_email">Adres e-mail:</label><br>
                <input type="email" id="new_email" name="email_to_add" required style="width: 300px;">
            </div>
            <br>
            <button type="submit">Dodaj Adres E-mail</button>
        </form>
    </div>

    <hr>

    <div>
        <h2>Importuj z pliku CSV</h2>
        <p>Możesz zaimportować listę dozwolonych adresów e-mail z pliku CSV. Plik powinien zawierać jedną kolumnę z nagłówkiem "email".</p>
        <form action="process_import_emails_csv.php" method="post" enctype="multipart/form-data">
            <div>
                <label for="csv_file">Wybierz plik CSV:</label><br>
                <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
            </div>
            <br>
            <button type="submit">Importuj Plik</button>
        </form>
    </div>

    <hr>

    <div>
        <h2>Lista Dozwolonych Adresów E-mail</h2>
        <?php if ($db_fetch_error && empty($allowed_emails_list)): ?>
            <p>Błąd ładowania listy dozwolonych e-maili.</p>
        <?php elseif (empty($allowed_emails_list)): ?>
            <p>Obecnie nie ma żadnych adresów e-mail na liście dozwolonych.</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Adres E-mail</th>
                    <th>Akcje</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($allowed_emails_list as $email_entry): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($email_entry['id']); ?></td>
                        <td><?php echo htmlspecialchars($email_entry['email']); ?></td>
                        <td>
                            <form method="post" action="process_delete_allowed_email.php" style="display:inline;" onsubmit="return confirm('Czy na pewno chcesz usunąć ten adres e-mail z listy dozwolonych?');">
                                <input type="hidden" name="delete_email_id" value="<?php echo htmlspecialchars($email_entry['id']); ?>">
                                <button type="submit" class="button-danger">Usuń</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

<?php endif; ?>

<?php
require_once '../inc/templates/footer.php';
?>