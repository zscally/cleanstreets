-- MySQL dump 10.13  Distrib 5.7.18, for Linux (x86_64)
--
-- Host: localhost    Database: sss
-- ------------------------------------------------------
-- Server version	5.7.18-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `AlertNotification`
--

DROP TABLE IF EXISTS `AlertNotification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AlertNotification` (
  `AlertID` int(11) NOT NULL AUTO_INCREMENT,
  `AlertTypeID` int(11) NOT NULL,
  `PickupAreaID` varchar(255) NOT NULL,
  `NotificationTypeID` int(11) NOT NULL,
  `AlertX` varchar(45) NOT NULL,
  `AlertY` varchar(45) NOT NULL,
  `AlertAddressID` int(11) NOT NULL,
  `AlertAddress` varchar(255) NOT NULL,
  `license_id` varchar(255) DEFAULT NULL,
  `council_district` varchar(255) DEFAULT NULL,
  `NotificationValue` varchar(255) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `NumberMissedNotifications` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `DateUpdated` datetime NOT NULL,
  `AlertDisableReason` varchar(255) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `lojic_arearoute` varchar(255) DEFAULT '0',
  PRIMARY KEY (`AlertID`),
  KEY `DateAdded` (`DateAdded`),
  KEY `NotificationValue` (`NotificationValue`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AlertNotification`
--

LOCK TABLES `AlertNotification` WRITE;
/*!40000 ALTER TABLE `AlertNotification` DISABLE KEYS */;
/*!40000 ALTER TABLE `AlertNotification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AlertReason`
--

DROP TABLE IF EXISTS `AlertReason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AlertReason` (
  `AlertReasonID` int(11) NOT NULL,
  `AlertReasonDescription` varchar(255) NOT NULL,
  PRIMARY KEY (`AlertReasonID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AlertReason`
--

LOCK TABLES `AlertReason` WRITE;
/*!40000 ALTER TABLE `AlertReason` DISABLE KEYS */;
/*!40000 ALTER TABLE `AlertReason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `AlertType`
--

DROP TABLE IF EXISTS `AlertType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AlertType` (
  `AlertTypeID` int(11) NOT NULL,
  `AlertDescription` varchar(255) NOT NULL,
  PRIMARY KEY (`AlertTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `AlertType`
--

LOCK TABLES `AlertType` WRITE;
/*!40000 ALTER TABLE `AlertType` DISABLE KEYS */;
INSERT INTO `AlertType` VALUES (1,'Street Sweeping Alerts');
/*!40000 ALTER TABLE `AlertType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `NotificationType`
--

DROP TABLE IF EXISTS `NotificationType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NotificationType` (
  `idNotificationType` int(11) NOT NULL,
  `NotificationDescription` varchar(255) NOT NULL,
  PRIMARY KEY (`idNotificationType`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `NotificationType`
--

LOCK TABLES `NotificationType` WRITE;
/*!40000 ALTER TABLE `NotificationType` DISABLE KEYS */;
INSERT INTO `NotificationType` VALUES (1,'Email'),(2,'Phone');
/*!40000 ALTER TABLE `NotificationType` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `canned_comments`
--

DROP TABLE IF EXISTS `canned_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `canned_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `text` longtext,
  `date_created` datetime NOT NULL,
  `is_active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `canned_comments`
--

LOCK TABLES `canned_comments` WRITE;
/*!40000 ALTER TABLE `canned_comments` DISABLE KEYS */;
INSERT INTO `canned_comments` VALUES (1,'Void - Phone','Courtesy Void - Phone.','2017-02-22 10:13:10',1),(2,'Void - Online','Courtesy Void - Online.','2017-02-22 10:13:34',1),(3,'Void - Lobby','Courtesy Void - Lobby.','2017-02-22 10:15:27',1),(4,'Denied - Existing','Denied - Existing Subscriber.','2017-02-22 10:16:16',1),(5,'Denied - Signed up','Denied - Signed up after appeal rights.','2017-02-22 10:16:53',1),(6,'Gant','Ganter is awesome!','2017-02-22 11:40:38',1);
/*!40000 ALTER TABLE `canned_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_by` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `comment` longtext NOT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `is_active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (1,1,3,'test','2017-05-09 09:01:57','2017-05-09 09:01:57',1),(2,1,3,'test','2017-05-09 09:02:04','2017-05-09 09:02:04',1);
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gov_delivery_subscribers`
--

DROP TABLE IF EXISTS `gov_delivery_subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gov_delivery_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `area_route` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `origin` varchar(255) NOT NULL,
  `created` varchar(255) NOT NULL,
  `found` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gov_delivery_subscribers`
--

LOCK TABLES `gov_delivery_subscribers` WRITE;
/*!40000 ALTER TABLE `gov_delivery_subscribers` DISABLE KEYS */;
/*!40000 ALTER TABLE `gov_delivery_subscribers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_queue`
--

DROP TABLE IF EXISTS `message_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_by` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `type` varchar(30) DEFAULT NULL,
  `area` varchar(2) NOT NULL,
  `route` varchar(2) NOT NULL,
  `cleaning_date` datetime NOT NULL,
  `send_date` datetime NOT NULL,
  `date_finished` datetime DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `message_queue_id_uindex` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_queue`
--

LOCK TABLES `message_queue` WRITE;
/*!40000 ALTER TABLE `message_queue` DISABLE KEYS */;
INSERT INTO `message_queue` VALUES (1,1,'Deleted','Day','01','06','2017-05-12 00:00:00','2017-05-12 15:00:00','2017-05-11 11:53:14','2017-05-10 10:48:09'),(2,1,'Deleted','Day','01','06','2017-05-11 00:00:00','2017-05-11 15:00:00','2017-05-11 11:53:16','2017-05-10 10:52:00'),(3,1,'Deleted','Week','01','06','2017-05-10 00:00:00','2017-05-10 15:00:00','2017-05-10 10:53:30','2017-05-10 10:53:23'),(4,1,'Sent','Day','01','06','2017-05-10 00:00:00','2017-05-10 15:00:00','2017-05-10 16:40:55','2017-05-10 11:01:47'),(5,1,'Sent','Day','01','06','2017-05-10 00:00:00','2017-05-10 15:00:00','2017-05-10 16:40:57','2017-05-10 03:52:14'),(6,1,'Sent','Week','01','06','2017-05-17 00:00:00','2017-05-10 15:00:00','2017-05-10 16:40:59','2017-05-10 03:52:56');
/*!40000 ALTER TABLE `message_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_types`
--

DROP TABLE IF EXISTS `notification_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_types_id_uindex` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_types`
--

LOCK TABLES `notification_types` WRITE;
/*!40000 ALTER TABLE `notification_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriber_routes`
--

DROP TABLE IF EXISTS `subscriber_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriber_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscriber_id` int(11) NOT NULL,
  `area` varchar(2) NOT NULL,
  `route` varchar(2) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` datetime NOT NULL,
  `is_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscriber_routes_id_uindex` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriber_routes`
--

LOCK TABLES `subscriber_routes` WRITE;
/*!40000 ALTER TABLE `subscriber_routes` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscriber_routes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscribers`
--

DROP TABLE IF EXISTS `subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(30) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `email` varchar(42) NOT NULL,
  `phone` varchar(14) DEFAULT NULL,
  `phone_is_mobile` int(1) NOT NULL DEFAULT '0',
  `address` varchar(255) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `coordinate_x` varchar(50) NOT NULL,
  `coordinate_y` varchar(50) NOT NULL,
  `council_district` varchar(255) NOT NULL,
  `latitude` varchar(50) NOT NULL,
  `longitude` varchar(50) NOT NULL,
  `license_tag_id` varchar(20) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` datetime DEFAULT NULL,
  `is_active` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscribers_id_uindex` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscribers`
--

LOCK TABLES `subscribers` WRITE;
/*!40000 ALTER TABLE `subscribers` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscribers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` int(11) NOT NULL DEFAULT '2',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (8,'Admin','Admin','admin@cleanstreets.com','$2y$10$BVU2QzPuEDqzka4at39J2erwHXT/I/C1LkAqFXJgLER/XzogTlLta',1,'2017-05-15 11:19:13','2017-05-16 11:59:24',1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-05-16 12:12:30
