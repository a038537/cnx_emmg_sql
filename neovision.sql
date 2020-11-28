-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 28. Nov 2020 um 20:00
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
  `ppua` varchar(8) NOT NULL,
  `chid` varchar(4) NOT NULL,
  `start-date` date NOT NULL,
  `stop-date` date NOT NULL,
  `access` varchar(8) NOT NULL,
  `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `abo`
--

INSERT INTO `abo` (`id`, `ppua`, `chid`, `start-date`, `stop-date`, `access`, `changed`) VALUES
(1, '80000000', '1010', '2020-11-01', '2021-01-31', '1FFFFFFF', '2020-11-28 18:29:13'),
(2, '80000001', '1010', '2020-11-01', '2020-12-31', '000000AA', '2020-11-28 18:28:15'),
(3, '80000000', '101f', '2020-11-01', '2021-01-31', '1FFFFFFF', '2020-11-28 18:29:24'),
(4, '80000001', '101f', '2020-11-01', '2021-01-31', '000000AA', '2020-11-28 18:27:52');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cards`
--

CREATE TABLE `cards` (
  `ppua` varchar(8) NOT NULL,
  `ppsa` varchar(6) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `surname` varchar(64) NOT NULL,
  `given_name` varchar(64) NOT NULL,
  `address` varchar(64) NOT NULL,
  `city` varchar(64) NOT NULL,
  `zip` varchar(6) NOT NULL,
  `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `cards`
--

INSERT INTO `cards` (`ppua`, `ppsa`, `deleted`, `surname`, `given_name`, `address`, `city`, `zip`, `changed`) VALUES
('80000000', '999999', 0, 'Trump', 'Donald', 'White House 1', 'Washington DC', '123456', '2020-11-28 18:57:59'),
('80000001', '990000', 0, 'Merkel', 'Angela', 'Bundestag', 'Berlin', '10627', '2020-11-28 18:58:23'),
('80000002', '990000', 0, 'Erdogan', 'Recep', 'Talallee', 'Istanbul', '654321', '2020-11-28 19:00:03');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ecmg_keys`
--

CREATE TABLE `ecmg_keys` (
  `id` int(2) NOT NULL,
  `ecmkey` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `ecmg_keys`
--

INSERT INTO `ecmg_keys` (`id`, `ecmkey`) VALUES
(20, 'BBAA998877665544FFEEDDCC33221100'),
(21, '77665544BBAA998833221100FFEEDDCC');

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
('100A', 'TESLA-2'),
('1010', 'NeoVision'),
('101F', 'NeoVision-PPV');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `abo`
--
ALTER TABLE `abo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ID` (`id`);

--
-- Indizes für die Tabelle `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`ppua`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
