
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS `banovani_korisnici` (
  `id`          int(11)   NOT NULL AUTO_INCREMENT,
  `korisnik_id` int(11)   NOT NULL,
  `razlog`      text      NOT NULL,
  `datum_bana`  timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `korisnik_id` (`korisnik_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS `feedback` (
  `id`           int(11)      NOT NULL AUTO_INCREMENT,
  `ime`          varchar(100) NOT NULL,
  `email`        varchar(100) NOT NULL,
  `poruka`       text         NOT NULL,
  `datum_slanja` timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS `filmovi` (
  `id`             int(11)      NOT NULL AUTO_INCREMENT,
  `naslov`         varchar(255) NOT NULL,
  `opis`           text         DEFAULT NULL,
  `slika`          varchar(255) DEFAULT NULL,
  `rating`         decimal(3,1) DEFAULT 0.0,
  `link_gledanje`  varchar(255) DEFAULT '#',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS `korisnici` (
  `id`             int(11)      NOT NULL AUTO_INCREMENT,
  `ime`            varchar(50)  NOT NULL,
  `email`          varchar(255) DEFAULT NULL,
  `prezime`        varchar(50)  NOT NULL,
  `datum_rodenja`  date         NOT NULL DEFAULT '2000-01-01',
  `username`       varchar(50)  NOT NULL,
  `password`       varchar(255) NOT NULL,
  `role`           enum('admin','guest') DEFAULT 'guest',
  `bio`            varchar(100) DEFAULT '',
  `profilna_slika` varchar(255) DEFAULT 'guest.png',
  `status`         enum('active','banned') DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS `postavke_sistema` (
  `kljuc`     varchar(50) NOT NULL,
  `vrijednost` text        DEFAULT NULL,
  PRIMARY KEY (`kljuc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS `zbirke` (
  `id`         int(11)      NOT NULL AUTO_INCREMENT,
  `user_id`    int(11)      NOT NULL,
  `naziv`      varchar(255) NOT NULL,
  `tip`        enum('javna','privatna') DEFAULT 'privatna',
  `created_at` timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE IF NOT EXISTS `zbirka_stavke` (
  `id`         int(11)             NOT NULL AUTO_INCREMENT,
  `zbirka_id`  int(11)             NOT NULL,
  `tmdb_id`    int(11)             NOT NULL,
  `media_type` enum('movie','tv')  NOT NULL,
  `dodano_at`  timestamp           NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `zbirka_id` (`zbirka_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
ALTER TABLE `banovani_korisnici`
  ADD CONSTRAINT `banovani_korisnici_ibfk_1`
  FOREIGN KEY (`korisnik_id`) REFERENCES `korisnici` (`id`) ON DELETE CASCADE;

ALTER TABLE `zbirka_stavke`
  ADD CONSTRAINT `zbirka_stavke_ibfk_1`
  FOREIGN KEY (`zbirka_id`) REFERENCES `zbirke` (`id`) ON DELETE CASCADE;
INSERT IGNORE INTO `postavke_sistema` (`kljuc`, `vrijednost`) VALUES
('hero_index', '0');
INSERT IGNORE INTO `korisnici`
  (`id`, `ime`, `prezime`, `email`, `datum_rodenja`, `username`, `password`, `role`, `bio`, `profilna_slika`, `status`)
VALUES
  (1, 'Admin', 'Administrator', 'admin@filmoteka.ba', '2000-01-01',
   'admin',
   '$2y$10$w6Ym75M5nN4mNfE630Hn/.Z/F3VfJ6W/R1X/oY6.h7UuE3HfeD/mO',
   'admin', '', 'guest.png', 'active');