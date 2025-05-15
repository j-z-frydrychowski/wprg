<?php
$cookie_name_sonda = "uzytkownikGlosowalSonda";
$cookie_value_sonda = "tak";
$czas_zycia_ciasteczka_sonda = time() + (86400 * 30);


$juz_glosowal = false;
$wiadomosc_dla_uzytkownika = "";
$pytanie_sondy = "Jaki jest Twój ulubiony język programowania webowego (backend)?";
$opcje_odpowiedzi = [
    "PHP" => "PHP",
    "Python (Django/Flask)" => "Python (Django/Flask)",
    "Node.js (JavaScript)" => "Node.js (JavaScript)",
    "Ruby (Ruby on Rails)" => "Ruby (Ruby on Rails)",
    "Java (Spring)" => "Java (Spring)",
    "Inny" => "Inny"
];

if (isset($_COOKIE[$cookie_name_sonda]) && $_COOKIE[$cookie_name_sonda] == $cookie_value_sonda) {
    $juz_glosowal = true;
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['glosuj'])) {
    if (!$juz_glosowal) {
        if (isset($_POST['odpowiedz']) && !empty($_POST['odpowiedz'])) {
            $wybrana_odpowiedz = $_POST['odpowiedz'];
            setcookie($cookie_name_sonda, $cookie_value_sonda, $czas_zycia_ciasteczka_sonda, "/");

            $juz_glosowal = true;
            $wiadomosc_dla_uzytkownika = "Dziękujemy za oddanie głosu! Wybrałeś/aś: " . htmlspecialchars($wybrana_odpowiedz) . ".";

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;

        } else {
            $wiadomosc_dla_uzytkownika = "BŁĄD: Proszę wybrać jedną z odpowiedzi przed zagłosowaniem.";
        }
    }
}

if ($juz_glosowal && empty($wiadomosc_dla_uzytkownika)) {
    $wiadomosc_dla_uzytkownika = "Już oddałeś/aś głos w tej sondzie. Dziękujemy za udział!";
}

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sonda Internetowa</title>
    <style>
        body {
            margin: 20px;
            background-color: #eef2f7;
            color: #333;
            line-height: 1.6;
        }
        .container {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 30px auto;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }
        fieldset {
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        legend {
            font-weight: bold;
            color: #2980b9;
            padding: 0 10px;
            font-size: 1.2em;
        }
        label {
            display: block;
            margin-bottom: 8px;
            cursor: pointer;
            color: #555;
        }
        input[type="radio"] {
            margin-right: 8px;
            vertical-align: middle;
        }
        button[type="submit"] {
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
            display: block;
            width: 100%;
            margin-top: 10px;
        }
        button[type="submit"]:hover {
            background-color: #2980b9;
        }
        .wiadomosc {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .wiadomosc.sukces {
            background-color: #e6ffe6;
            border: 1px solid #5cb85c;
            color: #3c763d;
        }
        .wiadomosc.info {
            background-color: #e7f3fe;
            border: 1px solid #3498db;
            color: #2980b9;
        }
        .wiadomosc.blad {
            background-color: #f8d7da;
            border: 1px solid #d9534f;
            color: #721c24;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Sonda Internetowa</h1>

    <?php if (!empty($wiadomosc_dla_uzytkownika)): ?>
        <?php
        $klasa_wiadomosci = 'info'; // Domyślna
        if (strpos(strtolower($wiadomosc_dla_uzytkownika), 'dziękujemy za oddanie głosu') !== false) {
            $klasa_wiadomosci = 'sukces';
        } elseif (strpos(strtolower($wiadomosc_dla_uzytkownika), 'błąd') !== false) {
            $klasa_wiadomosci = 'blad';
        }
        ?>
        <p class="wiadomosc <?php echo $klasa_wiadomosci; ?>"><?php echo htmlspecialchars($wiadomosc_dla_uzytkownika); ?></p>
    <?php endif; ?>


    <?php if (!$juz_glosowal): ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <fieldset>
                <legend><?php echo htmlspecialchars($pytanie_sondy); ?></legend>
                <?php foreach ($opcje_odpowiedzi as $wartosc => $etykieta): ?>
                    <div>
                        <input type="radio"
                               id="odp_<?php echo htmlspecialchars(strtolower(str_replace([' ', '(', ')', '.'], '_', $wartosc))); ?>"
                               name="odpowiedz"
                               value="<?php echo htmlspecialchars($wartosc); ?>"
                               required>
                        <label for="odp_<?php echo htmlspecialchars(strtolower(str_replace([' ', '(', ')', '.'], '_', $wartosc))); ?>">
                            <?php echo htmlspecialchars($etykieta); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </fieldset>
            <button type="submit" name="glosuj">Głosuj</button>
        </form>
    <?php
    endif; ?>

</div>

</body>
</html>