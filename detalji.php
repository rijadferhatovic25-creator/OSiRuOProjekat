<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$tmdb_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$media_type = isset($_GET['type']) && $_GET['type'] === 'tv' ? 'tv' : 'movie';

if ($tmdb_id === 0) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$kolekcije = [];
$stmt = $conn->prepare("SELECT id, naziv FROM zbirke WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $kolekcije[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filmoteka - Details</title>
    <link rel="stylesheet" href="stil.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background-color: #0b0b0b; margin: 0; padding: 0; min-height: 100vh; display: flex; flex-direction: column; position: relative; }
        .backdrop-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-size: cover; background-position: center; filter: blur(20px) brightness(0.25); z-index: -1; transform: scale(1.1); }
        .details-master-container { flex: 1; display: flex; align-items: center; justify-content: center; padding: 120px 20px 60px 20px; z-index: 2; }
        .details-box { background: rgba(17, 17, 17, 0.9); border: 1px solid rgba(255, 255, 255, 0.1); max-width: 900px; width: 100%; border-radius: 16px; padding: 40px; box-shadow: 0 20px 50px rgba(0,0,0,0.8); backdrop-filter: blur(10px); }
        .movie-main-info { display: flex; gap: 40px; }
        .details-poster { flex: 0 0 260px; border-radius: 10px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.05); height: 390px; }
        .details-poster img { width: 100%; height: 100%; object-fit: cover; }
        .details-content { flex: 1; }
        .movie-title-full { font-size: 36px; font-weight: 800; margin-bottom: 15px; color: #fff; line-height: 1.2; }
        .movie-meta-row { display: flex; gap: 20px; color: #aaa; font-size: 14px; font-weight: 600; margin-bottom: 25px; align-items: center; }
        .meta-badge { background: #e50914; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 12px; }
        .movie-overview-text { font-size: 16px; line-height: 1.7; color: #ccc; margin-bottom: 30px; }
        .additional-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px; font-size: 14px; }
        .info-item span { color: #666; font-weight: 600; display: block; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; margin-bottom: 2px; }
        .info-item p { color: #fff; font-weight: 500; margin: 0; }
        .bottom-utilities-bar { margin-top: 35px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .where-to-watch-section { display: flex; flex-direction: column; gap: 10px; }
        .wtw-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #aaa; }
        .provider-logos-container { display: flex; gap: 12px; align-items: center; }
        .provider-link { transition: transform 0.2s ease, filter 0.2s ease; display: inline-block; }
        .provider-link:hover { transform: scale(1.1); filter: brightness(1.2); }
        .provider-logo { width: 42px; height: 42px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); }
        .provider-logo img { width: 100%; height: 100%; object-fit: cover; }
        .no-providers { font-size: 14px; color: #555; font-weight: 500; }
        .bookmark-btn { background: none; border: none; cursor: pointer; padding: 8px; display: flex; align-items: center; justify-content: center; transition: transform 0.2s ease; }
        .bookmark-btn:hover { transform: scale(1.15); }
        .bookmark-btn svg { width: 32px; height: 32px; fill: none; stroke: #fff; stroke-width: 2; transition: stroke 0.2s ease, fill 0.2s ease; }
        .bookmark-btn:hover svg { stroke: #e50914; }
        .bookmark-btn.saved svg { fill: #e50914; stroke: #e50914; }
        
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(8px); z-index: 2000; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal-card { background: #111; border: 1px solid #222; border-radius: 12px; width: 100%; max-width: 400px; padding: 30px; box-shadow: 0 15px 40px rgba(0,0,0,0.7); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-title { font-size: 18px; font-weight: 700; color: #fff; }
        .modal-close { background: none; border: none; color: #666; font-size: 22px; cursor: pointer; }
        .modal-close:hover { color: #fff; }
        .modal-section-label { font-size: 11px; text-transform: uppercase; color: #666; font-weight: 700; letter-spacing: 1px; margin-bottom: 10px; display: block; }
        .collections-list { max-height: 160px; overflow-y: auto; margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px; }
        .collection-item-btn { background: #1a1a1a; border: 1px solid #222; color: #fff; text-align: left; padding: 12px 15px; border-radius: 6px; cursor: pointer; font-family: 'Montserrat', sans-serif; font-size: 14px; width: 100%; transition: all 0.2s; }
        .collection-item-btn:hover { border-color: #e50914; background: #222; }
        .create-coll-form { border-top: 1px solid #222; padding-top: 15px; display: flex; gap: 10px; }
        .modal-input { flex: 1; background: #161616; border: 1px solid #333; border-radius: 6px; padding: 10px 12px; color: #fff; font-family: 'Montserrat', sans-serif; font-size: 13px; }
        .modal-input:focus { outline: none; border-color: #e50914; }
        .modal-btn-submit { background: #e50914; color: #fff; border: none; padding: 10px 16px; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer; text-transform: uppercase; }

        .toast-notification { position: fixed; bottom: -100px; left: 50%; transform: translateX(-50%); background: #141414; border: 1px solid #e50914; color: #fff; padding: 14px 28px; border-radius: 30px; font-family: 'Montserrat', sans-serif; font-size: 14px; font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.6); z-index: 9999; transition: bottom 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.3s; opacity: 0; display: flex; align-items: center; gap: 10px; }
        .toast-notification.active { bottom: 40px; opacity: 1; }
        .toast-notification.error { border-color: #ff3333; background: #1a1111; }

        @media (max-width: 768px) { .movie-main-info { flex-direction: column; align-items: center; } .bottom-utilities-bar { flex-direction: column; text-align: center; } }
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

<div class="backdrop-bg" id="custom-backdrop"></div>

<div class="details-master-container">
    <div class="details-box">
        <div class="movie-main-info">
            <div class="details-poster">
                <img id="movie-poster" src="https://via.placeholder.com/500x750?text=Loading..." alt="Poster">
            </div>
            <div class="details-content">
                <h1 class="movie-title-full" id="movie-title">Loading...</h1>
                <div class="movie-meta-row">
                    <span class="meta-badge" id="movie-rating">★ 0.0</span>
                    <span id="movie-release-date">0000-00-00</span>
                    <span id="movie-runtime">0 min</span>
                </div>
                <p class="movie-overview-text" id="movie-overview">Loading film overview details...</p>
                <div class="additional-info-grid">
                    <div class="info-item"><span>Genres</span><p id="movie-genres">-</p></div>
                    <div class="info-item"><span>Status</span><p id="movie-status">-</p></div>
                </div>
            </div>
        </div>

        <div class="bottom-utilities-bar">
            <div class="where-to-watch-section">
                <div class="wtw-title">Where to Watch</div>
                <div class="provider-logos-container" id="providers-container">
                    <span class="no-providers">Loading platforms...</span>
                </div>
            </div>
            <div class="action-save-container">
                <button class="bookmark-btn" id="open-modal-btn" title="Save to Collection">
                    <svg viewBox="0 0 24 24"><path d="M5 3c-1.1 0-2 .9-2 2v16l9-4 9 4V5c0-1.1-.9-2-2-2H5z"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="collection-modal">
    <div class="modal-card">
        <div class="modal-header">
            <div class="modal-title">Save to Collection</div>
            <button class="modal-close" id="close-modal-btn">&times;</button>
        </div>
        <span class="modal-section-label">Select Collection</span>
        <div class="collections-list" id="modal-collections-box">
            <?php if(empty($kolekcije)): ?>
                <div class="empty-collections-msg" style="font-size:13px; color:#444; font-style:italic;">No collections. Create one below!</div>
            <?php else: ?>
                <?php foreach($kolekcije as $k): ?>
                    <button class="collection-item-btn" onclick="saveToExistingCollection(<?php echo $k['id']; ?>)">
                        <?php echo htmlspecialchars($k['naziv']); ?>
                    </button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <span class="modal-section-label">Or Create New</span>
        <div class="create-coll-form">
            <input type="text" id="new-collection-name" class="modal-input" placeholder="Collection name..." maxlength="50">
            <button class="modal-btn-submit" onclick="createNewCollectionOnly()">Create</button>
        </div>
    </div>
</div>

<div class="toast-notification" id="toast-alert">
    <span id="toast-icon"></span>
    <span id="toast-text"></span>
</div>

<script>
const TMDB_ID = <?php echo $tmdb_id; ?>;
const MEDIA_TYPE = "<?php echo $media_type; ?>";
const ACCESS_TOKEN = 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI2OTlmOTE2M2NjZDU0YTA1NjY4ZjUwZjE5YWMwYjBhOCIsIm5iZiI6MTc3NzY2NzY1NC43NTUsInN1YiI6IjY5ZjUwZTQ2ODg1MzY3ODg1YzFhYjQxNiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.xLRWJXH3J129cd_Z5DvIwBuTA0gjyXLqAAkyK9Kxf70';

const fetchOptions = { method: 'GET', headers: { accept: 'application/json', Authorization: `Bearer ${ACCESS_TOKEN}` } };

const modal = document.getElementById('collection-modal');
document.getElementById('open-modal-btn').addEventListener('click', () => modal.classList.add('active'));
document.getElementById('close-modal-btn').addEventListener('click', () => modal.classList.remove('active'));

function showToast(message, isError = false) {
    const toast = document.getElementById('toast-alert');
    const icon = document.getElementById('toast-icon');
    const text = document.getElementById('toast-text');
    
    text.textContent = message;
    
    if(isError) {
        toast.classList.add('error');
        icon.innerHTML = '⚠️';
    } else {
        toast.classList.remove('error');
        icon.innerHTML = '✅';
    }
    
    toast.classList.add('active');
    
    setTimeout(() => {
        toast.classList.remove('active');
    }, 4000);
}

document.addEventListener("DOMContentLoaded", () => {
    fetch(`https://api.themoviedb.org/3/${MEDIA_TYPE}/${TMDB_ID}?language=en-US`, fetchOptions)
        .then(res => res.json())
        .then(data => {
            if(data.backdrop_path) document.getElementById('custom-backdrop').style.backgroundImage = `url('https://image.tmdb.org/t/p/original${data.backdrop_path}')`;
            if(data.poster_path) document.getElementById('movie-poster').src = `https://image.tmdb.org/t/p/w500${data.poster_path}`;
            document.getElementById('movie-title').textContent = data.title || data.name;
            document.getElementById('movie-rating').textContent = `★ ${data.vote_average ? data.vote_average.toFixed(1) : 'N/A'}`;
            document.getElementById('movie-release-date').textContent = data.release_date || data.first_air_date || 'N/A';
            document.getElementById('movie-runtime').textContent = MEDIA_TYPE === 'movie' ? `${data.runtime || 0} min` : `${data.number_of_seasons || 0} Seasons`;
            document.getElementById('movie-overview').textContent = data.overview || 'No overview description available.';
            if(data.genres) document.getElementById('movie-genres').textContent = data.genres.map(g => g.name).join(', ');
            document.getElementById('movie-status').textContent = data.status || '-';
        })
        .catch(err => {
            document.getElementById('movie-title').textContent = "Error loading data";
        });

    fetch(`https://api.themoviedb.org/3/${MEDIA_TYPE}/${TMDB_ID}/watch/providers`, fetchOptions)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('providers-container');
            container.innerHTML = '';
            const results = data.results;

            if (results && (results.US || results.UK || results.DE)) {
                const regionalData = results.US || results.UK || results.DE;
                const providers = regionalData.flatrate || regionalData.buy || [];
                
                if(providers.length > 0) {
                    const uniqueProviders = Array.from(new Set(providers.map(p => p.provider_id)))
                        .map(id => providers.find(p => p.provider_id === id)).slice(0, 4);

                    uniqueProviders.forEach(p => {
                        let directUrl = '#';
                        const name = p.provider_name.toLowerCase();
                        
                        if (name.includes('netflix')) directUrl = 'https://www.netflix.com';
                        else if (name.includes('disney')) directUrl = 'https://www.disneyplus.com';
                        else if (name.includes('amazon') || name.includes('prime')) directUrl = 'https://www.primevideo.com';
                        else if (name.includes('hbo') || name.includes('max')) directUrl = 'https://www.max.com';
                        else if (name.includes('apple')) directUrl = 'https://tv.apple.com';
                        else if (name.includes('hulu')) directUrl = 'https://www.hulu.com';
                        else directUrl = `https://www.google.com/search?q=Watch+${encodeURIComponent(p.provider_name)}`;

                        const anchor = document.createElement('a');
                        anchor.href = directUrl;
                        anchor.target = '_blank';
                        anchor.className = 'provider-link';
                        
                        const logoWrap = document.createElement('div');
                        logoWrap.className = 'provider-logo';
                        logoWrap.innerHTML = `<img src="https://image.tmdb.org/t/p/w92${p.logo_path}" alt="${p.provider_name}" title="Open ${p.provider_name}">`;
                        
                        anchor.appendChild(logoWrap);
                        container.appendChild(anchor);
                    });
                } else { container.innerHTML = '<span class="no-providers">Not available.</span>'; }
            } else { container.innerHTML = '<span class="no-providers">Not available.</span>'; }
        })
        .catch(() => {
            document.getElementById('providers-container').innerHTML = '<span class="no-providers">Not available.</span>';
        });
});

function saveToExistingCollection(collectionId) {
    const formData = new FormData();
    formData.append('action', 'save_to_collection');
    formData.append('kolekcija_id', collectionId);
    formData.append('tmdb_id', TMDB_ID);
    formData.append('media_type', MEDIA_TYPE);

    fetch('kolekcije_handler.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            showToast('Saved successfully to collection!');
            document.getElementById('open-modal-btn').classList.add('saved');
            modal.classList.remove('active');
        } else { 
            showToast(data.message || 'An error occurred.', true); 
        }
    })
    .catch(() => {
        showToast('An error occurred while saving.', true);
    });
}

function createNewCollectionOnly() {
    const input = document.getElementById('new-collection-name');
    const naziv = input.value.trim();
    if(naziv === '') { 
        showToast('Please enter a collection name.', true); 
        return; 
    }

    const formData = new FormData();
    formData.append('action', 'just_create_collection');
    formData.append('naziv', naziv);

    fetch('kolekcije_handler.php', { method: 'POST', body: formData })
    .then(res => {
        if (!res.ok) {
            throw new Error('Network response was not ok');
        }
        return res.json();
    })
    .then(data => {
        if(data.success) {
            const box = document.getElementById('modal-collections-box');
            const emptyMsg = box.querySelector('.empty-collections-msg');
            if(emptyMsg) emptyMsg.remove();
            
            const newBtn = document.createElement('button');
            const createdId = data.new_id || data.id;
            newBtn.className = 'collection-item-btn';
            newBtn.textContent = naziv;
            newBtn.onclick = () => saveToExistingCollection(createdId);
            box.appendChild(newBtn);
            
            input.value = '';
            showToast('Collection created successfully!');
        } else { 
            showToast(data.message || 'An error occurred while trying to create a collection.', true); 
        }
    })
    .catch(err => {
        showToast('An error occurred while trying to create a collection.', true);
    });
}
</script>
</body>
</html>
