<?php
echo "Podaj ciąg liczb (może zawierać znaki \\/:*?\"<>|+-): ";
$inputString = trim(fgets(STDIN));
$unwantedCharacters = '/[\\/:*?"<>|+-]/';
$cleanString = preg_replace($unwantedCharacters, '', $inputString);
echo "Oczyszczony ciąg liczb: " . $cleanString . "\n";
?>
