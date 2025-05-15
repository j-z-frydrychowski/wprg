<?php
session_start();

define('UZYTKOWNICY_PLIK', 'uzytkownicy.txt');

$login_error = '';

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
    header("Location: logowanie.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['zaloguj'])) {
    $email_logowania = trim($_POST['email_logowania']);
    $haslo_logowania = $_POST['haslo_logowania'];

    if (empty($email_logowania) || empty($haslo_logowania)) {
        $login_error = "Email i hasło są wymagane.";
    } else {
        if (!file_exists(UZYTKOWNICY_PLIK)) {
            $login_error = "Brak zarejestrowanych użytkowników lub błąd pliku.";
        } else {
            $uchwyt_pliku = fopen(UZYTKOWNICY_PLIK, 'r');
            $uzytkownik_znaleziony = false;
            if ($uchwyt_pliku) {
                while (($linia = fgets($uchwyt_pliku)) !== false) {
                    $dane_uzytkownika = explode(';', trim($linia));
                    if (isset($dane_uzytkownika[2]) && $dane_uzytkownika[2] === $email_logowania) {
                        $uzytkownik_znaleziony = true;
                        if (isset($dane_uzytkownika[3]) && password_verify($haslo_logowania, $dane_uzytkownika[3])) {
                            // Poprawne logowanie
                            $_SESSION['user_email'] = $dane_uzytkownika[2];
                            $_SESSION['user_imie'] = $dane_uzytkownika[0];
                            header("Location: logowanie.php");
                            exit;
                        } else {
                            $login_error = "Nieprawidłowe hasło.";
                            break;
                        }
                    }
                }
                fclose($uchwyt_pliku);
                if (!$uzytkownik_znaleziony) {
                    $login_error = "Użytkownik o podanym adresie email nie został znaleziony.";
                }
            } else {
                $login_error = "Nie można otworzyć pliku z danymi użytkowników.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f7f6; color: #333; }
        .container { background-color: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 450px; margin: 40px auto; }
        h1 { text-align: center; color: #2c3e50; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type="email"], input[type="password"] {
            width: calc(100% - 22px); padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        button[type="submit"] {
            background-color: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 4px;
            cursor: pointer; font-size: 16px; width: 100%; transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover { background-color: #0056b3; }
        .error {
            padding: 12px; margin-bottom: 15px; border-radius: 4px; text-align: center;
            background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;
        }
        .success-login {
            padding: 15px; margin-bottom: 20px; border-radius: 4px; text-align: center;
            background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;
        }
        .logout-link a, .register-link a { color: #007bff; text-decoration: none; }
        .logout-link a:hover, .register-link a:hover { text-decoration: underline; }
        .logout-link { display: inline-block; margin-top: 10px; padding: 8px 15px; background-color: #6c757d; color: white !important; border-radius: 4px; }
        .logout-link:hover { background-color: #5a6268; }
        .text-center { text-align: center; }
        .mt-20 { margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <?php if (isset($_SESSION['user_email'])): ?>
        <h1>Witaj, <?php echo htmlspecialchars($_SESSION['user_imie']); ?>!</h1>
        <p class="success-login">Zostałeś poprawnie zalogowany jako <?php echo htmlspecialchars($_SESSION['user_email']); ?>.</p>
        <div class="text-center">
            <a href="?action=wyloguj" class="logout-link">Wyloguj</a>
        </div>
    <?php else: ?>
        <h1>Formularz Logowania</h1>
        <?php if ($login_error): ?>
            <p class="error"><?php echo htmlspecialchars($login_error); ?></p>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div>
                <label for="email_logowania">Email:</label>
                <input type="email" id="email_logowania" name="email_logowania" value="<?php echo htmlspecialchars($_POST['email_logowania'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="haslo_logowania">Hasło:</label>
                <input type="password" id="haslo_logowania" name="haslo_logowania" required>
            </div>
            <button type="submit" name="zaloguj">Zaloguj</button>
        </form>
        <div class="register-link text-center mt-20">
            Nie masz konta? <a href="rejestracja.php">Zarejestruj się</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>