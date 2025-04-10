<?php
function isPangram($sentence){
    $sentence = strtolower($sentence);
    $alphabet = range('a', 'z');
    foreach ($alphabet as $letter) {
        if (strpos($sentence, $letter) === false) {
            return false;
        }
    }
    return true;
}
$sentence = "The quick brown fox jumps over the lazy dog";
$result = isPangram($sentence);
echo $result ? 'true' : 'false';
?>