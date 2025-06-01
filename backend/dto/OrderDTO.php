<?php

namespace DTO;

class OrderDTO
{
    public array $customer;
    public array $items;
    public float $totalPrice;

    public function __construct(array $customer, array $items, float $totalPrice)
    {
        $this->customer = $customer;
        $this->items = $items;
        $this->totalPrice = $totalPrice;
    }
}
