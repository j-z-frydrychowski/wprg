<?php
session_start();

//Sprawdzenie sposobu przekazania danych
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../inc/config.php'; //załączenie informacji o bazie danych

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $errors = [];

    if (empty($email) || empty($password)) {
        $errors[] = "Proszę wprowadzić adres e-mail i hasło.";
    }

    if (empty($errors)) {

        //Łączenie się z bazą danych
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASSWORD, DB_OPTIONS);
            $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();

            //próba logowania
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                session_regenerate_id(true);
                header("Location: index.php?login_success=1");
                exit();
            } else {
                $errors[] = "Nieprawidłowy adres e-mail lub hasło.";
            }
        //Problem ze strony serwera
        } catch (PDOException $e) {
            $errors[] = "Wystąpił problem podczas logowania. Spróbuj ponownie później.";
            error_log("Błąd logowania do bazy danych: " . $e->getMessage());
        }
    }
    //dodanie do linku informacji o błędzie
    if (!empty($errors)) {
        header("Location: login.php?error=" . urlencode(implode("<br>", $errors)));
        exit();
    }
//przekierowanie do strony logowania w razie innej metody niż POST
} else {
    header("Location: login.php");
    exit();
}
?>