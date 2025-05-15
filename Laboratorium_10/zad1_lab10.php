<?php
$cookie_name = "licznikOdwiedzinUzytkownika";
$cel_odwiedzin = 5;
$czas_zycia_ciasteczka = time() + (86400 * 30);

$liczba_odwiedzin = 0;
$wiadomosc_o_celu = "";

if (isset($_POST['reset'])) {
    setcookie($cookie_name, "", time() - 3600, "/");
    unset($_COOKIE[$cookie_name]);
    $liczba_odwiedzin = 0;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


if (isset($_COOKIE[$cookie_name])) {
    $liczba_odwiedzin = (int)$_COOKIE[$cookie_name];
    $liczba_odwiedzin++;
} else {
    $liczba_odwiedzin = 1;
}

setcookie($cookie_name, (string)$liczba_odwiedzin, $czas_zycia_ciasteczka, "/"); // Wartość ciasteczka musi być stringiem

if ($liczba_odwiedzin >= $cel_odwiedzin) {
    $wiadomosc_o_celu = "Gratulacje! Odwiedziłeś tę stronę już " . $liczba_odwiedzin . " razy i osiągnąłeś cel " . $cel_odwiedzin . " wizyt!";
}

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Licznik Odwiedzin Strony</title>
    <style>
        body {
            margin: 20px;
            background-color: #f4f4f4;
            color: #333;
            text-align: center;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: inline-block;
            text-align: left;
        }
        p {
            font-size: 1.1em;
        }
        .highlight {
            color: green;
            font-weight: bold;
            padding: 10px;
            border: 1px solid green;
            background-color: #e6ffe6;
            border-radius: 4px;
            margin-top: 15px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            margin-top: 20px;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Witaj na Stronie z Licznikiem Odwiedzin!</h1>

    <p>Twoja liczba odwiedzin tej strony: <strong><?php echo $liczba_odwiedzin; ?></strong></p>

    <?php if (!empty($wiadomosc_o_celu)): ?>
        <p class="highlight"><?php echo htmlspecialchars($wiadomosc_o_celu); ?></p>
    <?php endif; ?>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <button type="submit" name="reset">Resetuj licznik odwiedzin</button>
    </form>
</div>

</body>
</html>