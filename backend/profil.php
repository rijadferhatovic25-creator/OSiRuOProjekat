<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT ime, prezime, bio, profilna_slika, status, role FROM korisnici WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user && $user['status'] === 'banned') {
    header("Location: logout.php");
    exit();
}

$ime = $user ? $user['ime'] : 'Admin';
$prezime = $user ? $user['prezime'] : 'Administrator';
$bio = $user ? $user['bio'] : '';
$profilna = ($user && !empty($user['profilna_slika'])) ? $user['profilna_slika'] : 'guest.png';

$slika_putanja = "uploads/" . $profilna;
if (!file_exists($slika_putanja) || $profilna == 'guest.png') {
    $slika_putanja = "https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png";
}

$sql = "SELECT z.id AS zbirka_id, z.naziv, z.tip, s.tmdb_id, s.media_type 
        FROM zbirke z 
        LEFT JOIN zbirka_stavke s ON z.id = s.zbirka_id 
        WHERE z.user_id = ? 
        ORDER BY z.id DESC";

$stmt_zbirke = $conn->prepare($sql);
$stmt_zbirke->bind_param("i", $user_id);
$stmt_zbirke->execute();
$result_zbirke = $stmt_zbirke->get_result();

$struktura = [];
while ($row = $result_zbirke->fetch_assoc()) {
    $z_id = $row['zbirka_id'];
    if ($z_id === null) continue;
    
    if (!isset($struktura[$z_id])) {
        $struktura[$z_id] = [
            'id' => $z_id,
            'naziv' => $row['naziv'],
            'tip' => $row['tip'],
            'filmovi' => []
        ];
    }
    if ($row['tmdb_id'] !== null) {
        $struktura[$z_id]['filmovi'][] = [
            'id' => $row['tmdb_id'],
            'type' => $row['media_type']
        ];
    }
}
$stmt_zbirke->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Filmoteka</title>
    <link rel="stylesheet" href="stil.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .admin-box {
            background: rgba(255, 0, 80, 0.08);
            border: 1px solid #ff0050;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            width: 100%;
        }
        .admin-box h4 {
            margin: 0 0 5px 0;
            color: #ff0050;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }
        .admin-box p {
            margin: 0;
            color: #888;
            font-size: 13px;
        }
        .admin-buttons-container {
            display: flex;
            gap: 15px;
            width: 100%;
            justify-content: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        .admin-btn {
            flex: 1;
            min-width: 180px;
            max-width: 240px;
            background: #ff0050;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            transition: background 0.2s, transform 0.2s;
            box-shadow: 0 4px 15px rgba(255, 0, 80, 0.2);
            font-size: 13px;
            font-family: 'Montserrat', sans-serif;
        }
        .admin-btn:hover {
            background: #e00045;
            transform: translateY(-2px);
        }
        .admin-btn i {
            margin-right: 8px;
        }

        .saved-master-container {
            clear: both;
            display: block;
            width: 100%;
            max-width: 1200px;
            margin: 60px auto 40px auto;
            padding: 0 20px;
            box-sizing: border-box;
            text-align: left !important;
        }

        .saved-section-title { 
            font-family: 'Montserrat', sans-serif; 
            font-size: 26px; 
            font-weight: 700; 
            margin-bottom: 25px; 
            color: #fff; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            text-align: left !important;
        }

        .zbirka-prozor { 
            background: #111; 
            border: 1px solid #222; 
            border-radius: 12px; 
            padding: 25px; 
            margin-bottom: 35px; 
            position: relative; 
            text-align: left !important;
        }

        .zbirka-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #1f1f1f; }
        .zbirka-naslov { font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px; font-family: 'Montserrat', sans-serif; }
        
        .tackice-meni-okvir { position: relative; }
        .tackice-btn { background: none; border: none; color: #666; font-size: 20px; cursor: pointer; padding: 5px; transition: color 0.2s; }
        .tackice-btn:hover { color: #fff; }
        
        .skriveni-meni { display: none; position: absolute; right: 0; top: 35px; background: #1a1a1a; border: 1px solid #333; border-radius: 6px; width: 150px; z-index: 10; box-shadow: 0 5px 15px rgba(0,0,0,0.5); }
        .skriveni-meni div { padding: 12px; font-size: 13px; cursor: pointer; color: #ccc; font-family: 'Montserrat', sans-serif; transition: background 0.2s, color 0.2s; text-align: left; }
        .skriveni-meni div:hover { background: #ff0050; color: #fff; }
        
        .filmovi-scroller { display: flex; gap: 15px; overflow-x: auto; padding-bottom: 15px; scroll-behavior: smooth; justify-content: flex-start; }
        .filmovi-scroller::-webkit-scrollbar { height: 6px; }
        .filmovi-scroller::-webkit-scrollbar-thumb { background: #222; border-radius: 4px; }
        .filmovi-scroller::-webkit-scrollbar-thumb:hover { background: #ff0050; }
        
        .film-kartica-omot { position: relative; flex: 0 0 160px; height: 240px; border-radius: 8px; overflow: hidden; background: #1a1a1a; transition: transform 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        .film-kartica-omot:hover { transform: scale(1.03); }
        .film-kartica-omot img { width: 100%; height: 100%; object-fit: cover; }
        
        .film-checkbox { position: absolute; top: 10px; left: 10px; width: 22px; height: 22px; z-index: 5; display: none; cursor: pointer; accent-color: #ff0050; }
        .kanta-btn { color: #ff0050; cursor: pointer; font-size: 18px; display: none; margin-left: 15px; transition: transform 0.2s; }
        .kanta-btn:hover { transform: scale(1.2); }
        
        .custom-alert-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 9999; align-items: center; justify-content: center; }
        .custom-alert-box { background: #141414; border: 1px solid #252525; padding: 25px; border-radius: 12px; width: 340px; text-align: center; font-family: 'Montserrat', sans-serif; box-shadow: 0 10px 30px rgba(0,0,0,0.7); }
        .custom-alert-box p { font-size: 15px; margin-bottom: 25px; color: #eee; line-height: 1.5; }
        .alert-gumbi { display: flex; justify-content: center; gap: 15px; }
        .alert-gumbi button { padding: 10px 25px; border: none; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer; transition: background 0.2s; font-family: 'Montserrat', sans-serif; }
        .btn-da { background: #ff0050; color: #fff; }
        .btn-da:hover { background: #cc003f; }
        .btn-ne { background: #333; color: #fff; }
        .btn-ne:hover { background: #444; }
    </style>
</head>
<body>

<header>
    <div class="logo">FILMOTEKA</div>
    <nav>
        <a href="index.php">Home</a>
        <a href="logout.php" class="logout-link">Logout</a>
    </nav>
</header>

<div class="profile-container" style="margin-top: 120px; display: flex; justify-content: center; padding: 0 20px;">
    <div class="profile-card" style="background: #111; border: 1px solid #222; border-radius: 16px; padding: 40px; width: 100%; max-width: 600px; display: flex; flex-direction: column; align-items: center; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        
        <div class="profile-avatar-container" style="margin-bottom: 20px;">
            <img src="<?php echo $slika_putanja; ?>" alt="Profile picture" style="width: 130px; height: 130px; border-radius: 50%; object-fit: cover; border: 3px solid #ff0050; box-shadow: 0 0 20px rgba(255,0,80,0.2);">
        </div>
        
        <div class="profile-info-box" style="text-align: center; margin-bottom: 25px;">
            <h2 class="profile-name" style="margin: 0 0 5px 0; font-size: 26px; font-weight: 700; color: #fff;"><?php echo htmlspecialchars($ime . ' ' . $prezime); ?></h2>
            <p style="margin: 0; color: #ff0050; font-weight: 600; font-size: 14px;">@<?php echo htmlspecialchars($_SESSION['username'] ?? 'admin'); ?></p>
        </div>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="admin-box">
                <h4><i class="fas fa-user-shield"></i> You are logged in as an administrator</h4>
                <p>Global system control dashboard</p>
            </div>
            
            <div class="admin-buttons-container">
                <a href="admin_panel.php" class="admin-btn">
                    <i class="fas fa-users"></i> Manage Profiles
                </a>
                <a href="admin_feedback.php" class="admin-btn">
                    <i class="fas fa-comments"></i> Manage Feedbacks
                </a>
            </div>
        <?php endif; ?>

        <?php if(!empty($bio)): ?>
            <div style="background: #161616; border: 1px solid #252525; padding: 15px 20px; border-radius: 10px; width: 100%; text-align: center; margin-bottom: 20px;">
                <p style="margin: 0; color: #aaa; line-height: 1.6; font-style: italic; font-size: 14px;">"<?php echo htmlspecialchars($bio); ?>"</p>
            </div>
        <?php endif; ?>

        <a href="edit_profil.php" class="btn btn-play edit-profile-btn" style="background: #222; border: 1px solid #333; color: #fff; text-decoration: none; padding: 10px 25px; border-radius: 6px; font-size: 14px; font-weight: 600; transition: all 0.2s; width: 100%; max-width: 200px; text-align: center;" onmouseover="this.style.background='#ff0050'; this.style.borderColor='#ff0050';" onmouseout="this.style.background='#222'; this.style.borderColor='#333';">
            <i class="fas fa-user-edit" style="margin-right: 5px;"></i> Edit profile
        </a>
    </div>
</div>

<div class="saved-master-container">
    <div class="saved-section-title">Saved:</div>

    <?php if(!empty($struktura)): ?>
        <?php foreach($struktura as $z): ?>
            <div class="zbirka-prozor" id="zbirka-prozor-<?php echo $z['id']; ?>" data-id="<?php echo $z['id']; ?>">
                <div class="zbirka-header">
                    <div class="zbirka-naslov">
                        <span>
                            <?php if($z['tip'] === 'privatna'): ?>
                                <i class="fas fa-lock" title="Private collection" style="color: #666;"></i>
                            <?php else: ?>
                                <i class="fas fa-lock-open" title="Public collection" style="color: #ff0050;"></i>
                            <?php endif; ?>
                        </span>
                        <span><?php echo htmlspecialchars($z['naziv']); ?></span>
                        <i class="fas fa-trash-alt kanta-btn" title="Delete" onclick="aktivirajKantu(<?php echo $z['id']; ?>)"></i>
                    </div>

                    <div class="tackice-meni-okvir">
                        <button class="tackice-btn" onclick="toggleMeni(event, <?php echo $z['id']; ?>)">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="skriveni-meni" id="meni-<?php echo $z['id']; ?>">
                            <div onclick="urediZbirku(<?php echo $z['id']; ?>)">Manage collection</div>
                            <div onclick="aktivirajSelectMultiple(<?php echo $z['id']; ?>)">Select multiple</div>
                        </div>
                    </div>
                </div>

                <div class="filmovi-scroller" id="scroller-<?php echo $z['id']; ?>">
                    <?php if(!empty($z['filmovi'])): ?>
                        <?php foreach($z['filmovi'] as $f): ?>
                            <div class="film-kartica-omot" id="film-<?php echo $z['id']; ?>-<?php echo $f['id']; ?>" data-tmdb="<?php echo $f['id']; ?>">
                                <input type="checkbox" class="film-checkbox checkbox-zbirka-<?php echo $z['id']; ?>" value="<?php echo $f['id']; ?>">
                                <a href="detalji.php?id=<?php echo $f['id']; ?>&type=<?php echo $f['type']; ?>">
                                    <img src="https://via.placeholder.com/160x240?text=Loading..." class="api-poster" data-id="<?php echo $f['id']; ?>" data-type="<?php echo $f['type']; ?>" alt="Poster">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #444; font-size: 14px; font-style: italic; font-family: 'Montserrat', sans-serif; text-align: left;">This collection is empty.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: #666; font-family: 'Montserrat', sans-serif; text-align: left;">You haven't created any collections yet.</p>
    <?php endif; ?>
</div>

<div class="custom-alert-overlay" id="custom-alert">
    <div class="custom-alert-box">
        <p>Are you sure you want to delete all items from this collection?</p>
        <div class="alert-gumbi">
            <button class="btn-da" id="alert-da">Yes</button>
            <button class="btn-ne" id="alert-ne">No</button>
        </div>
    </div>
</div>

<footer>
    © 2026 Filmoteka. All rights reserved.
</footer>

<script>
const ACCESS_TOKEN = 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI2OTlmOTE2M2NjZDU0YTA1NjY4ZjUwZjE5YWMwYjBhOCIsIm5iZiI6MT7766NzY1NC43NTUsInN1YiI6IjY5ZjUwZTQ2ODg1MzY3ODg1YzFhYjQxNiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.xLRWJXH3J129cd_Z5DvIwBuTA0gjyXLqAAkyK9Kxf70';
let trenutnaZbirkaZaCiscenje = null; 
let tipBrisanja = '';

document.addEventListener('click', () => {
    document.querySelectorAll('.skriveni-meni').forEach(m => m.style.display = 'none');
});

function toggleMeni(e, id) {
    e.stopPropagation();
    document.querySelectorAll('.skriveni-meni').forEach(m => {
        if(m.id !== 'meni-'+id) m.style.display = 'none';
    });
    let meni = document.getElementById('meni-'+id);
    meni.style.display = meni.style.display === 'block' ? 'none' : 'block';
}

function urediZbirku(id) {
    let prozor = document.getElementById('zbirka-prozor-'+id);
    let kanta = prozor.querySelector('.kanta-btn');
    kanta.style.display = kanta.style.display === 'inline-block' ? 'none' : 'inline-block';
}

function aktivirajSelectMultiple(id) {
    let prozor = document.getElementById('zbirka-prozor-'+id);
    let checkboxes = prozor.querySelectorAll('.film-checkbox');
    let kanta = prozor.querySelector('.kanta-btn');
    
    kanta.style.display = 'inline-block';
    checkboxes.forEach(c => {
        c.style.display = c.style.display === 'block' ? 'none' : 'block';
    });
}

function aktivirajKantu(zbirkaId) {
    let prozor = document.getElementById('zbirka-prozor-'+zbirkaId);
    let checkboxes = prozor.querySelectorAll('.film-checkbox:checked');
    let sviCheckboxes = prozor.querySelectorAll('.film-checkbox');
    const alertTekst = document.querySelector('.custom-alert-box p');

    if(sviCheckboxes.length > 0 && sviCheckboxes[0].style.display === 'block') {
        if(checkboxes.length > 0) {
            let selektovaniIdjevi = [];
            checkboxes.forEach(c => selektovaniIdjevi.push(parseInt(c.value)));
            
            let fd = new FormData();
            fd.append('zbirka_id', zbirkaId);
            fd.append('stavke', JSON.stringify(selektovaniIdjevi));

            fetch('akcije_zbirka.php?action=delete_items', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if(res.status === 'success') {
                    checkboxes.forEach(c => {
                        const el = document.getElementById(`film-${zbirkaId}-${c.value}`);
                        if(el) el.remove();
                    });
                    ponistiStanje(zbirkaId);
                }
            });
            return;
        }
    }

    if(sviCheckboxes.length > 0 && sviCheckboxes[0].style.display === 'block' && checkboxes.length === 0) {
        trenutnaZbirkaZaCiscenje = zbirkaId;
        tipBrisanja = 'sadrzaj';
        alertTekst.textContent = "Are you sure you want to delete all contents of this collection?";
        document.getElementById('custom-alert').style.display = 'flex';
        return;
    }

    trenutnaZbirkaZaCiscenje = zbirkaId;
    tipBrisanja = 'kompletna_zbirka';
    alertTekst.textContent = "Are you sure you want to completely delete this collection?";
    document.getElementById('custom-alert').style.display = 'flex';
}

document.getElementById('alert-ne').addEventListener('click', () => {
    document.getElementById('custom-alert').style.display = 'none';
    if(trenutnaZbirkaZaCiscenje) ponistiStanje(trenutnaZbirkaZaCiscenje);
    trenutnaZbirkaZaCiscenje = null;
    tipBrisanja = '';
});

document.getElementById('alert-da').addEventListener('click', () => {
    document.getElementById('custom-alert').style.display = 'none';
    if (!trenutnaZbirkaZaCiscenje) return;

    let fd = new FormData();
    fd.append('zbirka_id', trenutnaZbirkaZaCiscenje);

    if (tipBrisanja === 'sadrzaj') {
        fetch('akcije_zbirka.php?action=clear_collection', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.status === 'success') {
                let scroller = document.getElementById('scroller-'+trenutnaZbirkaZaCiscenje);
                if (scroller) {
                    scroller.innerHTML = '<p style="color:#444; font-size:14px; font-style:italic; font-family:\'Montserrat\', sans-serif; text-align: left;">This collection is empty.</p>';
                }
                ponistiStanje(trenutnaZbirkaZaCiscenje);
            }
            resetujState();
        });
    } else if (tipBrisanja === 'kompletna_zbirka') {
        fetch('akcije_zbirka.php?action=delete_collection', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.status === 'success') {
                let zbirkaElement = document.getElementById('zbirka-prozor-'+trenutnaZbirkaZaCiscenje);
                if (zbirkaElement) zbirkaElement.remove();
            }
            resetujState();
        });
    }
});

function resetujState() {
    trenutnaZbirkaZaCiscenje = null;
    tipBrisanja = '';
}

function ponistiStanje(id) {
    let prozor = document.getElementById('zbirka-prozor-'+id);
    if(prozor) {
        let kanta = prozor.querySelector('.kanta-btn');
        if(kanta) kanta.style.display = 'none';
        prozor.querySelectorAll('.film-checkbox').forEach(c => {
            c.checked = false;
            c.style.display = 'none';
        });
    }
}

document.querySelectorAll('.api-poster').forEach(img => {
    const id = img.getAttribute('data-id');
    const type = img.getAttribute('data-type');
    
    fetch(`https://api.themoviedb.org/3/${type}/${id}?language=en-US`, {
        method: 'GET',
        headers: { 
            accept: 'application/json',
            Authorization: `Bearer ${ACCESS_TOKEN}` 
        }
    })
    .then(r => r.json())
    .then(data => {
        if(data.poster_path) {
            img.src = `https://image.tmdb.org/t/p/w342${data.poster_path}`;
        } else {
            img.src = 'https://via.placeholder.com/160x240?text=No+Poster';
        }
    })
    .catch(err => {
        img.src = 'https://via.placeholder.com/160x240?text=Error';
    });
});
</script>
</body>
</html>
