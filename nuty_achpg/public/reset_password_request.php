<?php
// public/reset_password_request.php

// Rozpocznij sesję i dołącz config dla BASE_PATH
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../inc/config.php';
}

//Przekierowanie zalogowanego użytkownika
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_PATH . "/public/profile.php");
    exit();
}

$page_title = 'Resetowanie Hasła - Krok 1';
$page_alerts = []; // Dla komunikatów

// Sprawdzenie komunikatów GET
if (isset($_GET['status']) && isset($_GET['message'])) {
    $page_alerts[] = [
        'type' => htmlspecialchars($_GET['status']),
        'message' => htmlspecialchars($_GET['message'])
    ];
}

require_once '../inc/templates/header.php'; // Dołączenie globalnego nagłówka
?>

    <p>Jeśli zapomniałeś hasła, podaj poniżej swój adres e-mail. Jeśli konto o takim adresie istnieje w naszym systemie, wyślemy na nie dalsze instrukcje dotyczące resetowania hasła.</p>

    <form action="process_reset_password_request.php" method="post">
        <div>
            <label for="email">Twój adres e-mail:</label><br>
            <input type="email" id="email" name="email" required style="width: 300px;">
        </div>
        <br>
        <button type="submit">Wyślij instrukcje resetowania hasła</button>
    </form>
    <br>
    <p><a href="login.php">Wróć do logowania</a></p>

<?php
require_once '../inc/templates/footer.php'; // Dołączenie globalnej stopki
?>