<?php
session_start();

$poprawny_login = "student";
$poprawne_haslo = "informatyka123";

$wiadomosc_logowania = "";
$czy_zalogowany = isset($_SESSION['uzytkownik_zalogowany']) && $_SESSION['uzytkownik_zalogowany'] === $poprawny_login;


if (isset($_GET['action']) && $_GET['action'] === 'wyloguj') {
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['zaloguj_submit'])) {
    if (isset($_POST['login']) && isset($_POST['haslo'])) {
        $wprowadzony_login = $_POST['login'];
        $wprowadzone_haslo = $_POST['haslo'];

        if ($wprowadzony_login === $poprawny_login && $wprowadzone_haslo === $poprawne_haslo) {
            $_SESSION['uzytkownik_zalogowany'] = $wprowadzony_login;
            $czy_zalogowany = true;
            header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']));
            exit;

        } else {
            $wiadomosc_logowania = "Błędny login lub hasło.";
        }
    } else {
        $wiadomosc_logowania = "Proszę podać login i hasło.";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logowania</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            background-color: #fff;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #0056b3;
        }
        .link-wyloguj, .link-powrot {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 15px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .link-wyloguj:hover, .link-powrot:hover {
            background-color: #5a6268;
        }
        .wiadomosc {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 0.95em;
        }
        .wiadomosc.sukces {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .wiadomosc.blad {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
<div class="container">
    <?php if ($czy_zalogowany): ?>
        <h1>Witaj, <?php echo htmlspecialchars($_SESSION['uzytkownik_zalogowany']); ?>!</h1>
        <p class="wiadomosc sukces">Zostałeś poprawnie zalogowany.</p>
        <a href="?action=wyloguj" class="link-wyloguj">Wyloguj</a>
    <?php else: ?>
        <h1>Formularz Logowania</h1>
        <?php if (!empty($wiadomosc_logowania)): ?>
            <p class="wiadomosc blad"><?php echo htmlspecialchars($wiadomosc_logowania); ?></p>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div>
                <label for="login">Login:</label>
                <input type="text" id="login" name="login" required>
            </div>
            <div>
                <label for="haslo">Hasło:</label>
                <input type="password" id="haslo" name="haslo" required>
            </div>
            <button type="submit" name="zaloguj_submit">Zaloguj</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>