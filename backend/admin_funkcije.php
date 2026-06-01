<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'ban') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($user_id > 0 && !empty($reason)) {
            $stmt = $conn->prepare("UPDATE korisnici SET status = 'banned' WHERE id = ? AND role != 'admin'");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                $stmt_ban = $conn->prepare("INSERT INTO banovani_korisnici (korisnik_id, razlog) VALUES (?, ?)");
                $stmt_ban->bind_param("is", $user_id, $reason);
                $stmt_ban->execute();
                $stmt_ban->close();
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
            $stmt->close();
        }
        exit();
    }
}
?>
