<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../inc/config.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = $_GET['id'];

    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASSWORD, DB_OPTIONS);
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role != 'admin'");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            header("Location: index.php?delete_success=1");
        } else {
            header("Location: index.php?delete_error=" . urlencode("Nie można usunąć użytkownika lub nie znaleziono użytkownika o podanym ID."));
        }
        exit();
    } catch (PDOException $e) {
        header("Location: index.php?delete_error=" . urlencode("Wystąpił problem z usunięciem użytkownika."));
        exit();
    }
} else {
    header("Location: index.php?delete_error=" . urlencode("Nieprawidłowe ID użytkownika."));
    exit();
}
?>