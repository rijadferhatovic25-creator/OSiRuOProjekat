<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$show_deleted_alert = false;
if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $show_deleted_alert = true;
}

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_feedback.php?deleted=1");
    exit();
}

$result = $conn->query("SELECT id, ime, email, poruka, datum_slanja FROM feedback ORDER BY datum_slanja DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Feedback - Admin Panel</title>
    <link rel="stylesheet" href="stil.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .feedback-master-container {
            width: 90%;
            max-width: 1000px;
            margin: 120px auto 50px auto;
            font-family: 'Montserrat', sans-serif;
        }
        .feedback-card {
            background: #111;
            border: 1px solid #222;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            position: relative;
        }
        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid #222;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .feedback-user-info h3 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #fff;
        }
        .feedback-user-info p {
            margin: 0;
            font-size: 13px;
            color: #888;
        }
        .feedback-date {
            font-size: 12px;
            color: #666;
        }
        .feedback-body {
            color: #ddd;
            font-size: 15px;
            line-height: 1.6;
        }
        .delete-feedback-btn {
            background: #e50914;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            transition: background 0.2s;
            display: inline-block;
            margin-top: 15px;
        }
        .delete-feedback-btn:hover {
            background: #ff0050;
        }
        .no-feedback {
            text-align: center;
            padding: 50px;
            color: #555;
            font-size: 18px;
        }

        .custom-toast {
            position: fixed;
            bottom: -100px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(17, 17, 17, 0.98);
            border: 1px solid #ff0050;
            box-shadow: 0 10px 40px rgba(255, 0, 80, 0.25);
            padding: 15px 30px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            transition: bottom 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.4s;
            opacity: 0;
            pointer-events: none;
        }
        .custom-toast.active {
            bottom: 40px;
            opacity: 1;
        }
        .custom-toast i {
            color: #ff0050;
            font-size: 18px;
        }
        .custom-toast span {
            color: #fff;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(4px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
        .custom-confirm-box {
            background: #111;
            border: 1px solid #222;
            padding: 30px 35px;
            border-radius: 16px;
            width: 90%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0,0,0,0.6);
            transform: scale(0.85);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .modal-overlay.active .custom-confirm-box {
            transform: scale(1);
        }
        .confirm-title {
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 25px;
            letter-spacing: 0.3px;
            line-height: 1.5;
        }
        .confirm-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .confirm-btn {
            padding: 11px 28px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.2s;
        }
        .confirm-btn.yes {
            background: #e50914;
            color: #fff;
        }
        .confirm-btn.yes:hover {
            background: #ff0050;
        }
        .confirm-btn.no {
            background: #222;
            color: #aaa;
            border: 1px solid #333;
        }
        .confirm-btn.no:hover {
            background: #333;
            color: #fff;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">FILMOTEKA</div>
    <nav>
        <a href="index.php">Home</a>
        <a href="admin_panel.php">Admin Panel</a>
        <a href="profil.php">Profile</a>
        <a href="logout.php" class="logout-link">Logout</a>
    </nav>
</header>

<div class="feedback-master-container">
    <h1 style="font-size: 32px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 30px;">User Feedback</h1>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="feedback-card">
                <div class="feedback-header">
                    <div class="feedback-user-info">
                        <h3><?php echo htmlspecialchars($row['ime']); ?></h3>
                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($row['email']); ?></p>
                    </div>
                    <div class="feedback-date">
                        <?php echo date('M d, Y - H:i', strtotime($row['datum_slanja'])); ?>
                    </div>
                </div>
                <div class="feedback-body">
                    <?php echo nl2br(htmlspecialchars($row['poruka'])); ?>
                </div>
                <a href="admin_feedback.php?delete=<?php echo $row['id']; ?>" class="delete-feedback-btn target-delete-link">
                    <i class="fas fa-trash-alt"></i> Delete Feedback
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-feedback">
            <i class="fas fa-comment-slash" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
            No feedback received yet.
        </div>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="confirm-overlay">
    <div class="custom-confirm-box">
        <div class="confirm-title">Are you sure you want to delete this feedback?</div>
        <div class="confirm-buttons">
            <button class="confirm-btn no" id="confirm-no-btn">No</button>
            <button class="confirm-btn yes" id="confirm-yes-btn">Yes</button>
        </div>
    </div>
</div>

<div class="custom-toast" id="delete-toast">
    <i class="fas fa-check-circle"></i>
    <span>You have successfully deleted a feedback</span>
</div>

<footer>
    © 2026 Filmoteka. All rights reserved.
</footer>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const overlay = document.getElementById('confirm-overlay');
    const yesBtn = document.getElementById('confirm-yes-btn');
    const noBtn = document.getElementById('confirm-no-btn');
    let activeDeleteUrl = '';

    document.querySelectorAll('.target-delete-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            activeDeleteUrl = this.getAttribute('href');
            overlay.classList.add('active');
        });
    });

    noBtn.addEventListener('click', function() {
        overlay.classList.remove('active');
        activeDeleteUrl = '';
    });

    yesBtn.addEventListener('click', function() {
        if (activeDeleteUrl) {
            window.location.href = activeDeleteUrl;
        }
    });

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.classList.remove('active');
            activeDeleteUrl = '';
        }
    });

    <?php if ($show_deleted_alert): ?>
        const toast = document.getElementById('delete-toast');
        if (toast) {
            setTimeout(() => {
                toast.classList.add('active');
            }, 200);

            setTimeout(() => {
                toast.classList.remove('active');
                setTimeout(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('deleted');
                    window.history.replaceState({}, document.title, url.pathname);
                }, 500);
            }, 3500);
        }
    <?php endif; ?>
});
</script>
</body>
</html>

