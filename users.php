<?php
include 'includes/db.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? null;
if ($role != 'admin') {
    header("Location: index.php");
    exit;
}

$message = '';
$error = '';

// Handle Add/Edit User
if (isset($_POST['save_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_role = $_POST['user_role'];
    $id = $_POST['id'] ?? null;

    try {
        if ($id) {
            if (!empty($password)) {
                $stmt = $db->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
                $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $user_role, $id]);
            } else {
                $stmt = $db->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
                $stmt->execute([$username, $user_role, $id]);
            }
            $_SESSION['msg'] = "Foydalanuvchi muvaffaqiyatli yangilandi!";
        } else {
            $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $user_role]);
            $_SESSION['msg'] = "Yangi foydalanuvchi qo'shildi!";
        }
        $_SESSION['msg_type'] = "success";
        header("Location: users.php");
        exit;
    } catch (Exception $e) {
        $error = "Xatolik: " . $e->getMessage();
    }
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

if ($error) {
    echo "<div class='alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4'>
            $error
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
          </div>";
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
$users = $db->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$edit_user = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card p-4">
            <h4 class="mb-3"><?= $edit_user ? 'Foydalanuvchini tahrirlash' : 'Yangi foydalanuvchi' ?></h4>
            
            <form method="POST">
                <input type="hidden" name="id" value="<?= $edit_user['id'] ?? '' ?>">
                
                <div class="mb-3">
                    <label class="form-label">Login</label>
                    <input type="text" name="username" class="form-control" value="<?= $edit_user['username'] ?? '' ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Parol <?= $edit_user ? '(o\'zgartirish uchun yozing)' : '' ?></label>
                    <input type="password" name="password" class="form-control" <?= $edit_user ? '' : 'required' ?>>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Roli</label>
                    <select name="user_role" class="form-select" required>
                        <option value="admin" <?= (isset($edit_user) && $edit_user['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                        <option value="kassir" <?= (isset($edit_user) && $edit_user['role'] == 'kassir') ? 'selected' : '' ?>>Kassir</option>
                    </select>
                </div>
                
                <button type="submit" name="save_user" class="btn btn-primary w-100">
                    <i class="bi bi-check-circle"></i> <?= $edit_user ? 'Saqlash' : 'Qo\'shish' ?>
                </button>
                <?php if ($edit_user): ?>
                    <a href="users.php" class="btn btn-secondary w-100 mt-2">Bekor qilish</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-4">
            <h4 class="mb-3">Foydalanuvchilar ro'yxati</h4>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Login</th>
                        <th>Roli</th>
                        <th class="text-end">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= $u['username'] ?></td>
                        <td><span class="badge <?= $u['role'] == 'admin' ? 'bg-danger' : 'bg-success' ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td class="text-end">
                            <a href="users.php?edit=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <a href="delete.php?type=user&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
