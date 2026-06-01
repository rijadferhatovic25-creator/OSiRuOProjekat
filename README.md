# ☁️ OSiRuO – Dockerizacija i Cloud Deployment

## Dockerizacija projekta

U okviru predmeta Operativni sistemi i računarstvo u oblaku (OSiRuO), projekat **FILMOTEKA** je dockeriziran kako bi se omogućilo jednostavno pokretanje aplikacije u različitim okruženjima bez dodatne konfiguracije.

Aplikacija je podijeljena na tri odvojena servisa:

* **Frontend (Nginx)**
* **Backend (PHP-FPM)**
* **MySQL/MariaDB baza podataka**

Svi servisi se pokreću pomoću Docker Compose konfiguracije.

---

## Docker arhitektura

```text
                 Browser
                    │
                    ▼
             ┌─────────────┐
             │    Nginx    │
             │  Frontend   │
             └──────┬──────┘
                    │
                    ▼
             ┌─────────────┐
             │   PHP-FPM   │
             │   Backend   │
             └──────┬──────┘
                    │
                    ▼
             ┌─────────────┐
             │ MariaDB     │
             │ MySQL DB    │
             └─────────────┘
```

---

## Docker Compose

Za orkestraciju servisa korišten je `docker-compose.yml`.

Definisani servisi:

### mysql

* MariaDB 10.11
* Automatska inicijalizacija baze putem `init.sql`
* Persistencija podataka pomoću Docker volume-a
* Healthcheck konfiguracija

### backend

* PHP 8.2 FPM Alpine
* PDO MySQL i mysqli ekstenzije
* Povezivanje sa MySQL servisom putem Docker mreže
* Upload direktorij mapiran kao volume

### frontend

* Nginx Alpine
* Serviranje aplikacije
* Prosljeđivanje PHP zahtjeva backend servisu
* Dostupan na portu 8080

Pokretanje kompletnog sistema:

```bash
docker compose up --build
```

Zaustavljanje sistema:

```bash
docker compose down
```

---

## Docker Volumes

Za trajnu pohranu podataka korišten je named volume:

```yaml
volumes:
  mysql_data:
```

Na ovaj način podaci ostaju sačuvani čak i nakon restartovanja kontejnera.

---

## Frontend Dockerfile

Frontend koristi multi-stage build pristup.

### Build stage

* Base image: `node:18-alpine`
* Organizacija aplikacijskih fajlova
* Kreiranje dist direktorija

### Serve stage

* Base image: `nginx:alpine`
* Serviranje statičkih datoteka
* Proxy komunikacija prema PHP backendu

Prednost ovog pristupa je manja veličina finalnog image-a i bolja optimizacija produkcijskog okruženja.

---

## Backend Dockerfile

Backend koristi:

```text
php:8.2-fpm-alpine
```

Instalirane su potrebne PHP ekstenzije:

* mysqli
* pdo_mysql
* gd
* mbstring
* zip
* exif
* opcache

Također je implementiran Docker Healthcheck koji provjerava ispravnost PHP-FPM procesa.

---

## Mrežna komunikacija

Za komunikaciju između servisa kreirana je posebna Docker mreža:

```yaml
networks:
  filmoteka_net:
```

Servisi međusobno komuniciraju putem DNS imena:

* frontend → backend
* backend → mysql

bez potrebe za korištenjem IP adresa.

---

## Bash skripta

Kreirana je skripta:

```text
health-check.sh
```

Skripta automatski:

* šalje HTTP zahtjev aplikaciji
* provjerava statusni kod
* evidentira rezultat u log fajl
* koristi odgovarajuće exit kodove

Pokretanje:

```bash
chmod +x health-check.sh
./health-check.sh
```

Primjer izlaza:

```text
[2026-06-01 20:15:43] STATUS: 200 OK
Application is running successfully.
```

---

## Cloud Deployment

Docker image aplikacije je trebao biti deployan na cloud infrastrukturu kako bi aplikacija bila javno dostupna korisnicima.

### Deployment proces

1. Build Docker image-a
2. Push image-a na registry
3. Deploy aplikacije
4. Testiranje funkcionalnosti
5. Verifikacija HTTPS pristupa

### Produkcijski URL

N/A (nažalost)

---

## Doprinos članova tima

### Rijad Ferhatović

* Docker Compose konfiguracija
* Konfiguracija MariaDB servisa
* Docker networking
* Deployment aplikacije

### Alen Hajrić

* Frontend Docker konfiguracija
* Nginx konfiguracija
* Testiranje Docker okruženja
* Dokumentacija

### Amar Humić

* Backend Docker konfiguracija
* Bash skripta za health check
* Healthcheck implementacija
* Testiranje cloud deploymenta

---

## Naučene lekcije

Tokom realizacije projekta stečeno je praktično iskustvo u:

* Docker kontejnerizaciji web aplikacija
* Upravljanju više servisa pomoću Docker Compose-a
* Konfiguraciji PHP-FPM i Nginx okruženja
* Upravljanju Docker volume-ima
* Implementaciji health check mehanizama
* Cloud deployment procesima

Najveći izazovi bili su konfiguracija komunikacije između servisa, povezivanje aplikacije sa bazom podataka unutar Docker mreže te otklanjanje problema prilikom deploymenta i produkcijskog testiranja. Te kao najveći izazov nam je ostao cloud deployment koji nismo uspjeli izvršiti.
