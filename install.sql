CREATE TABLE IF NOT EXISTS `{#}ulogin_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `identity` varchar(255) NOT NULL,
  `network` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
