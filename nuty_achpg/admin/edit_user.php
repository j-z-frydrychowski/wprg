<?php
// admin/edit_user.php

$page_title = 'Panel - Edytuj Użytkownika';
$page_alerts = []; // Inicjalizacja alertów dla tej strony

// Dołączenie globalnego nagłówka (sesja, config, funkcje, podstawowa autoryzacja, początek HTML)
require_once '../inc/templates/header.php';


$can_access_page = false;
if (isset($user_role) && $user_role === 'admin') {
    $can_access_page = true;
} else {
    $page_alerts[] = ['type' => 'error', 'message' => 'Nie masz uprawnień do zarządzania użytkownikami.'];
}

// Zmienne dla danych użytkownika i widoczności formularza
$user_to_edit = null;
$form_visible = false;

// 3. Obsługa żądania POST (przetwarzanie formularza edycji)
if ($can_access_page && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id']) && is_numeric($_POST['user_id']) && isset($_POST['role'])) {
        $posted_user_id = (int)$_POST['user_id'];
        $new_role = trim($_POST['role']);
        $allowed_roles = ['chórzysta', 'bibliotekarz', 'zarzad']; // Admin nie może nadać roli 'admin' przez ten formularz

        // Walidacja, czy ID użytkownika z POST jest tym, którego można edytować (nie admin)
        // i czy nowa rola jest dozwolona.
        $valid_post_id = false;
        try {
            $pdo_check = getPdoConnection();
            $stmt_check = $pdo_check->prepare("SELECT role FROM users WHERE id = :id");
            $stmt_check->bindParam(':id', $posted_user_id, PDO::PARAM_INT);
            $stmt_check->execute();
            $user_being_edited_role = $stmt_check->fetchColumn();

            if ($user_being_edited_role && $user_being_edited_role !== 'admin') {
                $valid_post_id = true; // Można edytować tego użytkownika
            } else if ($user_being_edited_role === 'admin'){
                $page_alerts[] = ['type' => 'error', 'message' => 'Nie można edytować roli innego administratora poprzez ten formularz.'];
            } else {
                $page_alerts[] = ['type' => 'error', 'message' => 'Użytkownik o podanym ID nie istnieje.'];
            }

        } catch (PDOException $e) {
            error_log("Błąd PDO przy weryfikacji user_id w POST (edit_user.php): " . $e->getMessage());
            $page_alerts[] = ['type' => 'error', 'message' => 'Błąd serwera przy weryfikacji użytkownika.'];
            $valid_post_id = false; //błąd, ID nieedytowalne
        }


        if ($valid_post_id && in_array($new_role, $allowed_roles)) {
            try {
                $pdo_update = getPdoConnection();
                $stmt_update = $pdo_update->prepare("UPDATE users SET role = :role WHERE id = :id AND role != 'admin'");
                $stmt_update->bindParam(':role', $new_role, PDO::PARAM_STR);
                $stmt_update->bindParam(':id', $posted_user_id, PDO::PARAM_INT);

                if ($stmt_update->execute()) {
                    if ($stmt_update->rowCount() > 0) {
                        header("Location: index.php?edit_success=1");
                        exit();
                    } else {
                        // Nie zaktualizowano żadnego wiersza - np. użytkownik był adminem lub ID nie pasowało
                        header("Location: index.php?edit_error=" . urlencode("Nie udało się zaktualizować roli (użytkownik mógł być administratorem lub nie znaleziono)."));
                        exit();
                    }
                } else {
                    header("Location: index.php?edit_error=" . urlencode("Wystąpił błąd podczas wykonywania aktualizacji."));
                    exit();
                }
            } catch (PDOException $e) {
                error_log("Błąd PDO podczas aktualizacji roli użytkownika (ID: $posted_user_id): " . $e->getMessage());
                header("Location: index.php?edit_error=" . urlencode("Wystąpił problem serwera z aktualizacją roli."));
                exit();
            }
        } elseif ($valid_post_id) { // ID było OK, ale rola nie
            header("Location: index.php?edit_error=" . urlencode("Wybrano nieprawidłową rolę użytkownika."));
            exit();
        }

    } else if (isset($_POST['user_id'])) { // Jeśli user_id lub rola nie zostały przesłane
        $page_alerts[] = ['type' => 'error', 'message' => 'Niekompletne dane formularza.'];
    }
}

if ($can_access_page && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $user_id_get = (int)$_GET['id'];

        if ($user_id_get === $_SESSION['user_id']) {
            $page_alerts[] = ['type' => 'error', 'message' => 'Administrator nie może edytować własnej roli poprzez ten formularz.'];
        } else {
            try {
                $pdo = getPdoConnection();
                $stmt = $pdo->prepare("SELECT id, email, role FROM users WHERE id = :id AND role != 'admin'");
                $stmt->bindParam(':id', $user_id_get, PDO::PARAM_INT);
                $stmt->execute();
                $user_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user_to_edit) {
                    $form_visible = true; // Mamy dane użytkownika, pokaż formularz
                } else {
                    $page_alerts[] = ['type' => 'error', 'message' => 'Nie znaleziono użytkownika o podanym ID lub próbujesz edytować administratora.'];
                }
            } catch (PDOException $e) {
                error_log("Błąd PDO w admin/edit_user.php (pobieranie użytkownika GET): " . $e->getMessage());
                $page_alerts[] = ['type' => 'error', 'message' => 'Wystąpił błąd serwera podczas ładowania danych użytkownika.'];
            }
        }
    } elseif (isset($_GET['id'])) { // Jeśli ID jest, ale nie numeryczne
        $page_alerts[] = ['type' => 'error', 'message' => 'Nieprawidłowe ID użytkownika w adresie URL.'];
    }
    if (!isset($_GET['id']) && empty($page_alerts)) {
        $page_alerts[] = ['type' => 'info', 'message' => 'Aby edytować użytkownika, wybierz go z listy na stronie głównej panelu administratora.'];
    }
}

?>

<?php if ($can_access_page): ?>
    <?php if ($form_visible && $user_to_edit): // Wyświetl formularz tylko jeśli dane użytkownika są dostępne i można je edytować ?>
        <p><a href="index.php">Powrót do listy użytkowników</a></p>

        <form method="post" action="edit_user.php?id=<?php echo htmlspecialchars($user_to_edit['id']); // Wysyłamy POST na tę samą stronę z ID ?>">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_to_edit['id']); ?>">

            <div>
                <label for="email">E-mail:</label><br>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_to_edit['email']); ?>" readonly>
                <small>E-maila nie można edytować.</small>
            </div>
            <br>
            <div>
                <label for="role">Rola:</label><br>
                <select id="role" name="role">
                    <?php
                    $available_roles_for_form = ['chórzysta', 'bibliotekarz', 'zarzad'];
                    foreach ($available_roles_for_form as $role_value) :
                        $selected = ($user_to_edit['role'] === $role_value) ? 'selected' : '';
                        ?>
                        <option value="<?php echo htmlspecialchars($role_value); ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars(ucfirst($role_value)); // ucfirst dla ładniejszej nazwy ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <br>
            <button type="submit">Zapisz Zmiany</button>
        </form>
    <?php else: ?>
        <p><a href="index.php">Powrót do listy użytkowników</a></p>
    <?php endif; ?>
<?php endif;?>

<?php
require_once '../inc/templates/footer.php'; //dołączenie globalnej stopki
?>