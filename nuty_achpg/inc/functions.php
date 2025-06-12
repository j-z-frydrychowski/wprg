<?php

function getPdoConnection(){
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, DB_OPTIONS);
    return $pdo;
}

function authorize_user_access(array $allowed_roles = [], string $redirect_path_on_fail = null) {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $current_user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;

    if (!$current_user_id) {
        // Użytkownik w ogóle nie jest zalogowany
        $_SESSION['page_alerts_flash'] = [['type' => 'error', 'message' => 'Dostęp do tej strony wymaga zalogowania.']];
        $final_redirect_path = $redirect_path_on_fail ?? (BASE_PATH . "/public/login.php");
        header("Location: " . $final_redirect_path);
        exit();
    }

    // Jeśli podano $allowed_roles i rola użytkownika nie jest na liście
    elseif (!empty($allowed_roles) && !in_array($current_user_role, $allowed_roles)) {
        $_SESSION['page_alerts_flash'] = [['type' => 'error', 'message' => 'Nie masz wystarczających uprawnień, aby uzyskać dostęp do tej strony.']];
        $final_redirect_path = $redirect_path_on_fail ?? (BASE_PATH . "/public/profile.php"); // Domyślnie na profil
        header("Location: " . $final_redirect_path);
        exit();
    }

    else {
        return true;
    }

}

?>