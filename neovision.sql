-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 03. Dez 2020 um 16:05
-- Server-Version: 5.7.31-0ubuntu0.18.04.1
-- PHP-Version: 7.2.24-0ubuntu0.18.04.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `neovision`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `abo`
--

CREATE TABLE `abo` (
  `id` int(11) NOT NULL,
  `ppua` int(8) NOT NULL,
  `chid` varchar(4) NOT NULL,
  `acc` varchar(8) NOT NULL,
  `bos` date DEFAULT NULL,
  `eos` date DEFAULT NULL,
  `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `abo`
--

INSERT INTO `abo` (`id`, `ppua`, `chid`, `acc`, `bos`, `eos`, `changed`) VALUES
(1, 80000000, '1010', '1FFFFFFF', '2020-12-01', '2020-12-31', '2020-12-03 08:35:51');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cards`
--

CREATE TABLE `cards` (
  `ppua` int(8) NOT NULL,
  `ppsa` varchar(6) NOT NULL DEFAULT '990000',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `cust` int(8) DEFAULT NULL,
  `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `cards`
--

INSERT INTO `cards` (`ppua`, `ppsa`, `deleted`, `cust`, `changed`) VALUES
(80000000, '999999', 0, 1, '2020-12-03 12:39:05');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `firstname` varchar(32) DEFAULT NULL,
  `lastname` varchar(32) DEFAULT NULL,
  `street` varchar(32) DEFAULT NULL,
  `city` varchar(32) DEFAULT NULL,
  `zipcode` varchar(8) DEFAULT NULL,
  `state` varchar(32) DEFAULT NULL,
  `email` varchar(32) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `temp` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `customers`
--

INSERT INTO `customers` (`id`, `firstname`, `lastname`, `street`, `city`, `zipcode`, `state`, `email`, `phone`, `changed`, `temp`) VALUES
(1, 'Tony', 'Stark', 'Stark-Tower', 'NYC', '12345', 'USA', 'Tony.Stark@starkindustries.com', '', '2020-12-03 13:11:56', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ecmg_keys`
--

CREATE TABLE `ecmg_keys` (
  `id` int(2) NOT NULL,
  `ecmkey` varchar(32) NOT NULL,
  `modified` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `ecmg_keys`
--

INSERT INTO `ecmg_keys` (`id`, `ecmkey`, `modified`) VALUES
(20, 'BBAA998877665544FFEEDDCC33221100', '2020-12-01'),
(21, '0740eaf965328e4d6b38154e0ce0bad3', '2021-01-01');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `emmg_systemkey`
--

CREATE TABLE `emmg_systemkey` (
  `id` int(11) NOT NULL,
  `systemkey` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `emmg_systemkey`
--

INSERT INTO `emmg_systemkey` (`id`, `systemkey`) VALUES
(1, '10111213141516171819202122232425');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `providers`
--

CREATE TABLE `providers` (
  `chid` varchar(4) NOT NULL,
  `providername` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `providers`
--

INSERT INTO `providers` (`chid`, `providername`) VALUES
('1010', 'NeoVision');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `abo`
--
ALTER TABLE `abo`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`ppua`);

--
-- Indizes für die Tabelle `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `ecmg_keys`
--
ALTER TABLE `ecmg_keys`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `emmg_systemkey`
--
ALTER TABLE `emmg_systemkey`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `providers`
--
ALTER TABLE `providers`
  ADD PRIMARY KEY (`chid`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `abo`
--
ALTER TABLE `abo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT für Tabelle `cards`
--
ALTER TABLE `cards`
  MODIFY `ppua` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80000001;
--
-- AUTO_INCREMENT für Tabelle `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
