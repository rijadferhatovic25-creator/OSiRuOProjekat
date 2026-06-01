<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$result = $conn->query("SELECT id, ime, prezime, username, status, profilna_slika FROM korisnici WHERE role != 'admin'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filmoteka - User Management</title>
    <link rel="stylesheet" href="stil.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<header>
    <div class="logo">FILMOTEKA</div>
    <nav>
        <a href="index.php">Home</a>
        <a href="profil.php">Profile</a>
        <a href="logout.php" class="logout-link">Logout</a>
    </nav>
</header>

<div class="genre-master-container" style="margin-top: 120px; padding: 0 50px;">
    <div class="genre-page-title" style="font-size: 28px; font-weight: 700; margin-bottom: 30px; border-bottom: 2px solid #ff0050; padding-bottom: 10px;">Registered Profiles</div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px;">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $profilna = (!empty($row['profilna_slika'])) ? $row['profilna_slika'] : 'guest.png';
                $slika_putanja = "uploads/" . $profilna;
                if (!file_exists($slika_putanja) || $profilna == 'guest.png') {
                    $slika_putanja = "https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png";
                }
            ?>
                <div style="background: #111; border: 1px solid #222; border-radius: 12px; padding: 20px; text-align: center; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <img src="<?php echo $slika_putanja; ?>" alt="Profile" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #ff0050; margin-bottom: 15px;">
                    <h3 style="margin: 0 0 5px 0; font-size: 18px; color: #fff;"><?php echo htmlspecialchars($row['ime'] . ' ' . $row['prezime']); ?></h3>
                    <p style="margin: 0 0 15px 0; color: #888; font-size: 14px;">@<?php echo htmlspecialchars($row['username']); ?></p>
                    
                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <a href="pogledaj_profil.php?id=<?php echo $row['id']; ?>" style="background: #ff0050; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600;">View</a>
                        <?php if ($row['status'] !== 'banned'): ?>
                            <button onclick="openBanModal(<?php echo $row['id']; ?>)" style="background: #222; color: #ff0050; border: 1px solid #ff0050; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">Ban</button>
                        <?php else: ?>
                            <span style="background: #333; color: #555; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600;">Banned</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color: #888;">No registered users found.</p>
        <?php endif; ?>
    </div>
</div>

<div id="banModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: #111; border: 1px solid #ff0050; padding: 30px; border-radius: 12px; width: 90%; max-width: 450px;">
        <h3 style="margin-top: 0; color: #fff; margin-bottom: 20px;">Ban User</h3>
        <input type="hidden" id="banUserId">
        <div style="margin-bottom: 20px;">
            <label style="color: #888; display: block; margin-bottom: 8px; font-size: 14px;">Reason for Ban:</label>
            <textarea id="banReason" style="width: 100%; height: 100px; background: #1a1a1a; border: 1px solid #333; color: white; border-radius: 6px; padding: 10px; outline: none; font-family: inherit; resize: none;"></textarea>
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="confirmBan()" style="flex: 1; background: #ff0050; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: 600; cursor: pointer;">Confirm Ban</button>
            <button onclick="closeBanModal()" style="flex: 1; background: #222; color: #ccc; border: 1px solid #333; padding: 12px; border-radius: 6px; font-weight: 600; cursor: pointer;">Cancel</button>
        </div>
    </div>
</div>

<script>
function openBanModal(userId) {
    document.getElementById('banUserId').value = userId;
    document.getElementById('banModal').style.display = 'flex';
}

function closeBanModal() {
    document.getElementById('banModal').style.display = 'none';
    document.getElementById('banReason').value = '';
}

function confirmBan() {
    const userId = document.getElementById('banUserId').value;
    const reason = document.getElementById('banReason').value;

    if (!reason.trim()) {
        alert('Please enter a reason.');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'ban');
    formData.append('user_id', userId);
    formData.append('reason', reason);

    fetch('admin_funkcije.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error banning user.');
        }
    });
}
</script>
</body>
</html>
