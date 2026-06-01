<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You are not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'dohvati') {
    $stmt = $conn->prepare("SELECT id, naziv, tip FROM zbirke WHERE user_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $zbirke = [];
    while($row = $res->fetch_assoc()) { $zbirke[] = $row; }
    echo json_encode(['status' => 'success', 'data' => $zbirke]);
    exit();
}

if ($action === 'kreiraj') {
    $naziv = isset($_POST['naziv']) ? trim($_POST['naziv']) : '';
    $tip = isset($_POST['tip']) && $_POST['tip'] === 'public' ? 'public' : 'privatna';

    if (empty($naziv)) {
        echo json_encode(['status' => 'error', 'message' => 'Name field cannot be empty.']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO zbirke (user_id, naziv, tip) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $naziv, $tip);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'id' => $stmt->insert_id, 'naziv' => htmlspecialchars($naziv), 'tip' => $tip]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error creating collection.']);
    }
    exit();
}

if ($action === 'dodaj_film') {
    $zbirka_id = intval($_POST['zbirka_id']);
    $tmdb_id = intval($_POST['tmdb_id']);
    $media_type = $_POST['media_type'] === 'tv' ? 'tv' : 'movie';

    $check = $conn->prepare("SELECT id FROM zbirka_stavke WHERE zbirka_id = ? AND tmdb_id = ? AND media_type = ?");
    $check->bind_param("iis", $zbirka_id, $tmdb_id, $media_type);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'exists', 'message' => 'Already added to this collection.']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO zbirka_stavke (zbirka_id, tmdb_id, media_type) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $zbirka_id, $tmdb_id, $media_type);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Content successfully added!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Server error.']);
    }
    exit();
}

if ($action === 'obrisi_zbirku' || $action === 'delete_collection') {
    $zbirka_id = intval($_POST['zbirka_id']);
    $stmt = $conn->prepare("DELETE FROM zbirke WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $zbirka_id, $user_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit();
}

if ($action === 'ocisti_zbirku' || $action === 'clear_collection') {
    $zbirka_id = intval($_POST['zbirka_id']);
    
    $verif = $conn->prepare("SELECT id FROM zbirke WHERE id = ? AND user_id = ?");
    $verif->bind_param("ii", $zbirka_id, $user_id);
    $verif->execute();
    if ($verif->get_result()->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM zbirka_stavke WHERE zbirka_id = ?");
    $stmt->bind_param("i", $zbirka_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit();
}

if ($action === 'obrisi_stavke' || $action === 'delete_items') {
    $zbirka_id = intval($_POST['zbirka_id']);
    $stavke = json_decode($_POST['stavke'], true);

    $verif = $conn->prepare("SELECT id FROM zbirke WHERE id = ? AND user_id = ?");
    $verif->bind_param("ii", $zbirka_id, $user_id);
    $verif->execute();
    if ($verif->get_result()->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
        exit();
    }

    if (!empty($stavke)) {
        foreach ($stavke as $tmdb_id) {
            $stmt = $conn->prepare("DELETE FROM zbirka_stavke WHERE zbirka_id = ? AND tmdb_id = ?");
            $stmt->bind_param("ii", $zbirka_id, $tmdb_id);
            $stmt->execute();
        }
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No movies selected.']);
    }
    exit();
}
?>
