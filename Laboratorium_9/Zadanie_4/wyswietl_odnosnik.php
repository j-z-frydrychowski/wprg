<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zadanie 4</title>
</head>
<body>
<h1>Lista odnośników</h1>
<ul>
    <?php
    $nazwaPliku = 'odnosniki.txt';

    if (file_exists($nazwaPliku)) {
        $linie = file($nazwaPliku, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($linie) {
            foreach ($linie as $linia) {

                $elementy = explode(';', $linia);

                if (count($elementy) === 2) {
                    $adres = trim($elementy[0]);
                    $opis = trim($elementy[1]);

                    echo '<li><a href="' . htmlspecialchars($adres) . '">' . htmlspecialchars($opis) . '</a></li>';
                } else {
                    echo '<li style="color: red;">Nieprawidłowy format wiersza: ' . htmlspecialchars($linia) . '</li>';
                }
            }
        } else {
            echo '<p>Plik z odnośnikami jest pusty.</p>';
        }
    } else {
        echo '<p style="color: red;">Plik o nazwie "' . htmlspecialchars($nazwaPliku) . '" nie istnieje.</p>';
    }
    ?>
</ul>
</body>
</html>