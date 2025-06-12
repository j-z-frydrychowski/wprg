<?php

// dodatkowe sprawdzenie sesji przed header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Przekeirowanie zalogowanego użytkownika
if (isset($_SESSION['user_id'])) {

    if (!defined('BASE_PATH')) {
        require_once '../inc/config.php';
    }
    header("Location: " . BASE_PATH . "/public/profile.php");
    exit();
}


$page_title = 'Rejestracja Nowego Użytkownika';
$page_alerts = [];

//odczytanie komunikatów z GET
if (isset($_GET['status']) && isset($_GET['message'])) {
    $page_alerts[] = [
        'type' => htmlspecialchars($_GET['status']), // Oczekiwane 'success' lub 'error'
        'message' => htmlspecialchars($_GET['message'])
    ];
}

// Dołączenie globalnego nagłówka
require_once '../inc/templates/header.php';

?>

    <form action="process_registration.php" method="post">
        <div>
            <label for="name">Imię:</label><br>
            <input type="text" id="name" name="name" value="<?php echo isset($_GET['imie_val']) ? htmlspecialchars($_GET['imie_val']) : ''; ?>" required>
        </div>
        <br>
        <div>
            <label for="surname">Nazwisko:</label><br>
            <input type="text" id="surname" name="surname" value="<?php echo isset($_GET['nazwisko_val']) ? htmlspecialchars($_GET['nazwisko_val']) : ''; ?>" required>
        </div>
        <br>
        <div>
            <label for="email">Adres e-mail:</label><br>
            <input type="email" id="email" name="email" value="<?php echo isset($_GET['email_val']) ? htmlspecialchars($_GET['email_val']) : ''; ?>" required>
        </div>
        <br>
        <div>
            <label for="password">Hasło:</label><br>
            <input type="password" id="password" name="password" required>
            <small>Minimum 6 znaków.</small>
        </div>
        <br>
        <div>
            <label for="confirm_password">Powtórz hasło:</label><br>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <br>
        <button type="submit">Zarejestruj się</button>
    </form>
    <br>
    <p>Masz już konto? <a href="<?php echo BASE_PATH; ?>/public/login.php">Zaloguj się</a></p>


<?php
// Dołączenie globalnej stopki
require_once '../inc/templates/footer.php';
?>