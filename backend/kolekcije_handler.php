<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once 'session.php';
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'just_create_collection') {
    $naziv = isset($_POST['naziv']) ? trim($_POST['naziv']) : '';

    if (empty($naziv)) {
        echo json_encode(['success' => false, 'message' => 'Collection name cannot be empty.']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO zbirke (user_id, naziv, tip) VALUES (?, ?, 'privatna')");
    if ($stmt) {
        $stmt->bind_param("is", $user_id, $naziv);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'new_id' => $stmt->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error during creation.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare database statement.']);
    }
    exit();
}

if ($action === 'save_to_collection') {
    $kolekcija_id = isset($_POST['kolekcija_id']) ? intval($_POST['kolekcija_id']) : 0;
    $tmdb_id = isset($_POST['tmdb_id']) ? intval($_POST['tmdb_id']) : 0;
    $media_type = isset($_POST['media_type']) ? $_POST['media_type'] : 'movie';

    if ($media_type !== 'tv') {
        $media_type = 'movie';
    }

    if ($kolekcija_id === 0 || $tmdb_id === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
        exit();
    }

    $checkStmt = $conn->prepare("SELECT id FROM zbirka_stavke WHERE zbirka_id = ? AND tmdb_id = ? AND media_type = ?");
    if ($checkStmt) {
        $checkStmt->bind_param("iis", $kolekcija_id, $tmdb_id, $media_type);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'This item is already in the collection.']);
            $checkStmt->close();
            exit();
        }
        $checkStmt->close();
    }

    $stmt = $conn->prepare("INSERT INTO zbirka_stavke (zbirka_id, tmdb_id, media_type) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iis", $kolekcija_id, $tmdb_id, $media_type);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error while saving the item.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare database statement.']);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Unknown action requested.']);
exit();
