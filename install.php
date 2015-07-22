<?php

function install_package () {

	$db = new cmsModel();

	$db->filterEqual('name', 'ulogin');
	$controllers = $db->getFieldFiltered('controllers', 'id');

	if (!$controllers) {
		$db->insert(
			'controllers',
			array(
				"title" => 'uLogin - регистрация/авторизация через соцсети',
				'name' => 'ulogin',
				'is_enabled' => 1,
				'options' => '',
				'author' => 'uLogin Team',
				'url' => 'https://ulogin.ru',
				'version' => '2.0.2',
				'is_backend' => 1,
			)
		);
	} else {
		$db->update(
			'controllers',
			$controllers,
			array(
				'version' => '2.0.2',
			)
		);
	}


	$db->filterEqual('controller', 'ulogin');
	$widgets = $db->getFieldFiltered('widgets', 'id');

	if (!$widgets) {
		$db->insert(
			'widgets',
			array(
				'controller' => 'ulogin',
				'name' => 'panel',
				'title' => 'Войти с помощью',
				'author' => 'uLogin Team',
				'url' => 'https://ulogin.ru',
				'version' => '1.0',
			)
		);

		$db->insert(
			'widgets',
			array(
				'controller' => 'ulogin',
				'name' => 'networks',
				'title' => 'Мои аккаунты',
				'author' => 'uLogin Team',
				'url' => 'https://ulogin.ru',
				'version' => '1.0',
			)
		);
	}


	$db->filterEqual('name', 'ulogin');
	$group_id = $db->getFieldFiltered('{users}_groups', 'id');

	if (!$group_id) {
		$db->insert(
			'{users}_groups',
			array(
				'title' => 'uLogin-пользователи',
				'name' => 'ulogin',
			)
		);
	}

	return true;

}