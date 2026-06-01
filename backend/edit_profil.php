<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT ime, prezime, email, bio, profilna_slika FROM korisnici WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$trenutna_slika = !empty($user['profilna_slika']) ? $user['profilna_slika'] : 'guest.png';

$poruka = "";
$is_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ime = trim($_POST['ime']);
    $prezime = trim($_POST['prezime']);
    $email = trim($_POST['email']);
    $bio = trim($_POST['bio']);
    $nova_slika_ime = $trenutna_slika;

    if (isset($_FILES['profilna_slika']) && $_FILES['profilna_slika']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profilna_slika']['tmp_name'];
        $file_name = $_FILES['profilna_slika']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $dozvoljene_ekstenzije = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_ext, $dozvoljene_ekstenzije)) {
            $nova_slika_ime = $user_id . "_" . time() . "." . $file_ext;
            $upload_folder = 'uploads/';
            if (!is_dir($upload_folder)) {
                mkdir($upload_folder, 0777, true);
            }

            $dest_path = $upload_folder . $nova_slika_ime;

            if (move_uploaded_file($file_tmp, $dest_path)) {
                if ($trenutna_slika !== 'guest.png' && file_exists($upload_folder . $trenutna_slika)) {
                    unlink($upload_folder . $trenutna_slika);
                }
            } else {
                $poruka = "Failed to move uploaded file.";
                $is_error = true;
            }
        } else {
            $poruka = "Invalid file type. Only JPG, JPEG, PNG, WEBP and GIF are allowed.";
            $is_error = true;
        }
    }

    if (!$is_error) {
        $update_stmt = $conn->prepare("UPDATE korisnici SET ime = ?, prezime = ?, email = ?, bio = ?, profilna_slika = ? WHERE id = ?");
        $update_stmt->bind_param("sssssi", $ime, $prezime, $email, $bio, $nova_slika_ime, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['profilna_slika'] = $nova_slika_ime;
            
            // KLJUČNA IZMJENA: Preusmjeravanje na profil.php nakon uspješnog spašavanja
            header("Location: profil.php");
            exit();
        } else {
            $poruka = "Database error: Could not update profile.";
            $is_error = true;
        }
        $update_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filmoteka - Edit Profile</title>
    <link rel="stylesheet" href="stil.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0b0b0b; color: #fff; font-family: 'Montserrat', sans-serif; margin: 0; padding: 0; }
        .edit-container { max-width: 600px; margin: 120px auto 60px auto; background: #111; border: 1px solid #222; padding: 40px; border-radius: 12px; box-shadow: 0 15px 40px rgba(0,0,0,0.7); }
        .edit-title { font-size: 28px; font-weight: 800; margin-bottom: 30px; border-bottom: 1px solid #222; padding-bottom: 15px; text-align: center; }
        
        .avatar-centered-section { display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 35px; }
        
        .avatar-clickable-wrapper { position: relative; width: 130px; height: 130px; border-radius: 50%; overflow: hidden; border: 2px solid #e50914; box-shadow: 0 4px 20px rgba(229,9,20,0.2); background: #222; cursor: pointer; transition: transform 0.2s ease; }
        .avatar-clickable-wrapper:hover { transform: scale(1.03); }
        .avatar-clickable-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: filter 0.3s ease; }
        
        .avatar-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); opacity: 0; display: flex; align-items: center; justify-content: center; transition: opacity 0.3s ease; }
        .avatar-clickable-wrapper:hover .avatar-overlay { opacity: 1; }
        .avatar-clickable-wrapper:hover img { filter: brightness(0.6); }
        
        .pencil-icon { width: 26px; height: 26px; fill: none; stroke: rgba(255, 255, 255, 0.9); stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        #file-input { display: none; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 12px; text-transform: uppercase; color: #666; font-weight: 700; letter-spacing: 1px; margin-bottom: 8px; }
        .form-control { width: 100%; background: #161616; border: 1px solid #333; border-radius: 6px; padding: 12px; color: #fff; font-family: 'Montserrat', sans-serif; font-size: 14px; box-sizing: border-box; }
        .form-control:focus { outline: none; border-color: #e50914; }
        textarea.form-control { resize: vertical; min-height: 100px; }
        
        .btn-submit { background: #e50914; color: #fff; border: none; padding: 14px 30px; border-radius: 6px; font-weight: 700; font-size: 14px; cursor: pointer; text-transform: uppercase; width: 100%; transition: background 0.2s; margin-top: 10px; }
        .btn-submit:hover { background: #b80710; }
        
        .toast-notification { position: fixed; bottom: 40px; left: 50%; transform: translateX(-50%); background: #141414; border: 1px solid #ff3333; color: #fff; padding: 14px 28px; border-radius: 30px; font-size: 14px; font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.6); z-index: 9999; display: flex; align-items: center; gap: 10px; background: #1a1111; }
    </style>
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

<div class="edit-container">
    <div class="edit-title">Edit Profile</div>
    
    <form action="edit_profil.php" method="POST" enctype="multipart/form-data">
        
        <div class="avatar-centered-section">
            <div class="avatar-clickable-wrapper" onclick="triggerFileInput()" title="Change profile photo">
                <img id="avatar-preview" src="<?php echo ($trenutna_slika === 'guest.png') ? 'guest.png' : 'uploads/' . $trenutna_slika; ?>" alt="Profile Picture">
                <div class="avatar-overlay">
                    <svg class="pencil-icon" viewBox="0 0 24 24">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                </div>
            </div>
            <input type="file" name="profilna_slika" id="file-input" accept="image/*">
        </div>

        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="ime" class="form-control" value="<?php echo htmlspecialchars($user['ime']); ?>" required>
        </div>

        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="prezime" class="form-control" value="<?php echo htmlspecialchars($user['prezime']); ?>" required>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="form-group">
            <label>Biography</label>
            <textarea name="bio" class="form-control" placeholder="Tell us something about yourself..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
        </div>

        <button type="submit" class="btn-submit">Save Changes</button>
    </form>
</div>

<?php if (!empty($poruka) && $is_error): ?>
    <div class="toast-notification" id="toast-msg">
        <span>⚠️</span>
        <span><?php echo htmlspecialchars($poruka); ?></span>
    </div>
    <script>
        setTimeout(() => {
            const toast = document.getElementById('toast-msg');
            if(toast) toast.style.display = 'none';
        }, 4000);
    </script>
<?php endif; ?>

<script>
function triggerFileInput() {
    document.getElementById('file-input').click();
}

document.getElementById('file-input').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>
