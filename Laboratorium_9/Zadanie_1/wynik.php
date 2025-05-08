<?php

if (isset($_GET['data_urodzenia'])) {
    $dataUrodzeniaString = $_GET['data_urodzenia'];

    function jakiDzienTygodnia($data) {
        $timestamp = strtotime($data);
        $dzienTygodniaNumer = date('w', $timestamp);
        $dniTygodnia = ['Niedziela', 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota'];
        return $dniTygodnia[$dzienTygodniaNumer];
    }

    function obliczLata($data) {
        $teraz = new DateTime();
        $dataUrodzenia = new DateTime($data);
        $roznica = $teraz->diff($dataUrodzenia);
        return $roznica->y;
    }

    function dniDoUrodzin($data) {
        $teraz = new DateTime();
        $dataUrodzenia = new DateTime($data);
        $rokUrodzenia = $dataUrodzenia->format('Y');
        $miesiacDzienUrodzenia = $dataUrodzenia->format('m-d');
        $najblizszeUrodzinyString = date('Y') . '-' . $miesiacDzienUrodzenia;
        $najblizszeUrodziny = new DateTime($najblizszeUrodzinyString);

        if ($najblizszeUrodziny < $teraz) {
            $najblizszeUrodziny->modify('+1 year');
        }

        $roznica = $teraz->diff($najblizszeUrodziny);
        return $roznica->days;
    }

    $dzienUrodzenia = jakiDzienTygodnia($dataUrodzeniaString);
    $lataUkonczone = obliczLata($dataUrodzeniaString);
    $dniDoUrodzin = dniDoUrodzin($dataUrodzeniaString);

    echo "<h1>Wynik:</h1>";
    echo "<p>Urodziłeś się w: <strong>" . $dzienUrodzenia . "</strong>.</p>";
    echo "<p>Ukończyłeś już: <strong>" . $lataUkonczone . "</strong> lat.</p>";
    echo "<p>Do Twoich najbliższych urodzin pozostało: <strong>" . $dniDoUrodzin . "</strong> dni.</p>";

} else {
    echo "<p>Nie podano daty urodzenia.</p>";
}

?>