<?php
class NoweAuto
{
    protected string $model;
    protected float $cenaEuro;
    protected float $kursEuroPLN;

    public function __construct(string $model, float $cenaEuro, float $kursEuroPLN)
    {
        $this->model = $model;
        $this->cenaEuro = $cenaEuro;
        $this->kursEuroPLN = $kursEuroPLN;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getCenaEuro(): float
    {
        return $this->cenaEuro;
    }

    public function getKursEuroPLN(): float
    {
        return $this->kursEuroPLN;
    }

    public function setKursEuroPLN(float $kurs): void
    {
        if ($kurs > 0) {
            $this->kursEuroPLN = $kurs;
        }
    }

    public function ObliczCene(): float
    {
        return $this->cenaEuro * $this->kursEuroPLN;
    }

    public function wyswietlInformacje(): string
    {
        return "Model: " . $this->model . ", Cena bazowa: " . $this->cenaEuro . " EUR, Kurs EUR/PLN: " . $this->kursEuroPLN;
    }
}


class AutoZDodatkami extends NoweAuto
{
    protected float $alarm;
    protected float $radio;
    protected float $klimatyzacja;

    public function __construct(
        string $model,
        float $cenaEuro,
        float $kursEuroPLN,
        float $cenaAlarmEuro,
        float $cenaRadioEuro,
        float $cenaKlimatyzacjaEuro
    ) {
        parent::__construct($model, $cenaEuro, $kursEuroPLN);
        $this->alarm = $cenaAlarmEuro;
        $this->radio = $cenaRadioEuro;
        $this->klimatyzacja = $cenaKlimatyzacjaEuro;
    }

    public function getCenaAlarm(): float
    {
        return $this->alarm;
    }

    public function getCenaRadio(): float
    {
        return $this->radio;
    }

    public function getCenaKlimatyzacja(): float
    {
        return $this->klimatyzacja;
    }

    public function ObliczCene(): float
    {
        $lacznaCenaEuro = $this->cenaEuro + $this->alarm + $this->radio + $this->klimatyzacja;
        return $lacznaCenaEuro * $this->kursEuroPLN;
    }

    public function wyswietlInformacje(): string
    {
        return parent::wyswietlInformacje() .
            ", Alarm: " . $this->alarm . " EUR" .
            ", Radio: " . $this->radio . " EUR" .
            ", Klimatyzacja: " . $this->klimatyzacja . " EUR";
    }
}

class Ubezpieczenie extends AutoZDodatkami
{
    protected float $procentowaWartoscUbezpieczenia;
    protected int $liczbaLatPosiadania;

    public function __construct(
        string $model,
        float $cenaEuro,
        float $kursEuroPLN,
        float $cenaAlarmEuro,
        float $cenaRadioEuro,
        float $cenaKlimatyzacjaEuro,
        float $procentUbezpieczenia,
        int $latPosiadania
    ) {
        parent::__construct($model, $cenaEuro, $kursEuroPLN, $cenaAlarmEuro, $cenaRadioEuro, $cenaKlimatyzacjaEuro);
        $this->procentowaWartoscUbezpieczenia = $procentUbezpieczenia / 100;
        $this->liczbaLatPosiadania = $latPosiadania;
    }

    public function getProcentowaWartoscUbezpieczenia(): float
    {
        return $this->procentowaWartoscUbezpieczenia * 100;
    }

    public function getLiczbaLatPosiadania(): int
    {
        return $this->liczbaLatPosiadania;
    }

    public function ObliczCene(): float
    {
        $wartoscSamochoduZDodatkamiPLN = parent::ObliczCene();

        $mnoznikAmortyzacji = (100.0 - $this->liczbaLatPosiadania) / 100.0;

        if ($mnoznikAmortyzacji < 0) {
            $mnoznikAmortyzacji = 0;
        }

        $wartoscDoUbezpieczenia = $wartoscSamochoduZDodatkamiPLN * $mnoznikAmortyzacji;

        return $this->procentowaWartoscUbezpieczenia * $wartoscDoUbezpieczenia;
    }

    public function wyswietlInformacje(): string
    {
        return parent::wyswietlInformacje() .
            ", Procent ubezpieczenia: " . ($this->procentowaWartoscUbezpieczenia * 100) . "%" .
            ", Liczba lat posiadania: " . $this->liczbaLatPosiadania . " lat";
    }
}

?>