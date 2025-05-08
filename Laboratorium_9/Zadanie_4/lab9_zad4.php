<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zadanie 4</title>
</head>
<body>
<h1>Dodaj nowy odnośnik</h1>
<form action="dodaj_odnosnik.php" method="post">
    <div>
        <label for="odnosnik">Adres odnośnika:</label>
        <input type="url" id="odnosnik" name="odnosnik" required>
    </div>
    <br>
    <div>
        <label for="opis">Opis odnośnika:</label>
        <input type="text" id="opis" name="opis" required>
    </div>
    <br>
    <button type="submit">Dodaj odnośnik</button>
</form>
<br>
<p><a href="wyswietl_odnosnik.php">Zobacz listę odnośników</a></p>
</body>
</html>