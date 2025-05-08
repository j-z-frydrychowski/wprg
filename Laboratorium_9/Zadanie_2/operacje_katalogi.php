<?php

function zarzadzajKatalogiem($sciezkaBazowa, $nazwaKatalogu, $operacja = 'read') {

    if (substr($sciezkaBazowa, -1) !== '/') {
        $sciezkaBazowa .= '/';
    }

    $pelnaSciezka = $sciezkaBazowa . $nazwaKatalogu;
    $komunikat = '';

    switch ($operacja) {
        case 'read':
            if (is_dir($pelnaSciezka)) {
                $elementy = scandir($pelnaSciezka);
                // Usuń "." i ".." z listy
                $elementy = array_diff($elementy, ['.', '..']);
                if (!empty($elementy)) {
                    $komunikat = "Zawartość katalogu '$pelnaSciezka':<br>";
                    $komunikat .= "<ul><li>" . implode("</li><li>", $elementy) . "</li></ul>";
                } else {
                    $komunikat = "Katalog '$pelnaSciezka' jest pusty.";
                }
            } else {
                $komunikat = "Katalog '$pelnaSciezka' nie istnieje.";
            }
            break;

        case 'create':
            if (!is_dir($pelnaSciezka)) {
                if (mkdir($pelnaSciezka, 0777, true)) {
                    $komunikat = "Katalog '$pelnaSciezka' został pomyślnie utworzony.";
                } else {
                    $komunikat = "Nie udało się utworzyć katalogu '$pelnaSciezka'. Sprawdź uprawnienia.";
                }
            } else {
                $komunikat = "Katalog '$pelnaSciezka' już istnieje.";
            }
            break;

        case 'delete':
            if (is_dir($pelnaSciezka)) {
                $elementy = scandir($pelnaSciezka);
                $elementy = array_diff($elementy, ['.', '..']);
                if (empty($elementy)) {
                    if (rmdir($pelnaSciezka)) {
                        $komunikat = "Katalog '$pelnaSciezka' został pomyślnie usunięty.";
                    } else {
                        $komunikat = "Nie udało się usunąć katalogu '$pelnaSciezka'. Sprawdź uprawnienia.";
                    }
                } else {
                    $komunikat = "Nie można usunąć katalogu '$pelnaSciezka', ponieważ nie jest pusty.";
                }
            } else {
                $komunikat = "Katalog '$pelnaSciezka' nie istnieje.";
            }
            break;

        default:
            $komunikat = "Nieznana operacja: '$operacja'. Dostępne operacje to: read, create, delete.";
            break;
    }

    return $komunikat;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sciezka = $_POST['sciezka'];
    $nazwaKatalogu = $_POST['nazwa_katalogu'];
    $operacja = $_POST['operacja'];

    // Wywołanie funkcji i wyświetlenie komunikatu
    $wynikOperacji = zarzadzajKatalogiem($sciezka, $nazwaKatalogu, $operacja);
    echo "<h1>Wynik operacji:</h1>";
    echo "<p>$wynikOperacji</p>";
    echo '<p><a href="lab9_zad2.php">Powrót do formularza</a></p>';
} else {
    echo "<p>Nieprawidłowe żądanie.</p>";
}

?>