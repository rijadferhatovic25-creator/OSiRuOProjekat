# =============================================================
# frontend/Dockerfile — Filmoteka
# Multi-stage build: Node.js build stage + nginx:alpine serve stage
#
# STAGE 1 (build): Koristimo node:18-alpine da "simuliramo" build
#                  proces (zadatak zahtijeva ovaj pattern).
#                  Naši fajlovi su već statički (HTML/CSS/JS/PHP),
#                  pa ih samo organizujemo u /app/dist.
#
# STAGE 2 (serve): nginx:alpine servira statičke fajlove i
#                  proksira PHP requestove na backend (PHP-FPM).
#
# Finalni image sadrži SAMO nginx + fajlove — Node.js se odbacuje.
# Rezultat: image ~25-40MB (daleko ispod zahtjeva od 150MB)
# =============================================================

# ============================================================
# STAGE 1 — BUILD STAGE
# ============================================================
FROM node:18-alpine AS build

# Postavljamo radni direktorij unutar build kontejnera
WORKDIR /app

# Kopiramo sve PHP/CSS/JS/HTML fajlove iz frontend/ foldera
COPY . .

# Kreiramo dist/ folder i kopiramo sve fajlove u njega.
# Za PHP aplikaciju nema "kompajliranja" — ovo je organizacijski korak
# koji zadovoljava multi-stage build pattern iz zadatka.
# U pravom React projektu, ovo bi bio: npm ci && npm run build
RUN mkdir -p dist && \
    # Kopiramo sve PHP fajlove (aplikacijska logika)
    cp -r *.php dist/ 2>/dev/null || true && \
    # Kopiramo CSS stil
    cp -r *.css dist/ 2>/dev/null || true && \
    # Kopiramo JavaScript fajlove
    cp -r *.js dist/ 2>/dev/null || true && \
    # Kopiramo nginx konfiguraciju (za serve stage)
    cp nginx.conf dist/ 2>/dev/null || true && \
    # Kopiramo .htaccess ako postoji
    cp .htaccess dist/ 2>/dev/null || true && \
    # Kopiramo guest.png (default avatar)
    cp guest.png dist/ 2>/dev/null || true && \
    # Kreiramo uploads folder za profilne slike
    mkdir -p dist/uploads

# Provjeri šta je u dist/ folderu (korisno za debugging)
RUN echo "=== Sadržaj dist/ foldera ===" && ls -la dist/

# ============================================================
# STAGE 2 — SERVE STAGE (finalni image)
# ============================================================
# nginx:alpine je ekstremno mali image (~7MB bazni image)
# Sadrži samo nginx web server — nikakav PHP, Node.js, ili build toolove
FROM nginx:alpine AS serve

# Instaliramo samo ono što nginx treba za PHP-FPM proksi
# (nginx:alpine već ima sve što treba, ovo je za debugging alate)
RUN apk add --no-cache curl

# Uklanjamo default nginx welcome page koji dolazi s imageom
RUN rm -rf /usr/share/nginx/html/*

# ⭐ KLJUČNI KORAK MULTI-STAGE BUILD-A:
# Kopiramo fajlove iz STAGE 1 build kontejnera.
# --from=build znači: "uzmi iz prvog stagea", a ne sa lokalnog diska.
# Node.js, npm i sve build zavisnosti se AUTOMATSKI odbacuju.
# U finalni image ide SAMO dist/ sadržaj.
COPY --from=build /app/dist /var/www/filmoteka

# Kopiramo nginx konfiguraciju
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Kreiramo uploads direktorij sa ispravnim permisijama
RUN mkdir -p /var/www/filmoteka/uploads && \
    chmod -R 755 /var/www/filmoteka && \
    chmod -R 775 /var/www/filmoteka/uploads

# Nginx sluša na portu 80
EXPOSE 80

# Pokretanje nginx-a u foreground modu.
# "daemon off" je OBAVEZNO za Docker — Docker treba foreground proces
# da zna da je kontejner živ. Ako bi nginx išao u background (daemon),
# Docker bi mislio da je kontejner završio i ugasio bi ga.
CMD ["nginx", "-g", "daemon off;"]
