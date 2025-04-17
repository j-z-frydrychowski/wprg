<?php


function utworzTablice(int $a, int $b, int $c, int $d): array
{
    $wynikowaTablica = [];
    
    if ($a > $b) {
        echo "Błąd: Początkowy indeks 'a' musi być mniejszy lub równy końcowemu indeksowi 'b'.\n";
        return $wynikowaTablica;
    }

    if ($c > $d) {
        echo "Błąd: Początkowa wartość 'c' musi być mniejsza lub równa końcowej wartości 'd'.\n";
        return $wynikowaTablica;
    }

    $liczbaIndeksow = $b - $a + 1;
    $liczbaWartosci = $d - $c + 1;

    if ($liczbaIndeksow !== $liczbaWartosci) {
        echo "Błąd: Liczba indeksów (od a do b) musi być równa liczbie wartości (od c do d).\n";
        return $wynikowaTablica;
    }

    $aktualnaWartosc = $c;
    for ($i = $a; $i <= $b; $i++) {
        $wynikowaTablica[$i] = $aktualnaWartosc;
        $aktualnaWartosc++;
    }
    
    return $wynikowaTablica;
}

$startIndeks = 5;
$koniecIndeks = 10;
$startWartosc = 100;
$koniecWartosc = 105;

$mojaTablica = utworzTablice($startIndeks, $koniecIndeks, $startWartosc, $koniecWartosc);

echo "Utworzona tablica:\n";
print_r($mojaTablica);

echo "\n";

$blednaTablica1 = utworzTablice(10, 5, 1, 5);
$blednaTablica2 = utworzTablice(1, 5, 10, 5);
$blednaTablica3 = utworzTablice(1, 6, 10, 13);
?>
