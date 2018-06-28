-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 28, 2018 at 08:26 PM
-- Server version: 5.7.22-0ubuntu0.16.04.1
-- PHP Version: 7.0.30-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `s255320`
--

-- --------------------------------------------------------

--
-- Table structure for table `Reservations`
--

CREATE TABLE `Reservations` (
  `user` varchar(64) NOT NULL,
  `seats` int(11) NOT NULL,
  `start` varchar(255) NOT NULL,
  `end` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Reservations`
--

INSERT INTO `Reservations` (`user`, `seats`, `start`, `end`) VALUES
('u1@p.it', 4, 'ff', 'kk'),
('u2@p.it', 1, 'bb', 'ee'),
('u3@p.it', 1, 'dd', 'ee'),
('u4@p.it', 1, 'al', 'dd');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `user` varchar(50) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `timestamp` int(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`user`, `pass`, `token`, `timestamp`) VALUES
('u1@p.it', '$2y$10$TRqatl9O4xizhSVgv7yxxerZhOnsOoupB/bHR3tiHQPWHDYftKpBu', NULL, NULL),
('u2@p.it', '$2y$10$BGO83gVKtFWVpm4yNAlWt.A8z5rRzJJ26YwW4CbgJAT60IRPDihj6', NULL, NULL),
('u3@p.it', '$2y$10$f6OxbH6tGRmreWopcCxwieEh8HUrUW0Yb4rZiE80MEufI8pm2D4h2', NULL, NULL),
('u4@p.it', '$2y$10$gFRtWMx.DBnhviYk/2o58Oxxs/BjV4y2sLKn/DtplZsXidvYLp5ja', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Reservations`
--
ALTER TABLE `Reservations`
  ADD PRIMARY KEY (`user`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`user`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
