<?php
// admin/process_import_emails_csv.php
session_start();

require_once '../inc/config.php';
require_once '../inc/functions.php';

// Domyślne wartości dla przekierowania
$redirect_page_target = BASE_PATH . "/admin/manage_allowed_emails.php";
$redirect_params = ['status' => 'error', 'message' => 'Wystąpił nieoczekiwany błąd.'];

// Autoryzacja
$can_manage_emails = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'zarzad'])) {
    $can_manage_emails = true;
}

if (!$can_manage_emails) {
    $redirect_params['message'] = 'Nie masz uprawnień do wykonania tej akcji.';
    header("Location: " . $redirect_page_target . "?" . http_build_query($redirect_params));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sprawdzenie, czy plik został przesłany i czy nie było błędów systemowych
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['csv_file']['tmp_name'];
        $file_size = $_FILES['csv_file']['size'];
        $file_mime_type = mime_content_type($file_tmp_path);

        $max_file_size = 5 * 1024 * 1024; // 5 MB

        if ($file_size > $max_file_size) {
            $redirect_params['message'] = 'Plik jest zbyt duży. Maksymalny rozmiar to 5MB.';
        } elseif ($file_mime_type !== 'text/csv' && $file_mime_type !== 'text/plain') { // Niektóre systemy rozpoznają CSV jako text/plain
            $redirect_params['message'] = 'Nieprawidłowy typ pliku. Dozwolone są tylko pliki CSV.';
        } else {
            $emails_to_import = [];
            $header_row = true;
            $email_column_index = -1;

            if (($handle = fopen($file_tmp_path, "r")) !== FALSE) {
                while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if ($header_row) {
                        // Szukamy indeksu kolumny 'email'
                        $header = array_map('strtolower', array_map('trim', $row));
                        $email_column_index = array_search('email', $header);
                        if ($email_column_index === false) {
                            $redirect_params['message'] = "Nie znaleziono wymaganej kolumny 'email' w nagłówku pliku CSV.";
                            fclose($handle);
                            header("Location: " . $redirect_page_target . "?" . http_build_query($redirect_params));
                            exit();
                        }
                        $header_row = false;
                        continue; // Przejdź do następnego wiersza
                    }

                    // Przetwarzanie wierszy z danymi
                    if (isset($row[$email_column_index])) {
                        $email = trim($row[$email_column_index]);
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            // Dodaj do listy tylko unikalne i poprawne e-maile
                            if (!in_array($email, $emails_to_import)) {
                                $emails_to_import[] = $email;
                            }
                        }
                    }
                }
                fclose($handle);

                // Zapis e-maili do bazy danych
                if (!empty($emails_to_import)) {
                    try {
                        $pdo = getPdoConnection();
                        $pdo->beginTransaction();

                        // Używamy INSERT IGNORE, aby pominąć e-maile, które już istnieją w bazie
                        $stmt = $pdo->prepare("INSERT IGNORE INTO allowed_emails (email) VALUES (?)");
                        $newly_added_count = 0;

                        foreach ($emails_to_import as $email_to_add) {
                            $stmt->execute([$email_to_add]);
                            if ($stmt->rowCount() > 0) {
                                $newly_added_count++;
                            }
                        }
                        $pdo->commit();

                        $redirect_params['status'] = 'success';
                        $redirect_params['message'] = "Import zakończony. Dodano " . $newly_added_count . " nowych adresów e-mail do listy dozwolonych.";

                    } catch (PDOException $e) {
                        if (isset($pdo) && $pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        error_log("Błąd PDO w process_import_emails_csv.php: " . $e->getMessage());
                        $redirect_params['message'] = "Wystąpił błąd serwera podczas importu danych do bazy.";
                    }
                } else {
                    $redirect_params['status'] = 'info';
                    $redirect_params['message'] = "Plik CSV został przetworzony, ale nie znaleziono w nim żadnych nowych, poprawnych adresów e-mail do zaimportowania.";
                }

            } else {
                $redirect_params['message'] = "Nie można otworzyć przesłanego pliku.";
            }
        }
    } else {
        $redirect_params['message'] = "Nie przesłano pliku lub wystąpił błąd podczas przesyłania.";
    }
} else {
    $redirect_params['message'] = "Nieprawidłowe żądanie.";
}

// Przekierowanie z powrotem na stronę zarządzania
header("Location: " . $redirect_page_target . "?" . http_build_query($redirect_params));
exit();
?>