const API_KEY = '699f9163ccd54a05668f50f19ac0b0a8';
const ACCESS_TOKEN = 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI2OTlmOTE2M2NjZDU0YTA1NjY4ZjUwZjE5YWMwYjBhOCIsIm5iZiI6MTc3NzY2NzY1NC43NTUsInN1YiI6IjY5ZjUwZTQ2ODg1MzY3ODg1YzFhYjQxNiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.xLRWJXH3J129cd_Z5DvIwBuTA0gjyXLqAAkyK9Kxf70';
const BASE_URL = 'https://api.themoviedb.org/3';
const POSTER_URL = 'https://image.tmdb.org/t/p/w500';
const BACKDROP_URL = 'https://image.tmdb.org/t/p/original';

const options = {
    method: 'GET',
    headers: {
        accept: 'application/json',
        Authorization: `Bearer ${ACCESS_TOKEN}`
    }
};

let safeIsAdmin = (typeof isAdmin !== 'undefined') ? isAdmin : false;

async function fetchMovies(endpoint, containerId) {
    try {
        const res = await fetch(`${BASE_URL}${endpoint}`, options);
        const data = await res.json();
        
        const container = document.getElementById(containerId);
        if (!container) return;

        if (data.results) {
            container.innerHTML = ''; 
            data.results.forEach(movie => {
                const card = document.createElement('div');
                card.className = 'movie-card';
                
                const path = movie.poster_path ? `${POSTER_URL}${movie.poster_path}` : 'https://via.placeholder.com/500x750?text=No+Poster';
                const mediaType = movie.title ? 'movie' : 'tv';
                
                card.innerHTML = `
                    <a href="detalji.php?id=${movie.id}&type=${mediaType}">
                        <img src="${path}" alt="${movie.title || movie.name}">
                    </a>
                `;
                container.appendChild(card);
            });
        }
    } catch (err) {
        console.error("Error while fetching bottom movies:", err);
    }
}

async function fetchHeroMovie() {
    try {
        const res = await fetch(`${BASE_URL}/movie/popular?language=en-US&page=1`, options);
        const data = await res.json();
        if (data.results && data.results.length > 0) {
            const movie = data.results[0];
            
            const heroSection = document.getElementById('hero-slider');
            const heroTitle = document.getElementById('hero-title');
            const heroDescription = document.getElementById('hero-description');
            const heroPlayBtn = document.getElementById('hero-play-btn');
            const heroInfoBtn = document.getElementById('hero-info-btn');
            
            if (heroSection && movie.backdrop_path) {
                heroSection.style.backgroundImage = `url('${BACKDROP_URL}${movie.backdrop_path}')`;
            }
            
            if (heroTitle) {
                let ratingHtml = '';
                if (movie.vote_average) {
                    ratingHtml = `<span class="hero-rating"><i class="fas fa-star"></i> ${movie.vote_average.toFixed(1)}</span> `;
                }
                heroTitle.innerHTML = ratingHtml + movie.title;
            }
            
            if (heroDescription) {
                heroDescription.textContent = movie.overview || 'No description available.';
            }
            
            if (heroPlayBtn) {
                heroPlayBtn.href = `https://www.themoviedb.org/movie/${movie.id}`;
            }
            
            if (heroInfoBtn) {
                heroInfoBtn.href = `detalji.php?id=${movie.id}&type=movie`;
            }
            
            currentHeroMovieId = movie.id;
        } else {
            fallbackHeroStaticText();
        }
    } catch (err) {
        fallbackHeroStaticText();
    }
}

function fallbackHeroStaticText() {
    const heroTitle = document.getElementById('hero-title');
    const heroDescription = document.getElementById('hero-description');
    if (heroTitle) heroTitle.textContent = 'No movies in the database';
    if (heroDescription) heroDescription.textContent = 'Please create the movies table or add movies into the database through the admin panel.';
}

function slideHero(direction) {
    if (!safeIsAdmin) return;

    const formData = new FormData();
    formData.append('action', 'update_hero_index');
    formData.append('current_id', currentHeroMovieId);
    formData.append('direction', direction); 
    
    fetch('admin_funkcije.php', {
        method: 'POST',
        body: formData
    })
    .then(() => {
        window.location.reload();
    })
    .catch(err => console.error(err));
}

document.addEventListener('DOMContentLoaded', () => {
    if (typeof loadHeroFromTMDB !== 'undefined' && loadHeroFromTMDB) {
        fetchHeroMovie();
    }

    if (document.getElementById('popular-movies')) fetchMovies('/movie/popular?language=en-US&page=1', 'popular-movies');
    if (document.getElementById('top-rated')) fetchMovies('/movie/top_rated?language=en-US&page=1', 'top-rated');
    if (document.getElementById('popular-tv')) fetchMovies('/tv/popular?language=en-US&page=1', 'popular-tv');

    const prevBtn = document.querySelector('.hero-prev');
    const nextBtn = document.querySelector('.hero-next');
    
    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            slideHero(-1);
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            slideHero(1);
        });
    }

    const header = document.querySelector('header');
    const nav = document.querySelector('nav');
    if (header && nav) {
        if (!document.querySelector('.hamburger-menu')) {
            const burger = document.createElement('button');
            burger.className = 'hamburger-menu';
            burger.innerHTML = '<span></span><span></span><span></span>';
            header.insertBefore(burger, nav);

            burger.addEventListener('click', (e) => {
                e.stopPropagation();
                burger.classList.toggle('active');
                nav.classList.toggle('active');
            });

            document.addEventListener('click', (e) => {
                if (!nav.contains(e.target) && !burger.contains(e.target)) {
                    burger.classList.remove('active');
                    nav.classList.remove('active');
                }
            });
        }
    }
});