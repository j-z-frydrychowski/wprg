<?php
// public/process_reset_password_request.php
session_start();

require_once '../inc/config.php'; // Dla BASE_PATH, stałych DB i DEBUG_MODE
require_once '../inc/functions.php'; // Dla getPdoConnection()

// Domyślne wartości dla przekierowania
$redirect_page_target = BASE_PATH . "/public/reset_password_request.php";
$redirect_params = ['status' => 'error', 'message' => 'Wystąpił nieoczekiwany błąd.'];

// Użytkownik już zalogowany nie powinien tu trafiać
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_PATH . "/public/profile.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);

        if (empty($email)) {
            $redirect_params['message'] = "Adres e-mail nie może być pusty.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $redirect_params['message'] = "Podany adres e-mail ma nieprawidłowy format.";
        } else {
            // E-mail jest formalnie poprawny, kontynuuj
            try {
                $pdo = getPdoConnection();

                // 1. Sprawdź, czy użytkownik o takim e-mailu istnieje
                $stmt_user = $pdo->prepare("SELECT id FROM users WHERE email = :email");
                $stmt_user->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt_user->execute();
                $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

                $email_sent_or_simulated = false;

                if ($user_data) {
                    $user_id = $user_data['id'];

                    // 2. Wygeneruj bezpieczny token
                    $token = bin2hex(random_bytes(32)); // Generuje 64-znakowy token

                    // 3. Ustaw czas wygaśnięcia tokenu (np. 1 godzina)
                    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    // 4. Zapisz token w tabeli password_resets i usuń stare tokeny
                    $stmt_delete_old = $pdo->prepare("DELETE FROM password_resets WHERE user_id = :user_id");
                    $stmt_delete_old->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt_delete_old->execute();

                    $stmt_insert_token = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
                    $stmt_insert_token->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt_insert_token->bindParam(':token', $token, PDO::PARAM_STR);
                    $stmt_insert_token->bindParam(':expires_at', $expires_at, PDO::PARAM_STR);

                    if ($stmt_insert_token->execute()) {
                        // 5. Przygotuj link resetujący
                        $reset_link = BASE_PATH . "/public/reset_password_form.php?token=" . urlencode($token);
                        $full_reset_link_for_email = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $reset_link;
                        //wersja do debugowania
                        if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
                            $debug_message = " (DEBUG: Link resetujący: <a href=\"" . htmlspecialchars($full_reset_link_for_email) . "\">" . htmlspecialchars($full_reset_link_for_email) . "</a> )";
                            $_SESSION['debug_reset_link'] = $full_reset_link_for_email; // Zapisz do sesji dla wyświetlenia na stronie
                        }
                        $email_sent_or_simulated = true;
                    } else {
                        error_log("Nie udało się zapisać tokenu resetującego dla user_id: $user_id");
                        $redirect_params['message'] = "Wystąpił błąd podczas generowania linku resetującego. Spróbuj ponownie.";
                    }
                }

                if ($email_sent_or_simulated || !$user_data) {
                    $redirect_params['status'] = 'success';
                    $redirect_params['message'] = "Jeśli konto powiązane z adresem " . htmlspecialchars($email) . " istnieje w naszym systemie, wysłaliśmy na nie instrukcje dotyczące resetowania hasła.";
                    if (isset($_SESSION['debug_reset_link'])) {
                        $redirect_params['message'] .= " DEBUG: Link został wygenerowany.";
                    }
                }

            } catch (PDOException $e) {
                error_log("Błąd PDO w process_reset_password_request.php (email: $email): " . $e->getMessage());
                $redirect_params['message'] = "Wystąpił błąd serwera. Spróbuj ponownie później.";
            }
        }
    } else {
        $redirect_params['message'] = "Nie podano adresu e-mail.";
    }
} else {
    $redirect_params['message'] = "Nieprawidłowe żądanie.";
}

$query_string = http_build_query($redirect_params);
header("Location: " . $redirect_page_target . (strpos($redirect_page_target, '?') === false ? '?' : '&') . $query_string);
exit();
?>