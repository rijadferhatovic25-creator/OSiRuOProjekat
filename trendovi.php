<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

$saved_hero_index = 0; 
$is_admin_js = $is_admin ? 'true' : 'false';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trending & Top Rated - Filmoteka</title>
    <link rel="stylesheet" href="stil.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        .genre-master-container {
            margin-top: 110px;
            padding: 0 50px;
            font-family: 'Montserrat', sans-serif;
        }

        .genre-page-title {
            font-size: 24px;
            font-weight: 700;
            margin-top: 40px;
            margin-bottom: 15px;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .movies-slider-container {
            width: 100%;
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 15px;
            scroll-behavior: smooth;
        }

        .movies-slider-container::-webkit-scrollbar {
            height: 8px;
        }
        .movies-slider-container::-webkit-scrollbar-track {
            background: #111;
            border-radius: 10px;
        }
        .movies-slider-container::-webkit-scrollbar-thumb {
            background: #ff0050;
            border-radius: 10px;
        }

        .trends-grid {
            display: inline-flex;
            gap: 20px;
            width: 100%;
        }

        .trends-grid .movie-card-link {
            flex: 0 0 calc((100% - (4 * 20px)) / 5);
            display: inline-block;
            text-decoration: none;
        }

        .trends-grid .movie-card {
            width: 100%;
            position: relative;
            background: #111;
            border-radius: 8px;
            overflow: hidden;
            aspect-ratio: 2 / 3; 
        }

        .trends-grid .movie-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        @media (max-width: 1200px) {
            .trends-grid .movie-card-link {
                flex: 0 0 calc((100% - (3 * 20px)) / 4);
            }
        }
        @media (max-width: 900px) {
            .trends-grid .movie-card-link {
                flex: 0 0 calc((100% - (1 * 20px)) / 2.5);
            }
            .genre-master-container { padding: 0 20px; }
        }
        @media (max-width: 600px) {
            .trends-grid .movie-card-link {
                flex: 0 0 calc(100% - 60px); 
            }
        }
    </style>
</head>
<body>

<header>
    <div class="logo"><a href="index.php" style="color: #e50914; text-decoration: none;">FILMOTEKA</a></div>
    <nav>
        <a href="index.php">Home</a>
        <a href="trendovi.php" style="color: #e50914;">Trends</a>
        <?php if ($is_admin): ?>
            <a href="admin_panel.php">Admin Panel</a>
        <?php endif; ?>
        <a href="o_nama.php">About Us</a>
        <a href="kontakt.php">Contact</a>
        <a href="profil.php">Profile</a>
        <a href="logout.php" class="logout-link">Logout</a>
    </nav>
</header>

<div id="hero" style="display: none;">
    <div id="hero-title"></div>
    <div id="hero-description"></div>
    <a id="hero-play-btn"></a>
    <a id="hero-info-btn"></a>
</div>

<div class="genre-master-container">
    
    <div class="genre-page-title">Popular Movies</div>
    <div class="movies-slider-container">
        <div class="trends-grid" id="popular-movies"></div>
    </div>

    <div class="genre-page-title">Top Rated Movies Of All Time</div>
    <div class="movies-slider-container">
        <div class="trends-grid" id="top-rated"></div>
    </div>

    <div class="genre-page-title">Popular TV Series</div>
    <div class="movies-slider-container">
        <div class="trends-grid" id="popular-tv"></div>
    </div>
</div>

<footer>
    &copy; 2026 Filmoteka. All rights reserved.
</footer>

<script>
    const isAdmin = <?php echo $is_admin_js; ?>;
    let currentHeroIndex = <?php echo $saved_hero_index; ?>;
</script>

<script src="script.js"></script>

</body>
</html>
