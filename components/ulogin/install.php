<?php

function info_component_ulogin(){
	global $ulogin_component;

	$ulogin_component['title']        = 'uLogin - авторизация'; //Заголовок компонента
	$ulogin_component['description']  =
"uLogin — это инструмент, который позволяет пользователям получить единый доступ к различным Интернет-сервисам без необходимости повторной регистрации,<br/>" .
"а владельцам сайтов — получить дополнительный приток клиентов из социальных сетей и популярных порталов (Google, Яндекс, Mail.ru, ВКонтакте, Facebook и др.)<br/>" .
"<br/>" .
"Пакет дополнения включает в себя 3 модуля:<br/>" .
"- Войти с помощью - обеспечивает вход/регистрацию пользователей через популярные социальные сети и порталы;<br/>" .
"- Мои аккаунты - позволяет пользователю просматривать подключенные аккаунты соцсетей, добавлять новые;<br/>" .
"- Сообщения uLogin - обеспечивает вывод сообщений.<br/>" .
"Данные модули устанавливаются автоматически вместе с компонентом.<br/>" .
"<br/>" .
"<br/>" .
"Вы можете создать свой виджет uLogin и редактировать его самостоятельно:<br/>" .
"Для создания виджета необходимо зайти в Личный Кабинет (ЛК) на сайте http://ulogin.ru/lk.php,<br/>" .
"добавить свой сайт к списку \"Мои сайты\" и на вкладке \"Виджеты\" добавить новый виджет. Отредактируйте свой виджет.<br/>" .
"Важно! Для успешной работы плагина необходимо включить в обязательных полях профиля поле Еmail в Личном кабинете uLogin.<br/>" .
"<br/>" .
"На своём сайте на странице Компоненты в настройках uLogin укажите значение поля uLogin ID.<br/>" .
"Модули \"Войти с помощью\" и \"Мои аккаунты\" также могут иметь своё значение uLogin ID, отличное от настроек в компонентах.<br/>";
	$ulogin_component['link']         = 'ulogin'; //ссылка на компонент
	$ulogin_component['author']       = 'uLogin Team'; //Автор компонента
	$ulogin_component['internal']     = '0';
	$ulogin_component['version']      = '2.0.3'; //версия

	$inCore = cmsCore::getInstance();
	$inCore->loadModel('ulogin');

	return $ulogin_component;
}

function install_component_ulogin(){
	ulogin_installer();
}


function upgrade_component_ulogin(){
	ulogin_installer();
}


function ulogin_installer() {
	global $ulogin_component;

	$inConf = cmsConfig::getInstance();
	include(PATH.'/includes/dbimport.inc.php');
	dbRunSQL(PATH.'/components/ulogin/install.sql', $inConf->db_prefix);

	cmsCore::loadModel('ulogin');
	$ulogin_model = new cms_model_ulogin();

	$res[] = $ulogin_model->registerComponent(
		array(
			'title' => $ulogin_component['title'],
			'link' => $ulogin_component['link'],
			'config' => '',
			'internal' => $ulogin_component['internal'],
			'author' => $ulogin_component['author'] ,
			'published' => '1',
			'version' => $ulogin_component['version'] ,
			'system' => '1',
		)
	);

	$res[] = $ulogin_model->registerModule(
		array(
			'name' => 'ulogin_panel',
			'title' => 'Войти с помощью',
			'content' => 'mod_ulogin_panel',
		)
	);

	$res[] = $ulogin_model->registerModule(
		array(
			'name' => 'ulogin_networks',
			'title' => 'Мои аккаунты',
			'content' => 'mod_ulogin_networks',
		)
	);

	$res[] = $ulogin_model->registerModule(
		array(
			'name' => 'ulogin_messages',
			'title' => 'Сообщения uLogin',
			'content' => 'mod_ulogin_messages',
			'position' => 'maintop',
			'showtitle' => '0',
			'ordering' => '999',
			'css_prefix' => 'ulogin_messages_',
		)
	);

	$res[] = $ulogin_model->registerGroup(
		array(
			'alias' => 'ulogin',
			'title' => 'Пользователи uLogin',
			'is_admin' => '0',
			'access' => 'comments/add, comments/bbcode, comments/add_published, comments/delete, content/add, board/add, board/autoadd',
		)
	);

	if ($res[0] && $res[1] && $res[2] && $res[3] && $res[4]) {
		cmsCore::clearCache();
		cmsCore::addSessionMessage('Установка прошла успешно.', 'success');
	} else {
		cmsCore::addSessionMessage('При установке возникли ошибки.', 'error');
	}

	return;
}