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

class Cart
{
    private array $products;

    public function __construct()
    {
        $this->products = [];
    }

    public function addProduct(Product $product): void
    {
        $this->products[] = $product;
    }

    public function removeProduct(Product $productToRemove): void
    {
        foreach ($this->products as $key => $productInCart) {
            if ($productInCart === $productToRemove) {
                unset($this->products[$key]);
                $this->products = array_values($this->products);
                return;
            }
        }
    }

    public function getTotal(): float
    {
        $totalPrice = 0.0;
        foreach ($this->products as $product) {
            $totalPrice += $product->getPrice() * $product->getQuantity();
        }
        return $totalPrice;
    }

    public function __toString(): string
    {
        if (empty($this->products)) {
            return "Cart is empty.\nTotal price: 0";
        }

        $output = "Products in cart:\n";
        foreach ($this->products as $product) {
            $output .= $product->__toString() . "\n"; // Wykorzystujemy metodę __toString() z klasy Product
        }
        $output .= "Total price: " . $this->getTotal();
        return $output;
    }

    public function getProducts(): array
    {
        return $this->products;
    }
}


?>