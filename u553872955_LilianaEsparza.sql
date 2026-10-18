
-- EE 470 Database Assignment - Part 1
-- Student: Liliana Esparza
-- Fall 2026
--
-- This SQL script contains my database tables
-- for the IoT database assignment.
--
-- Book: Stores book names and IDs.
-- Price: Stores the prices for each book.
-- sensor_register: Stores registered sensor
-- names, manufacturers, and locations.
-- sensor_data: Stores temperature and humidity
-- readings with their timestamps.
-- sensor_combined: Combines sensor registration
-- information with sensor readings.
--
-- The foreign key prevents unregistered sensors
-- from inserting data into sensor_data.

-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 18, 2026 at 10:06 PM
-- Server version: 11.8.9-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u553872955_LilianaEsparza`
--

-- --------------------------------------------------------

--
-- Table structure for table `Book`
--

CREATE TABLE `Book` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Book`
--

INSERT INTO `Book` (`id`, `name`) VALUES
(1, 'MariaDB Book'),
(2, 'SQLDB Book');

-- --------------------------------------------------------

--
-- Table structure for table `Price`
--

CREATE TABLE `Price` (
  `id` int(11) NOT NULL,
  `price` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Price`
--

INSERT INTO `Price` (`id`, `price`) VALUES
(1, 250),
(2, 225);

-- --------------------------------------------------------

--
-- Stand-in structure for view `sensor_combined`
-- (See below for the actual view)
--
CREATE TABLE `sensor_combined` (
`node_name` varchar(10)
,`manufacturer` varchar(10)
,`longitude` decimal(15,10)
,`latitude` decimal(15,10)
,`time_received` datetime
,`temperature` decimal(6,2)
,`humidity` decimal(6,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `sensor_data`
--

CREATE TABLE `sensor_data` (
  `node_name` varchar(10) NOT NULL,
  `time_received` datetime NOT NULL,
  `temperature` decimal(6,2) NOT NULL,
  `humidity` decimal(6,2) NOT NULL
) ;

--
-- Dumping data for table `sensor_data`
--

INSERT INTO `sensor_data` (`node_name`, `time_received`, `temperature`, `humidity`) VALUES
('node_1', '2022-10-01 11:00:00', 22.50, 45.00),
('node_1', '2022-10-01 11:30:00', 23.00, 47.00),
('node_1', '2022-10-01 12:00:00', 23.50, 48.00),
('node_1', '2022-10-01 12:30:00', 24.00, 50.00),
('node_2', '2022-10-01 11:00:00', 25.00, 55.00),
('node_2', '2022-10-01 11:30:00', 25.50, 56.00),
('node_2', '2022-10-01 12:00:00', 26.00, 58.00),
('node_2', '2022-10-01 12:30:00', 26.50, 60.00),
('node_3', '2022-10-01 11:00:00', 20.00, 40.00),
('node_3', '2022-10-01 11:30:00', 20.50, 42.00),
('node_3', '2022-10-01 12:00:00', 21.00, 43.00),
('node_3', '2022-10-01 12:30:00', 21.50, 45.00),
('node_4', '2022-10-01 11:00:00', 28.00, 65.00),
('node_4', '2022-10-01 11:30:00', 28.50, 66.00),
('node_4', '2022-10-01 12:00:00', 29.00, 68.00),
('node_4', '2022-10-01 12:30:00', 29.50, 70.00),
('node_5', '2022-10-01 11:00:00', 18.00, 35.00),
('node_5', '2022-10-01 11:30:00', 18.50, 37.00),
('node_5', '2022-10-01 12:00:00', 19.00, 38.00),
('node_5', '2022-10-01 12:30:00', 19.50, 40.00);

-- --------------------------------------------------------

--
-- Table structure for table `sensor_register`
--

CREATE TABLE `sensor_register` (
  `node_name` varchar(10) NOT NULL,
  `manufacturer` varchar(10) NOT NULL,
  `longitude` decimal(15,10) DEFAULT NULL,
  `latitude` decimal(15,10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sensor_register`
--

INSERT INTO `sensor_register` (`node_name`, `manufacturer`, `longitude`, `latitude`) VALUES
('node_1', 'Bosch', -122.4580000000, 38.3396000000),
('node_2', 'Honeywell', -122.4590000000, 38.3400000000),
('node_3', 'Siemens', -122.4600000000, 38.3410000000),
('node_4', 'DHT', -122.4610000000, 38.3420000000),
('node_5', 'Sensirion', -122.4620000000, 38.3430000000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Book`
--
ALTER TABLE `Book`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Price`
--
ALTER TABLE `Price`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sensor_data`
--
ALTER TABLE `sensor_data`
  ADD PRIMARY KEY (`node_name`,`time_received`);

--
-- Indexes for table `sensor_register`
--
ALTER TABLE `sensor_register`
  ADD PRIMARY KEY (`node_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Book`
--
ALTER TABLE `Book`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Price`
--
ALTER TABLE `Price`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

-- --------------------------------------------------------

--
-- Structure for view `sensor_combined`
--
DROP TABLE IF EXISTS `sensor_combined`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u553872955_db_LilianaEsp`@`127.0.0.1` SQL SECURITY DEFINER VIEW `sensor_combined`  AS SELECT `d`.`node_name` AS `node_name`, `r`.`manufacturer` AS `manufacturer`, `r`.`longitude` AS `longitude`, `r`.`latitude` AS `latitude`, `d`.`time_received` AS `time_received`, `d`.`temperature` AS `temperature`, `d`.`humidity` AS `humidity` FROM (`sensor_data` `d` join `sensor_register` `r` on(`d`.`node_name` = `r`.`node_name`)) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sensor_data`
--
ALTER TABLE `sensor_data`
  ADD CONSTRAINT `sensor_data_ibfk_1` FOREIGN KEY (`node_name`) REFERENCES `sensor_register` (`node_name`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
