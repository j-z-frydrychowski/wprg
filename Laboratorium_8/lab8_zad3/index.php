<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zadanie 3</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Operacje na ciągach znaków</h1>
    <form method="post" action="">
        <div class="form-group">
            <label for="inputString">Wprowadź ciąg znaków:</label>
            <input type="text" id="inputString" name="inputString">
        </div>
        <div class="form-group">
            <label for="operation">Wybierz operację:</label>
            <select id="operation" name="operation">
                <option value="reverse">Odwrócenie ciągu znaków</option>
                <option value="uppercase">Zamiana na wielkie litery</option>
                <option value="lowercase">Zamiana na małe litery</option>
                <option value="length">Liczenie liczby znaków</option>
                <option value="trim">Usuwanie białych znaków</option>
            </select>
        </div>
        <button type="submit" class="button">Wykonaj</button>
    </form>

    <div id="result" class="result-container">
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $inputString = $_POST["inputString"];
            $operation = $_POST["operation"];

            if (empty($inputString)) {
                echo '<p class="error">Proszę wprowadzić ciąg znaków.</p>';
            } else {
                echo '<p>Wprowadzony ciąg: <strong>' . htmlspecialchars($inputString) . '</strong></p>';
                echo '<p>Wybrana operacja: <strong>';
                switch ($operation) {
                    case "reverse":
                        echo 'Odwrócenie ciągu znaków';
                        $result = strrev($inputString);
                        break;
                    case "uppercase":
                        echo 'Zamiana na wielkie litery';
                        $result = strtoupper($inputString);
                        break;
                    case "lowercase":
                        echo 'Zamiana na małe litery';
                        $result = strtolower($inputString);
                        break;
                    case "length":
                        echo 'Liczenie liczby znaków';
                        $result = strlen($inputString);
                        break;
                    case "trim":
                        echo 'Usuwanie białych znaków';
                        $result = preg_replace('/^\s+|\s+$/u', '', $inputString);
                        var_dump($result);
                        break;
                    default:
                        echo 'Nieznana operacja';
                        $result = "Błąd: Nieznana operacja";
                }
                echo '</strong></p>';
                echo '<p class="result-text">Wynik: <strong>';
                if (is_numeric($result)) {
                    echo $result;
                } else {
                    echo htmlspecialchars($result);
                }
                echo '</strong></p>';
            }
        }
        ?>
    </div>
</div>
</body>
</html>