<?php

session_start();

require_once '../backend/core/Database.php';
require_once '../backend/controller/OrderController.php';
require_once '../backend/dto/OrderDTO.php';
require_once '../backend/repositories/CustomerRepository.php';
require_once '../backend/repositories/OrderRepository.php';
require_once '../backend/repositories/ProductRepository.php';
require_once '../backend/services/OrderService.php';

use Controller\OrderController;
use core\Database;

if (!isset($_SESSION['cart']) || !isset($_SESSION['order'])) {
    header('Location: cart.php');
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

$controller = new OrderController($pdo);
$controller->handleSessionOrder($_SESSION);
$controller->handleRequest();
