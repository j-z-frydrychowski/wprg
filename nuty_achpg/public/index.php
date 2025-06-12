<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteka Nut - Menu</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div>
    <h1>Biblioteka Nut</h1>

    <?php if (isset($_SESSION['user_id'])): ?>
        <p>Witaj, zalogowany użytkowniku!</p>
        <p><a href="logout.php">Wyloguj się</a></p>

        <h2>Menu</h2>
        <ul>
            <li><a href="biblioteka.php" class="button">Przejdź do Biblioteki Nut</a></li>
            <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'zarzad')): ?>
                <li><a href="../admin/index.php" class="button">Panel Administratora</a></li>
            <?php endif; ?>
        </ul>

    <?php else: ?>
        <?php if (isset($_GET['login_success']) && $_GET['login_success'] == 1): ?>
            <p class="success">Zalogowano pomyślnie!</p>
        <?php endif; ?>
        <p>Aby uzyskać dostęp do biblioteki nut, musisz się zalogować lub zarejestrować.</p>
        <p><a href="login.php">Zaloguj się</a> | <a href="rejestracja.php" class="button">Zarejestruj się</a></p>
    <?php endif; ?>

</div>
</body>
</html>