<?php
include 'includes/db.php';

// Handle Add/Edit Table
if (isset($_POST['save_table'])) {
    $name = $_POST['name'];
    $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $db->prepare("UPDATE tables SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
    } else {
        $stmt = $db->prepare("INSERT INTO tables (name) VALUES (?)");
        $stmt->execute([$name]);
    }
    header("Location: tables.php");
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

$tables = $db->query("SELECT * FROM tables ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$edit_table = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM tables WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_table = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card p-4">
            <h4 class="mb-3"><?= $edit_table ? 'Stolni tahrirlash' : 'Yangi stol' ?></h4>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $edit_table['id'] ?? '' ?>">
                <div class="mb-3">
                    <label class="form-label">Stol nomi / raqami</label>
                    <input type="text" name="name" class="form-control" value="<?= $edit_table['name'] ?? '' ?>" placeholder="Masalan: Stol 1, Olib ketish, Dostavka" required>
                </div>
                <button type="submit" name="save_table" class="btn btn-primary w-100">
                    <i class="bi bi-check-circle"></i> <?= $edit_table ? 'Saqlash' : 'Qo\'shish' ?>
                </button>
                <?php if ($edit_table): ?>
                    <a href="tables.php" class="btn btn-secondary w-100 mt-2">Bekor qilish</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-4">
            <h4 class="mb-3">Stollar ro'yxati</h4>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nomi</th>
                        <th class="text-end">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tables as $tab): ?>
                    <tr>
                        <td><?= $tab['id'] ?></td>
                        <td><?= $tab['name'] ?></td>
                        <td class="text-end">
                            <a href="tables.php?edit=<?= $tab['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="delete.php?type=table&id=<?= $tab['id'] ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($tables)): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted">Hozircha stollar yo'q</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
