<?php
include 'includes/db.php';

$table_id = $_GET['table_id'] ?? null;
if (!$table_id) {
    echo json_encode(['success' => false, 'message' => 'No table ID']);
    exit;
}

$order = $db->query("SELECT * FROM orders WHERE table_id = $table_id AND status = 'active' LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if ($order) {
    $items = $db->query("SELECT oi.*, p.name, p.price as current_price 
                        FROM order_items oi 
                        JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id = {$order['id']}")->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'order_id' => $order['id'], 
        'waiter_id' => $order['waiter_id'],
        'items' => $items
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'No active order']);
}
?>
