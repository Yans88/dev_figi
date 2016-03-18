-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 09, 2012 at 03:57 PM
-- Server version: 5.5.3-m3-community
-- PHP Version: 5.4.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `figi`
--

-- --------------------------------------------------------

--
-- Table structure for table `condemned_issue`
--

ALTER TABLE `condemned_issue` ADD `recommended_by` int(11) DEFAULT NULL;
ALTER TABLE `condemned_issue` ADD `recommendation_datetime` datetime DEFAULT NULL;
ALTER TABLE `condemned_issue` ADD `recommendation_remark` varchar(255) DEFAULT NULL;
ALTER TABLE `condemned_issue` MODIFY `issue_status` enum('PENDING','APPROVED','REJECTED','CONDEMNED','RECOMMENDED','DISPOSED') DEFAULT NULL; 


--
-- Table structure for table `condemned_signature`
--

ALTER TABLE `condemned_signature` ADD `recommendation_signature` mediumblob;

-- --------------------------------------------------------

--
-- Table structure for table `disposal_file`
--

CREATE TABLE IF NOT EXISTS `disposal_file` (
  `id_file` int(11) NOT NULL AUTO_INCREMENT,
  `id_issue` int(11) NOT NULL,
  `doctype` tinyint(4) NOT NULL DEFAULT '0',
  `filename` varchar(100) NOT NULL,
  `data` blob NOT NULL,
  PRIMARY KEY (`id_file`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `disposal_info`
--

CREATE TABLE IF NOT EXISTS `disposal_info` (
  `id_issue` int(11) NOT NULL,
  `disposal_method` tinyint(4) NOT NULL DEFAULT '0',
  `disposal_date` date DEFAULT NULL,
  `disposal_cost` varchar(16) DEFAULT NULL,
  `disposal_reference` varchar(16) DEFAULT NULL,
  `vendor_name` varchar(64) DEFAULT NULL,
  `vendor_address` varchar(255) DEFAULT NULL,
  `contact_person` varchar(64) DEFAULT NULL,
  `contact_number` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id_issue`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
