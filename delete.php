<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? '';
$redirect = $_SERVER['HTTP_REFERER'] ?: 'index.php';

if ($type && $id) {
    try {
        switch ($type) {
            case 'waiter':
                $stmt = $db->prepare("DELETE FROM waiters WHERE id = ?");
                $msg = "Ishchi o'chirildi!";
                break;
            case 'table':
                $stmt = $db->prepare("DELETE FROM tables WHERE id = ?");
                $msg = "Stol o'chirildi!";
                break;
            case 'product':
                // Delete image first
                $s = $db->prepare("SELECT image FROM products WHERE id = ?");
                $s->execute([$id]);
                $img = $s->fetchColumn();
                if ($img && file_exists("assets/uploads/" . $img)) {
                    unlink("assets/uploads/" . $img);
                }
                $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
                $msg = "Mahsulot o'chirildi!";
                break;
            case 'category':
                $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
                $msg = "Kategoriya o'chirildi!";
                break;
            case 'user':
                if ($id == $_SESSION['user_id']) {
                    $_SESSION['msg'] = "O'zingizni o'chira olmaysiz!";
                    $_SESSION['msg_type'] = "danger";
                    header("Location: " . $redirect);
                    exit;
                }
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $msg = "Foydalanuvchi o'chirildi!";
                break;
            default:
                die("Invalid type");
        }

        $stmt->execute([$id]);
        $_SESSION['msg'] = $msg;
        $_SESSION['msg_type'] = "success";

    } catch (Exception $e) {
        $_SESSION['msg'] = "O'chirishning iloji yo'q! Bu ma'lumot bilan bog'liq boshqa ma'lumotlar (buyurtmalar) bor.";
        $_SESSION['msg_type'] = "danger";
    }
}

header("Location: " . $redirect);
exit;
