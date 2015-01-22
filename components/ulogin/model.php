<?php

class cms_model_ulogin {

	public function __construct(){
		$this->inDB = cmsDatabase::getInstance();
		$this->inUser = cmsUser::getInstance();
	}

/* ==================================================================================================== */
	/**
	 * Проверка, есть ли пользователь с указанным id в базе
	 * @param $u_id
	 * @return bool
	 */
	public function checkUloginUserId ($u_id) {
		$sql = "SELECT id
				FROM cms_users
				WHERE id = $u_id";

		$result = $this->inDB->query($sql);

		if ($this->inDB->error()) { return false; }

		return $this->inDB->num_rows($result) > 0 ? true : false;
	}

//--------------------
	/**
	 * Получение данных о пользователе по логину, email или id
	 * @param string $id
	 * @return array
	 */
	public function getUser ($id = '') {

		$users = $this->inUser;

		$user = $users->getShortUserData($id);

		return $user;
	}


//--------------------
	/**
	 * Получение данных о пользователе из таблицы ulogin_user
	 * @param array $fields
	 * @return array|bool|null
	 */
	public function getUloginUserItem ($fields = array()) {
		if (!is_array($fields) || empty($fields)) { return false; }

		$sql = "SELECT *
				FROM cms_ulogin_user";

		$sql .= $this->addWhere($fields);

		$result = $this->inDB->query($sql);

		if ($this->inDB->error()) { return false; }

		return $this->inDB->fetch_assoc($result);
	}


//--------------------
	/**
	 * Получение массива соцсетей пользователя по значению поля $user_id
	 * @param int $user_id
	 * @return mixed
	 */
	public function getUloginUserNetworks ($user_id = 0) {
		$sql = "SELECT network
				FROM cms_ulogin_user
				WHERE user_id = $user_id";

		$result = $this->inDB->query($sql);

		if ($this->inDB->error()) { return false; }

		$result = $this->inDB->fetch_all($result);
		$networks = array();

		foreach ($result as $value) {
			$networks[] = $value->network;
		}

		return $networks;
	}


//--------------------
	/**
	 * Удаление данных о пользователе из таблицы ulogin_user
	 * @param int $user_id
	 * @return bool
	 */
	public function deleteUloginUser ($data = array()) {
		$where = $this->addWhere($data, false);
		return $this->inDB->delete('cms_ulogin_user', $where);
	}


//--------------------
	/**
	 * Добавление данных о пользователе в таблицы ulogin_user
	 * @param array $data
	 * @return mixed
	 */
	public function addUloginAccount ($data = array()) {
		return $this->inDB->insert('cms_ulogin_user', $data);
	}


//--------------------
	/**
	 * Получение id группы uLogin
	 * @return mixed
	 */
	public function getDefaultGroupId($getUloginGroup = false) {
		cmsCore::loadModel('registration');
		$registration_model = new cms_model_registration();
		if (!$getUloginGroup) {
			$group_id = $registration_model->config['default_gid'];
		} else {
			$group_id = $this->inDB->get_field( 'cms_user_groups', 'alias=\'ulogin\'', 'id' );
			if ( !$group_id ) {
				$registration_model = new cms_model_registration();
				$group_id = $registration_model->config['default_gid'];
			}
		}
		return $group_id;
	}

	/**
	 * Получение групп пользователей
	 * @return array|bool|mysqli_result
	 */
	public function getGroups() {
		$sql = "SELECT id, alias, title
				FROM cms_user_groups";

		$result = $this->inDB->query($sql);
		if ($this->inDB->error()) { return false; }
		$result = $this->inDB->fetch_all($result);
		return $result;
	}

//--------------
	/** Получение условия where для массива данных $fields
	 * @param array $fields
	 * @return string
	 */
	private function addWhere ($fields = array(), $withWhereWord = true) {
		$i = 0;
		$sql = '';

		foreach ($fields as $field => $value) {

			if ($i == 0) {
				$sql .= $withWhereWord ? " WHERE " : "";
			} else {
				$sql .= " AND ";
			}

			$sql .= "$field = '$value'";
			$i++;

		}

		return $sql;
	}


//-----------------------------------------------------------------
//-----------------------------------------------------------------
	/**
	 * Добавление пользовательских данных
	 * @param $user
	 * @return bool|int
	 */
	public function insertUserData ($user) {
		$result = $this->inDB->insert('cms_users', $user);
		if (!$result) { return false; }

		$user['user_id'] = $result;

		$result = $this->inDB->insert('cms_user_profiles', $user);
		if (!$result) { return false; }

		return $user['user_id'];
	}


	/**
	 * Обновление пользовательских данных
	 * @param $user
	 * @return bool|int
	 */
	public function updateUserData ($user) {
		$user['user_id'] = $user['id'];

		$result = $this->inDB->update('cms_users', $user, $user['id']);
		if (!$result) { return false; }

		$profile_id = $this->inDB->get_field('cms_user_profiles', "user_id='{$user['user_id']}'", 'id');

		$result = $this->inDB->update('cms_user_profiles', $user, $profile_id);
		if (!$result) { return false; }

		return $user['id'];
	}


	/**
	 * Обновление даты последнего визита, ip и отметка, что пользователь онлайн
	 * @param $id
	 */
	public function updateUserVisitData ($id) {
		cmsUser::setUserLogdate( $id );
		$this->inDB->query( "UPDATE cms_users SET last_ip = '{$this->inUser->ip}', is_logged_once = 1 WHERE id = '{$id}'" );
		// помечаем, что пользователь онлайн
		$this->inDB->query( "UPDATE cms_online SET user_id = '{$id}' WHERE sess_id = '" . session_id() . "'" );
	}


//-----------------------------------------------------------------
//-----------------------------------------------------------------
	/**
	 * Добавление/обновление компонента в БД
	 * @param array $params
	 * @return bool
	 */
	public function registerComponent($params = array()) {

		$update_params = array();
		$update_params['version'] = $params['version'];

		$component_id = $this->inDB->get_field('cms_components', 'link=\'' . $params['link'] . '\'', 'id');

		if ($component_id) {
			$this->inDB->update('cms_components', $update_params, $component_id);
			return true;
		}

		$component_id = $this->inDB->insert('cms_components', $params);

		if (!$component_id) { return false; }

		return true;
	}


//--------------
	/**
	 * Добавление/обновление модуля в БД
	 * @param array $params
	 * @return bool
	 */
	public function registerModule($params = array()) {
		$params_def = array(
			'name' => '',
			'title' => '',
			'content' => '',
			'position' => 'sidebar',
			'showtitle' => '1',
			'css_prefix' => '',
			'ordering' => '1',
			'author' => 'uLogin Team',
			'version' => '1.0',

			'is_external' => '1',
			'published' => '1',
			'user' => '0',
			'config' => '',
			'original' => '1',
			'access_list' => '',
			'cache' => '0',
			'cachetime' => '1',
			'cacheint' => 'HOUR',
		);

		$params = array_merge($params_def, $params);

		$update_params = array();
		$update_params['version'] = $params['version'];

		$module_id = $this->inDB->get_field('cms_modules', 'name=\'' . $params['name'] . '\'', 'id');

		if ($module_id) {
			$this->inDB->update('cms_modules', $update_params, $module_id);
			return true;
		}

		$module_id = $this->inDB->insert('cms_modules', $params);

		if (!$module_id) { return false; }

		cmsCore::clearCache();

		$result = $this->inDB->insert(
			'cms_modules_bind',
			array(
				'module_id' => $module_id,
				'menu_id' => 0,
				'position' => $params['position'],
			)
		);

		return $result ? true : false;
	}


//--------------
	/**
	 * Регистрация группы uLogin
	 * @return bool
	 */
	public function registerGroup($params = array()) {
		$group_id = $this->inDB->get_field('cms_user_groups', 'alias=\'ulogin\'', 'id');
		if ($group_id > 0) { return true; }

		cmsCore::loadModel('registration');
		$registration_model = new cms_model_registration();
		$default_gid = $registration_model->config['default_gid'];
		$default_group = $this->inDB->get_fields('cms_user_groups', 'id=\'' . $default_gid . '\'', 'id, access');

		if ($default_group) {
			$params['access'] = $default_group['access'];
		}

		$result = $this->inDB->insert('cms_user_groups', $params);

		return $result ? true : false;
	}

}