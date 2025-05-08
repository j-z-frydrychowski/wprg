<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zadanie 2</title>
</head>
<body>
<form action="operacje_katalogi.php" method="post">
    <label for="sciezka">Ścieżka:</label>
    <input type="text" id="sciezka" name="sciezka" value="./php/images/" required><br><br>

    <label for="nazwa_katalogu">Nazwa katalogu:</label>
    <input type="text" id="nazwa_katalogu" name="nazwa_katalogu" required><br><br>

    <label for="operacja">Operacja:</label>
    <select id="operacja" name="operacja">
        <option value="read" selected>Odczytaj</option>
        <option value="create">Stwórz</option>
        <option value="delete">Usuń</option>
    </select><br><br>

    <input type="submit" value="Wykonaj operację">
</form>
</body>
</html>