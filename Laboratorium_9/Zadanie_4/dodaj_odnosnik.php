<?php

$nazwaPliku = 'odnosniki.txt';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $odnosnik = $_POST['odnosnik'];
    $opis = $_POST['opis'];

    $liniaDoZapisu = $odnosnik . ';' . $opis . "\n";

    if (file_put_contents($nazwaPliku, $liniaDoZapisu, FILE_APPEND)) {
        echo '<p style="color: green;">Odnośnik został pomyślnie dodany do pliku.</p>';
        echo '<p><a href="lab9_zad4.php">Dodaj kolejny odnośnik</a></p>';
        echo '<p><a href="wyswietl_odnosnik.php">Zobacz listę odnośników</a></p>';
    } else {
        echo '<p style="color: red;">Wystąpił problem z zapisem do pliku.</p>';
        echo '<p><a href="lab9_zad4.php">Spróbuj ponownie</a></p>';
    }
} else {
    echo '<p style="color: red;">Nieprawidłowe żądanie.</p>';
    echo '<p><a href="lab9_zad4.php">Powrót do formularza</a></p>';
}

?>