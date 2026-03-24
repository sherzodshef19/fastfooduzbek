<?php
include 'includes/db.php';

// Start session to check role before including header.php (which starts output)
session_start();
$role = $_SESSION['role'] ?? null;

if ($role != 'admin') {
    header("Location: index.php");
    exit;
}

// Handle Check (AJAX) - MUST BE BEFORE HTML/HEADER IF WE WANT TO RETURN JSON
if (isset($_GET['check_ip'])) {
    $ip = $_GET['check_ip'];
    $port = 9100; // Standard RAW port for POS printers
    
    // Use fsockopen for a simple connection test
    $connection = @fsockopen($ip, $port, $errno, $errstr, 2);
    
    header('Content-Type: application/json');
    if ($connection) {
        fclose($connection);
        echo json_encode(['success' => true, 'message' => "Printer bilan aloqa o'rnatildi (Ulandi)"]);
    } else {
        echo json_encode(['success' => false, 'message' => "Printer bilan bog'lanib bo'lmadi ($errstr)"]);
    }
    exit;
}

// Handle Save
if (isset($_POST['save_settings'])) {
    foreach ($_POST['set'] as $key => $value) {
        $stmt = $db->prepare("UPDATE settings SET value = ? WHERE key = ?");
        $stmt->execute([$value, $key]);
    }
    $_SESSION['msg'] = "So'zlamalar saqlandi!";
    $_SESSION['msg_type'] = "success";
    header("Location: settings.php");
    exit;
}

// Get current settings
$settings_raw = $db->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_ASSOC);
$settings = [];
foreach ($settings_raw as $s) {
    $settings[$s['key']] = $s['value'];
}

// Handle Database Clearing
if (isset($_POST['clear_database'])) {
    $type = $_POST['clear_type'] ?? '';
    
    try {
        if ($type === 'orders_only') {
            $db->exec("DELETE FROM order_items");
            $db->exec("DELETE FROM orders");
            $_SESSION['msg'] = "Buyurtmalar tarixi muvaffaqiyatli tozalandi!";
        } elseif ($type === 'full_clear') {
            // Disable foreign keys temporarily if needed, though SQLite allows simple deletes
            $db->exec("DELETE FROM order_items");
            $db->exec("DELETE FROM orders");
            $db->exec("DELETE FROM products");
            $db->exec("DELETE FROM categories");
            $db->exec("DELETE FROM tables");
            $db->exec("DELETE FROM waiters");
            // Keep admin users, delete others if any
            $db->exec("DELETE FROM users WHERE role != 'admin'");
            
            $_SESSION['msg'] = "Baza to'liq tozalandi! Tizim boshlang'ich holatga qaytarildi.";
        }
        $_SESSION['msg_type'] = "warning";
    } catch (Exception $e) {
        $_SESSION['msg'] = "Xatolik yuz berdi: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
    }
    
    header("Location: settings.php");
    exit;
}

include 'includes/header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-5">
        <div class="card p-5 border-0 shadow-lg" style="border-radius: 40px; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px);">
            <div class="text-center mb-5">
                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm mb-3" style="width: 80px; height: 80px; background: var(--primary-gradient) !important;">
                    <i class="bi bi-gear-fill text-white fs-1"></i>
                </div>
                <h2 class="fw-800 mb-1">So'zlamalar</h2>
                <p class="text-muted">Tizim va printer konfiguratsiyasi</p>
            </div>
            
            <?php if (isset($_SESSION['msg'])): ?>
                <div class="alert alert-<?= $_SESSION['msg_type'] ?> alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['msg'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-5 pb-2 border-bottom">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-light rounded-circle p-2 me-3">
                            <i class="bi bi-shop text-primary"></i>
                        </div>
                        <h5 class="m-0 fw-bold">Do'kon ma'lumotlari</h5>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted ps-2 mb-2">DO'KON NOMI (CHEK UCHUN)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3"><i class="bi bi-tag text-muted"></i></span>
                            <input type="text" name="set[store_name]" class="form-control border-start-0 py-3 rounded-end-pill" value="<?= $settings['store_name'] ?? 'FAST FOOD' ?>" placeholder="Do'kon nomi">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted ps-2 mb-2">MANZIL</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3"><i class="bi bi-geo-alt text-muted"></i></span>
                            <input type="text" name="set[store_address]" class="form-control border-start-0 py-3 rounded-end-pill" value="<?= $settings['store_address'] ?? '' ?>" placeholder="Toshkent sh., Chilonzor">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted ps-2 mb-2">TELEFON</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3"><i class="bi bi-telephone text-muted"></i></span>
                            <input type="text" name="set[store_phone]" class="form-control border-start-0 py-3 rounded-end-pill" value="<?= $settings['store_phone'] ?? '' ?>" placeholder="+998 90 123 45 67">
                        </div>
                    </div>
                </div>

                <div class="mb-5 pb-2">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-light rounded-circle p-2 me-3">
                            <i class="bi bi-printer text-primary"></i>
                        </div>
                        <h5 class="m-0 fw-bold">POS Printerlar</h5>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted ps-2 mb-2">KASSA PRINTER IP</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3"><i class="bi bi-pc-display text-muted"></i></span>
                            <input type="text" name="set[kassa_printer_ip]" class="form-control border-start-0 py-3 rounded-0" value="<?= $settings['kassa_printer_ip'] ?? '127.0.0.1' ?>" placeholder="192.168.1.100">
                            <button type="button" class="btn btn-light border border-start-0 rounded-end-pill px-4 text-primary fw-bold" onclick="checkPrinter('kassa_printer_ip')">
                                <i class="bi bi-broadcast"></i> Test
                            </button>
                        </div>
                        <div id="status-kassa_printer_ip" class="small mt-2 px-3 fw-500"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted ps-2 mb-2">OSHXONA PRINTER IP (COOK)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3"><i class="bi bi-cup-hot text-muted"></i></span>
                            <input type="text" name="set[cook_printer_ip]" class="form-control border-start-0 py-3 rounded-0" value="<?= $settings['cook_printer_ip'] ?? '127.0.0.1' ?>" placeholder="192.168.1.101">
                            <button type="button" class="btn btn-light border border-start-0 rounded-end-pill px-4 text-primary fw-bold" onclick="checkPrinter('cook_printer_ip')">
                                <i class="bi bi-broadcast"></i> Test
                            </button>
                        </div>
                        <div id="status-cook_printer_ip" class="small mt-2 px-3 fw-500"></div>
                    </div>
                </div>

                <div class="d-grid gap-2 pt-2 mb-5">
                    <button type="submit" name="save_settings" class="btn btn-primary btn-lg rounded-pill py-3 shadow-lg fw-bold">
                        <i class="bi bi-cloud-check me-2"></i> So'zlamalarni saqlash
                    </button>
                </div>
            </form>

            <!-- Danger Zone -->
            <div class="mt-5 pt-4 border-top text-center">
                <div class="d-flex align-items-center justify-content-center mb-4">
                    <div class="bg-danger-subtle rounded-circle p-2 me-3">
                        <i class="bi bi-exclamation-triangle text-danger"></i>
                    </div>
                    <h5 class="m-0 fw-bold text-danger">Xavfli zona</h5>
                </div>
                
                <p class="small text-muted mb-4">Diqqat: Ushbu amallarni ortga qaytarib bo'lmaydi. Iltimos, ehtiyot bo'ling.</p>

                <div class="d-grid gap-3">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill py-2" onclick="confirmClear('orders_only')">
                        <i class="bi bi-trash3 me-2"></i> Buyurtmalarni tozalash (Sotuv tarixi)
                    </button>
                    <button type="button" class="btn btn-danger btn-sm rounded-pill py-2" onclick="confirmClear('full_clear')">
                        <i class="bi bi-fire me-2"></i> To'liq tozalash (Full Reset)
                    </button>
                </div>
                
                <div class="mt-4">
                    <a href="index.php" class="btn btn-link text-muted text-decoration-none small">Bosh sahifaga qaytish</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clear Database Modal -->
<div class="modal fade" id="clearDataModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 30px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle"></i> Diqqat!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p id="clearModalMsg" class="mb-0"></p>
                <form id="clearForm" method="POST">
                    <input type="hidden" name="clear_database" value="1">
                    <input type="hidden" name="clear_type" id="clear_type_input">
                    <div id="fullClearConfirm" class="mt-3 d-none">
                        <label class="form-label small fw-bold">Tasdiqlash uchun "TOZALASH" so'zini yozing:</label>
                        <input type="text" id="confirmText" class="form-control rounded-pill text-center border-danger" placeholder="...">
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Bekor qilish</button>
                <button type="button" id="confirmClearBtn" class="btn btn-danger rounded-pill px-4 shadow" disabled>Tozalash</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentClearType = '';
    const clearModalElement = document.getElementById('clearDataModal');
    if (!clearModalElement) return;
    
    const clearModal = new bootstrap.Modal(clearModalElement);

    window.confirmClear = function(type) {
        currentClearType = type;
        document.getElementById('clear_type_input').value = type;
        const msgElement = document.getElementById('clearModalMsg');
        const fullClearSection = document.getElementById('fullClearConfirm');
        const confirmBtn = document.getElementById('confirmClearBtn');
        const confirmInput = document.getElementById('confirmText');

        confirmInput.value = '';
        
        if (type === 'orders_only') {
            msgElement.innerText = "Barcha buyurtmalar va sotuv tarixi o'chib ketadi. Mahsulotlar va boshqa ma'lumotlar saqlanib qoladi. Rozimisiz?";
            fullClearSection.classList.add('d-none');
            confirmBtn.disabled = false;
        } else {
            msgElement.innerHTML = "<strong>Diqqat!</strong> Butun baza (maxsulotlar, ishchilar, stollar, kategoriyalar va barcha buyurtmalar) o'tib ketadi. Faqat admin foydalanuvchisi qoladi. Bu amalni ortga qaytarib bo'lmaydi!";
            fullClearSection.classList.remove('d-none');
            confirmBtn.disabled = true;
        }
        
        clearModal.show();
    }

    document.getElementById('confirmText').addEventListener('input', function() {
        const confirmBtn = document.getElementById('confirmClearBtn');
        if (currentClearType === 'full_clear') {
            confirmBtn.disabled = this.value.toUpperCase() !== 'TOZALASH';
        }
    });

    document.getElementById('confirmClearBtn').addEventListener('click', function() {
        document.getElementById('clearForm').submit();
    });
});

function checkPrinter(type) {
    const input = document.querySelector(`input[name="set[${type}]"]`);
    const statusDiv = document.getElementById(`status-${type}`);
    if (!input || !statusDiv) return;
    const ip = input.value;

    if (!ip) {
        statusDiv.innerHTML = '<span class="text-danger">IP manzili kiritilmagan</span>';
        return;
    }

    statusDiv.innerHTML = '<span class="text-muted"><div class="spinner-border spinner-border-sm me-2 text-primary" role="status"></div>Tekshirilmoqda...</span>';

    fetch(`settings.php?check_ip=${ip}`)
    .then(res => {
        if (!res.ok) throw new Error('Network response was not ok');
        return res.json();
    })
    .then(data => {
        if (data.success) {
            statusDiv.innerHTML = `<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> ${data.message}</span>`;
        } else {
            statusDiv.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> ${data.message}</span>`;
        }
    })
    .catch(err => {
        statusDiv.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i> Xatolik: Aloqa o'rnatib bo'lmadi</span>`;
    });
}
</script>

<?php include 'includes/footer.php'; ?>
