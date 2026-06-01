<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filmoteka - Unlimited Movies & TV Shows</title>
    <link rel="stylesheet" href="stil.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .landing-hero {
            position: relative;
            width: 100%;
            height: 95vh;
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 0 20px;
            transition: background-image 1s ease-in-out;
        }
        .landing-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(11, 11, 11, 0.5) 0%, rgba(11, 11, 11, 0.95) 100%);
            z-index: 1;
        }
        .landing-hero-content {
            max-width: 800px;
            position: relative;
            z-index: 2;
            animation: fadeIn 0.8s ease-in-out;
        }
        .landing-title {
            font-size: 56px;
            font-weight: 800;
            margin-bottom: 20px;
            letter-spacing: -1px;
            line-height: 1.1;
        }
        .landing-subtitle {
            font-size: 24px;
            font-weight: 500;
            color: #ccc;
            margin-bottom: 35px;
        }
        .landing-btn-large {
            display: inline-block;
            background: #e50914;
            color: #fff;
            padding: 16px 45px;
            font-size: 18px;
            font-weight: 700;
            text-decoration: none;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 20px rgba(229, 9, 20, 0.4);
            transition: background 0.2s, transform 0.2s;
        }
        .landing-btn-large:hover {
            background: #c40812;
            transform: translateY(-2px);
        }
        .landing-nav-btn {
            background: #e50914;
            color: #fff;
            padding: 8px 18px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .landing-nav-btn:hover {
            background: #c40812;
        }
        .trending-section {
            padding: 60px 50px;
            background: #0b0b0b;
        }
        .trending-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 30px;
            letter-spacing: 0.5px;
        }
        .trending-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 25px;
        }
        .trending-card {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            aspect-ratio: 2/3;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .trending-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .trending-card:hover {
            transform: scale(1.05);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 768px) {
            .landing-title { font-size: 38px; }
            .landing-subtitle { font-size: 18px; }
            .trending-section { padding: 40px 20px; }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">FILMOTEKA</div>
    <nav>
        <a href="login.php?signin=1" class="landing-nav-btn">Sign In</a>
    </nav>
</header>

<div class="landing-hero" id="hero-slider">
    <div class="landing-hero-content">
        <h1 class="landing-title" id="hero-title">Unlimited movies, TV shows, and more.</h1>
        <p class="landing-subtitle">Create your personal movie collections and tracks.</p>
        <a href="registracija.php" class="landing-btn-large">Get Started Now</a>
    </div>
</div>

<div class="trending-section">
    <h2 class="trending-title">Trending Movies</h2>
    <div class="trending-grid" id="trending-container"></div>
</div>

<footer>
    © 2026 Filmoteka. All rights reserved.
</footer>

<script>
const ACCESS_TOKEN = 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI2OTlmOTE2M2NjZDU0YTA1NjY4ZjUwZjE5YWMwYjBhOCIsIm5iZiI6MTc3NzY2NzY1NC43NTUsInN1YiI6IjY5ZjUwZTQ2ODg1MzY3ODg1YzFhYjQxNiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.xLRWJXH3J129cd_Z5DvIwBuTA0gjyXLqAAkyK9Kxf70';

let backgroundMovies = [];
let currentBackgroundIndex = 0;

document.addEventListener("DOMContentLoaded", function() {
    fetch('https://api.themoviedb.org/3/trending/movie/week?language=en-US', {
        headers: { Authorization: `Bearer ${ACCESS_TOKEN}` }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('trending-container');
        if (data.results) {
            backgroundMovies = data.results.filter(m => m.backdrop_path);
            
            if(backgroundMovies.length > 0) {
                changeHeroBackground();
                setInterval(changeHeroBackground, 5000);
            }

            data.results.slice(0, 12).forEach(movie => {
                const card = document.createElement('div');
                card.className = 'trending-card';
                const posterPath = movie.poster_path 
                    ? `https://image.tmdb.org/t/p/w500${movie.poster_path}` 
                    : 'https://via.placeholder.com/500x750?text=No+Poster';
                
                card.innerHTML = `
                    <a href="login.php?signin=1">
                        <img src="${posterPath}" alt="${movie.title}">
                    </a>
                `;
                container.appendChild(card);
            });
        }
    })
    .catch(err => console.error(err));
});

function changeHeroBackground() {
    if (backgroundMovies.length === 0) return;
    const hero = document.getElementById('hero-slider');
    const movie = backgroundMovies[currentBackgroundIndex];
    
    hero.style.backgroundImage = `url('https://image.tmdb.org/t/p/original${movie.backdrop_path}')`;
    
    currentBackgroundIndex = (currentBackgroundIndex + 1) % backgroundMovies.length;
}
</script>

</body>
</html>
