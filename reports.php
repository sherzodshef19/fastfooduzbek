<?php
include 'includes/db.php';

// 1. Data Processing & Filters
$waiters = $db->query("SELECT * FROM waiters ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$from_date = $_GET['from_date'] ?? date('Y-m-d');
$to_date = $_GET['to_date'] ?? date('Y-m-d');
$waiter_id = $_GET['waiter_id'] ?? '';

$sql = "SELECT o.*, w.name as waiter_name, t.name as table_name 
        FROM orders o 
        LEFT JOIN waiters w ON o.waiter_id = w.id 
        LEFT JOIN tables t ON o.table_id = t.id 
        WHERE DATE(o.created_at) BETWEEN ? AND ?";
$params = [$from_date, $to_date];

if ($waiter_id) {
    $sql .= " AND o.waiter_id = ?";
    $params[] = $waiter_id;
}

$sql .= " ORDER BY o.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch items for these orders to show in table and calc total qty
$order_ids = array_map(function($o) { return $o['id']; }, $orders);
$order_items_map = [];
$total_items_count = 0;
if (!empty($order_ids)) {
    $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
    $items_stmt = $db->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id IN ($placeholders)");
    $items_stmt->execute($order_ids);
    $all_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($all_items as $item) {
        $order_items_map[$item['order_id']][] = $item['name'] . ' (' . $item['quantity'] . ')';
        $total_items_count += $item['quantity'];
    }
}

$total_period = 0;
foreach ($orders as $o) {
    if ($o['status'] == 'paid') {
        $total_period += $o['total_amount'];
    }
}

include 'includes/header.php';

// Access Control
if ($role !== 'admin') {
    echo "<div class='alert alert-danger'>Kirish taqiqlangan!</div>";
    include 'includes/footer.php';
    exit;
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="bi bi-bar-chart"></i> Savdo Hisoboti</h4>
            </div>

            <div class="mb-4 bg-light p-3 rounded-4">
                <form class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label extra-small text-muted mb-1">Dan</label>
                        <input type="date" name="from_date" class="form-control" value="<?= $from_date ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label extra-small text-muted mb-1">Gacha</label>
                        <input type="date" name="to_date" class="form-control" value="<?= $to_date ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label extra-small text-muted mb-1">Ishchi</label>
                        <select name="waiter_id" class="form-select">
                            <option value="">Barchasi</option>
                            <?php foreach ($waiters as $w): ?>
                                <option value="<?= $w['id'] ?>" <?= $waiter_id == $w['id'] ? 'selected' : '' ?>><?= $w['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filter</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="table-light">
                            <th>ID</th>
                            <th>Sana</th>
                            <th>Ishchi</th>
                            <th>Mahsulotlar</th>
                            <th>Stol</th>
                            <th>Summa</th>
                            <th class="text-end">Harakat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                        <tr class="<?= $o['status'] == 'active' ? 'opacity-75' : '' ?>">
                            <td><span class="badge bg-light text-dark">#<?= $o['id'] ?></span></td>
                            <td class="small"><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></td>
                            <td class="fw-600"><?= $o['waiter_name'] ?></td>
                            <td class="extra-small text-muted">
                                <?= isset($order_items_map[$o['id']]) ? implode(', ', $order_items_map[$o['id']]) : '-' ?>
                            </td>
                            <td><?= $o['table_name'] ?></td>
                            <td>
                                <div class="fw-bold"><?= number_format($o['total_amount'], 0, '.', ' ') ?> so'm</div>
                                <?php if ($o['status'] == 'active'): ?>
                                    <span class="badge bg-warning extra-small">To'lanmagan</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button onclick="showReceipt(<?= $o['id'] ?>)" class="btn btn-sm btn-outline-primary border-0 rounded-circle"><i class="bi bi-eye-fill"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Ushbu kunda buyurtmalar yo'q</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($orders)): ?>
                    <tfoot class="table-light border-top-2">
                        <tr class="fw-bold h5">
                            <td colspan="3" class="text-end">JAMI:</td>
                            <td><?= $total_items_count ?> ta mahsulot</td>
                            <td></td>
                            <td class="text-primary"><?= number_format($total_period, 0, '.', ' ') ?> so'm</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0" style="border-radius: 20px;">
            <div class="modal-body p-4" id="receipt-content"></div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Yopish</button>
                <button type="button" class="btn btn-primary rounded-3 px-4" onclick="window.print()">Print</button>
            </div>
        </div>
    </div>
</div>

<script>
function showReceipt(orderId) {
    fetch('get_receipt.php?order_id=' + orderId)
    .then(res => res.text())
    .then(html => {
        document.getElementById('receipt-content').innerHTML = html;
        new bootstrap.Modal(document.getElementById('receiptModal')).show();
    });
}
</script>

<?php include 'includes/footer.php'; ?>
