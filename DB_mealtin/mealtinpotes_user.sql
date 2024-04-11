-- MySQL dump 10.13  Distrib 8.0.34, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: mealtinpotes
-- ------------------------------------------------------
-- Server version	8.0.35

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (33,'azerty@azerty.com','[]','$2y$13$x294wHLOeTRfW4QslEGspOaGJvVwgr1MQpvX7RSRSJ4JK5ItEUbyy',0,'Azerty','Qwerty','0689905062'),(34,'dphiane@gmail.com','[]','$2y$13$MVqywpFbIe6RAaWwCqg7XOn7lSHxANqAU2l1/MGCrOPgmr2eXfegG',0,'dom','phiane','0760423143'),(35,'dphiane@yahoo.fr','[]','$2y$13$wtuzgeupwJUTTF/XF9ttHehwAzZpWqZdrGo7xLcnnTWPmKlNF90OK',0,'Azerty','Qwerty','0689905062'),(36,'phiane@gmail.com','[]','$2y$13$5CcrO1SZBOYWN/ICgCM.du6mo7QDqg83XM6RBWMkGnf0u2gq05o72',0,'David','Phiane','0689905061'),(41,'seiya@gmail.com','[]','$2y$13$rt/PLf3gspKgoSi8gfXEY.Ydrm4/b4Uiyi0FzmjDC8EsEC0NQj/ze',1,'seiya','phiane','0689905062'),(42,'azerty@azer','[]','$2y$13$lO0tqKQ46YHb1SKxaKf34eyHUOBK.aWY.lFnAJ7BetOh4YbMZIIke',0,'dom','ph','0689905061'),(47,'bernard.normand@live.com','[]','k?r1K;JE]',0,'Jeanne','Foucher','09 85 90 70 69'),(48,'marc.maillard@riou.fr','[]','p)8PY?>^0(LXT(',0,'Gérard','Seguin','0513975810'),(49,'jeanne.adam@roche.com','[]','~?0I,uQoS',0,'Joseph','Hamel','+33 2 63 35 43 36'),(50,'maryse.mahe@martin.net','[]','_\'e@u;PT&^9W6?Ll^{',0,'Victor','Duval','+33 (0)1 22 27 10 89'),(51,'vincent29@noos.fr','[]','l2rYdy]D+Om',0,'Gabriel','Hoarau','0444682450'),(52,'leclerc.eugene@wanadoo.fr','[]','SlPUz8',0,'Camille','Rolland','+33 7 94 08 82 15'),(53,'suzanne.guyon@laposte.net','[]','()2xj(C$i(@pIyp0aL)',0,'Jeannine','Blanchet','0497965852'),(54,'michel01@guillaume.net','[]','6!-/58b',0,'Thierry','Salmon','0769465382'),(55,'yroyer@gmail.com','[]','8E<xx(H',0,'Alix','Philippe','+33 5 55 58 85 13'),(56,'gaudin.xavier@thibault.fr','[]','V)(_8Q',0,'Lucie','Antoine','+33 2 62 25 59 14'),(57,'xnoel@francois.fr','[]','@A[b#Il',0,'Patricia','Daniel','+33 (0)9 48 26 28 70'),(58,'audrey.lesage@dubois.com','[]','Ne;:OOOG4I_~',0,'François','Aubert','0222758861'),(59,'kgarcia@yahoo.fr','[]','O,BB27B%9',0,'Frédérique','Alves','08 93 55 97 15'),(60,'yroy@pereira.com','[]','HBU:}\"Mcou.tt',0,'Victor','Levy','+33 9 33 42 21 32'),(61,'maillot.adelaide@tele2.fr','[]','4R?ycSjOB[OSl^',0,'Olivier','Gomez','0283007169'),(62,'alfred.daniel@laposte.net','[]','Axqv7MBt*&6F9\"',0,'Colette','Philippe','+33 1 07 07 61 85'),(63,'gerard02@gmail.com','[]','M5V#_$qD5I94',0,'René','Bernard','08 10 18 48 35'),(64,'garnier.yves@sauvage.net','[]','0^l(Ojt',0,'Mathilde','Jacques','+33 3 00 90 80 24'),(65,'stephane93@dbmail.com','[]','\'^>35H^\'',0,'Amélie','Martin','0819309163'),(66,'julien.victor@yahoo.fr','[]','E6VXn9',0,'Lucie','Guyon','+33 (0)5 46 96 01 45'),(67,'tdevaux@remy.fr','[]','wuJziu(1Z?GLUd',0,'Sophie','Godard','+33 4 66 74 25 03'),(68,'victor45@bigot.net','[]','W=z^Gh<f0RZsFAm>jzK',0,'Lorraine','Riou','0804050769'),(69,'mmartineau@gmail.com','[]',':GY<NfwEz1crS+s\'Q',0,'Léon','Laine','+33 4 04 56 49 85'),(70,'roland17@club-internet.fr','[]','ep|&TV|vJyMo',0,'Marcelle','Dupuy','04 89 86 53 86'),(71,'martin.sylvie@rocher.fr','[]','%5mFF@#JBDn|',0,'Camille','Gillet','0995386248'),(72,'garcia.margot@bonnet.com','[]','EdWT\".56hcHxaMKV.[G',0,'Tristan','Navarro','0912439563'),(73,'leblanc.cecile@yahoo.fr','[]','|$0k@6GLWzI)V3,2C(z9',0,'Véronique','Michaud','09 51 00 28 93'),(74,'vlopes@sfr.fr','[]','vP>Y%$',0,'Timothée','Imbert','+33 2 91 43 35 66'),(75,'audrey89@turpin.fr','[]','-V~XntahdE5H.8Y}x^{',0,'Adrienne','Blondel','0455346435'),(76,'imbert.capucine@noos.fr','[]','BejK:IvNL',0,'Denise','Baron','0138109269'),(77,'cclement@berthelot.com','[]','`46Xgk>*npI1',0,'Étienne','Royer','+33 (0)4 97 92 46 25'),(78,'jacques.nguyen@dbmail.com','[]','8z/Q+l{;zO!xwj)0JF',0,'Pauline','Roger','08 12 93 95 40'),(79,'francois.christophe@live.com','[]','\\d^x}o%gm9Nt',0,'Sabine','Ramos','+33 (0)5 24 06 49 12'),(80,'didier.xavier@yahoo.fr','[]','{RR+$Ti<',0,'André','Marechal','01 88 83 80 50'),(81,'umorel@henry.fr','[]',';CHQE/0?r',0,'Michel','Maillot','+33 (0)6 85 14 83 42'),(82,'roland95@club-internet.fr','[]','yCNY5e5\"l?%8z4IQ',0,'François','Dupuis','0909980333'),(83,'monique31@dbmail.com','[]','yK.FFR,;#!q',0,'Lucas','Martin','+33 1 05 88 70 00'),(84,'renaud.aime@live.com','[]','oRz1u)',0,'Audrey','Guyon','03 50 18 41 95'),(85,'nathalie.bourdon@hotmail.fr','[]','YM-T{rC\")G\\xA',0,'Eugène','Lambert','+33 1 34 77 33 26'),(86,'thomas42@live.com','[]','xvq~F7p~XBK5Y`a',0,'Joséphine','Martins','03 73 75 19 90'),(87,'lucas.traore@orange.fr','[]','VXC&yu\'`:&1`kEl',0,'Michel','Pages','+33 (0)1 51 23 25 53'),(88,'hamon.claude@sfr.fr','[]','NM1M~$?JLp\"68D]Qh',0,'Philippe','Regnier','+33 4 04 66 52 49'),(89,'mary.anouk@laroche.net','[]','Q,1?5]U<ui_9L[?Nj/)Q',0,'Charlotte','Couturier','0666450693'),(90,'roland.gaudin@gillet.fr','[]',';fsQQyY!%S',0,'Danielle','Perez','01 25 34 09 78'),(91,'thibault.rene@wanadoo.fr','[]','C`3+6_pSG6\'*|g>',0,'Andrée','Besnard','01 59 85 94 71'),(92,'oguichard@diaz.fr','[]','_/=0l%',0,'Marcelle','Pottier','+33 4 15 74 05 12'),(93,'poulain.lucy@hotmail.fr','[]','Z^<#txly\'%j|/31;z',0,'Susan','Marie','+33 (0)5 68 92 36 30'),(94,'vpinto@valentin.org','[]','U3?(%@0@_uJx$DQ4',0,'Susanne','Hernandez','03 15 10 29 43'),(95,'iramos@free.fr','[]','fhal_%Rlsn2<He8',0,'Marthe','Ribeiro','0613359135'),(96,'emilie91@guichard.com','[]','P_rC\'H@Bfk6O>m\'#',0,'Denis','Carre','0737511819'),(97,'chartier.genevieve@tele2.fr','[]','&dTn8%+S\\Da_<Ti\'`r',0,'Agathe','Blin','+33 (0)2 65 36 75 40'),(98,'thibault83@laposte.net','[]','fYgd1j_Z7n',0,'Rémy','Renaud','+33 (0)1 38 86 11 04'),(99,'fischer.zacharie@fontaine.org','[]','WLV5e6m+n0f[3>uD',0,'François','Chauveau','0990263213'),(100,'rleblanc@tele2.fr','[]','3H[]G{gX_T2;{o(iBC2V',0,'Matthieu','Parent','0898717965'),(101,'michelle87@orange.fr','[]','*M_7\\c5j)gTP>{u?:gu4',0,'Margaux','Chauvin','+33 (0)1 98 40 11 97'),(102,'david51@begue.fr','[]','ZM~+Zs5n\'[{1L',0,'Alphonse','Marion','+33 6 18 63 83 58'),(103,'lweber@hotmail.fr','[]','5]qy3rV./33Ov{1^HIdL',0,'François','Lucas','+33 1 15 90 80 35'),(104,'francoise59@imbert.org','[]','a1*F@5;\'vlRIgT',0,'Stéphanie','Chretien','0576751255'),(105,'stephane.hebert@laposte.net','[]','r;vP!d2o<y',0,'Marcel','Pages','+33 4 65 88 89 06'),(106,'bcolas@wanadoo.fr','[]','M9=MnGmy!w[4?jy#|Q.',0,'Rémy','Moreno','03 05 31 37 01'),(107,'legros.susan@texier.fr','[]','W?0f&wyJ-GDse',0,'Pauline','Rolland','+33 (0)3 81 47 81 57'),(108,'brun.guillaume@gmail.com','[]','(?WU@\\[*Q[C+k(E3G',0,'Georges','Lopez','0988505660'),(109,'laurent.sanchez@ollivier.com','[]','ps,fYiN65M7p',0,'Grégoire','Lefevre','+33 8 92 10 35 55'),(110,'nguyen.gabriel@potier.net','[]','>Rr5X,XOA%9xfb\"6Hw',0,'Thierry','Paris','+33 (0)9 02 75 41 46'),(111,'thibaut.jacquet@gmail.com','[]','rhnd@g3P\\2)RG1][',0,'Gabrielle','Chevallier','0474439364'),(112,'edith.bonneau@lacroix.fr','[]','`ew<_~fO{c;87N3gb*',0,'Dominique','Lacombe','+33 (0)1 70 90 99 33'),(113,'mpires@hotmail.fr','[]','Xe\'uu@)Xc:_OSpi9@~%l',0,'Virginie','Coulon','01 96 28 74 36'),(114,'christine94@devaux.net','[]','ej<\'@Jo}R',0,'Corinne','Leclercq','+33 (0)8 01 83 08 65'),(115,'vincent.olivier@yahoo.fr','[]','JdlX%h9,KQ~SgZ_-~',0,'Audrey','Gaillard','0789018915'),(116,'xavier47@gmail.com','[]','}heG}bJ\"~N<6_N7|V',0,'Laurence','Barthelemy','+33 (0)1 63 94 28 01');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-04-11 13:02:48
