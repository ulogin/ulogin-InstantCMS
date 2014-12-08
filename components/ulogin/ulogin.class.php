<?php

class uloginClass {
	protected $u_data;
	public $inUser;
	public $currentUserId;
	public $isUserLogined;
	public $doRedirect;
	public $model;
	private $token;
	public static $u_inc;



	public function __construct() {
		$this->doRedirect = true;
		$this->model = new cms_model_ulogin();
	}


	/**
	 * Отправляет данные как ответ на ajax запрос, если код выполняется в результате вызова callback функции,
	 * либо добавляет сообщение в сессию для вывода в режиме redirect
	 * @param array $params
	 */
	protected function sendMessage ($params = array()) {
		if ($this->doRedirect){
			$class = ($params['answerType'] == 'error' || $params['answerType'] == 'success')
				? $params['answerType']
				: 'info';

			if (!empty($params['script'])) {
				$_SESSION['ulogin_script'] = $params['script'];
			}

			$message = (!empty($params['title']) ? $params['title']  . ' <br>' : '') . $params['msg'];

			$_SESSION['ulogin_message'][] = array(
				'msg' => $message,
				'class' => $class,
			);

			cmsCore::redirectBack();
		} else {
			echo json_encode(array(
				'title' => isset($params['title']) ? $params['title'] : '',
				'msg' => isset($params['msg']) ? $params['msg'] : '',
				'answerType' => isset($params['answerType']) ? $params['answerType'] : '',
				'existIdentity' => isset($params['existIdentity']) ? $params['existIdentity'] : '0',
				'networks' => isset($params['networks']) ? $params['networks'] : '',
			));
			exit;
		}
	}



	public function uloginLogin ($title = '', $msg = '') {
		$this->u_data = $this->uloginParseRequest();
		if ( !$this->u_data ) {
			return;
		}

		try {
			$u_user_db = $this->model->getUloginUserItem(array('identity' => $this->u_data['identity']));
			$user_id = 0;

			if ( $u_user_db ) {

				if ($this->model->checkUloginUserId($u_user_db['user_id'])) {
					$user_id = $u_user_db['user_id'];
				}

				if ( isset( $user_id ) && intval( $user_id ) > 0 ) {
					if ( !$this->checkCurrentUserId( $user_id ) ) {
						// если $user_id != ID текущего пользователя
						return;
					}
				} else {
					// данные о пользователе есть в ulogin_table, но отсутствуют в modx. Необходимо переписать запись в ulogin_table и в базе modx.
					$user_id = $this->newUloginAccount( $u_user_db );
				}

			} else {
				// пользователь НЕ обнаружен в ulogin_table. Необходимо добавить запись в ulogin_table и в базе modx.
				$user_id = $this->newUloginAccount();
			}

			// обновление данных и Вход
			if ( $user_id > 0 ) {
				$this->loginUser( $user_id );

				$networks = $this->model->getUloginUserNetworks( $user_id );
				$this->sendMessage( array(
					'title' => $title,
					'msg' => $msg,
					'networks' => $networks,
					'answerType' => 'success',
				) );
			}
			return;
		}

		catch (Exception $e){
			$this->sendMessage (array(
				'title' => "Ошибка при работе с БД.",
				'msg' => "Exception: " . $e->getMessage(),
				'answerType' => 'error'
			));
			return;
		}
	}



	/**
	 * Добавление в таблицу uLogin
	 * @param $u_user_db - при непустом значении необходимо переписать данные в таблице uLogin
	 */
	protected function newUloginAccount($u_user_db = ''){
		$u_data = $this->u_data;

		if ($u_user_db) {
			// данные о пользователе есть в ulogin_user, но отсутствуют в modx => удалить их
			$this->model->deleteUloginUser($u_user_db['id']);
		}

		$CMSuser = $this->model->getUser($u_data['email']);

		// $emailExists == true -> есть пользователь с таким email
		$user_id = 0;
		$emailExists = false;
		if ($CMSuser) {
			$user_id = $CMSuser['id']; // id юзера с тем же email
			$emailExists = true;
		}

		// $isUserLogined == true -> пользователь онлайн
		$currentUserId = $this->currentUserId;
		$isUserLogined = $this->isUserLogined;

		if (!$emailExists && !$isUserLogined) {
			// отсутствует пользователь с таким email в базе -> регистрация в БД
			$user_id = $this->regUser();
			$this->addUloginAccount($user_id);
		} else {
			// существует пользователь с таким email или это текущий пользователь
			if (intval($u_data["verified_email"]) != 1){
				// Верификация аккаунта

				$this->sendMessage(
					array(
						'title' => 'Подтверждение аккаунта.',
						'msg' => 'Электронный адрес данного аккаунта совпадает с электронным адресом существующего пользователя. ' .
						         '<br>Требуется подтверждение на владение указанным email.',
						'script' => array('token' => $this->token),
							'answerType' => 'verify',
					)
				);
				return false;
			}

			$user_id = $isUserLogined ? $currentUserId : $user_id;

			$other_u = $this->model->getUloginUserItem(array(
				'user_id' => $user_id,
			));

			if ($other_u) {
				// Синхронизация аккаунтов
				if(!$isUserLogined && !isset($u_data['merge_account'])){
					$this->sendMessage(
						array(
							'title' => 'Синхронизация аккаунтов.',
							'msg' => 'С данным аккаунтом уже связаны данные из другой социальной сети. ' .
							         '<br>Требуется привязка новой учётной записи социальной сети к этому аккаунту.',
							'script' => array('token' => $this->token, 'identity' => $other_u['identity']),
							'answerType' => 'merge',
							'existIdentity' => $other_u['identity']
						)
					);
					return false;
				}
			}

			$this->addUloginAccount($user_id);
		}

		return $user_id;
	}


	/**
	 * Регистрация пользователя в БД modx
	 * @return mixed
	 */
	protected function regUser(){
		// см. components/registration/frontend.php:157
		$u_data = $this->u_data;

		$error_msg = '';

		cmsCore::loadModel('registration');
		$model = new cms_model_registration();
		cmsCore::loadModel('users');
		$users_model = new cms_model_users();

		// регистрация закрыта
		if(!$model->config['is_on']){
			$error_msg = 'Регистрация закрыта.';
		}
		// регистрация по инвайтам
		if ($model->config['reg_type']=='invite'){
			if (!$users_model->checkInvite(cmsUser::sessionGet('invite_code'))) {
				$error_msg = 'Регистрация разрешена только по приглашению.';
			}
		}

		if($error_msg){
			$this->sendMessage (array(
				'title' => "Ошибка при регистрации.",
				'msg' => $error_msg,
				'answerType' => 'error'
			));
		}

		$password = md5($u_data['identity'].time().rand());

		$login = $this->generateNickname($u_data['first_name'],$u_data['last_name'],$u_data['nickname'],$u_data['bdate']);

		$CMSuser = array(
			'password' => md5($password),
			'login' => $login,
			'nickname' => $login,
			'email' => $u_data['email'],
			'city' => isset($u_data['city']) ? $u_data['city'] : '',
			'birthdate' => isset($u_data['bdate']) ? date("Y-m-d", strtotime($u_data['bdate'])) : '0000-00-00',

			'is_locked' => 0,
			'regdate' => date('Y-m-d H:i:s'),
			'logdate' => date('Y-m-d H:i:s'),
			'icq' => '',
			'is_deleted' => 0,
			'rating' => 0,
			'points' => 0,
			'last_ip' => '',
			'status' => '',
			'status_date' => '0000-00-00 00:00:00',
			'invdate' => '0000-00-00 00:00:00',
			'openid' => '',

			'description' => '',
			'showmail' => 0,
			'showbirth' => 0,
			'showicq' => 0,
			'karma' => 0,
			'imageurl' => '',
			'allow_who' => 'all',
			'signature' => '',
			'signature_html' => '',
			'formsdata' => '',
			'email_newmsg' => 0,
			'cm_subscribe' => 'none',
			'stats' => '',
		);

		$inCore = cmsCore::getInstance();
		$cfg = $inCore->loadComponentConfig('ulogin');
		$CMSuser['group_id'] = !empty($cfg['group_id']) ? $cfg['group_id'] : $this->model->getDefaultGroupId();

		if (isset($u_data['sex'])){
			if ($u_data['sex'] == 1) { $CMSuser['gender'] = 'f'; }
			elseif ($u_data['sex'] == 2) { $CMSuser['gender'] = 'm'; }
			else $CMSuser['gender'] = 0;
		}

		if (cmsUser::sessionGet('invite_code')){

			$invite_code = cmsUser::sessionGet('invite_code');
			$CMSuser['invited_by'] = (int)$users_model->getInviteOwner($invite_code);

			if ($CMSuser['invited_by']){ $users_model->closeInvite($invite_code); }

			cmsUser::sessionDel('invite_code');

		} else {
			$CMSuser['invited_by'] = 0;
		}

		$CMSuser = cmsCore::callEvent('USER_BEFORE_REGISTER', $CMSuser);

		$CMSuser['id'] = $this->model->isertUserData($CMSuser);
		if(!$CMSuser['id']){
			$this->sendMessage (array(
				'title' => "Ошибка при регистрации.",
				'msg' => "Произошла ошибка при регистрации пользователя.",
				'answerType' => 'error'
			));
		}

		cmsCore::callEvent('USER_REGISTER', $CMSuser);

		cmsActions::log('add_user', array(
			'object' => '',
			'user_id' => $CMSuser['id'],
			'object_url' => '',
			'object_id' => $CMSuser['id'],
			'target' => '',
			'target_url' => '',
			'target_id' => 0,
			'description' => ''
		));

		if ($model->config['send_greetmsg']){ $model->sendGreetsMessage($CMSuser['id']); }
		$model->sendRegistrationNotice($password, $CMSuser['id']);

//		$back_url = $inUser->signInUser($item['login'], $password, true);
//		cmsCore::redirect($back_url);

		return $CMSuser['id'];
	}




	/**
	 * Добавление записи в таблицу ulogin_user
	 * @param $user_id
	 * @return bool
	 */
	protected function addUloginAccount($user_id){
		$user = $this->model->addUloginAccount(array(
			'user_id' => $user_id,
			'identity' => strval($this->u_data['identity']),
			'network' => $this->u_data['network'],
		));

		if (!$user) {
			$this->sendMessage (array(
				'title' => "Произошла ошибка при авторизации.",
				'msg' => "Не удалось записать данные об аккаунте.",
				'answerType' => 'error'
			));
			return false;
		}

		return true;
	}




	/**
	 * Выполнение входа пользователя в систему по $user_id
	 * @param $u_user
	 * @param int $user_id
	 */
	protected function loginUser($user_id = 0){
		// см. \cmsUser::loadUser
		$inDB   = cmsDatabase::getInstance();

		$u_data = $this->u_data;

		$CMSuser = $this->inUser->loadUser($user_id);

		if(!$CMSuser) {
			$this->sendMessage(
				array(
					'title' => '',
					'msg' => 'Произошла ошибка при авторизации.',
					'answerType' => 'error',
				)
			);
			return false;
		}

		// обновление данных
		if (
			empty($CMSuser['birthdate'])
			|| empty($CMSuser['city'])
			|| empty($CMSuser['gender'])
			|| empty($CMSuser['imageurl'])
			|| strpos($CMSuser['imageurl'],'nopic.jpg') !== false
		) {

			$CMSuser['imageurl'] =
				(empty( $CMSuser['imageurl'] ) || strpos($CMSuser['imageurl'],'nopic.jpg') !== false)
				&& ( isset( $u_data['photo_big'] ) || isset( $u_data['photo'] ) )
					? $this->createAvatar($CMSuser)
					: $CMSuser['orig_imageurl'];

			if ((empty($CMSuser['birthdate']) || $CMSuser['birthdate'] == '0000-00-00') && isset($u_data['bdate'])) {
				$CMSuser['birthdate'] = date("Y-m-d", strtotime($u_data['bdate']));
			}

			$CMSuser['city'] = empty( $CMSuser['city'] ) && isset( $u_data['city'] ) ? $u_data['city'] : $CMSuser['city'];

			if (intval($CMSuser['gender']) == 0 && intval($u_data['sex']) > 0) {
				if ($u_data['sex'] == 1) { $CMSuser['gender'] = 'f'; }
				else { $CMSuser['gender'] = 'm'; }
			}

			$result = $this->model->updateUserData($CMSuser);

			if(!$result){
				$this->sendMessage (array(
					'title' => "Ошибка при регистрации.",
					'msg' => "Ошибка при обновлении дынных пользователя.",
					'answerType' => 'error'
				));
				return false;
			}

		}

		if (!$this->isUserLogined) {
			// вход
			$_SESSION['user'] = $CMSuser;
			cmsCore::callEvent('USER_LOGIN', $_SESSION['user']);

			// обновление даты последнего визита, ip и отметка, что пользователь онлайн
			$this->model->updateUserVisitData($CMSuser['id']);
		}

		return true;
	}


	/**
	 * Создание аватара
	 * @return string
	 */
	protected function createAvatar() {
		$u_data = $this->u_data;

		$file_url = ( !empty( $u_data['photo_big'] ) )
			? $u_data['photo_big']
			: ( !empty( $u_data['photo'] )  ? $u_data['photo'] : '' );

		$filename = '';

		$upload_dir = PATH.'/images/users/avatars/';

		$inCore = cmsCore::getInstance();
		$cfg = $inCore->loadComponentConfig('users');

		$q = isset( $file_url ) ? true : false;

		if ($q) {
			$size = getimagesize( $file_url );

			switch ( $size[2] ) {
				case IMAGETYPE_GIF:
					$dest_ext = 'gif';
					break;
				case IMAGETYPE_JPEG:
					$dest_ext = 'jpg';
					break;
				case IMAGETYPE_PNG:
					$dest_ext = 'png';
					break;
				default:
					$dest_ext = 'jpg';
					break;
			}

			$filename = substr( md5( $file_url . microtime( true ) ), 0, 16 ) . '.' . $dest_ext;

			$path = $upload_dir . $filename;
			$path_small = $upload_dir . 'small/' . $filename;

			cmsCore::includeGraphics();
			@copy( $file_url, $path );
			$q1 = @img_resize($path, $path,  $cfg['medw'], $cfg['medh']);
			@copy( $path, $path_small );
			$q2 = @img_resize($path_small, $path_small, $cfg['smallw'], $cfg['smallw']);

			$q = $q1 && $q2;
		}


		if (!$q) {
			return '';
		}

		return $filename;
	}



	/**
	 * Проверка текущего пользователя
	 * @param $user_id
	 */
	protected function checkCurrentUserId($user_id){
		$currentUserId = $this->currentUserId;
		if($this->isUserLogined) {
			if ($currentUserId == $user_id) {
				return true;
			}
			$this->sendMessage (
				array(
					'title' => '',
					'msg' => 'Данный аккаунт привязан к другому пользователю. ' .
					         '</br>Вы не можете использовать этот аккаунт',
					'answerType' => 'error',
				)
			);
			return false;
		}
		return true;
	}



	/**
	 * Обработка ответа сервера авторизации
	 */
	protected function uloginParseRequest(){
		$this->token = cmsCore::request('token');

		if (!$this->token) {
			$this->sendMessage (array(
				'title' => "Произошла ошибка при авторизации.",
				'msg' => "Не был получен токен uLogin.",
				'answerType' => 'error'
			));
			return false;
		}

		$s = $this->getUserFromToken();

		if (!$s){
			$this->sendMessage (array(
				'title' => "Произошла ошибка при авторизации.",
				'msg' => "Не удалось получить данные о пользователе с помощью токена.",
				'answerType' => 'error'
			));
			return false;
		}

		$this->u_data = json_decode($s, true);

		if (!$this->checkTokenError()){
			return false;
		}

		$inUser = cmsUser::getInstance();
		$this->inUser = $inUser;
		$this->currentUserId = $inUser->id;
		$this->isUserLogined = $inUser->id > 0 ? true : false;//$inUser->isOnline($inUser->id);

		return $this->u_data;
	}


	/**
	 * "Обменивает" токен на пользовательские данные
	 */
	protected function getUserFromToken() {
		$response = false;
		if ($this->token){
			$request = 'http://ulogin.ru/token.php?token=' . $this->token . '&host=' . $_SERVER['HTTP_HOST'];
			if(in_array('curl', get_loaded_extensions())){
				$c = curl_init($request);
				curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
				$response = curl_exec($c);
				curl_close($c);

			}elseif (function_exists('file_get_contents') && ini_get('allow_url_fopen')){
				$response = file_get_contents($request);
			}
		}
		return $response;
	}


	/**
	 * Проверка пользовательских данных, полученных по токену
	 */
	protected function checkTokenError(){
		if (!is_array($this->u_data)){
			$this->sendMessage (array(
				'title' => "Произошла ошибка при авторизации.",
				'msg' => "Данные о пользователе содержат неверный формат.",
				'answerType' => 'error'
			));
			return false;
		}

		if (isset($this->u_data['error'])){
			$strpos = strpos($this->u_data['error'],'host is not');
			if ($strpos){
				$this->sendMessage (array(
					'title' => "Произошла ошибка при авторизации.",
					'msg' => "<i>ERROR</i>: адрес хоста не совпадает с оригиналом " . sub($this->u_data['error'],intval($strpos)+12),
					'answerType' => 'error'
				));
				return false;
			}
			switch ($this->u_data['error']){
				case 'token expired':
					$this->sendMessage (array(
						'title' => "Произошла ошибка при авторизации.",
						'msg' => "<i>ERROR</i>: время жизни токена истекло",
						'answerType' => 'error'
					));
					break;
				case 'invalid token':
					$this->sendMessage (array(
						'title' => "Произошла ошибка при авторизации.",
						'msg' => "<i>ERROR</i>: неверный токен",
						'answerType' => 'error'
					));
					break;
				default:
					$this->sendMessage (array(
						'title' => "Произошла ошибка при авторизации.",
						'msg' => "<i>ERROR</i>: " . $this->u_data['error'],
						'answerType' => 'error'
					));
			}
			return false;
		}
		if (!isset($this->u_data['identity'])){
			$this->sendMessage (array(
				'title' => "Произошла ошибка при авторизации.",
				'msg' => "В возвращаемых данных отсутствует переменная <b>identity</b>.",
				'answerType' => 'error'
			));
			return false;
		}
		if (!isset($this->u_data['email'])){
			$this->sendMessage (array(
				'title' => "Произошла ошибка при авторизации.",
				'msg' => "В возвращаемых данных отсутствует переменная <b>email</b>",
				'answerType' => 'error'
			));
			return false;
		}
		return true;
	}


	/**
	 * Гнерация логина пользователя
	 * в случае успешного выполнения возвращает уникальный логин пользователя
	 * @param $first_name
	 * @param string $last_name
	 * @param string $nickname
	 * @param string $bdate
	 * @param array $delimiters
	 * @return string
	 */
	protected function generateNickname($first_name, $last_name="", $nickname="", $bdate="", $delimiters=array('.', '_')) {
		$delim = array_shift($delimiters);

		$first_name = $this->translitIt($first_name);
		$first_name_s = substr($first_name, 0, 1);

		$variants = array();
		if (!empty($nickname))
			$variants[] = $nickname;
		$variants[] = $first_name;
		if (!empty($last_name)) {
			$last_name = $this->translitIt($last_name);
			$variants[] = $first_name.$delim.$last_name;
			$variants[] = $last_name.$delim.$first_name;
			$variants[] = $first_name_s.$delim.$last_name;
			$variants[] = $first_name_s.$last_name;
			$variants[] = $last_name.$delim.$first_name_s;
			$variants[] = $last_name.$first_name_s;
		}
		if (!empty($bdate)) {
			$date = explode('.', $bdate);
			$variants[] = $first_name.$date[2];
			$variants[] = $first_name.$delim.$date[2];
			$variants[] = $first_name.$date[0].$date[1];
			$variants[] = $first_name.$delim.$date[0].$date[1];
			$variants[] = $first_name.$delim.$last_name.$date[2];
			$variants[] = $first_name.$delim.$last_name.$delim.$date[2];
			$variants[] = $first_name.$delim.$last_name.$date[0].$date[1];
			$variants[] = $first_name.$delim.$last_name.$delim.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name.$date[2];
			$variants[] = $last_name.$delim.$first_name.$delim.$date[2];
			$variants[] = $last_name.$delim.$first_name.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name.$delim.$date[0].$date[1];
			$variants[] = $first_name_s.$delim.$last_name.$date[2];
			$variants[] = $first_name_s.$delim.$last_name.$delim.$date[2];
			$variants[] = $first_name_s.$delim.$last_name.$date[0].$date[1];
			$variants[] = $first_name_s.$delim.$last_name.$delim.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name_s.$date[2];
			$variants[] = $last_name.$delim.$first_name_s.$delim.$date[2];
			$variants[] = $last_name.$delim.$first_name_s.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name_s.$delim.$date[0].$date[1];
			$variants[] = $first_name_s.$last_name.$date[2];
			$variants[] = $first_name_s.$last_name.$delim.$date[2];
			$variants[] = $first_name_s.$last_name.$date[0].$date[1];
			$variants[] = $first_name_s.$last_name.$delim.$date[0].$date[1];
			$variants[] = $last_name.$first_name_s.$date[2];
			$variants[] = $last_name.$first_name_s.$delim.$date[2];
			$variants[] = $last_name.$first_name_s.$date[0].$date[1];
			$variants[] = $last_name.$first_name_s.$delim.$date[0].$date[1];
		}
		$i=0;

		$exist = true;
		while (true) {
			if ($exist = $this->userExist($variants[$i])) {
				foreach ($delimiters as $del) {
					$replaced = str_replace($delim, $del, $variants[$i]);
					if($replaced !== $variants[$i]){
						$variants[$i] = $replaced;
						if (!$exist = $this->userExist($variants[$i]))
							break;
					}
				}
			}
			if ($i >= count($variants)-1 || !$exist)
				break;
			$i++;
		}

		if ($exist) {
			while ($exist) {
				$nickname = $first_name.mt_rand(1, 100000);
				$exist = $this->userExist($nickname);
			}
			return $nickname;
		} else
			return $variants[$i];
	}


	/**
	 * Проверка существует ли пользователь с заданным логином
	 */
	protected function userExist($login){
		if (!$this->model->getUser($login)){
			return false;
		}
		return true;
	}


	/**
	 * Транслит
	 */
	protected function translitIt($str) {
		$tr = array(
			"А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
			"Д"=>"d","Е"=>"e","Ж"=>"j","З"=>"z","И"=>"i",
			"Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
			"О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
			"У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch",
			"Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"",
			"Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
			"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
			"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
			"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
			"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
			"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
			"ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
		);
		if (preg_match('/[^A-Za-z0-9\_\-]/', $str)) {
			$str = strtr($str,$tr);
			$str = preg_replace('/[^A-Za-z0-9\_\-\.]/', '', $str);
		}
		return $str;
	}



	/**
	 * Удаление привязки к аккаунту соцсети в таблице ulogin_user для текущего пользователя
	 */
	public function deleteAccount() {
		$inUser = cmsUser::getInstance();
		$this->inUser = $inUser;
		$this->currentUserId = $inUser->id;
		$this->isUserLogined = $inUser->id > 0 ? true : false;

		if(!$this->isUserLogined) {exit;}

		$user_id = $this->currentUserId;

		$network = cmsCore::inRequest('network') ? cmsCore::request('network') : '';

		if ($user_id > 0 && $network != '') {
			try {
				$this->model->deleteUloginUser( array('user_id' => $user_id, 'network' => $network) );

				echo json_encode(array(
					'title' => '',
					'msg' => "Удаление аккаунта $network успешно выполнено",
					'answerType' => 'success'
				));
				exit;
			} catch (Exception $e) {
				echo json_encode(array(
					'title' => "Ошибка при удалении аккаунта",
					'msg' => "Exception: " . $e->getMessage(),
					'answerType' => 'error'
				));
				exit;
			}
		}
		exit;
	}
}