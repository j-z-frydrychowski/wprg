<?php

$plikDozwolonychIP = 'dozwolone_ip.txt';
$domyslnaStrona = 'strona_domyslna.php';
$stronaSpecjalna = 'strona_specjalna.php';

$uzytkownikIP = $_SERVER['REMOTE_ADDR'];

if (file_exists($plikDozwolonychIP)) {
    $dozwoloneIP = file($plikDozwolonychIP, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (in_array($uzytkownikIP, $dozwoloneIP)) {
        include($stronaSpecjalna);
    } else {
        include($domyslnaStrona);
    }
} else {
    echo '<p style="color: red;">Błąd: Nie znaleziono pliku z dozwolonymi adresami IP.</p>';
    include($domyslnaStrona);
}
?>