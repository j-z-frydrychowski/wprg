<?php
function printMatrix($matrix) {
    foreach ($matrix as $row) {
        echo implode(" ", $row) . "\n";
    }
}
function multiplyMatrices($matrixA, $matrixB)
{
    $rowsA = count($matrixA);
    $colsA = count($matrixA[0]);
    $rowsB = count($matrixB);
    $colsB = count($matrixB[0]);

    if ($colsA !== $rowsB) {
        echo "Liczba kolumn w pierwszej macierzy musi być równa liczbie wierszy w drugiej macierzy.";
        return 0;
    }
    else {
        $result = array_fill(0, $rowsA, array_fill(0, $colsB, 0));
        for ($i = 0; $i < $rowsA; $i++) {
            for ($j = 0; $j < $colsB; $j++) {
                for ($k = 0; $k < $colsA; $k++) {
                    $result[$i][$j] += $matrixA[$i][$k] * $matrixB[$k][$j];
                }
            }
        }
        return $result;
    }
}
$A = [[1, 2, 3], [4, 5, 6]];
$B = [[7, 8], [9, 10], [11, 12]];
$C = multiplyMatrices($A, $B);
printMatrix($C);
?>
