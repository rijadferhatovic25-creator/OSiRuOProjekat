<?php
session_start();
$is_logged = isset($_SESSION['user_id']);
$is_admin = ($is_logged && $_SESSION['role'] === 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Filmoteka</title>
    <link rel="stylesheet" href="stil.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .about-master {
            max-width: 1200px;
            margin: 120px auto 60px auto;
            padding: 0 20px;
        }
        .about-hero {
            text-align: center;
            margin-bottom: 60px;
        }
        .about-hero h1 {
            font-size: 48px;
            font-weight: 800;
            color: #e50914;
            margin-bottom: 20px;
        }
        .about-hero p {
            font-size: 18px;
            color: #ccc;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }
        .team-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 40px;
            text-align: center;
            letter-spacing: 1px;
        }
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            max-width: 800px;
            margin: 0 auto;
        }
        .team-card {
            background: #141414;
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }
        .team-card:hover {
            transform: translateY(-5px);
            border-color: #e50914;
        }
        .team-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 20px auto;
            background: #222;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 3px solid #e50914;
        }
        .team-avatar i {
            font-size: 50px;
            color: #bbb;
        }
        .team-card h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #fff;
        }
        .team-card .role {
            color: #e50914;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 15px;
        }
        .team-card .trait {
            color: #888;
            font-size: 14px;
            line-height: 1.5;
            font-style: italic;
            max-width: 240px;
            margin: 0 auto;
        }
        .team-note {
            text-align: center;
            margin-top: 35px;
            font-size: 12px;
            color: #555;
            font-style: italic;
            letter-spacing: 0.5px;
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
            <a href="o_nama.php" style="color: #e50914;">About Us</a>
            <a href="kontakt.php">Contact</a>
            <a href="profil.php">Profile</a>
            <a href="logout.php" class="logout-link">Logout</a>
        <?php else: ?>
            <a href="landing.php">Home</a>
            <a href="o_nama.php" style="color: #e50914;">About Us</a>
            <a href="kontakt.php">Contact</a>
            <a href="login.php?signin=1" class="landing-nav-btn" style="background: #e50914; color: #fff; padding: 8px 18px; text-decoration: none; border-radius: 4px;">Sign In</a>
        <?php endif; ?>
    </nav>
</header>

<div class="about-master">
    <div class="about-hero">
        <h1>Our Vision</h1>
        <p>Filmoteka was born as a passion project for all cinema enthusiasts. Our mission is to provide an elegant, fast, and modern interface where movie lovers can explore endless databases, track their favorite releases, and organize custom, tailored movie collections.</p>
    </div>

    <h2 class="team-title">Meet The Team</h2>
    <div class="team-grid">
        
        <div class="team-card">
            <div class="team-avatar">
                <i class="fas fa-user-ninja"></i>
            </div>
            <h3>Rijad Ferhatović</h3>
            <div class="role">Full-Stack Developer</div>
            <p class="trait">"Master of database queries and robust architecture, turning complex backend logic into smooth user experiences."</p>
        </div>

        <div class="team-card">
            <div class="team-avatar">
                <i class="fas fa-user-astronaut"></i>
            </div>
            <h3>Alen Hajrić</h3>
            <div class="role">Full-Stack Developer</div>
            <p class="trait">"Pixel-perfect frontend crafter with an eye for stunning UI animations and clean, modern application layouts."</p>
        </div>

        <div class="team-card">
            <div class="team-avatar">
                <i class="fas fa-user-secret"></i>
            </div>
            <h3>Amar Humić</h3>
            <div class="role">Full-Stack Developer</div>
            <p class="trait">"Highly adaptable developer focused on scalable solutions, efficient problem-solving, and seamless integration between frontend and backend systems."</p>
        </div>

    </div>

    <div class="team-note">
        * We couldn't have agreed about a name, so our team will, for now, be nameless.
    </div>
</div>

<footer>
    &copy; 2026 Filmoteka. All rights reserved.
</footer>

</body>
</html>