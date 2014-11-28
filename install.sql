INSERT INTO `{#}widgets` (`controller`, `name`, `title`, `author`, `url`, `version`)
VALUES ('ulogin', 'panel', 'Войти с помощью', 'uLogin Team', 'https://ulogin.ru', '1.0');

INSERT INTO `{#}widgets` (`controller`, `name`, `title`, `author`, `url`, `version`)
VALUES ('ulogin', 'networks', 'Мои аккаунты', 'uLogin Team', 'https://ulogin.ru', '1.0');

INSERT INTO `{#}controllers` (`title`, `name`, `is_enabled`, `options`, `author`, `url`, `version`, `is_backend`)
VALUES ('uLogin - регистрация/авторизация через соцсети', 'ulogin', 1, '', 'uLogin Team', 'https://ulogin.ru', '2.0.0', 1);

INSERT INTO `{#}users_groups` (`title`, `name`)
VALUES ('uLogin-пользователи', 'ulogin');

CREATE TABLE IF NOT EXISTS `{#}ulogin_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `identity` varchar(255) NOT NULL,
  `network` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
