<?php

class Product
{
    private string $name;
    private float $price;
    private int $quantity;

    public function __construct(string $name, float $price, int $quantity)
    {
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        if ($price >= 0) { // Cena nie powinna być ujemna
            $this->price = $price;
        }
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        if ($quantity >= 0) { // Ilość nie powinna być ujemna
            $this->quantity = $quantity;
        }
    }

    public function __toString(): string
    {
        return "Product: " . $this->name . ", Price: " . $this->price . ", Quantity: " . $this->quantity;
    }
}

?>