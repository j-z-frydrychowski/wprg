<?php

$nazwaPliku = 'licznik.txt';

if (!file_exists($nazwaPliku)) {
    file_put_contents($nazwaPliku, '1');
    $licznik = 1;
    echo "Witryna została odwiedzona po raz pierwszy!";
} else {
    $plik = fopen($nazwaPliku, 'r+');

    if ($plik) {
        $licznikString = trim(fread($plik, filesize($nazwaPliku)));
        $licznik = (int)$licznikString;

        $licznik++;

        rewind($plik);

        fwrite($plik, (string)$licznik);

        fclose($plik);

        echo "Witryna została odwiedzona <strong>" . $licznik . "</strong> razy.";
    } else {
        echo "Wystąpił problem z otwarciem pliku licznika.";
    }
}

?><?php
