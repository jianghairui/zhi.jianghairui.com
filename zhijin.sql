/*
SQLyog Ultimate v12.09 (64 bit)
MySQL - 5.7.25-0ubuntu0.16.04.2 : Database - zhijin
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`zhijin` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `zhijin`;

/*Table structure for table `admin` */

DROP TABLE IF EXISTS `admin`;

CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `realname` varchar(30) DEFAULT NULL COMMENT '管理员姓名',
  `username` varchar(30) DEFAULT NULL COMMENT '账号',
  `gender` tinyint(4) DEFAULT NULL COMMENT '1男2女',
  `email` varchar(50) DEFAULT NULL,
  `tel` varchar(15) DEFAULT NULL COMMENT '手机号',
  `password` varchar(32) DEFAULT NULL COMMENT '密码',
  `create_time` int(10) unsigned DEFAULT NULL COMMENT '添加时间',
  `last_login_ip` varchar(20) DEFAULT '0.0.0.0',
  `last_login_time` int(10) unsigned DEFAULT NULL COMMENT '最后登录时间',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态0冻结1正常',
  `login_times` int(11) DEFAULT '0' COMMENT '登陆次数',
  `level` tinyint(4) DEFAULT '1' COMMENT '管理员等级',
  `desc` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

/*Data for the table `admin` */

insert  into `admin`(`id`,`realname`,`username`,`gender`,`email`,`tel`,`password`,`create_time`,`last_login_ip`,`last_login_time`,`status`,`login_times`,`level`,`desc`) values (1,'姜海蕤','jianghairui',1,'1873645345@qq.com','13102163019','32d707d6e29a579e36c4e8031b2018da',1540099895,'60.24.14.214',1555898464,1,108,1,'好人啊\n'),(2,'韩驰','hanchi',1,'','','182190c915daadda6d909f9691819e45',1540102491,'180.212.36.73',1544778022,0,0,1,''),(3,'荣建','rongjian',1,'','','182190c915daadda6d909f9691819e45',1540102510,'221.196.194.14',1540102759,0,0,1,''),(4,'测试用户','test',2,'','','182190c915daadda6d909f9691819e45',1540103141,'60.25.57.208',1543394548,0,0,1,''),(5,'李钟毓','lizhongyu',1,'326693610@qq.com','17631617680','48dc8c0909ae3afb132c13d4256910e1',1553735897,'218.68.108.150',1553738908,0,1,1,'');

/*Table structure for table `auth_group` */

DROP TABLE IF EXISTS `auth_group`;

CREATE TABLE `auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '',
  `desc` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `rules` varchar(1000) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `auth_group` */

/*Table structure for table `auth_group_access` */

DROP TABLE IF EXISTS `auth_group_access`;

CREATE TABLE `auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `auth_group_access` */

/*Table structure for table `auth_rule` */

DROP TABLE IF EXISTS `auth_rule`;

CREATE TABLE `auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(80) NOT NULL DEFAULT '',
  `title` char(30) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `condition` char(100) NOT NULL DEFAULT '',
  `pid` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `auth_rule` */

/*Table structure for table `device` */

DROP TABLE IF EXISTS `device`;

CREATE TABLE `device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL COMMENT '设备名',
  `device_num` varchar(15) DEFAULT NULL COMMENT '设备号',
  `total_price` decimal(10,2) DEFAULT '0.00' COMMENT '总销量',
  `unit_price` decimal(10,2) DEFAULT '1.00' COMMENT '单价',
  `gift_url` varchar(500) DEFAULT NULL COMMENT '纸巾接口地址',
  `qrcode` varchar(50) DEFAULT NULL COMMENT '原始二维码信息',
  `stock` int(11) DEFAULT '0' COMMENT '库存',
  `admin_id` int(11) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL COMMENT '通知邮箱',
  PRIMARY KEY (`id`),
  KEY `DEVICE_UNIQUE` (`device_num`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;

/*Data for the table `device` */

insert  into `device`(`id`,`name`,`device_num`,`total_price`,`unit_price`,`gift_url`,`qrcode`,`stock`,`admin_id`,`email`) values (1,'山海测试纸巾机','104803','0.06','0.01','http://zhi.jianghairui.com/api/pay/prepay?device_num=104803','6f3c26f687e70848ea2535aae63762d3',0,NULL,'670359193@qq.com'),(2,'南京图书馆','104804','0.00','2.00','http://zhi.jianghairui.com/api/pay/prepay?device_num=104804','cc6fd93add5fa6aa8f37aab2c0995783',0,NULL,'jhr@bwgsd.com'),(3,'南京图书馆','104802','0.00','2.00','http://zhi.jianghairui.com/api/pay/prepay?device_num=104802','4beae4b15e648d470f08eba830839867',0,NULL,'1873645345@qq.com'),(4,'江南水乡图书馆','104801','0.00','1.00','http://zhi.jianghairui.com/api/pay/prepay?device_num=104801','f12213a60451ceadfd1c3efa15ae9a4e',0,NULL,'boss@jianghairui.com'),(5,'广东省立中山图书馆','104800','0.00','1.00','http://zhi.jianghairui.com/api/pay/prepay?device_num=104800','dd81db1219217ed5aa6f8e64222c7533',0,NULL,'1873645345@qq.com'),(6,'吉林省博物院','103611','0.00','1.00','http://zhi.jianghairui.com/api/pay/prepay?device_num=103611','2d5ebaeaef417ec0d1c00289050f9a2a',0,NULL,'1873645345@qq.com'),(7,'北京鲁迅博物馆','103609','0.00','1.00','http://zhi.jianghairui.com/api/pay/prepay?device_num=103609','f3abb80f91dbc996924b8ea9c181d7e1',0,NULL,'1873645345@qq.com'),(8,'南京图书馆','103602','0.00','2.00','http://zhi.jianghairui.com/api/pay/prepay?device_num=103602','a90125963b290abc58ad3c49b98c1b08',0,NULL,'1873645345@qq.com'),(9,'荆州博物馆','103598','0.00','1.00','http://zhi.jianghairui.com/api/pay/prepay?device_num=103598','5269f82640fb5eb9c7b826237938c7cb',0,NULL,'1873645345@qq.com'),(10,'滨海新区博物馆','103595','0.00','1.00','http://zhi.jianghairui.com/api/pay/prepay?device_num=103595','f20d4a54f036aeebf2b3f5d001864cd4',0,NULL,'1873645345@qq.com'),(11,'山海测试纸巾机','106391','0.00','0.02','http://zhi.jianghairui.com/api/pay/prepay?device_num=106391','307aba8aae192c913482db1e58a39b38',106,NULL,'1873645345@qq.com');

/*Table structure for table `order` */

DROP TABLE IF EXISTS `order`;

CREATE TABLE `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pay_order_sn` varchar(25) DEFAULT NULL COMMENT '支付订单号',
  `device_num` varchar(20) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总价',
  `num` int(11) NOT NULL COMMENT '购买的数量',
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单价',
  `openid` char(35) DEFAULT NULL COMMENT 'openid',
  `trans_id` varchar(30) DEFAULT NULL COMMENT '微信端单号',
  `refund_apply` tinyint(4) DEFAULT '0' COMMENT '1申请退款2已退款',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未支付1支付成功',
  `create_time` int(11) DEFAULT NULL COMMENT '微信下单时间',
  `pay_time` int(11) DEFAULT NULL COMMENT '支付时间',
  `refund_time` int(11) DEFAULT NULL,
  `out_paper` tinyint(4) DEFAULT '0' COMMENT '1出纸成功2出纸失败',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='订单表';

/*Data for the table `order` */

insert  into `order`(`id`,`pay_order_sn`,`device_num`,`total_price`,`num`,`unit_price`,`openid`,`trans_id`,`refund_apply`,`status`,`create_time`,`pay_time`,`refund_time`,`out_paper`) values (1,'155585900285303100756','104803','0.02',2,'0.01','ozo1bwXGCpPQnHdtqtCZEhQOwDDU','4200000309201904219308247649',2,1,1555859002,1555859009,1555859139,0),(2,'155586064439697100544','104803','0.03',3,'0.01','ozo1bwXGCpPQnHdtqtCZEhQOwDDU','4200000311201904214750239738',0,1,1555860644,1555860650,NULL,0),(3,'155586342981785200700','104803','0.01',1,'0.01','ozo1bwXGCpPQnHdtqtCZEhQOwDDU','4200000311201904222943380003',2,1,1555863429,1555863436,1555900264,0),(4,'155590175243308600535','106391','1.00',1,'1.00','ozo1bwXGCpPQnHdtqtCZEhQOwDDU','4200000320201904229668571894',2,1,1555901752,1555901758,1555901905,0),(5,'155590177280770500846','106391','2.00',2,'1.00','ozo1bwXGCpPQnHdtqtCZEhQOwDDU','4200000315201904225608862283',2,1,1555901772,1555901779,1555901986,0),(6,'155590204062382400628','106391','0.04',2,'0.02','ozo1bwXGCpPQnHdtqtCZEhQOwDDU','4200000321201904225617995939',2,1,1555902040,1555902046,1555902059,0);

/*Table structure for table `pay_error` */

DROP TABLE IF EXISTS `pay_error`;

CREATE TABLE `pay_error` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL,
  `pay_order_sn` char(25) DEFAULT NULL,
  `content` text,
  `type` tinyint(4) DEFAULT '1' COMMENT '1notify',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `pay_error` */

/*Table structure for table `pay_log` */

DROP TABLE IF EXISTS `pay_log`;

CREATE TABLE `pay_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pay_order_sn` char(25) DEFAULT NULL COMMENT '支付订单号',
  `total_price` decimal(10,2) DEFAULT '0.00' COMMENT '支付金额',
  `order_sn` text COMMENT '订单流水号',
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '发起订单时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `pay_log` */

/*Table structure for table `syslog` */

DROP TABLE IF EXISTS `syslog`;

CREATE TABLE `syslog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `detail` varchar(255) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `type` tinyint(4) DEFAULT NULL COMMENT '0登录1增2删3改',
  PRIMARY KEY (`id`),
  KEY `sys_admin_id` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

/*Data for the table `syslog` */

insert  into `syslog`(`id`,`admin_id`,`detail`,`create_time`,`ip`,`type`) values (1,1,'登录账号',1555594030,'60.25.10.181',0),(2,1,'登录账号',1555594240,'60.25.10.181',0),(3,1,'登录账号',1555597877,'60.25.10.181',0),(4,1,'登录账号',1555636804,'60.24.14.214',0),(5,1,'登录账号',1555652109,'60.24.14.214',0),(6,1,'登录账号',1555772449,'60.25.10.181',0),(7,1,'登录账号',1555773764,'60.25.10.181',0),(8,1,'登录账号',1555856088,'60.25.10.181',0),(9,1,'登录账号',1555857507,'60.25.10.181',0),(10,1,'登录账号',1555898465,'60.24.14.214',0);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
