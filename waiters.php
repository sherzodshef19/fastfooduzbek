<?php
include 'includes/db.php';

// Handle Add/Edit Waiter
if (isset($_POST['save_waiter'])) {
    $name = $_POST['name'];
    $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $db->prepare("UPDATE waiters SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
    } else {
        $stmt = $db->prepare("INSERT INTO waiters (name) VALUES (?)");
        $stmt->execute([$name]);
    }
    header("Location: waiters.php");
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
$waiters = $db->query("SELECT * FROM waiters ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$edit_waiter = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM waiters WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_waiter = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card p-4">
            <h4 class="mb-3"><?= $edit_waiter ? 'Ishchini tahrirlash' : 'Yangi ishchi' ?></h4>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $edit_waiter['id'] ?? '' ?>">
                <div class="mb-3">
                    <label class="form-label">F.I.O</label>
                    <input type="text" name="name" class="form-control" value="<?= $edit_waiter['name'] ?? '' ?>" placeholder="Ishchi ismini kiriting" required>
                </div>
                <button type="submit" name="save_waiter" class="btn btn-primary w-100">
                    <i class="bi bi-check-circle"></i> <?= $edit_waiter ? 'Saqlash' : 'Qo\'shish' ?>
                </button>
                <?php if ($edit_waiter): ?>
                    <a href="waiters.php" class="btn btn-secondary w-100 mt-2">Bekor qilish</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-4">
            <h4 class="mb-3">Ishchilar ro'yxati</h4>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ismi</th>
                        <th class="text-end">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($waiters as $waiter): ?>
                    <tr>
                        <td><?= $waiter['id'] ?></td>
                        <td><?= $waiter['name'] ?></td>
                        <td class="text-end">
                            <a href="waiters.php?edit=<?= $waiter['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="delete.php?type=waiter&id=<?= $waiter['id'] ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($waiters)): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted">Hozircha ishchilar yo'q</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
