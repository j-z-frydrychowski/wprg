<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../inc/config.php';
}

if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_PATH . "/public/profile.php");
    exit();
}

$page_title = 'Logowanie';
$page_alerts = [];

if (isset($_GET['error'])) {
    $page_alerts[] = ['type' => 'error', 'message' => htmlspecialchars($_GET['error'])];
}
if (isset($_GET['registration_success']) && $_GET['registration_success'] == 1) {
    $page_alerts[] = ['type' => 'success', 'message' => 'Rejestracja zakończona pomyślnie. Możesz się teraz zalogować.'];
}
if (isset($_GET['logout_success']) && $_GET['logout_success'] == 1) {
    $page_alerts[] = ['type' => 'info', 'message' => 'Pomyślnie wylogowano.'];
}

require_once '../inc/templates/header.php';


?>

    <form action="process_login.php" method="post">
        <div>
            <label for="email">Adres e-mail:</label><br>
            <input type="email" id="email" name="email" required>
        </div>
        <br>
        <div>
            <label for="password">Hasło:</label><br>
            <input type="password" id="password" name="password" required>
        </div>
        <br>
        <button type="submit">Zaloguj się</button>
    </form>
    <br>
    <p>Nie masz konta? <a href="rejestracja.php">Zarejestruj się</a></p>
    <p><a href="reset_password_request.php">Zapomniałeś hasła?</a></p>

<?php

require_once '../inc/templates/footer.php';
?>