<?php
include 'includes/db.php';
include 'includes/printer.php';

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    echo json_encode(['success' => false, 'message' => "Order ID not provided"]);
    exit;
}

// Fetch settings
$settings_raw = $db->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_ASSOC);
$settings = [];
foreach ($settings_raw as $s) {
    $settings[$s['key']] = $s['value'];
}

$kassa_ip = $settings['kassa_printer_ip'] ?? '';
$cook_ip = $settings['cook_printer_ip'] ?? '';

// Fetch Order
$stmt = $db->prepare("SELECT o.*, w.name as waiter_name, t.name as table_name 
                    FROM orders o 
                    LEFT JOIN waiters w ON o.waiter_id = w.id 
                    LEFT JOIN tables t ON o.table_id = t.id 
                    WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo json_encode(['success' => false, 'message' => "Order not found"]);
    exit;
}

// Fetch Items
$stmt = $db->prepare("SELECT oi.*, p.name as product_name 
                    FROM order_items oi 
                    LEFT JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$results = [];

// 1. Print to Kassa Printer
if ($kassa_ip) {
    $printer = new PosPrinter($kassa_ip);
    $data = PosPrinter::formatReceipt($order, $items, [
        'name' => $settings['store_name'] ?? 'FAST FOOD',
        'address' => $settings['store_address'] ?? '',
        'phone' => $settings['store_phone'] ?? ''
    ]);
    $results['kassa'] = $printer->send($data);
} else {
    $results['kassa'] = ['success' => false, 'message' => "Kassa printer IP topilmadi"];
}

// 2. Print to Cook Printer
if ($cook_ip) {
    $printer = new PosPrinter($cook_ip);
    $data = PosPrinter::formatKitchen($order, $items);
    $results['cook'] = $printer->send($data);
} else {
    $results['cook'] = ['success' => false, 'message' => "Cook printer IP topilmadi"];
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'results' => $results
]);
