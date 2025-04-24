<?php

echo "Podaj liczbę zmiennoprzecinkową: ";
$inputString = trim(fgets(STDIN));

$comaPosition = strpos($inputString, ',');

if ($comaPosition === false) {
    echo "Podany ciąg nie zawiera części ułamkowej.\n";
} else {
    $decimalPart = substr($inputString, $comaPosition + 1);
    $decimalDigitsCount = strlen($decimalPart);
    echo "Ilość cyfr po przecinku: " . $decimalDigitsCount . "\n";
}
?>