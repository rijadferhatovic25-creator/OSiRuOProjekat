<?php
// =============================================================
// db.php — Filmoteka Database Konekcija
//
// ORIGINALNI KOD (loše — hardkodirani kredencijali):
// $host = "localhost";
// $user = "root";
// $pass = "";
// $db_name = "filmoteka_db";
//
// NOVI KOD (ispravno — čita iz environment varijabli):
// Docker Compose injektuje varijable iz .env fajla u svaki
// kontejner. getenv() ih čita unutar PHP-a.
//
// Tok: .env fajl → docker-compose.yml → Docker → kontejner → getenv()
// =============================================================

// getenv('VARIJABLA') čita environment varijablu koju je Docker injektovao.
// Operator ?: daje defaultnu vrijednost ako varijabla nije postavljena
// (npr. kada koristiš XAMPP lokalno bez Dockera).

$host    = getenv('DB_HOST')     ?: 'localhost';   // U Dockeru: "mysql" (ime servisa)
$user    = getenv('DB_USER')     ?: 'root';         // U Dockeru: "filmoteka_user"
$pass    = getenv('DB_PASSWORD') ?: '';             // U Dockeru: iz .env fajla
$db_name = getenv('DB_NAME')     ?: 'filmoteka_db'; // Ime baze ostaje isto
$port    = intval(getenv('DB_PORT') ?: 3306);       // MySQL port (3306 je default)

// Kreiramo mysqli konekciju
// mysqli konstruktor: (host, user, password, database, port)
$conn = new mysqli($host, $user, $pass, $db_name, $port);

// Provjera konekcije
if ($conn->connect_error) {
    // U produkciji: logiraj grešku, ali NE prikazuj detalje korisniku
    // connect_error može sadržati lozinku ili interne podatke!
    error_log("DB konekcija neuspješna: " . $conn->connect_error);

    // Vrati generičku grešku korisniku
    // Provjeri da li je request za JSON (API pozivi) ili HTML (stranice)
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        http_response_code(503);
        echo json_encode(['error' => 'Servis privremeno nedostupan.']);
    } else {
        http_response_code(503);
        echo '<!DOCTYPE html><html><body style="background:#0b0b0b;color:#fff;font-family:sans-serif;text-align:center;padding:50px;">
              <h1 style="color:#e50914;">503 — Servis nedostupan</h1>
              <p>Baza podataka je privremeno nedostupna. Pokušaj ponovo za nekoliko minuta.</p>
              </body></html>';
    }
    exit(1);
}

// Postavljamo charset na utf8mb4
// Ovo je KRITIČNO za BHS karaktere i emoji podršku
// utf8mb4 je superset UTF-8 koji podržava sve Unicode karaktere
if (!$conn->set_charset("utf8mb4")) {
    error_log("Greška pri postavljanju charset-a: " . $conn->error);
}
?>
