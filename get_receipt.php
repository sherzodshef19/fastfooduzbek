<?php
include 'includes/db.php';

$order_id = $_GET['order_id'] ?? $_GET['id'] ?? null;
if (!$order_id) die("No order ID");

$stmt = $db->prepare("SELECT o.*, w.name as waiter_name, t.name as table_name 
                    FROM orders o 
                    LEFT JOIN waiters w ON o.waiter_id = w.id 
                    LEFT JOIN tables t ON o.table_id = t.id 
                    WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT oi.*, p.name as product_name 
                    FROM order_items oi 
                    LEFT JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px;">
    <strong>FAST FOOD</strong><br>
    Toshkent sh., Chilonzor<br>
    Tel: +998 90 123 45 67
</div>

<div style="font-size: 12px; margin-bottom: 10px;">
    ID: #<?= $order['id'] ?><br>
    Sana: <?= $order['created_at'] ?><br>
    Ishchi: <?= $order['waiter_name'] ?><br>
    Stol: <?= $order['table_name'] ?>
</div>

<table style="width: 100%; font-size: 12px; border-collapse: collapse;">
    <tr style="border-bottom: 1px dashed #000;">
        <th style="text-align: left;">Nomi</th>
        <th style="text-align: right;">Soni</th>
        <th style="text-align: right;">Narxi</th>
    </tr>
    <?php foreach ($items as $item): ?>
    <tr>
        <td><?= $item['product_name'] ?></td>
        <td style="text-align: right;">x<?= $item['quantity'] ?></td>
        <td style="text-align: right;"><?= number_format($item['price'] * $item['quantity'], 0, '.', ' ') ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<div style="text-align: right; border-top: 1px dashed #000; margin-top: 10px; padding-top: 5px; font-weight: bold;">
    JAMI: <?= number_format($order['total_amount'], 0, '.', ' ') ?> so'm
</div>

<div style="text-align: center; margin-top: 20px; font-size: 10px;">
    Xizmatimizdan foydalanganingiz uchun rahmat!
</div>
