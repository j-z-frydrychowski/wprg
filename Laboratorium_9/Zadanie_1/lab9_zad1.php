<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zadanie 1</title>
</head>
<body>
<h1>Podaj swoją datę urodzenia:</h1>
<form action="wynik.php" method="get">
    <label for="data_urodzenia">Data urodzenia (RRRR-MM-DD):</label>
    <input type="date" id="data_urodzenia" name="data_urodzenia" required><br><br>
    <input type="submit" value="Sprawdź!">
</form>
</body>
</html>