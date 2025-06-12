<?php
// public/profile.php

$page_title = 'Mój Profil';
$page_alerts = [];

require_once '../inc/templates/header.php'; // Nagłówek

authorize_user_access();

$user_profile_data = null;

if (isset($user_id)) {
    try {
        $pdo = getPdoConnection();
        $stmt = $pdo->prepare("
            SELECT 
                u.email, 
                u.role,
                p.imie, 
                p.nazwisko, 
                p.data_urodzenia, 
                p.glos 
            FROM 
                users u
            LEFT JOIN 
                user_profiles p ON u.id = p.user_id 
            WHERE 
                u.id = :id
        ");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user_profile_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user_profile_data) {
            $page_alerts[] = ['type' => 'error', 'message' => 'Nie udało się załadować danych Twojego profilu (brak użytkownika).'];
        }

    } catch (PDOException $e) {
        error_log("Błąd PDO w public/profile.php (ID: $user_id): " . $e->getMessage());
        $page_alerts[] = ['type' => 'error', 'message' => 'Wystąpił błąd serwera podczas ładowania Twojego profilu.'];
    }
} else {
    $page_alerts[] = ['type' => 'error', 'message' => 'Nie jesteś zalogowany lub sesja wygasła.'];
}

?>

<?php if ($user_profile_data): ?>
    <h2>Twoje Dane Profilowe</h2>
    <table>
        <tr>
            <td>Adres e-mail:</td>
            <td><?php echo htmlspecialchars($user_profile_data['email']); ?></td>
        </tr>
        <tr>
            <td>Imię:</td>
            <td><?php echo $user_profile_data['imie'] ? htmlspecialchars($user_profile_data['imie']) : '<em>Nie podano</em>'; ?></td>
        </tr>
        <tr>
            <td>Nazwisko:</td>
            <td><?php echo $user_profile_data['nazwisko'] ? htmlspecialchars($user_profile_data['nazwisko']) : '<em>Nie podano</em>'; ?></td>
        </tr>
        <tr>
            <td>Data urodzenia:</td>
            <td>
                <?php
                if ($user_profile_data['data_urodzenia']) {
                    echo htmlspecialchars($user_profile_data['data_urodzenia']); // Format YYYY-MM-DD
                } else {
                    echo '<em>Nie podano</em>';
                }
                ?>
            </td>
        </tr>
        <tr>
            <td>Głos:</td>
            <td><?php echo $user_profile_data['glos'] ? htmlspecialchars($user_profile_data['glos']) : '<em>Nie podano</em>'; ?></td>
        </tr>
        <tr>
            <td>Rola w systemie:</td>
            <td><?php echo htmlspecialchars(ucfirst($user_profile_data['role'])); ?></td>
        </tr>
    </table>
    <br>
    <p><a href="<?php echo BASE_PATH; ?>/public/edit_profile.php">Edytuj Profil</a></p>

<?php else: ?>
    <?php if (empty($page_alerts)): ?>
        <p>Nie można wyświetlić danych profilu. Spróbuj ponownie później.</p>
    <?php endif; ?>
<?php endif; ?>

<?php
require_once '../inc/templates/footer.php';
?>