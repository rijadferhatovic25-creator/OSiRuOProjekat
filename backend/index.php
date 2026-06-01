<?php
require_once 'session.php';
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT role, status, username FROM korisnici WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($user['status'] === 'banned') {
    header("Location: logout.php");
    exit();
}

$_SESSION['role'] = $user['role'];
$_SESSION['username'] = $user['username'];
$is_admin = ($user['role'] === 'admin') ? 1 : 0;

$hero_res = $conn->query("SELECT vrijednost FROM postavke_sistema WHERE kljuc = 'hero_index'");
$saved_hero_id = 0;
if ($hero_res && $hero_row = $hero_res->fetch_assoc()) {
    $saved_hero_id = intval($hero_row['vrijednost']);
}

$hero_movie = null;

try {
    $table_check = $conn->query("SHOW TABLES LIKE 'filmovi'");
    if ($table_check && $table_check->num_rows > 0) {
        if ($saved_hero_id > 0) {
            $movie_stmt = $conn->prepare("SELECT id, naslov, opis, slika, rating, link_gledanje FROM filmovi WHERE id = ?");
            $movie_stmt->bind_param("i", $saved_hero_id);
            $movie_stmt->execute();
            $hero_movie = $movie_stmt->get_result()->fetch_assoc();
            $movie_stmt->close();
        }

        if (!$hero_movie) {
            $rand_res = $conn->query("SELECT id, naslov, opis, slika, rating, link_gledanje FROM filmovi ORDER BY RAND() LIMIT 1");
            if ($rand_res && $rand_res->num_rows > 0) {
                $hero_movie = $rand_res->fetch_assoc();
                $hero_id_fallback = $hero_movie['id'];
                $conn->query("UPDATE postavke_sistema SET vrijednost = '$hero_id_fallback' WHERE kljuc = 'hero_index'");
            }
        }
    }
} catch (Exception $e) {
    $hero_movie = null;
}

$tmdb_fallback = false;
if (!$hero_movie) {
    $tmdb_fallback = true;
    $hero_movie = [
        'id' => 0,
        'naslov' => 'Loading...',
        'opis' => '',
        'slika' => '',
        'rating' => '0.0',
        'link_gledanje' => '#'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filmoteka</title>
    <link rel="stylesheet" href="stil.css?v=<?php echo time(); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .search-form {
            display: flex;
            align-items: center;
            background: #141414;
            border: 1px solid #333;
            border-radius: 20px;
            padding: 4px 12px;
            margin-right: 15px;
            transition: border-color 0.2s;
        }
        .search-form:focus-within {
            border-color: #ff0050;
        }
        .search-input {
            background: none;
            border: none;
            color: #fff;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            padding: 4px 8px;
            outline: none;
            width: 180px;
            transition: width 0.3s;
        }
        .search-input:focus {
            width: 240px; 
        }
        .search-btn {
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
            font-size: 14px;
            transition: color 0.2s;
        }
        .search-btn:hover {
            color: #ff0050;
        }

        header nav {
            display: flex;
            align-items: center;
        }

        .genres-overlay { 
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(0,0,0,0.9); 
            z-index: 10000; 
            align-items: center; 
            justify-content: center; 
          }
         .genres-container-box { 
            background: #111; 
            border: 1px solid #ff0050; 
            width: 90%; 
            max-width: 650px; 
            border-radius: 14px; 
            padding: 30px; 
            box-shadow: 0 10px 40px rgba(255,0,80,0.2); 
            position: relative; 
            font-family: 'Montserrat', sans-serif; 
          }
         .genres-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 25px; 
            border-bottom: 1px solid #222; 
            padding-bottom: 15px; 
          }
         .genres-header h3 { 
            margin: 0; 
            font-size: 22px; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            color: #fff; 
          }
         .genres-close-btn { 
            background: none; 
            border: none; 
            color: #888; 
            font-size: 24px; 
            cursor: pointer; 
            transition: color 0.2s; 
          }
         .genres-close-btn:hover { 
            color: #ff0050; 
          }
         .genres-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); 
            gap: 12px; 
          }
         .genre-item-link { 
            background: #1a1a1a; 
            color: #ddd; 
            text-decoration: none; 
            padding: 12px 8px; 
            text-align: center; 
            border-radius: 8px; 
            font-size: 13px; 
            font-weight: 600; 
            border: 1px solid #252525; 
            transition: all 0.2s ease; 
          }
         .genre-item-link:hover { 
            background: #ff0050; 
            color: #fff; 
            border-color: #ff0050; 
            transform: translateY(-2px); 
          }

          .hero-slider-container {
              position: relative;
              width: 100%;
          }

          .hero-rating {
              color: #ffc107;
              margin-right: 10px;
              font-weight: 700;
          }
    </style>
</head>
<body>

<header>
    <div class="logo">FILMOTEKA</div>
    <nav>
        <form action="zanr.php" method="GET" class="search-form">
            <input type="text" name="search" class="search-input" placeholder="Search movies..." required>
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </form>

        <a href="index.php" style="color: #ff0050;">Home</a>
        <a href="trendovi.php">Trends</a>
        <a href="#" id="genres-nav-trigger">Genres</a>
        <?php if ($user['role'] === 'admin'): ?>
            <a href="admin_panel.php">Admin Panel</a>
        <?php endif; ?>
        <a href="o_nama.php">About Us</a>
        <a href="kontakt.php">Contact</a>
        <a href="profil.php" class="user-profile-link">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
        <a href="logout.php" class="logout-link">Logout</a>
    </nav>
</header>

<div class="hero-slider-container">
    <section class="hero" id="hero-slider" style="background-image: url('<?php echo htmlspecialchars($hero_movie['slika']); ?>'); background-size: cover; background-position: center; background-color: #000;">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <span class="hero-tag">FEATURED</span>
            <h1 id="hero-title">
                <?php if(!empty($hero_movie['rating']) && $hero_movie['rating'] > 0): ?>
                    <span class="hero-rating"><i class="fas fa-star"></i> <?php echo number_format($hero_movie['rating'], 1); ?></span>
                <?php endif; ?>
                <?php echo htmlspecialchars($hero_movie['naslov']); ?>
            </h1>
            <p id="hero-description"><?php echo htmlspecialchars($hero_movie['opis']); ?></p>
            
            <div class="hero-buttons" id="hero-buttons-container">
                <a href="<?php echo htmlspecialchars($hero_movie['link_gledanje']); ?>" target="_blank" class="btn btn-play" id="hero-play-btn" style="text-decoration: none;">► Watch</a>
                <a href="detalji.php?id=<?php echo $hero_movie['id']; ?>&type=movie" class="btn btn-info" id="hero-info-btn" style="text-decoration: none;">ⓘ Info</a>
            </div>
        </div>
    </section>
</div>

<main>
    <section class="section" id="popular">
        <div class="section-header">
            <h2>🔥 Popular movies</h2>
        </div>
        <div class="movie-row" id="popular-movies"></div>
    </section>

    <section class="section" id="series">
        <div class="section-header">
            <h2>📺 Popular series</h2>
        </div>
        <div class="movie-row" id="popular-tv"></div>
    </section>

    <section class="section" id="mylist">
        <div class="section-header">
            <h2>⭐ Top Rated</h2>
        </div>
        <div class="movie-row" id="top-rated"></div>
    </section>
</main>

<div class="genres-overlay" id="genres-modal">
    <div class="genres-container-box">
        <div class="genres-header">
            <h3>Select Genre</h3>
            <button class="genres-close-btn" id="genres-modal-close">&times;</button>
        </div>
        <div class="genres-grid">
            <a href="zanr.php?id=28&name=Action" class="genre-item-link">Action</a>
            <a href="zanr.php?id=12&name=Adventure" class="genre-item-link">Adventure</a>
            <a href="zanr.php?id=16&name=Animation" class="genre-item-link">Animation</a>
            <a href="zanr.php?id=35&name=Comedy" class="genre-item-link">Comedy</a>
            <a href="zanr.php?id=80&name=Crime" class="genre-item-link">Crime</a>
            <a href="zanr.php?id=99&name=Documentary" class="genre-item-link">Documentary</a>
            <a href="zanr.php?id=18&name=Drama" class="genre-item-link">Drama</a>
            <a href="zanr.php?id=10751&name=Family" class="genre-item-link">Family</a>
            <a href="zanr.php?id=14&name=Fantasy" class="genre-item-link">Fantasy</a>
            <a href="zanr.php?id=36&name=History" class="genre-item-link">History</a>
            <a href="zanr.php?id=27&name=Horror" class="genre-item-link">Horror</a>
            <a href="zanr.php?id=10402&name=Music" class="genre-item-link">Music</a>
            <a href="zanr.php?id=9648&name=Mystery" class="genre-item-link">Mystery</a>
            <a href="zanr.php?id=10749&name=Romance" class="genre-item-link">Romance</a>
            <a href="zanr.php?id=878&name=Sci-Fi" class="genre-item-link">Sci-Fi</a>
            <a href="zanr.php?id=10770&name=TV-Movie" class="genre-item-link">TV Movie</a>
            <a href="zanr.php?id=53&name=Thriller" class="genre-item-link">Thriller</a>
            <a href="zanr.php?id=10752&name=War" class="genre-item-link">War</a>
            <a href="zanr.php?id=37&name=Western" class="genre-item-link">Western</a>
        </div>
    </div>
</div>

<footer>
    © 2026 Filmoteka. All rights reserved.
</footer>

<script>
    const isAdmin = <?php echo $is_admin; ?>;
    let currentHeroMovieId = <?php echo $hero_movie['id']; ?>;
    const loadHeroFromTMDB = <?php echo $tmdb_fallback ? 'true' : 'false'; ?>;
</script>
<script src="script.js"></script>

<script>
    const genresModal = document.getElementById('genres-modal');
    
    document.getElementById('genres-nav-trigger').addEventListener('click', (e) => {
        e.preventDefault();
        genresModal.style.display = 'flex';
    });
    
    document.getElementById('genres-modal-close').addEventListener('click', () => {
        genresModal.style.display = 'none';
    });
    
    window.addEventListener('click', (e) => {
        if (e.target === genresModal) {
            genresModal.style.display = 'none';
        }
    });
</script>
</body>
</html>
