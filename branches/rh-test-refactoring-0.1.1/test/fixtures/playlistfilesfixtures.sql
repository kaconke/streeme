-- phpMyAdmin SQL Dump
-- version 3.2.2.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 30, 2010 at 04:49 AM
-- Server version: 5.1.37
-- PHP Version: 5.2.10-2ubuntu6.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `streemetest`
--

--
-- Dumping data for table `album`
--

INSERT INTO `album` (`id`, `scan_id`, `name`, `amazon_flagged`, `meta_flagged`, `folders_flagged`, `service_flagged`, `has_art`) VALUES
(1, 1, 'Gorillaz Compilation', 0, 0, 0, 0, 0),
(2, 1, 'með suð í eyrum við spilum endalaust', 0, 0, 0, 0, 0);

--
-- Dumping data for table `artist`
--

INSERT INTO `artist` (`id`, `name`) VALUES
(1, 'Sigur Ros'),
(2, 'Gorillaz');

--
-- Dumping data for table `scan`
--

INSERT INTO `scan` (`id`, `scan_time`, `scan_type`) VALUES
(1, '2010-08-08 12:03:20', 'library');

--
-- Dumping data for table `song`
--

INSERT INTO `song` (`id`, `unique_id`, `artist_id`, `album_id`, `genre_id`, `last_scan_id`, `name`, `length`, `accurate_length`, `filesize`, `bitrate`, `yearpublished`, `tracknumber`, `label`, `mtime`, `atime`, `filename`) VALUES
(1, '9qw9dwj9wqdjw9qjqw9jd', 1, 1, 0, 1, 'Submarine', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22303290923, NULL, 'file://localhost/home/Submarine.mp3'),
(2, 'dj9jd9wejd0ejf90ewjf', 1, 1, 0, 1, 'Felix', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 194909095, NULL, 'file://localhost/home/Felix.mp3'),
(3, 'dwq09dw9qdid9wi9qdiq', 2, 2, 0, 1, 'Yellow', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 191919109, NULL, 'file://localhost/home/Yellow.mp3'),
(4, '9ewf9ewjfa0jew90fejf9fje', 2, 2, 0, 1, 'Sinkhole', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1995095509, NULL, 'file://localhost/home/Sinkhole.mp3');