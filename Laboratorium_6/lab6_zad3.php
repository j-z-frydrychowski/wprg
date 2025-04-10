<?php
function sequences_n($poczatek, $iloraz_roznica, $liczba_elementow)
{
    if (is_numeric($poczatek) && is_numeric($iloraz_roznica) && is_numeric($liczba_elementow)) {
        if ($liczba_elementow > 0) {
            $tab_art[] = $poczatek;
            $tab_geo[] = $poczatek;
            $liczba_elementow--;
            $i = 0;
            while ($liczba_elementow != 0) {
                $tab_art[] = $tab_art[$i] + $iloraz_roznica;
                $tab_geo[] = $tab_geo[$i] * $iloraz_roznica;
                $i++;
                $liczba_elementow--;
            }
            echo "Aritmetic: ";
            foreach ($tab_art as $number) {
                echo $number . ", ";
            };
            echo "\nGeometric: ";
            foreach ($tab_geo as $number) {
                echo $number . ", ";
            }
        }
        else
            echo "N must be positive number!";
    }
    else
        echo "Parameters must be numeric!";
}
sequences_n(1,2,6);
?>
