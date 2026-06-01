<?php
require_once 'session.php';
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$genre_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$genre_name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '';

if (!empty($search_query)) {
    $page_title = "Search results for: " . htmlspecialchars($search_query);
} else {
    $page_title = "Genre: " . (!empty($genre_name) ? $genre_name : 'Movies');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Filmoteka</title>
    <link rel="stylesheet" href="stil.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        .genre-master-container {
            width: 100%;
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            box-sizing: border-box;
        }

        .genre-page-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 30px;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-left: 5px solid #ff0050;
            padding-left: 15px;
        }

        .movies-vertical-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 25px;
            margin-bottom: 50px;
        }

        .grid-movie-card {
            position: relative;
            height: 320px;
            border-radius: 8px;
            overflow: hidden;
            background: #111;
            border: 1px solid #222;
            transition: all 0.2s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        }

        .grid-movie-card:hover {
            transform: scale(1.04);
            border-color: #ff0050;
            box-shadow: 0 5px 20px rgba(255,0,80,0.3);
        }

        .grid-movie-card a {
            display: block;
            width: 100%;
            height: 100%;
        }

        .grid-movie-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @media (max-width: 992px) {
            .movies-vertical-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 576px) {
            .movies-vertical-grid { grid-template-columns: repeat(2, 1fr); }
        }
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

<div class="genre-master-container">
    <div class="genre-page-title"><?php echo $page_title; ?></div>
    
    <div class="movies-vertical-grid" id="genre-movies-container">
    </div>
</div>

<footer>
    © 2026 Filmoteka. All rights reserved.
</footer>

<script>
const ACCESS_TOKEN = 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI2OTlmOTE2M2NjZDU0YTA1NjY4ZjUwZjE5YWMwYjBhOCIsIm5iZiI6MTc3NzY2NzY1NC43NTUsInN1YiI6IjY5ZjUwZTQ2ODg1MzY3ODg1YzFhYjQxNiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.xLRWJXH3J129cd_Z5DvIwBuTA0gjyXLqAAkyK9Kxf70';
const container = document.getElementById('genre-movies-container');

const searchQuery = "<?php echo urlencode($search_query); ?>";
const genreId = <?php echo $genre_id; ?>;

let url = `https://api.themoviedb.org/3/discover/movie?with_genres=${genreId}&sort_by=popularity.desc&language=en-US&page=1`;
if (searchQuery !== "") {
    url = `https://api.themoviedb.org/3/search/multi?query=${searchQuery}&include_adult=false&language=en-US&page=1`;
}

fetch(url, {
    headers: { Authorization: `Bearer ${ACCESS_TOKEN}` }
})
.then(response => response.json())
.then(data => {
    if(data.results && data.results.length > 0) {
        data.results.forEach(item => {
            if(item.media_type === 'person') return;

            const posterPath = item.poster_path 
                ? `https://image.tmdb.org/t/p/w342${item.poster_path}` 
                : 'https://via.placeholder.com/220x320?text=No+Poster';

            const mediaType = item.media_type ? item.media_type : 'movie';
            const title = item.title ? item.title : item.name;

            const card = document.createElement('div');
            card.className = 'grid-movie-card';
            card.innerHTML = `
                <a href="detalji.php?id=${item.id}&type=${mediaType}">
                    <img src="${posterPath}" alt="${title}">
                </a>
            `;
            container.appendChild(card);
        });
    } else {
        container.innerHTML = '<p style="color:#666; font-family:\'Montserrat\',sans-serif;">No results found for your search query.</p>';
    }
})
.catch(error => {
    console.error("Error:", error);
    container.innerHTML = '<p style="color:#666; font-family:\'Montserrat\',sans-serif;">An error occurred with the API connection.</p>';
});
</script>

</body>
</html>
