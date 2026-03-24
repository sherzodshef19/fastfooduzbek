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
    /* POS Monoblock Optimization */
    .navbar { display: none !important; } /* Hide standard navbar on Kassa */
    body { padding-top: 0 !important; overflow: hidden; height: 100vh; }
    .container { max-width: 100% !important; padding: 0 15px !important; }

    .pos-header {
        height: 60px;
        background: white;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        padding: 0 20px;
        position: fixed;
        top: 0;
        left: 0;
        width: 66.666%; /* Only take up the product area width */
        z-index: 1040;
    }
    
    .pos-content {
        margin-top: 60px;
        height: calc(100vh - 60px);
        overflow-y: auto;
        padding-bottom: 50px;
    }
    
    .category-scroll {
        display: flex;
        overflow-x: auto;
        padding-bottom: 5px;
        gap: 8px;
        scrollbar-width: none;
    }
    .category-scroll::-webkit-scrollbar { display: none; }
    
    .nav-pills .nav-link {
        background: white;
        color: var(--dark-color);
        white-space: nowrap;
        border-radius: 12px;
        padding: 12px 20px;
        font-weight: 600;
        border: 1px solid rgba(0,0,0,0.05);
        font-size: 0.9rem;
    }
    .nav-pills .nav-link.active {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 12px rgba(255, 107, 107, 0.2);
    }

    .product-card {
        border-radius: 20px;
        border: 1px solid rgba(0,0,0,0.05);
        background: white;
        overflow: hidden;
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    .product-card:active { transform: scale(0.95); }
    
    .product-img-container {
        position: relative;
        height: 100px;
        overflow: hidden;
    }
    
    .cart-sidebar {
        background: white;
        border-left: 1px solid rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        height: 100vh;
        position: fixed;
        right: 0;
        top: 0;
        width: 33.333%;
        z-index: 1050;
    }
    
    .cart-container {
        flex-grow: 1;
        overflow-y: auto;
        padding: 15px;
    }

    .cart-item {
        background: #F8F9FA;
        border-radius: 15px;
        padding: 10px;
        margin-bottom: 10px;
    }

    .quantity-controls .btn-qty {
        width: 32px;
        height: 32px;
        font-size: 1.2rem;
    }

    .pos-footer-btns {
        padding: 15px;
        background: white;
        border-top: 1px solid rgba(0,0,0,0.05);
    }

    .extra-small { font-size: 0.75rem; }
    .fw-800 { font-weight: 800; }
</style>


<div class="pos-header">
    <div class="dropdown me-3">
        <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" type="button" data-bs-toggle="dropdown" style="background: var(--primary-gradient) !important; border:none;">
            <i class="bi bi-grid-fill me-2"></i> MENU
        </button>
        <ul class="dropdown-menu shadow border-0 mt-2" style="border-radius: 15px;">
            <li><a class="dropdown-item py-2" href="index.php"><i class="bi bi-calculator me-2"></i> Kassa</a></li>
            <?php if ($role == 'admin'): ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2" href="categories.php"><i class="bi bi-tags me-2"></i> Kategoriyalar</a></li>
                <li><a class="dropdown-item py-2" href="products.php"><i class="bi bi-box-seam me-2"></i> Mahsulotlar</a></li>
                <li><a class="dropdown-item py-2" href="tables.php"><i class="bi bi-table me-2"></i> Stollar</a></li>
                <li><a class="dropdown-item py-2" href="waiters.php"><i class="bi bi-people me-2"></i> Ishchilar</a></li>
                <li><a class="dropdown-item py-2" href="users.php"><i class="bi bi-person-gear me-2"></i> Foydalanuvchilar</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2" href="reports.php"><i class="bi bi-bar-chart-line me-2"></i> Hisobotlar</a></li>
                <li><a class="dropdown-item py-2" href="settings.php"><i class="bi bi-gear me-2"></i> So'zlamalar</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Chiqish</a></li>
        </ul>
    </div>
    <a class="navbar-brand me-auto fw-800" href="index.php" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">FAST FOOD</a>
    <div class="d-flex align-items-center">
        <span class="badge bg-light text-dark me-2 py-2 px-3 border" id="pos-waiter-display">Ishchi: -</span>
        <span class="badge bg-light text-dark py-2 px-3 border" id="pos-table-display">Stol: -</span>
    </div>
</div>

<div class="container-fluid">
    <div class="row g-0">
        <!-- Products Area -->
        <div class="col-lg-8 pe-3 pos-content">
            <!-- Categories Section -->
            <div class="mb-4">
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
                    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
                        <?php foreach ($products as $p): ?>
                            <?= renderProductCard($p) ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php foreach ($categories as $cat): ?>
                    <div class="tab-pane fade" id="pills-cat-<?= $cat['id'] ?>">
                        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
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
            <div class="cart-sidebar">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0"><i class="bi bi-bag-check me-2 text-primary"></i> Savat</h5>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2" id="cart-count">0 ta</span>
                </div>
                
                <div class="p-3 bg-light-subtle border-bottom">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label extra-small text-muted mb-1">Ishchi</label>
                            <select id="waiter_id" class="form-select form-select-sm border-0 bg-light rounded-3 py-2" onchange="updatePosDisplay()">
                                <option value="">Tanlang...</option>
                                <?php foreach ($waiters as $w): ?>
                                    <option value="<?= $w['id'] ?>"><?= $w['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label extra-small text-muted mb-1">Stol / Joy</label>
                            <select id="table_id" class="form-select form-select-sm border-0 bg-light rounded-3 py-2" onchange="updatePosDisplay()">
                                <option value="">Tanlang...</option>
                                    <?php foreach ($tables as $tab): ?>
                                        <option value="<?= $tab['id'] ?>" class="<?= $tab['is_busy'] ? 'bg-light text-danger fw-bold' : '' ?>">
                                            <?= $tab['name'] ?> <?= $tab['is_busy'] ? '(Band)' : '(Bo\'sh)' ?>
                                        </option>
                                    <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="cart-container" id="cart-container">
                    <div class="text-center py-5 text-muted" id="empty-cart-msg">
                        <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                        Savat bo'sh
                    </div>
                </div>

                <div class="pos-footer-btns">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-bold">JAMI:</span>
                        <span id="cart-total" class="fs-3 fw-800 text-primary">0 so'm</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary flex-grow-1 py-3 fw-bold rounded-4" onclick="submitOrder('active')">
                            <i class="bi bi-save me-1"></i> Saqlash
                        </button>
                        <button class="btn btn-primary flex-grow-1 py-3 fw-bold rounded-4 shadow-sm" style="background: var(--primary-gradient) !important;" onclick="submitOrder('paid')">
                            <i class="bi bi-check2-all me-1"></i> TO'LOV
                        </button>
                    </div>
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
        <div class="product-card h-100 shadow-sm" onclick="addToCart(<?= htmlspecialchars(json_encode($p)) ?>)">
            <div class="product-img-container">
                <?php if ($p['image']): ?>
                    <img src="assets/uploads/<?= $p['image'] ?>" class="w-100 h-100 object-fit-cover">
                <?php else: ?>
                    <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light">
                        <i class="bi bi-image text-muted opacity-50" style="font-size: 2rem;"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body p-2 text-center">
                <h6 class="fw-bold mb-1 small text-truncate"><?= $p['name'] ?></h6>
                <div class="text-primary fw-bold small"><?= number_format($p['price'], 0, '.', ' ') ?> so'm</div>
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
    updatePosDisplay(); // Initial display
});

function updatePosDisplay() {
    const waiterText = document.getElementById('waiter_id').options[document.getElementById('waiter_id').selectedIndex].text;
    const tableText = document.getElementById('table_id').options[document.getElementById('table_id').selectedIndex].text;
    
    document.getElementById('pos-waiter-display').innerText = "Ishchi: " + (waiterText === 'Tanlang...' ? '-' : waiterText);
    document.getElementById('pos-table-display').innerText = "Stol: " + (tableText === 'Tanlang...' ? '-' : tableText.split(' (')[0]);
}

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

let last_receipt_order_id = null;
function showReceipt(orderId) {
    last_receipt_order_id = orderId;
    fetch('get_receipt.php?id=' + orderId)
    .then(res => res.text())
    .then(html => {
        document.getElementById('receipt-content').innerHTML = html;
        new bootstrap.Modal(document.getElementById('receiptModal')).show();
    });
}

function printReceipt() {
    if (!last_receipt_order_id) return;
    
    const printBtn = document.querySelector('#receiptModal .btn-primary');
    const originalText = printBtn.innerHTML;
    printBtn.disabled = true;
    printBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>...';

    // 1. Server-side print (to both IP printers)
    fetch('print_handler.php?order_id=' + last_receipt_order_id)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            console.log("Printing started:", data.results);
            // Optionally close the modal on success
            bootstrap.Modal.getInstance(document.getElementById('receiptModal')).hide();
        } else {
            alert("Printer xatosi: " + data.message);
        }
    })
    .catch(err => console.error("Print error:", err))
    .finally(() => {
        printBtn.disabled = false;
        printBtn.innerHTML = originalText;
    });
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
