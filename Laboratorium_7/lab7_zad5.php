<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zadanie 5</title>
</head>
<body>

<div>
    <h2>Prosty Kalkulator</h2>
    <form method="post" action="">
        <label for="number1_simple">Liczba 1:</label>
        <input type="text" name="number1_simple" id="number1_simple"><br><br>

        <label for="simple_op">Operacja:</label>
        <select name="simple_op" id="simple_op">
            <option value="add">+</option>
            <option value="sub">-</option>
            <option value="mul">*</option>
            <option value="div">/</option>
        </select><br><br>

        <label for="number2_simple">Liczba 2:</label>
        <input type="text" name="number2_simple" id="number2_simple"><br><br>

        <input type="submit" value="Oblicz Proste">
        <input type="hidden" name="calc_type" value="simple">
    </form>
    <div class="result">
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["calc_type"] == "simple") {
            $num1_str = isset($_POST["number1_simple"]) ? $_POST["number1_simple"] : '';
            $num2_str = isset($_POST["number2_simple"]) ? $_POST["number2_simple"] : '';
            $operation = isset($_POST["simple_op"]) ? $_POST["simple_op"] : '';

            if (!is_numeric($num1_str) || !is_numeric($num2_str)) {
                echo "<p class='error'>Proszę wprowadzić poprawne liczby.</p>";
            } else {
                $num1 = (float) $num1_str;
                $num2 = (float) $num2_str;

                switch ($operation) {
                    case "add":
                        echo "<p>Wynik dodawania: " . ($num1 + $num2) . "</p>";
                        break;
                    case "sub":
                        echo "<p>Wynik odejmowania: " . ($num1 - $num2) . "</p>";
                        break;
                    case "mul":
                        echo "<p>Wynik mnożenia: " . ($num1 * $num2) . "</p>";
                        break;
                    case "div":
                        if ($num2 == 0) {
                            echo "<p class='error'>Nie można dzielić przez zero!</p>";
                        } else {
                            echo "<p>Wynik dzielenia: " . ($num1 / $num2) . "</p>";
                        }
                        break;
                    default:
                        echo "<p class='error'>Nieznana operacja.</p>";
                }
            }
        }
        ?>
    </div>
</div>

<br>

<div>
    <h2>Zaawansowany Kalkulator</h2>
    <form method="post" action="">
        <label for="adv_number">Liczba:</label>
        <input type="text" name="adv_number" id="adv_number"><br><br>

        <label for="hex_number" style="display: none;">Liczba szesnastkowa:</label>
        <input type="text" name="hex_number" id="hex_number" style="display: none;"><br><br>

        <label for="advanced_op">Operacja zaawansowana:</label>
        <select name="advanced_op" id="advanced_op">
            <option value="cos">Cosinus</option>
            <option value="sin">Sinus</option>
            <option value="tan">Tangens</option>
            <option value="bin_to_dec">Bin -> Dec</option>
            <option value="dec_to_bin">Dec -> Bin</option>
            <option value="dec_to_hex">Dec -> Hex</option>
            <option value="hex_to_dec">Hex -> Dec</option>
        </select><br><br>

        <input type="submit" value="Oblicz Zaawansowane">
        <input type="hidden" name="calc_type" value="advanced">
    </form>

    <div class="result">
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["calc_type"] == "advanced") {
            $operation_advanced = isset($_POST["advanced_op"]) ? $_POST["advanced_op"] : '';

            switch ($operation_advanced) {
                case "cos":
                    $num_adv_str = isset($_POST["adv_number"]) ? $_POST["adv_number"] : '';
                    if (!is_numeric($num_adv_str)) {
                        echo "<p class='error'>Proszę wprowadzić poprawną liczbę dla funkcji trygonometrycznej.</p>";
                    } else {
                        $num_adv = (float) $num_adv_str;
                        $rad = deg2rad($num_adv);
                        $result = "Cosinus(" . $num_adv . "): " . cos($rad);
                        echo "<p>" . $result . "</p>";
                    }
                    break;
                case "sin":
                    $num_adv_str = isset($_POST["adv_number"]) ? $_POST["adv_number"] : '';
                    if (!is_numeric($num_adv_str)) {
                        echo "<p class='error'>Proszę wprowadzić poprawną liczbę dla funkcji trygonometrycznej.</p>";
                    } else {
                        $num_adv = (float) $num_adv_str;
                        $rad = deg2rad($num_adv);
                        $result = "Sinus(" . $num_adv . "): " . sin($rad);
                        echo "<p>" . $result . "</p>";
                    }
                    break;
                case "tan":
                    $num_adv_str = isset($_POST["adv_number"]) ? $_POST["adv_number"] : '';
                    if (!is_numeric($num_adv_str)) {
                        echo "<p class='error'>Proszę wprowadzić poprawną liczbę dla funkcji trygonometrycznej.</p>";
                    } else {
                        $num_adv = (float) $num_adv_str;
                        $rad = deg2rad($num_adv);
                        $result = "Tangens(" . $num_adv . "): " . tan($rad);
                        echo "<p>" . $result . "</p>";
                    }
                    break;
                case "bin_to_dec":
                    $bin_adv = isset($_POST["adv_number"]) ? $_POST["adv_number"] : '';
                    if (!preg_match('/^[01]+$/', $bin_adv)) {
                        echo "<p class='error'>Proszę wprowadzić poprawną liczbę binarną.</p>";
                    } else {
                        echo "<p>Binarnie " . $bin_adv . " to dziesiętnie " . bindec($bin_adv) . "</p>";
                    }
                    break;
                case "dec_to_bin":
                    $dec_adv_str = isset($_POST["adv_number"]) ? $_POST["adv_number"] : '';
                    if (!is_numeric($dec_adv_str) || (int)$dec_adv_str != $dec_adv_str || $dec_adv_str < 0) {
                        echo "<p class='error'>Proszę wprowadzić poprawną nieujemną liczbę dziesiętną.</p>";
                    } else {
                        echo "<p>Dziesiętnie " . $dec_adv_str . " to binarnie " . decbin((int)$dec_adv_str) . "</p>";
                    }
                    break;
                case "dec_to_hex":
                    $dec_hex_adv_str = isset($_POST["adv_number"]) ? $_POST["adv_number"] : '';
                    if (!is_numeric($dec_hex_adv_str) || (int)$dec_hex_adv_str != $dec_hex_adv_str || $dec_hex_adv_str < 0) {
                        echo "<p class='error'>Proszę wprowadzić poprawną nieujemną liczbę dziesiętną.</p>";
                    } else {
                        echo "<p>Dziesiętnie " . $dec_hex_adv_str . " to szesnastkowo " . dechex((int)$dec_hex_adv_str) . "</p>";
                    }
                    break;
                case "hex_to_dec":
                    $hex_adv = isset($_POST["hex_number"]) ? $_POST["hex_number"] : '';
                    $hex_adv_upper = strtoupper($hex_adv);
                    if (!ctype_xdigit($hex_adv_upper)) {
                        echo "<p class='error'>Proszę wprowadzić poprawną liczbę szesnastkową.</p>";
                    } else {
                        echo "<p>Szesnastkowo " . $hex_adv_upper . " to dziesiętnie " . hexdec($hex_adv) . "</p>";
                    }
                    break;
                default:
                    echo "<p class='error'>Nieznana operacja zaawansowana.</p>";
            }
        }
        ?>
    </div>
</div>
</body>
</html>