<?php
include 'includes/db.php';
include 'includes/header.php';

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$products = $db->query("SELECT * FROM products ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$tables = $db->query("SELECT t.*, (SELECT COUNT(*) FROM orders o WHERE o.table_id = t.id AND o.status = 'active') as is_busy FROM tables t ORDER BY t.name ASC")->fetchAll(PDO::FETCH_ASSOC);
$waiters = $db->query("SELECT * FROM waiters ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$products_by_cat = [];
foreach ($products as $p) {
    $products_by_cat[$p['category_id']][] = $p;
}
?>

<style>
    .category-scroll {
        display: flex;
        overflow-x: auto;
        padding-bottom: 10px;
        gap: 12px;
        scrollbar-width: none;
    }
    .category-scroll::-webkit-scrollbar { display: none; }
    
    .nav-pills .nav-link {
        background: white;
        color: var(--dark-color);
        white-space: nowrap;
        border-radius: 16px;
        padding: 10px 24px;
        font-weight: 600;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .nav-pills .nav-link.active {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
    }

    .product-card {
        border-radius: 28px;
        border: 1px solid rgba(255,255,255,0.6);
        background: rgba(255,255,255,0.8);
        overflow: hidden;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .product-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0,0,0,0.08);
    }
    .product-img-container {
        position: relative;
        height: 140px;
        overflow: hidden;
    }
    .product-card img {
        transition: transform 0.6s ease;
    }
    .product-card:hover img {
        transform: scale(1.1);
    }
    .btn-add-quick {
        position: absolute;
        bottom: 10px;
        right: 10px;
        width: 35px;
        height: 35px;
        border-radius: 12px;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        color: var(--primary-color);
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
    }
    .product-card:hover .btn-add-quick {
        opacity: 1;
        transform: translateY(0);
    }

    .cart-sidebar {
        background: white;
        border-radius: 32px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        height: calc(100vh - 120px);
        position: sticky;
        top: 100px;
    }
    .cart-item {
        background: #F8F9FA;
        border-radius: 18px;
        padding: 12px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }
    .cart-item:hover {
        background: white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .quantity-controls {
        background: white;
        border-radius: 12px;
        padding: 4px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }
    .btn-qty {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        border: none;
        background: #F0F2F5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        transition: all 0.2s;
    }
    .btn-qty:hover {
        background: var(--primary-color);
        color: white;
    }
    .table-busy {
        background: rgba(255, 107, 107, 0.2) !important;
        border: 1px solid #FF6B6B !important;
    }
</style>


<div class="row g-4 mb-5">
    <div class="col-lg-8">
        <!-- Categories Section -->
        <div class="mb-4">
            <h5 class="fw-bold mb-3">Bo'limlar</h5>
            <div class="category-scroll nav nav-pills" id="pills-tab" role="tablist">
                <button class="nav-link active" id="pills-all-tab" data-bs-toggle="pill" data-bs-target="#pills-all" type="button">Hammasi</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="nav-link" id="pills-cat-<?= $cat['id'] ?>-tab" data-bs-toggle="pill" data-bs-target="#pills-cat-<?= $cat['id'] ?>" type="button"><?= $cat['name'] ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Products Section -->
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-all">
                <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-4">
                    <?php foreach ($products as $p): ?>
                        <?= renderProductCard($p) ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php foreach ($categories as $cat): ?>
                <div class="tab-pane fade" id="pills-cat-<?= $cat['id'] ?>">
                    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-4">
                        <?php if (isset($products_by_cat[$cat['id']])): ?>
                            <?php foreach ($products_by_cat[$cat['id']] as $p): ?>
                                <?= renderProductCard($p) ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center text-muted py-5">
                                <i class="bi bi-box2 fs-1 d-block mb-3"></i>
                                Hozircha mahsulot yo'q
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Cart Sidebar -->
    <div class="col-lg-4">
        <div class="cart-sidebar p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0"><i class="bi bi-bag-check me-2 text-primary"></i> Buyurtma</h5>
                <span class="badge bg-light text-dark rounded-pill px-3 py-2" id="cart-count">0 ta mahsulot</span>
            </div>
            
            <div class="row g-2 mb-4">
                <div class="col-6">
                    <label class="form-label extra-small text-muted mb-1">Xizmat ko'rsatuvchi</label>
                    <select id="waiter_id" class="form-select form-select-sm border-0 bg-light rounded-3 py-2">
                        <option value="">Tanlang...</option>
                        <?php foreach ($waiters as $w): ?>
                            <option value="<?= $w['id'] ?>"><?= $w['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label extra-small text-muted mb-1">Stol / Joy</label>
                    <select id="table_id" class="form-select form-select-sm border-0 bg-light rounded-3 py-2">
                        <option value="">Tanlang...</option>
                            <?php foreach ($tables as $tab): ?>
                                <option value="<?= $tab['id'] ?>" class="<?= $tab['is_busy'] ? 'bg-light text-danger fw-bold' : '' ?>">
                                    <?= $tab['name'] ?> <?= $tab['is_busy'] ? '(Band)' : '(Bo\'sh)' ?>
                                </option>
                            <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex-grow-1 overflow-auto mb-4 pe-2" id="cart-container">
                <div class="text-center py-5 text-muted" id="empty-cart-msg">
                    <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                    Savat bo'sh
                </div>
                <!-- Items will be injected here -->
            </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fw-bold">Umumiy summa:</span>
                    <span id="cart-total" class="fs-4 fw-800 text-primary">0 so'm</span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1 py-3" onclick="submitOrder('active')">
                        <i class="bi bi-save me-1"></i> Saqlash
                    </button>
                    <button class="btn btn-success flex-grow-1 py-3" onclick="submitOrder('paid')">
                        <i class="bi bi-check2-all me-1"></i> To'lov & Check
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function renderProductCard($p) {
    ob_start();
    ?>
    <div class="col">
        <div class="product-card h-100" onclick="addToCart(<?= htmlspecialchars(json_encode($p)) ?>)">
            <div class="product-img-container">
                <?php if ($p['image']): ?>
                    <img src="assets/uploads/<?= $p['image'] ?>" class="w-100 h-100 object-fit-cover">
                <?php else: ?>
                    <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light bg-gradient">
                        <i class="bi bi-image text-muted opacity-50" style="font-size: 3rem;"></i>
                        <span class="extra-small text-muted">Rasm yo'q</span>
                    </div>
                <?php endif; ?>
                <div class="btn-add-quick">
                    <i class="bi bi-plus-lg"></i>
                </div>
            </div>
            <div class="card-body p-3">
                <h6 class="fw-bold mb-1 text-truncate"><?= $p['name'] ?></h6>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-primary fw-bold"><?= number_format($p['price'], 0, '.', ' ') ?></span>
                    <span class="extra-small text-muted">so'm</span>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>

<script>
let cart = [];
let current_order_id = null;

document.addEventListener('DOMContentLoaded', function() {
    const tableSelect = document.getElementById('table_id');
    tableSelect.addEventListener('change', function() {
        if (this.value) {
            checkActiveOrder(this.value);
        } else {
            resetCart();
        }
    });
});

function checkActiveOrder(tableId) {
    fetch('get_active_order.php?table_id=' + tableId)
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            current_order_id = res.order_id;
            document.getElementById('waiter_id').value = res.waiter_id;
            cart = res.items.map(i => ({
                id: i.product_id,
                name: i.name,
                price: Number(i.price),
                quantity: Number(i.quantity)
            }));
            renderCart();
        } else {
            resetCart();
        }
    });
}

function resetCart() {
    current_order_id = null;
    cart = [];
    renderCart();
}

function addToCart(product) {
    const tableId = document.getElementById('table_id').value;
    if (!tableId) {
        alert('Iltimos, avval stolni tanlang!');
        return;
    }
    const p = {
        id: Number(product.id),
        name: product.name,
        price: Number(product.price),
        quantity: 1
    };
    const existing = cart.find(item => item.id === p.id);
    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push(p);
    }
    renderCart();
}

function updateQuantity(id, delta) {
    const item = cart.find(item => item.id == id);
    if (item) {
        item.quantity += delta;
        if (item.quantity <= 0) {
            cart = cart.filter(i => i.id != id);
        }
    }
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cart-container');
    const countBadge = document.getElementById('cart-count');
    
    container.innerHTML = '';
    let total = 0;
    let itemCount = 0;

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5 text-muted" id="empty-cart-msg">
                <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                Savat bo'sh
            </div>
        `;
    } else {
        cart.forEach(item => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            itemCount += item.quantity;
            
            const div = document.createElement('div');
            div.className = 'cart-item d-flex justify-content-between align-items-center';
            div.innerHTML = `
                <div>
                    <div class="fw-bold small mb-1">${item.name}</div>
                    <div class="text-primary fw-bold small">${numberFormat(subtotal)} <span class="extra-small text-muted">so'm</span></div>
                </div>
                <div class="quantity-controls">
                    <button class="btn-qty" onclick="updateQuantity(${item.id}, -1)">-</button>
                    <span class="small fw-bold px-1">${item.quantity}</span>
                    <button class="btn-qty" onclick="updateQuantity(${item.id}, 1)">+</button>
                </div>
            `;
            container.appendChild(div);
        });
    }

    document.getElementById('cart-total').innerText = numberFormat(total) + " so'm";
    countBadge.innerText = itemCount + ' ta mahsulot';
}

function numberFormat(num) {
    return new Intl.NumberFormat('uz-UZ').format(num);
}

function submitOrder(status = 'active') {
    const waiterSelect = document.getElementById('waiter_id');
    const tableSelect = document.getElementById('table_id');
    const waiter_id = waiterSelect.value;
    const table_id = tableSelect.value;

    waiterSelect.classList.remove('is-invalid');
    tableSelect.classList.remove('is-invalid');

    if (cart.length === 0) {
        alert('Savat bo\'sh! Mahsulot qo\'shing.');
        return;
    }

    if (!waiter_id || !table_id) {
        if (!waiter_id) waiterSelect.classList.add('is-invalid');
        if (!table_id) tableSelect.classList.add('is-invalid');
        alert('Ishchi va stolni tanlang!');
        return;
    }

    const data = {
        order_id: current_order_id,
        waiter_id,
        table_id,
        items: cart,
        status: status,
        total: cart.reduce((sum, item) => sum + (item.price * item.quantity), 0)
    };

    fetch('process.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            if (status === 'paid') {
                showReceipt(res.order_id);
                resetCart();
            } else {
                alert('Buyurtma saqlandi!');
                location.reload(); // Refresh to show 'Band' in dropdown
            }
        } else {
            alert('Xatolik: ' + res.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Server bilan aloqada xatolik!');
    });
}

function showReceipt(orderId) {
    fetch('get_receipt.php?id=' + orderId)
    .then(res => res.text())
    .then(html => {
        document.getElementById('receipt-content').innerHTML = html;
        new bootstrap.Modal(document.getElementById('receiptModal')).show();
    });
}

function printReceipt() {
    const content = document.getElementById('receipt-content').innerHTML;
    const printWindow = window.open('', '', 'height=600,width=400');
    printWindow.document.write('<html><head><title>Check</title>');
    printWindow.document.write('<style>body{font-family:monospace;padding:20px;}</style></head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}
</script>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0" style="border-radius: 24px; overflow: hidden;">
            <div class="modal-body p-4" id="receipt-content">
                <!-- Receipt content for printing -->
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Yopish</button>
                <button type="button" class="btn btn-primary rounded-3 px-4" onclick="printReceipt()">Print</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
