<?php
include 'includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    try {
        $db->beginTransaction();

        $order_id = $data['order_id'] ?? null;
        $status = $data['status'] ?? 'active';

        if ($order_id) {
            // Update existing order total and status
            $stmt = $db->prepare("UPDATE orders SET total_amount = ?, status = ? WHERE id = ?");
            $stmt->execute([$data['total'], $status, $order_id]);
            
            // Delete old items and re-insert (simplest way for merging)
            $db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$order_id]);
        } else {
            // Create new order
            $stmt = $db->prepare("INSERT INTO orders (waiter_id, table_id, total_amount, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['waiter_id'], $data['table_id'], $data['total'], $status]);
            $order_id = $db->lastInsertId();
        }

        foreach ($data['items'] as $item) {
            $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['id'] ?? $item['product_id'], $item['quantity'], $item['price']]);
        }

        $db->commit();
        echo json_encode(['success' => true, 'order_id' => $order_id]);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
