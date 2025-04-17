<?php
function wstawZnak(array $tablica_liczb, int $n): array
{
    $rozmiar_tablicy = count($tablica_liczb);

    if ($n < 0 || $n > $rozmiar_tablicy) {
        echo "BŁĄD";
        return [];
    }

    array_splice($tablica_liczb, $n, 0, ['$']);
    return $tablica_liczb;
}
$moja_tablica = [10, 20, 30, 40, 50];

echo "Oryginalna tablica: ";
print_r($moja_tablica);

$wynik1 = wstawZnak($moja_tablica, 2);
echo "\nTablica po wstawieniu '$' na pozycji 2: ";
print_r($wynik1);

$wynik2 = wstawZnak($moja_tablica, 0);
echo "\nTablica po wstawieniu '$' na pozycji 0: ";
print_r($wynik2);

$wynik_blad2 = wstawZnak($moja_tablica, 10);
echo "\nWynik dla błędnej pozycji 10: ";
print_r($wynik_blad2);
?>