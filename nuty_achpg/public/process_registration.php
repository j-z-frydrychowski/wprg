<?php
// public/process_registration.php
session_start(); // Rozpocznij sesję, jeśli planujesz używać komunikatów flash lub innych danych sesji

require_once '../inc/config.php'; // Dla BASE_PATH i stałych DB
require_once '../inc/functions.php'; // Dla getPdoConnection()

// Domyślne wartości dla przekierowania w razie błędu - z powrotem do formularza rejestracji
$redirect_page = BASE_PATH . "/public/rejestracja.php";
$redirect_params = ['status' => 'error', 'message' => 'Wystąpił nieoczekiwany błąd.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Walidacja istnienia kluczy POST, aby uniknąć błędów "undefined array key"
    $imie = isset($_POST['name']) ? trim($_POST['name']) : ''; // W formularzu rejestracji było 'name'
    $nazwisko = isset($_POST['surname']) ? trim($_POST['surname']) : ''; // W formularzu rejestracji było 'surname'
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : ''; // Hasła nie trimujemy
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    $errors = [];

    // Walidacja imienia i nazwiska
    if (empty($imie)) {
        $errors[] = "Imię jest wymagane.";
    } elseif (strlen($imie) > 100) {
        $errors[] = "Imię jest zbyt długie.";
    }
    if (empty($nazwisko)) {
        $errors[] = "Nazwisko jest wymagane.";
    } elseif (strlen($nazwisko) > 100) {
        $errors[] = "Nazwisko jest zbyt długie.";
    }

    // Walidacja adresu e-mail
    if (empty($email)) {
        $errors[] = "Adres e-mail jest wymagany.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Nieprawidłowy format adresu e-mail.";
    }

    // Walidacja hasła
    if (empty($password)) {
        $errors[] = "Hasło jest wymagane.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Hasło musi mieć co najmniej 6 znaków.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Podane hasła nie są identyczne.";
    }

    // Jeśli nie ma podstawowych błędów walidacji, kontynuuj ze sprawdzeniami bazodanowymi
    if (empty($errors)) {
        try {
            $pdo = getPdoConnection();

            // Sprawdzenie, czy e-mail znajduje się na liście dozwolonych
            $stmt_allowed = $pdo->prepare("SELECT COUNT(*) FROM allowed_emails WHERE email = :email");
            $stmt_allowed->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt_allowed->execute();
            if ($stmt_allowed->fetchColumn() == 0) {
                $errors[] = "Podany adres e-mail nie jest uprawniony do rejestracji.";
            }

            // Sprawdzenie, czy użytkownik o takim e-mailu już istnieje w tabeli users
            if (empty($errors)) { // Kontynuuj tylko, jeśli e-mail jest dozwolony
                $stmt_exists = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
                $stmt_exists->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt_exists->execute();
                if ($stmt_exists->fetchColumn() > 0) {
                    $errors[] = "Użytkownik o podanym adresie e-mail już istnieje.";
                }
            }

            // Jeśli nadal brak błędów, można rejestrować użytkownika
            if (empty($errors)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $default_role = 'chórzysta'; // Domyślna rola dla nowych użytkowników

                // Rozpocznij transakcję
                $pdo->beginTransaction();

                // 1. Dodaj użytkownika do tabeli 'users'
                $stmt_insert_user = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (:email, :password, :role)");
                $stmt_insert_user->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt_insert_user->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
                $stmt_insert_user->bindParam(':role', $default_role, PDO::PARAM_STR);

                if ($stmt_insert_user->execute()) {
                    $new_user_id = $pdo->lastInsertId(); // Pobierz ID nowo dodanego użytkownika

                    // 2.Dodaj wpis do tabeli 'user_profiles'
                    $stmt_insert_profile = $pdo->prepare(
                        "INSERT INTO user_profiles (user_id, imie, nazwisko, data_urodzenia, glos) 
                         VALUES (:user_id, :imie, :nazwisko, NULL, NULL)"
                    );
                    $stmt_insert_profile->bindParam(':user_id', $new_user_id, PDO::PARAM_INT);
                    $stmt_insert_profile->bindParam(':imie', $imie, PDO::PARAM_STR);
                    $stmt_insert_profile->bindParam(':nazwisko', $nazwisko, PDO::PARAM_STR);

                    if ($stmt_insert_profile->execute()) {
                        // Obie operacje INSERT się powiodły, zatwierdź transakcję
                        $pdo->commit();
                        $redirect_params['status'] = 'success';
                        $redirect_params['message'] = 'Rejestracja zakończona pomyślnie. Możesz się teraz zalogować.';
                        $redirect_page = BASE_PATH . "/public/login.php?registration_success=1"; // Standardowe przekierowanie po sukcesie
                    } else {
                        $pdo->rollBack(); // Wycofaj transakcję, jeśli dodanie profilu się nie udało
                        $errors[] = "Wystąpił błąd podczas tworzenia profilu użytkownika.";
                        error_log("Błąd przy INSERT do user_profiles dla user_id: $new_user_id");
                    }
                } else {
                    $pdo->rollBack(); // Wycofaj transakcję
                    $errors[] = "Nie udało się zarejestrować użytkownika.";
                }
            }

        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack(); // Wycofaj transakcję w razie błędu PDO
            }
            error_log("Błąd PDO w process_registration.php: " . $e->getMessage());
            $errors[] = "Wystąpił błąd serwera podczas rejestracji. Spróbuj ponownie później.";
        }
    }

    // Jeśli były jakiekolwiek błędy
    if (!empty($errors)) {
        $redirect_params['status'] = 'error';
        $redirect_params['message'] = implode(" ", $errors);
    }

} else {
    $redirect_params['message'] = "Nieprawidłowe żądanie.";
    // Jeśli nie POST, również wróć do formularza rejestracji
}

// Przekierowanie
$query_string = http_build_query($redirect_params);
header("Location: " . $redirect_page . (strpos($redirect_page, '?') === false ? '?' : '&') . $query_string);
exit();
?>