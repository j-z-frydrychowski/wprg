<?php
if (!empty($_POST)) {
    echo "<table>\n";
    echo "<thead>\n";
    echo "<tr><th>Pole</th><th>Wartość</th></tr>\n";
    echo "</thead>\n";
    echo "<tbody>\n";

    foreach ($_POST as $klucz => $wartosc) {
        echo "<tr>\n";
        echo "<td>" . htmlspecialchars($klucz) . "</td>\n";
        echo "<td>" . htmlspecialchars($wartosc) . "</td>\n";
        echo "</tr>\n";
    }

    echo "</tbody>\n";
    echo "</table>\n";
} else {
    echo "<p>Brak danych z formularza.</p>\n";
}
?>