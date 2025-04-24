<?php

echo "Podaj ciąg znaków: ";
$inputString = trim(fgets(STDIN));

$vowels = 'aeiouAEIOU';
$vowelCount = 0;

for ($i = 0; $i < strlen($inputString); $i++) {
    $currentChar = $inputString[$i];
    if (strpos($vowels, $currentChar) !== false) {
        $vowelCount++;
    }
}
echo "W podanym ciągu znajduje się " . $vowelCount . " samogłosek.\n";
?>