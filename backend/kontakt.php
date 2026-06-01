<?php
require_once 'session.php';
require_once 'db.php';

$is_logged = isset($_SESSION['user_id']);
$is_admin = ($is_logged && $_SESSION['role'] === 'admin');

$error = "";
$success = "";

if (isset($_POST['send_message'])) {
    $ime = trim($_POST['ime']);
    $email = trim($_POST['email']);
    $poruka = trim($_POST['poruka']);

    if (empty($ime) || empty($email) || empty($poruka)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email)) {
        $error = "Invalid email format!";
    } else {
        $stmt = $conn->prepare("INSERT INTO feedback (ime, email, poruka) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $ime, $email, $poruka);
        
        if ($stmt->execute()) {
            $success = "Your message has been sent successfully!";
        } else {
            $error = "An error occurred while sending your message.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Filmoteka</title>
    <link rel="stylesheet" href="stil.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .contact-master {
            max-width: 600px;
            margin: 120px auto 60px auto;
            padding: 0 20px;
            font-family: 'Montserrat', sans-serif;
        }
        .contact-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .contact-header h1 {
            font-size: 40px;
            font-weight: 800;
            color: #e50914;
            margin-bottom: 10px;
        }
        .contact-header p {
            color: #ccc;
            font-size: 16px;
        }
        .contact-form-container {
            background: #141414;
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .contact-form-container .input-group {
            position: relative;
            margin-bottom: 25px;
        }
        .contact-form-container .input-group input,
        .contact-form-container .input-group textarea {
            background: #333333;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            padding: 14px 20px;
            font-size: 14px;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
            outline: none;
            box-sizing: border-box;
            width: 100%;
        }
        .contact-form-container .input-group textarea {
            height: 150px;
            resize: none;
        }
        .contact-form-container .input-group input:focus,
        .contact-form-container .input-group textarea:focus {
            background: #454545;
            border-color: #e50914;
            box-shadow: 0 0 8px rgba(229, 9, 20, 0.4);
        }
        .contact-form-container .input-group label {
            position: absolute;
            left: 20px;
            top: 14px;
            color: #aaa;
            transition: all 0.3s ease;
            pointer-events: none;
        }
        .contact-form-container .input-group input:focus ~ label,
        .contact-form-container .input-group input:not(:placeholder-shown) ~ label,
        .contact-form-container .input-group textarea:focus ~ label,
        .contact-form-container .input-group textarea:not(:placeholder-shown) ~ label {
            top: -22px;
            left: 5px;
            font-size: 12px;
            color: #e50914;
        }
        .contact-btn {
            background: #e50914;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 14px;
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s ease, transform 0.1s ease;
            box-shadow: 0 4px 12px rgba(229, 9, 20, 0.2);
        }
        .contact-btn:hover {
            background: #c40812;
        }
        .contact-btn:active {
            transform: scale(0.98);
        }
        .alert {
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .alert-danger {
            background: rgba(229, 9, 20, 0.15);
            color: #ff4a5a;
            border: 1px solid rgba(229, 9, 20, 0.3);
        }
        .alert-success {
            background: rgba(40, 167, 69, 0.15);
            color: #2bed6b;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
    </style>
</head>
<body>

<header>
    <div class="logo"><a href="landing.php" style="color: #e50914; text-decoration: none;">FILMOTEKA</a></div>
    <nav>
        <?php if ($is_logged): ?>
            <a href="index.php">Home</a>
            <a href="trendovi.php">Trends</a>
            <?php if ($is_admin): ?>
                <a href="admin_panel.php">Admin Panel</a>
            <?php endif; ?>
            <a href="o_nama.php">About Us</a>
            <a href="kontakt.php" style="color: #e50914;">Contact</a>
            <a href="profil.php">Profile</a>
            <a href="logout.php" class="logout-link">Logout</a>
        <?php else: ?>
            <a href="landing.php">Home</a>
            <a href="o_nama.php">About Us</a>
            <a href="kontakt.php" style="color: #e50914;">Contact</a>
            <a href="login.php?signin=1" class="landing-nav-btn" style="background: #e50914; color: #fff; padding: 8px 18px; text-decoration: none; border-radius: 4px;">Sign In</a>
        <?php endif; ?>
    </nav>
</header>

<div class="contact-master">
    <div class="contact-header">
        <h1>Contact Us</h1>
        <p>Have questions or feedback? Get in touch with our team.</p>
    </div>

    <div class="contact-form-container">
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if(!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="kontakt.php" method="POST">
            <div class="input-group">
                <input type="text" name="ime" placeholder=" " required id="contact-name">
                <label for="contact-name">Name</label>
            </div>
            <div class="input-group">
                <input type="email" name="email" placeholder=" " required id="contact-email">
                <label for="contact-email">Email</label>
            </div>
            <div class="input-group">
                <textarea name="poruka" placeholder=" " required id="contact-message"></textarea>
                <label for="contact-message">Message</label>
            </div>
            <button type="submit" name="send_message" class="contact-btn">Send Message</button>
        </form>
    </div>
</div>

<footer>
    &copy; 2026 Filmoteka. All rights reserved.
</footer>

</body>
</html>
