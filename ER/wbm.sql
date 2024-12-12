-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Dic 09, 2024 alle 11:29
-- Versione del server: 8.0.26
-- Versione PHP: 8.0.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `my_rotathomas`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `WBM_album`
--

CREATE TABLE `WBM_album` (
  `id` int NOT NULL,
  `titolo` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `anno` int DEFAULT NULL,
  `immagine` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'default.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `WBM_artista`
--

CREATE TABLE `WBM_artista` (
  `id` int NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `immagine` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'default.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `WBM_artista_album`
--

CREATE TABLE `WBM_artista_album` (
  `id_artista` int NOT NULL,
  `id_album` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `WBM_brano`
--

CREATE TABLE `WBM_brano` (
  `id` int NOT NULL,
  `titolo` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `id_album` int NOT NULL,
  `mp3` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `WBM_utente`
--

CREATE TABLE `WBM_utente` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `immagine` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'default.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `WBM_utente_brani`
--

CREATE TABLE `WBM_utente_brani` (
  `id_utente` int NOT NULL,
  `id_brano` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `WBM_album`
--
ALTER TABLE `WBM_album`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `WBM_artista`
--
ALTER TABLE `WBM_artista`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `WBM_artista_album`
--
ALTER TABLE `WBM_artista_album`
  ADD PRIMARY KEY (`id_artista`,`id_album`),
  ADD KEY `id_album` (`id_album`);

--
-- Indici per le tabelle `WBM_brano`
--
ALTER TABLE `WBM_brano`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_album` (`id_album`);

--
-- Indici per le tabelle `WBM_utente`
--
ALTER TABLE `WBM_utente`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `WBM_utente_brani`
--
ALTER TABLE `WBM_utente_brani`
  ADD PRIMARY KEY (`id_utente`,`id_brano`),
  ADD KEY `id_brano` (`id_brano`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `WBM_album`
--
ALTER TABLE `WBM_album`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `WBM_artista`
--
ALTER TABLE `WBM_artista`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `WBM_brano`
--
ALTER TABLE `WBM_brano`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `WBM_utente`
--
ALTER TABLE `WBM_utente`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `WBM_artista_album`
--
ALTER TABLE `WBM_artista_album`
  ADD CONSTRAINT `WBM_artista_album_ibfk_1` FOREIGN KEY (`id_artista`) REFERENCES `WBM_artista` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `WBM_artista_album_ibfk_2` FOREIGN KEY (`id_album`) REFERENCES `WBM_album` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `WBM_brano`
--
ALTER TABLE `WBM_brano`
  ADD CONSTRAINT `WBM_brano_ibfk_1` FOREIGN KEY (`id_album`) REFERENCES `WBM_album` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `WBM_utente_brani`
--
ALTER TABLE `WBM_utente_brani`
  ADD CONSTRAINT `WBM_utente_brani_ibfk_1` FOREIGN KEY (`id_utente`) REFERENCES `WBM_utente` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `WBM_utente_brani_ibfk_2` FOREIGN KEY (`id_brano`) REFERENCES `WBM_brano` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
