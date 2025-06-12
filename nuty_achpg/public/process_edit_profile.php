<?php

session_start();

require_once '../inc/config.php';
require_once '../inc/functions.php';

$redirect_page = BASE_PATH . "/public/edit_profile.php";
$redirect_params = ['status' => 'error', 'message' => 'Wystąpił nieoczekiwany błąd.'];

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_PATH . "/public/login.php?error_message=" . urlencode("Musisz być zalogowany, aby edytować profil."));
    exit();
}
$user_id = $_SESSION['user_id'];

$available_voice_parts = ['Sopran', 'Alt', 'Tenor', 'Bas', 'Sopran I', 'Sopran II', 'Alt I', 'Alt II', 'Tenor I', 'Tenor II', 'Baryton', 'Inny', ''];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $imie = isset($_POST['imie']) ? trim($_POST['imie']) : null;
    $nazwisko = isset($_POST['nazwisko']) ? trim($_POST['nazwisko']) : null;
    $data_urodzenia = isset($_POST['data_urodzenia']) ? trim($_POST['data_urodzenia']) : null;
    $glos = isset($_POST['glos']) ? trim($_POST['glos']) : null;

    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_new_password = isset($_POST['confirm_new_password']) ? $_POST['confirm_new_password'] : '';

    $errors = [];
    $success_messages = [];

    // --- Walidacja i aktualizacja danych profilowych ---
    if (strlen($imie) > 100) $errors[] = "Imię jest zbyt długie.";
    if (strlen($nazwisko) > 100) $errors[] = "Nazwisko jest zbyt długie.";
    if (!empty($data_urodzenia) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_urodzenia)) {
        $errors[] = "Nieprawidłowy format daty urodzenia (oczekiwano RRRR-MM-DD).";
    } elseif (empty($data_urodzenia)) {
        $data_urodzenia = null;
    }
    if (!in_array($glos, $available_voice_parts)) {
        $errors[] = "Wybrano nieprawidłowy głos.";
    } elseif (empty($glos)) {
        $glos = null;
    }

    if (empty($errors)) {
        try {
            $pdo = getPdoConnection();

            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM user_profiles WHERE user_id = :user_id");
            $stmt_check->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_check->execute();
            $profile_exists = $stmt_check->fetchColumn() > 0;

            if ($profile_exists) {
                $stmt_profile = $pdo->prepare("UPDATE user_profiles SET imie = :imie, nazwisko = :nazwisko, data_urodzenia = :data_urodzenia, glos = :glos WHERE user_id = :user_id");
            } else {
                $stmt_profile = $pdo->prepare("INSERT INTO user_profiles (user_id, imie, nazwisko, data_urodzenia, glos) VALUES (:user_id, :imie, :nazwisko, :data_urodzenia, :glos)");
            }
            $stmt_profile->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_profile->bindParam(':imie', $imie, PDO::PARAM_STR);
            $stmt_profile->bindParam(':nazwisko', $nazwisko, PDO::PARAM_STR);
            $stmt_profile->bindParam(':data_urodzenia', $data_urodzenia, $data_urodzenia === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt_profile->bindParam(':glos', $glos, $glos === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

            if ($stmt_profile->execute()) {
                if ($stmt_profile->rowCount() > 0 || (!$profile_exists && $stmt_profile->rowCount() === 0) ) {
                    $success_messages[] = "Dane profilowe zostały zaktualizowane.";
                } else if ($profile_exists && $stmt_profile->rowCount() === 0) {
                    $success_messages[] = "Dane profilowe nie wymagały aktualizacji (pozostały takie same).";
                }
            } else {
                $errors[] = "Nie udało się zaktualizować danych profilowych.";
            }

        } catch (PDOException $e) {
            error_log("Błąd PDO przy aktualizacji profilu (ID: $user_id): " . $e->getMessage());
            $errors[] = "Wystąpił błąd serwera podczas aktualizacji profilu.";
        }
    }

    if (!empty($new_password) || !empty($confirm_new_password) || !empty($current_password) ) {
        if (empty($current_password)) {
            $errors[] = "Aby zmienić hasło, musisz podać swoje obecne hasło.";
        }
        if (empty($new_password)) {
            $errors[] = "Nowe hasło nie może być puste.";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "Nowe hasło musi mieć co najmniej 6 znaków.";
        }
        if ($new_password !== $confirm_new_password) {
            $errors[] = "Nowe hasła nie są identyczne.";
        }

        if (empty($errors) && !empty($current_password)) {
            try {
                $pdo_pass = getPdoConnection();
                $stmt_pass = $pdo_pass->prepare("SELECT password FROM users WHERE id = :id");
                $stmt_pass->bindParam(':id', $user_id, PDO::PARAM_INT);
                $stmt_pass->execute();
                $user_account_data = $stmt_pass->fetch(PDO::FETCH_ASSOC);

                if ($user_account_data && password_verify($current_password, $user_account_data['password'])) {
                    // Obecne hasło poprawne, można zmienić
                    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt_update_pass = $pdo_pass->prepare("UPDATE users SET password = :password WHERE id = :id");
                    $stmt_update_pass->bindParam(':password', $hashed_new_password, PDO::PARAM_STR);
                    $stmt_update_pass->bindParam(':id', $user_id, PDO::PARAM_INT);

                    if ($stmt_update_pass->execute()) {
                        $success_messages[] = "Hasło zostało pomyślnie zmienione.";
                    } else {
                        $errors[] = "Nie udało się zmienić hasła w bazie danych.";
                    }
                } else {
                    $errors[] = "Podane obecne hasło jest nieprawidłowe.";
                }
            } catch (PDOException $e) {
                error_log("Błąd PDO przy zmianie hasła (ID: $user_id): " . $e->getMessage());
                $errors[] = "Wystąpił błąd serwera podczas zmiany hasła.";
            }
        }
    }


    // Ustawienie finalnego statusu i komunikatu dla przekierowania
    if (empty($errors) && !empty($success_messages)) {
        $redirect_params['status'] = 'success';
        $redirect_params['message'] = implode(" ", $success_messages);
        $redirect_page = BASE_PATH . "/public/profile.php";
    } elseif (!empty($errors)) {
        $redirect_params['status'] = 'error';
        $redirect_params['message'] = implode(" ", $errors);

    } else {
        $redirect_params['status'] = 'info';
        $redirect_params['message'] = 'Nie wprowadzono żadnych zmian.';
        $redirect_page = BASE_PATH . "/public/profile.php";
    }

} else {
    $redirect_params['message'] = 'Nieprawidłowe żądanie.';
    $redirect_page = BASE_PATH . "/public/profile.php";
}

$final_redirect_url = $redirect_page;
if (!empty($redirect_params)) {
    if (strpos($redirect_page, BASE_PATH) !== 0 && $redirect_page !== 'edit_nuty.php') {
        $final_redirect_url = BASE_PATH . "/public/" . $redirect_page;
    }
    if (isset($redirect_params['id']) && strpos($redirect_page, 'edit_profile.php') !== false) {
        $query_params_to_build = ['status' => $redirect_params['status'], 'message' => urlencode($redirect_params['message'])];
        $final_redirect_url = $redirect_page;
        $final_redirect_url .= "?" . http_build_query($query_params_to_build);
    } else {
        $query_params_to_build = ['status' => $redirect_params['status'], 'message' => urlencode($redirect_params['message'])];
        $final_redirect_url = $redirect_page;
        $final_redirect_url .= "?" . http_build_query($query_params_to_build);
    }
}


header("Location: " . $final_redirect_url);
exit();
?>
