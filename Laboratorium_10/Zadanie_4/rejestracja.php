<?php
session_start();
define('UZYTKOWNICY_PLIK', 'uzytkownicy.txt');

$errors = [];
$success_message = '';

function czyEmailUnikalny($email, $plik) {
    if (!file_exists($plik)) {
        return true;
    }
    $uchwyt_pliku = fopen($plik, 'r');
    if ($uchwyt_pliku) {
        while (($linia = fgets($uchwyt_pliku)) !== false) {
            $dane_uzytkownika = explode(';', trim($linia));
            if (isset($dane_uzytkownika[2]) && $dane_uzytkownika[2] === $email) {
                fclose($uchwyt_pliku);
                return false;
            }
        }
        fclose($uchwyt_pliku);
    }
    return true;
}

function walidujHaslo($haslo, &$errors_ref) {
    if (strlen($haslo) < 6) {
        $errors_ref[] = "Hasło musi mieć co najmniej 6 znaków.";
    }
    if (!preg_match('/[A-Z]/', $haslo)) {
        $errors_ref[] = "Hasło musi zawierać co najmniej jedną wielką literę.";
    }
    if (!preg_match('/[0-9]/', $haslo)) {
        $errors_ref[] = "Hasło musi zawierać co najmniej jedną cyfrę.";
    }
    if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $haslo)) {
        $errors_ref[] = "Hasło musi zawierać co najmniej jeden znak specjalny (np. !@#$%^&*).";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rejestruj'])) {
    $imie = trim($_POST['imie']);
    $nazwisko = trim($_POST['nazwisko']);
    $email = trim($_POST['email']);
    $haslo = $_POST['haslo'];

    if (empty($imie)) {
        $errors[] = "Imię jest wymagane.";
    }
    if (empty($nazwisko)) {
        $errors[] = "Nazwisko jest wymagane.";
    }
    if (empty($email)) {
        $errors[] = "Email jest wymagany.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Niepoprawny format adresu email.";
    } elseif (!czyEmailUnikalny($email, UZYTKOWNICY_PLIK)) {
        $errors[] = "Ten adres email jest już zarejestrowany.";
    }

    if (empty($haslo)) {
        $errors[] = "Hasło jest wymagane.";
    } else {
        walidujHaslo($haslo, $errors);
    }

    if (empty($errors)) {
        $hashed_haslo = password_hash($haslo, PASSWORD_DEFAULT);
        $linia_danych = implode(';', [$imie, $nazwisko, $email, $hashed_haslo]) . PHP_EOL;

        if (file_put_contents(UZYTKOWNICY_PLIK, $linia_danych, FILE_APPEND | LOCK_EX)) {
            $success_message = "Rejestracja zakończona sukcesem! Możesz się teraz zalogować.";
            $_POST = array();
            $imie = $nazwisko = $email = $haslo = '';
        } else {
            $errors[] = "Wystąpił błąd podczas zapisu danych. Spróbuj ponownie.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja Użytkownika</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f7f6; color: #333; }
        .container { background-color: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; margin: 40px auto; }
        h1 { text-align: center; color: #2c3e50; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: calc(100% - 22px); padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        button[type="submit"] {
            background-color: #28a745; color: white; padding: 12px 20px; border: none; border-radius: 4px;
            cursor: pointer; font-size: 16px; width: 100%; transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover { background-color: #218838; }
        .errors, .success {
            padding: 12px; margin-bottom: 15px; border-radius: 4px; text-align: left;
            list-style-position: inside; /* dla błędów */
        }
        .errors { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .errors li { margin-bottom: 5px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .login-link { text-align: center; margin-top: 20px; }
        .login-link a { color: #007bff; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
        small { display: block; margin-top: -10px; margin-bottom: 10px; color: #666; font-size: 0.85em; }
    </style>
</head>
<body>
<div class="container">
    <h1>Formularz Rejestracyjny</h1>

    <?php if (!empty($errors)): ?>
        <ul class="errors">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div>
            <label for="imie">Imię:</label>
            <input type="text" id="imie" name="imie" value="<?php echo htmlspecialchars($_POST['imie'] ?? ''); ?>" required>
        </div>
        <div>
            <label for="nazwisko">Nazwisko:</label>
            <input type="text" id="nazwisko" name="nazwisko" value="<?php echo htmlspecialchars($_POST['nazwisko'] ?? ''); ?>" required>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>
        <div>
            <label for="haslo">Hasło:</label>
            <input type="password" id="haslo" name="haslo" required>
            <small>Min. 6 znaków, 1 wielka litera, 1 cyfra, 1 znak specjalny (np. !@#$).</small>
        </div>
        <button type="submit" name="rejestruj">Zarejestruj się</button>
    </form>
    <div class="login-link">
        Masz już konto? <a href="logowanie.php">Zaloguj się</a>
    </div>
</div>
</body>
</html>