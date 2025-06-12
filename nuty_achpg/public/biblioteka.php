<?php
// public/biblioteka.php

$page_title = 'Biblioteka Nut'; // Tytuł tej konkretnej strony
$page_alerts = [];             // Inicjalizacja tablicy na komunikaty dla tej strony

//dodawanie alertów przekazanych przez skrypty
if (isset($_GET['delete_status']) && isset($_GET['delete_message'])) {
    $status_type = 'info'; // Domyślny typ
    if ($_GET['delete_status'] === 'success') {
        $status_type = 'success';
    } elseif ($_GET['delete_status'] === 'error') {
        $status_type = 'error';
    } elseif ($_GET['delete_status'] === 'warning') {
        $status_type = 'warning';
    }
    $page_alerts[] = ['type' => $status_type, 'message' => htmlspecialchars($_GET['delete_message'])];
}


//dołączenie nagłówka
require_once '../inc/templates/header.php';


if (!$user_id) { // $user_id jest dostępne z header.php
    header("Location: " . BASE_PATH . "/public/login.php?error_message=" . urlencode("Dostęp do tej strony wymaga zalogowania."));
    exit();
}

$nuty = []; // Inicjalizacja tablicy na nuty

try {
    $pdo = getPdoConnection();
    $stmt = $pdo->query("SELECT id, tytul, autor, kategoria, plik FROM nuty ORDER BY tytul ASC");
    $nuty = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $page_alerts[] = ['type' => 'error', 'message' => "Wystąpił problem z pobraniem danych z biblioteki. Prosimy spróbować ponownie później."];
    error_log("Błąd PDO w public/biblioteka.php przy pobieraniu nut: " . $e->getMessage());
}

?>

    <h2>Dostępne nuty:</h2>

<?php if (empty($nuty) && !array_some($page_alerts, fn($a) => $a['type'] === 'error' && str_contains($a['message'], 'pobraniem danych'))): // Pokaż tylko jeśli nie ma błędu pobierania i lista jest pusta ?>
    <p>Brak dostępnych nut w bibliotece.</p>
<?php elseif (!empty($nuty)): ?>
    <ul>
        <?php foreach ($nuty as $nuta): ?>
            <li>
                Tytuł: <?php echo htmlspecialchars($nuta['tytul']); ?><br>
                Autor: <?php echo htmlspecialchars($nuta['autor']); ?><br>
                Kategoria: <?php echo htmlspecialchars($nuta['kategoria']); ?><br>
                <a href="<?php echo BASE_PATH . '/public/' . htmlspecialchars($nuta['plik']); ?>" target="_blank">Pobierz nuty</a>
            </li>
            <hr>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
if (!function_exists('array_some')) {
    function array_some(array $array, callable $callback): bool {
        foreach ($array as $key => $value) {
            if ($callback($value, $key, $array)) {
                return true;
            }
        }
        return false;
    }
}
?>

<?php
//Dołączenie globalnej stopki
require_once '../inc/templates/footer.php';
?>