<?php
include 'includes/db.php';

// Handle Add/Edit Category
if (isset($_POST['save_category'])) {
    $name = $_POST['name'];
    $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $db->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
    } else {
        $stmt = $db->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
    }
    header("Location: categories.php");
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
$categories = $db->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$edit_cat = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_cat = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card p-4">
            <h4 class="mb-3"><?= $edit_cat ? 'Kategoriyani tahrirlash' : 'Yangi kategoriya' ?></h4>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $edit_cat['id'] ?? '' ?>">
                <div class="mb-3">
                    <label class="form-label">Nomi</label>
                    <input type="text" name="name" class="form-control" value="<?= $edit_cat['name'] ?? '' ?>" required>
                </div>
                <button type="submit" name="save_category" class="btn btn-primary w-100">
                    <i class="bi bi-check-circle"></i> <?= $edit_cat ? 'Saqlash' : 'Qo\'shish' ?>
                </button>
                <?php if ($edit_cat): ?>
                    <a href="categories.php" class="btn btn-secondary w-100 mt-2">Bekor qilish</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-4">
            <h4 class="mb-3">Kategoriyalar ro'yxati</h4>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nomi</th>
                        <th class="text-end">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= $cat['id'] ?></td>
                        <td><?= $cat['name'] ?></td>
                        <td class="text-end">
                            <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="delete.php?type=category&id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted">Hozircha kategoriyalar yo'q</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
