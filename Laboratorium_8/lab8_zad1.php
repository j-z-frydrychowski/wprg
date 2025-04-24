<?php
echo "Podaj ciąg znaków: ";
$inputString = trim(fgets(STDIN));

echo "\n";
$uppercaseString = strtoupper($inputString);
echo "Ciąg dużymi literami: " . $uppercaseString . "\n";
$lowercaseString = strtolower($inputString);
echo "Ciąg małymi literami: " . $lowercaseString . "\n";
$firstLetterUppercase = ucfirst($inputString);
echo "Pierwsza litera dużą literą: " . $firstLetterUppercase . "\n";
$wordsCapitalized = ucwords($inputString);
echo "Pierwsze litery słów dużą literą: " . $wordsCapitalized . "\n";

?>