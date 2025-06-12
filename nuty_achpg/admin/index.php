<?php
// admin/index.php

// Ustawienie tytułu strony (używane w inc/templates/header.php)
$page_title = 'Panel Główny Administratora';

//dołączenie nagłówka
require_once '../inc/templates/header.php';

/** @var string|null $user_role */ //komentarz usuwający błąd IDE z definicją zmiennej $user_role
/** @var string|null $user_id */

// DODATKOWA AUTORYZACJA SPECYFICZNA DLA TEJ STRONY
if ($user_role !== 'admin') {
    // Użytkownik jest zalogowany, ale nie jest adminem - przekieruj lub pokaż błąd
    $page_alerts[] = ['type' => 'error', 'message' => 'Nie masz uprawnień do tej strony.'];
}

// Inicjalizacja zmiennych dla danych i błędów specyficznych dla tej strony
$users = [];
$allowed_emails = [];

// Logika pobierania danych z bazy
try {
    $pdo = getPdoConnection();
    $stmt_users = $pdo->prepare("SELECT id, email, role FROM users WHERE role != 'admin' ORDER BY email ASC");
    $stmt_users->execute();
    $users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $page_alerts[] = ['type' => 'error', 'message' => "Wystąpił problem z pobraniem listy użytkowników."];
    error_log("Błąd PDO w admin/index.php (users): " . $e->getMessage());
}

try {
    $pdo = getPdoConnection();
    $stmt_emails = $pdo->prepare("SELECT id, email FROM allowed_emails ORDER BY email ASC");
    $stmt_emails->execute();
    $allowed_emails = $stmt_emails->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $page_alerts[] = ['type' => 'error', 'message' => "Wystąpił problem z pobraniem listy dozwolonych adresów e-mail."];
    error_log("Błąd PDO w admin/index.php (allowed_emails): " . $e->getMessage());
}

if (isset($_GET['edit_success']) && $_GET['edit_success'] == 1) {
    $page_alerts[] = ['type' => 'success', 'message' => 'Rola użytkownika została pomyślnie zaktualizowana.'];
}

if (isset($_GET['error_message'])) {
    $page_alerts[] = ['type' => 'error', 'message' => htmlspecialchars($_GET['error_message'])];
}


?>

    <div>
        <h2>Zarządzanie Użytkownikami</h2>
        <?php if (empty($users) && !in_array("Wystąpił problem z pobraniem listy użytkowników.", array_column($page_alerts, 'message'))): ?>
            <p>Brak zarejestrowanych użytkowników (oprócz administratorów).</p>
        <?php elseif (!empty($users)): ?>
            <table>
                <thead>
                <tr><th>ID</th><th>E-mail</th><th>Rola</th><th>Akcje</th></tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user_item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user_item['id']); ?></td>
                        <td><?php echo htmlspecialchars($user_item['email']); ?></td>
                        <td><?php echo htmlspecialchars($user_item['role']); ?></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo htmlspecialchars($user_item['id']); ?>">Edytuj</a> |
                            <a href="delete_user.php?id=<?php echo htmlspecialchars($user_item['id']); ?>" onclick="return confirm('Czy na pewno chcesz usunąć tego użytkownika?');">Usuń</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div>
        <h2>Dopuszczone Adresy E-mail do Rejestracji</h2>
        <?php if (empty($allowed_emails) && !in_array("Wystąpił problem z pobraniem listy dozwolonych adresów e-mail.", array_column($page_alerts, 'message'))): ?>
            <p>Brak zdefiniowanych dozwolonych adresów e-mail.</p>
        <?php elseif (!empty($allowed_emails)): ?>
            <table>
                <thead>
                <tr><th>ID</th><th>E-mail</th><th>Akcje</th></tr>
                </thead>
                <tbody>
                <?php foreach ($allowed_emails as $email_entry): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($email_entry['id']); ?></td>
                        <td><?php echo htmlspecialchars($email_entry['email']); ?></td>
                        <td>
                            <a href="process_delete_allowed_email.php?id=<?php echo htmlspecialchars($email_entry['id']); ?>" onclick="return confirm('Czy na pewno chcesz usunąć ten adres e-mail z listy dozwolonych?');">Usuń</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

<?php
// Dołączenie globalnej stopki
require_once '../inc/templates/footer.php';
?>