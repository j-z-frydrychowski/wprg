<?php
// public/edit_profile.php

$page_title = 'Edytuj Mój Profil';
$page_alerts = []; // Inicjalizacja alertów dla tej strony


if (isset($_GET['status']) && isset($_GET['message'])) {
    $page_alerts[] = [
        'type' => htmlspecialchars($_GET['status']), // Oczekiwane 'success' lub 'error'
        'message' => htmlspecialchars($_GET['message'])
    ];
}

// Dołączenie globalnego nagłówka
require_once '../inc/templates/header.php';

if (!$user_id) { // $user_id jest dostępne z header.php
    header("Location: " . BASE_PATH . "/public/login.php?error_message=" . urlencode("Dostęp do tej strony wymaga zalogowania."));
    exit();
}

// Zmienne na dane profilu i flagę widoczności formularza
$profile_data = null;
$form_visible = false;

// Głosy dostępne do wyboru
$available_voice_parts = ['Sopran I', 'Sopran II', 'Alt I', 'Alt II', 'Tenor I', 'Tenor II', 'Baryton', 'Bas'];

// Pobieranie aktualnych danych profilu zalogowanego użytkownika
if (isset($user_id)) {
    try {
        $pdo = getPdoConnection();
        $stmt = $pdo->prepare("
            SELECT 
                u.email, 
                p.imie, 
                p.nazwisko, 
                p.data_urodzenia, 
                p.glos 
            FROM 
                users u
            LEFT JOIN 
                user_profiles p ON u.id = p.user_id 
            WHERE 
                u.id = :id
        ");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $profile_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($profile_data) {
            $form_visible = true;
        } else {
            $page_alerts[] = ['type' => 'error', 'message' => 'Nie udało się załadować danych Twojego profilu.'];
        }

    } catch (PDOException $e) {
        error_log("Błąd PDO w public/edit_profile.php (ID: $user_id): " . $e->getMessage());
        $page_alerts[] = ['type' => 'error', 'message' => 'Wystąpił błąd serwera podczas ładowania Twojego profilu.'];
    }
} else {
    $page_alerts[] = ['type' => 'error', 'message' => 'Nie jesteś zalogowany lub sesja wygasła.'];
}

?>

<?php if ($form_visible && $profile_data):?>
    <form action="process_edit_profile.php" method="post">
        <h3>Dane Podstawowe</h3>
        <div>
            <label for="email">Adres e-mail (login):</label><br>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($profile_data['email']); ?>" readonly>
            <small>Adresu e-mail (loginu) nie można zmienić poprzez ten formularz.</small>
        </div>
        <br>
        <div>
            <label for="imie">Imię:</label><br>
            <input type="text" id="imie" name="imie" value="<?php echo htmlspecialchars($profile_data['imie'] ?? ''); ?>">
        </div>
        <br>
        <div>
            <label for="nazwisko">Nazwisko:</label><br>
            <input type="text" id="nazwisko" name="nazwisko" value="<?php echo htmlspecialchars($profile_data['nazwisko'] ?? ''); ?>">
        </div>
        <br>
        <div>
            <label for="data_urodzenia">Data urodzenia:</label><br>
            <input type="date" id="data_urodzenia" name="data_urodzenia" value="<?php echo htmlspecialchars($profile_data['data_urodzenia'] ?? ''); ?>">
        </div>
        <br>
        <div>
            <label for="glos">Głos:</label><br>
            <select id="glos" name="glos">
                <option value="">-- Wybierz głos --</option>
                <?php foreach ($available_voice_parts as $voice_part): ?>
                    <option value="<?php echo htmlspecialchars($voice_part); ?>"
                        <?php if (($profile_data['glos'] ?? '') === $voice_part) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($voice_part); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <hr style="margin-top: 20px; margin-bottom: 20px;">

        <h3>Zmiana Hasła (wypełnij tylko jeśli chcesz zmienić)</h3>
        <div>
            <label for="current_password">Obecne hasło:</label><br>
            <input type="password" id="current_password" name="current_password">
            <small>Wymagane tylko jeśli zmieniasz hasło.</small>
        </div>
        <br>
        <div>
            <label for="new_password">Nowe hasło:</label><br>
            <input type="password" id="new_password" name="new_password">
            <small>Minimum 6 znaków.</small>
        </div>
        <br>
        <div>
            <label for="confirm_new_password">Potwierdź nowe hasło:</label><br>
            <input type="password" id="confirm_new_password" name="confirm_new_password">
        </div>
        <br>
        <button type="submit" name="submit_edit_profile">Zapisz Zmiany</button>
    </form>
<?php else: ?>
    <?php
    if (empty($page_alerts)) {
        echo "<p>Nie można załadować formularza edycji profilu. Spróbuj odświeżyć stronę lub skontaktuj się z administratorem.</p>";
    }
    ?>
    <p><a href="<?php echo BASE_PATH; ?>/public/profile.php">Powrót do mojego profilu</a></p>
<?php endif; ?>


<?php
require_once '../inc/templates/footer.php'; // Dołączenie globalnej stopki
?>