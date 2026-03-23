<?php
include 'includes/db.php';

// Handle Add/Edit Product
if (isset($_POST['save_product'])) {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $id = $_POST['id'] ?? null;
    $image_name = $_POST['current_image'] ?? '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "assets/uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $image_name = time() . "_" . uniqid() . "." . $extension;
        $target_file = $target_dir . $image_name;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    if ($id) {
        $stmt = $db->prepare("UPDATE products SET category_id = ?, name = ?, price = ?, image = ? WHERE id = ?");
        $stmt->execute([$category_id, $name, $price, $image_name, $id]);
    } else {
        $stmt = $db->prepare("INSERT INTO products (category_id, name, price, image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$category_id, $name, $price, $image_name]);
    }
    header("Location: products.php");
    exit;
}

include 'includes/header.php';

// Display Messages
if (isset($_SESSION['msg'])) {
    echo "<div class='alert alert-{$_SESSION['msg_type']} alert-dismissible fade show rounded-4 shadow-sm mb-4'>
            {$_SESSION['msg']}
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
          </div>";
    unset($_SESSION['msg'], $_SESSION['msg_type']);
}

if ($role != 'admin') {
    header("Location: index.php");
    exit;
}
?>
<style>
    .admin-card {
        background: white;
        border-radius: 32px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        border: none;
    }
    .table thead th {
        background: #F8F9FA;
        border: none;
        padding: 15px;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
    }
    .table td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #F0F2F5;
    }
</style>

<?php
$products = $db->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$edit_prod = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_prod = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card p-4">
            <h4 class="mb-3"><?= $edit_prod ? 'Mahsulotni tahrirlash' : 'Yangi mahsulot' ?></h4>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $edit_prod['id'] ?? '' ?>">
                <input type="hidden" name="current_image" value="<?= $edit_prod['image'] ?? '' ?>">
                
                <div class="mb-3">
                    <label class="form-label">Kategoriya</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Tanlang...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($edit_prod) && $edit_prod['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Nomi</label>
                    <input type="text" name="name" class="form-control" value="<?= $edit_prod['name'] ?? '' ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Summasi</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?= $edit_prod['price'] ?? '' ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Rasmi</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <?php if (isset($edit_prod['image']) && $edit_prod['image']): ?>
                        <img src="assets/uploads/<?= $edit_prod['image'] ?>" class="mt-2 rounded" style="width: 50px;">
                    <?php endif; ?>
                </div>
                
                <button type="submit" name="save_product" class="btn btn-primary w-100">
                    <i class="bi bi-check-circle"></i> <?= $edit_prod ? 'Saqlash' : 'Qo\'shish' ?>
                </button>
                <?php if ($edit_prod): ?>
                    <a href="products.php" class="btn btn-secondary w-100 mt-2">Bekor qilish</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-4">
            <h4 class="mb-3">Mahsulotlar ro'yxati</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Rasm</th>
                            <th>Nomi</th>
                            <th>Kategoriya</th>
                            <th>Summasi</th>
                            <th class="text-end">Amallar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $prod): ?>
                        <tr>
                            <td>
                                <?php if ($prod['image']): ?>
                                    <img src="assets/uploads/<?= $prod['image'] ?>" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded text-center pt-2" style="width: 40px; height: 40px;"><i class="bi bi-image text-muted"></i></div>
                                <?php endif; ?>
                            </td>
                            <td><?= $prod['name'] ?></td>
                            <td><span class="badge bg-info text-dark"><?= $prod['cat_name'] ?></span></td>
                            <td><?= number_format($prod['price'], 0, '.', ' ') ?> so'm</td>
                            <td class="text-end">
                                <a href="products.php?edit=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <a href="delete.php?type=product&id=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Hozircha mahsulotlar yo'q</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
