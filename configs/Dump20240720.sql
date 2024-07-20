-- MariaDB dump 10.19  Distrib 10.4.24-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: lou_geh_library
-- ------------------------------------------------------
-- Server version	10.4.24-MariaDB

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
-- Table structure for table `book_categories`
--

DROP TABLE IF EXISTS `book_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `book_categories` (
  `isbn` varchar(13) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`isbn`,`category_id`),
  KEY `fk_book_categories_category_id` (`category_id`),
  CONSTRAINT `fk_book_categories_category_id` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_book_categories_isbn` FOREIGN KEY (`isbn`) REFERENCES `books` (`isbn`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `book_categories`
--

LOCK TABLES `book_categories` WRITE;
/*!40000 ALTER TABLE `book_categories` DISABLE KEYS */;
INSERT INTO `book_categories` VALUES ('1',1),('10',1),('11',1),('12',1),('13',1),('14',1),('16',1),('18',1),('20',1),('21',1),('26',1),('3',1),('4',1),('5',1),('6',1),('7',1),('8',1),('9',1);
/*!40000 ALTER TABLE `book_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `books`
--

DROP TABLE IF EXISTS `books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `books` (
  `isbn` varchar(13) NOT NULL,
  `publication_year` year(4) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `number_of_pages` int(11) NOT NULL,
  `publisher_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`isbn`),
  UNIQUE KEY `unique_isbn` (`isbn`),
  KEY `publisher_id` (`publisher_id`),
  CONSTRAINT `books_ibfk_1` FOREIGN KEY (`publisher_id`) REFERENCES `publishers` (`publisher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `books`
--

LOCK TABLES `books` WRITE;
/*!40000 ALTER TABLE `books` DISABLE KEYS */;
INSERT INTO `books` VALUES ('1',2001,'lll','lll',123,1),('10',2005,'iji','jiji',100,1),('11',2001,'hi','auth2',200,1),('12',2001,'mm','o',120,1),('13',0000,'k','opo',100,1),('14',2001,'jo','lojk',120,1),('16',2001,'ji','iji',1,1),('18',2002,'dssd','as',1,1),('2',2008,'lplp','ss',5,1),('20',0000,'jij','jiji',1,1),('21',2001,'wqwq','qwe',200,1),('26',2002,'jkj','jkj',200,1),('29',2009,'jkjk','jkj',200,1),('3',2001,'jkjk','hj',454,1),('33',2001,'sample','kjkj',200,3),('3434',2001,'ffd','fdfd',2223,1),('4',2005,'esds','kol',123,1),('5',2005,'jkjk','huh',202,1),('6',2001,'okok','koko',200,1),('7',2002,'oko','njhn',200,1),('777',1977,'777','77',777,2),('8',2003,'oko','hg',200,1),('878',0000,'jhjh','nj',878,1),('9',2006,'hujh','jiji',62,1);
/*!40000 ALTER TABLE `books` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `borrows`
--

DROP TABLE IF EXISTS `borrows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `borrows` (
  `borrow_id` int(11) NOT NULL AUTO_INCREMENT,
  `reader_number` int(11) DEFAULT NULL,
  `copy_number` int(11) DEFAULT NULL,
  `isbn` varchar(13) DEFAULT NULL,
  `borrow_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  PRIMARY KEY (`borrow_id`),
  KEY `reader_number` (`reader_number`),
  KEY `copy_number` (`copy_number`),
  KEY `isbn` (`isbn`),
  CONSTRAINT `borrows_ibfk_1` FOREIGN KEY (`reader_number`) REFERENCES `readers` (`reader_number`),
  CONSTRAINT `borrows_ibfk_2` FOREIGN KEY (`copy_number`) REFERENCES `copies` (`copy_number`),
  CONSTRAINT `borrows_ibfk_3` FOREIGN KEY (`isbn`) REFERENCES `books` (`isbn`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `borrows`
--

LOCK TABLES `borrows` WRITE;
/*!40000 ALTER TABLE `borrows` DISABLE KEYS */;
INSERT INTO `borrows` VALUES (1,7,1,'1','2024-07-19','2024-07-19'),(2,7,2,'1','2024-07-18','2024-07-20'),(3,7,1,'1','2024-07-19','2024-07-19'),(4,7,1,'1','2024-07-19','2024-07-19'),(5,6,1,'1','2024-07-19','2024-07-19'),(6,6,1,'1','2024-07-19','2024-07-19'),(7,6,1,'1','2024-07-19','2024-07-19'),(8,6,1,'1','2024-07-19','2024-07-19'),(9,7,1,'1','2024-07-20',NULL);
/*!40000 ALTER TABLE `borrows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `parent_category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`category_id`),
  KEY `parent_category_id` (`parent_category_id`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'horror',1),(2,'thrill',NULL),(3,'as',NULL),(4,'as',NULL),(5,'saas',NULL),(6,'saas',NULL),(7,'action',1),(8,'action',1),(9,'anime',2),(10,'anime',2),(11,'mmm',5),(12,'eeee',1),(13,'gggg',1),(14,'gggg',1),(15,'dssd',3),(16,'qqq',2),(17,'romance',7),(18,'hala',NULL),(19,'mmmmm',NULL),(20,'assaa',NULL);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `copies`
--

DROP TABLE IF EXISTS `copies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `copies` (
  `copy_number` int(11) NOT NULL AUTO_INCREMENT,
  `isbn` varchar(13) DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  PRIMARY KEY (`copy_number`),
  KEY `isbn` (`isbn`),
  CONSTRAINT `copies_ibfk_1` FOREIGN KEY (`isbn`) REFERENCES `books` (`isbn`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `copies`
--

LOCK TABLES `copies` WRITE;
/*!40000 ALTER TABLE `copies` DISABLE KEYS */;
INSERT INTO `copies` VALUES (1,'1','gsc'),(2,'1','gsc');
/*!40000 ALTER TABLE `copies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `publishers`
--

DROP TABLE IF EXISTS `publishers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `publishers` (
  `publisher_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  PRIMARY KEY (`publisher_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `publishers`
--

LOCK TABLES `publishers` WRITE;
/*!40000 ALTER TABLE `publishers` DISABLE KEYS */;
INSERT INTO `publishers` VALUES (1,'pub1','gsc'),(2,'louvilla','gsc'),(3,'lablab','gsc'),(4,'plplplpl','lplplp'),(5,'fdfdfd','fdfd'),(6,'asas','assa'),(7,'ccccccccc','c'),(8,'ghgh','gfgf'),(9,'1111','11');
/*!40000 ALTER TABLE `publishers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `readers`
--

DROP TABLE IF EXISTS `readers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `readers` (
  `reader_number` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `family_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`reader_number`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `readers`
--

LOCK TABLES `readers` WRITE;
/*!40000 ALTER TABLE `readers` DISABLE KEYS */;
INSERT INTO `readers` VALUES (6,'usera','$2y$10$1/RiwR9oqdgjhcmDX4O3p.6m8OeM0AkjdUqNBGElXOTWFsbv37fkq','user','a','gsc','2001-03-11',1),(7,'userb','$2y$10$uoqXCcSmm.atu.UzUPEur.wKEujaLo43BvBYVu/rb0P4YgCnFw296','user','b','gsc','2001-03-11',0),(8,'userc','$2y$10$nkIWikoxJ3FKxVnonmQt8u11cHc8W9q.09z7lxraU945Xns2/aUHS','user','c','gsc','2001-03-11',0),(9,'userd','$2y$10$iw0sqjIltnygkpujAtfogOrc1Z2wzfRmKTCrKmRp3N2.cHgueQ..m','user','d','gsc','2001-03-11',0),(10,'userabc','$2y$10$Ul22xye8jsPttRzew5TYV.4bnpuoh.a0U3oFu/C1ygKZwj4X.yDWK','user','abc','gsc','2001-03-11',0),(11,'userab','$2y$10$ANcs0SGIDyeezXSkyxeqJuSXa51FNJLRqDiOvjLCGNXHQlvTuT3GC','user','abc','gsc','2001-03-11',0),(12,'userabcdef','$2y$10$6OanmMrCJ/PlEpj1XFu8a..RMBjSppeIyIO66ncQ4Dym7EjtmDVnq','user','a','gsc','2001-03-11',0),(13,'azzy','$2y$10$L.RGo1KlN3XltwPoz1JG9.fyQGAAAinZSMNqPZu/fjCrRRdsJCTTm','aklkl','lklk','gsc','2001-03-11',0),(14,'limpin','$2y$10$lJ7oU335Y6R0bug76rFTWeKffygAB47KOv5Rnz1ZRpZZqLkar48Qi','limpin','l','gsc','1200-03-11',0),(15,'axxx','$2y$10$43lfGKC9GmahFO83Jh6bLu2V4jx4tXKyy8yYjLwremKyQN2EKimoK','user','x','gsc','2001-03-11',0),(16,'userx','$2y$10$a1AAf1xJoGcPOL20R4hVku.4CxnOo0xfBTwPIhmXy1WY1xvpfwNFu','user','x','gsc','2001-03-11',0),(17,'userz','$2y$10$KaGf6hV0uJtmmJLj1JgcEO1OloXeNKmDf.0PQYaEzJ1McxmDkawoW','user','z','jkjk','2001-03-11',0),(18,'userm','$2y$10$VyR/bX8wZnItf1qKArh29uiXtX7GlIr84.W8hHxrl8hA.dIBo.PrS','user','m','jkjk','0000-00-00',0);
/*!40000 ALTER TABLE `readers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-07-20 12:11:25
