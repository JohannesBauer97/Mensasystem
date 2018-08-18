-- phpMyAdmin SQL Dump
-- version 4.6.6
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 18, 2018 at 12:51 PM
-- Server version: 5.5.60-0+deb8u1
-- PHP Version: 7.0.31-1~dotdeb+8.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mensasystem`
--
CREATE DATABASE IF NOT EXISTS `mensasystem` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `mensasystem`;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_artikel`
--

CREATE TABLE `tbl_artikel` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_bin NOT NULL,
  `description` text COLLATE utf8_bin,
  `price` double DEFAULT NULL,
  `dauerhaftesAngebot` tinyint(4) NOT NULL DEFAULT '0',
  `active` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_artikel_likes`
--

CREATE TABLE `tbl_artikel_likes` (
  `id` int(11) NOT NULL,
  `artikel` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_dauerhaftAngebot`
--

CREATE TABLE `tbl_dauerhaftAngebot` (
  `id` int(11) NOT NULL,
  `artikel` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_motd`
--

CREATE TABLE `tbl_motd` (
  `id` int(11) NOT NULL,
  `msg` text COLLATE utf8_bin,
  `active` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_speiseplan`
--

CREATE TABLE `tbl_speiseplan` (
  `id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `artikel` int(11) NOT NULL,
  `active` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_umsatz`
--

CREATE TABLE `tbl_umsatz` (
  `id` int(11) NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user` int(11) DEFAULT NULL,
  `umsatzArt` int(11) DEFAULT NULL,
  `artikel` int(11) DEFAULT NULL,
  `price` double DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `speiseplan` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_umsatzArt`
--

CREATE TABLE `tbl_umsatzArt` (
  `id` int(11) NOT NULL,
  `title` varchar(45) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id` int(11) NOT NULL,
  `login` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vorname` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nachname` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rolle` int(11) DEFAULT NULL,
  `passwort` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `kontostand` double DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_userrolle`
--

CREATE TABLE `tbl_userrolle` (
  `id` int(11) NOT NULL,
  `name` varchar(45) COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `tbl_userrolle`
--

INSERT INTO `tbl_userrolle` (`id`, `name`) VALUES
(1, 'Caterer'),
(2, 'Kunde');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_artikel`
--
ALTER TABLE `tbl_artikel`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_artikel_likes`
--
ALTER TABLE `tbl_artikel_likes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `artikellikes_idx` (`artikel`),
  ADD KEY `userlikes_idx` (`user`);

--
-- Indexes for table `tbl_dauerhaftAngebot`
--
ALTER TABLE `tbl_dauerhaftAngebot`
  ADD PRIMARY KEY (`id`),
  ADD KEY `artikeldauerhaft_idx` (`artikel`);

--
-- Indexes for table `tbl_motd`
--
ALTER TABLE `tbl_motd`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_speiseplan`
--
ALTER TABLE `tbl_speiseplan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `artikel_idx` (`artikel`);

--
-- Indexes for table `tbl_umsatz`
--
ALTER TABLE `tbl_umsatz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_idx` (`user`),
  ADD KEY `artikel_idx` (`artikel`),
  ADD KEY `umsatzArt_idx` (`umsatzArt`),
  ADD KEY `speiseplan111_idx` (`speiseplan`);

--
-- Indexes for table `tbl_umsatzArt`
--
ALTER TABLE `tbl_umsatzArt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD KEY `userrolle_idx` (`rolle`);

--
-- Indexes for table `tbl_userrolle`
--
ALTER TABLE `tbl_userrolle`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_artikel`
--
ALTER TABLE `tbl_artikel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `tbl_artikel_likes`
--
ALTER TABLE `tbl_artikel_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
--
-- AUTO_INCREMENT for table `tbl_dauerhaftAngebot`
--
ALTER TABLE `tbl_dauerhaftAngebot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `tbl_motd`
--
ALTER TABLE `tbl_motd`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `tbl_speiseplan`
--
ALTER TABLE `tbl_speiseplan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;
--
-- AUTO_INCREMENT for table `tbl_umsatz`
--
ALTER TABLE `tbl_umsatz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;
--
-- AUTO_INCREMENT for table `tbl_umsatzArt`
--
ALTER TABLE `tbl_umsatzArt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;
--
-- AUTO_INCREMENT for table `tbl_userrolle`
--
ALTER TABLE `tbl_userrolle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_artikel_likes`
--
ALTER TABLE `tbl_artikel_likes`
  ADD CONSTRAINT `artikellikes` FOREIGN KEY (`artikel`) REFERENCES `tbl_artikel` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `userlikes` FOREIGN KEY (`user`) REFERENCES `tbl_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `tbl_dauerhaftAngebot`
--
ALTER TABLE `tbl_dauerhaftAngebot`
  ADD CONSTRAINT `artikeldauerhaft` FOREIGN KEY (`artikel`) REFERENCES `tbl_artikel` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `tbl_speiseplan`
--
ALTER TABLE `tbl_speiseplan`
  ADD CONSTRAINT `artikel` FOREIGN KEY (`artikel`) REFERENCES `tbl_artikel` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `tbl_umsatz`
--
ALTER TABLE `tbl_umsatz`
  ADD CONSTRAINT `artikel1` FOREIGN KEY (`artikel`) REFERENCES `tbl_artikel` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `speiseplan111` FOREIGN KEY (`speiseplan`) REFERENCES `tbl_speiseplan` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `umsatzArt` FOREIGN KEY (`umsatzArt`) REFERENCES `tbl_umsatzArt` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `user` FOREIGN KEY (`user`) REFERENCES `tbl_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD CONSTRAINT `userrolle` FOREIGN KEY (`rolle`) REFERENCES `tbl_userrolle` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
