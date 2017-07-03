<?php

// todo: вынести в языки

class actionUloginLogin extends cmsAction {
    protected $u_data;
    protected $currentUserId;
    protected $_messParam = array();
    protected $_doRedirect = true;

    public function run(){
        $title = '';

        $this->currentUserId = cmsUser::getInstance()->id;

        if ($this->request->isAjax()) {
            $this->_doRedirect = false;
        }

        if (cmsUser::isLogged()) {
            $msg = 'Аккаунт успешно добавлен';
        } else {
            $msg = 'Вход успешно выполнен';
        }

        $this->uloginLogin($title, $msg);

        if ($this->request->isAjax()) {
            exit;
        }
    }

    /**
     * Отправляет данные как ответ на ajax запрос, если код выполняется в результате вызова callback функции,
     * либо добавляет сообщение в сессию для вывода в режиме redirect
     * @param array $params
     */
    protected function sendMessage ($params = array()) {
        if ($this->_doRedirect){

            $class = ($params['answerType'] == 'error' || $params['answerType'] == 'success')
                ? $params['answerType']
                : 'info';

            if (!empty($params['script'])) {
                $params['msg'] .= $params['script'];
            }

            cmsUser::addSessionMessage((!empty($params['title']) ? $params['title']  . ' <br>' : '') . $params['msg'], $class);

            $this->redirectBack();

        } else {
            echo json_encode(array(
                'title' => isset($params['title']) ? $params['title'] : '',
                'msg' => isset($params['msg']) ? $params['msg'] : '',
                'answerType' => isset($params['answerType']) ? $params['answerType'] : '',
                'userId' => isset($params['userId']) ? $params['userId'] : '0',
                'existIdentity' => isset($params['existIdentity']) ? $params['existIdentity'] : '0',
                'networks' => isset($params['networks']) ? $params['networks'] : '',
                'redirect' => isset($params['redirect']) ? $params['redirect'] : '',
            ));
            exit;
        }
    }



    protected function uloginLogin ($title = '', $msg = '') {
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
//					'redirect' => $redirect_url,
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

        if(!(new auth($this->request))->isEmailAllowed($u_data['email'])){
            $this->sendMessage (array(
                'title' => "Ошибка при регистрации.",
                'msg' => sprintf(LANG_AUTH_RESTRICTED_EMAIL, $u_data['email']),
                'answerType' => 'error'
            ));
            return false;
        }
        if(!(new auth($this->request))->isIPAllowed(cmsUser::get('ip'))){
            $this->sendMessage (array(
                'title' => "Ошибка при регистрации.",
                'msg' => sprintf(LANG_AUTH_RESTRICTED_IP, cmsUser::get('ip')),
                'answerType' => 'error'
            ));
            return false;
        }

        $CMSuser = $this->model->getUser(array(
            'email' => $u_data['email'],
        ));

        // $check_m_user == true -> есть пользователь с таким email
        $user_id = 0;
        $check_m_user = false;
        if ($CMSuser) {
            $user_id = $CMSuser['id']; // id юзера с тем же email
            $check_m_user = true;
        }

        // $isLoggedIn == true -> пользователь онлайн
        $currentUserId = $this->currentUserId;
        $isLoggedIn = cmsUser::isLogged();

        if (!$check_m_user && !$isLoggedIn) {
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
                        'script' => '<script type="text/javascript">uLogin.mergeAccounts("' . $this->request->get('token') . '")</script>',
                        'answerType' => 'verify',
                    )
                );
                return false;
            }

            $user_id = $isLoggedIn ? $currentUserId : $user_id;

            $other_u = $this->model->getUloginUserItem(array(
                'user_id' => $user_id,
            ));

            if ($other_u) {
                // Синхронизация аккаунтов
                if(!$isLoggedIn && !isset($u_data['merge_account'])){
                    $this->sendMessage(
                        array(
                            'title' => 'Синхронизация аккаунтов.',
                            'msg' => 'С данным аккаунтом уже связаны данные из другой социальной сети. ' .
                                '<br>Требуется привязка новой учётной записи социальной сети к этому аккаунту.',
                            'script' => '<script type="text/javascript">uLogin.mergeAccounts("' . $this->request->get('token') . '","' . $other_u['identity'] . '")</script>',
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
     * Регистрация пользователя в БД
     * @return mixed
     */
    protected function regUser(){
        $u_data = $this->u_data;

        $password = md5($u_data['identity'].time().rand());

        $first_name = !empty($u_data['first_name']) ? $u_data['first_name'] : '';
        $last_name = !empty($u_data['last_name']) ? $u_data['last_name'] : '';
        $nickname = !empty($u_data['nickname']) ? $u_data['nickname'] : '';
        $bdate = !empty($u_data['bdate']) ? $u_data['bdate'] : '';

        $CMSuser = array(
            'password1' => $password,
            'password2' => $password,
            'nickname' => $this->generateNickname($first_name, $last_name, $nickname, $bdate),
            'email' => $u_data['email'],
            'site' => isset($u_data['profile']) ? $u_data['profile'] : '',
            'phone' => isset($u_data['phone']) ? $u_data['phone'] : '',
        );

        $ulogin_group_id = $this->getOptions();
        $ulogin_group_id = !empty($ulogin_group_id['group_id']) ? $ulogin_group_id['group_id'] : -1;

        $ulogin_group_id = $ulogin_group_id > 0 ? $ulogin_group_id : $this->model->getUloginGroupId();

        if ($ulogin_group_id) {
            $CMSuser['groups'] = array($ulogin_group_id);
        }

        if ($bdate) {
            $CMSuser['birth_date'] = date("Y-m-d H:i:s", strtotime($bdate));
        }

        $city_id = isset($u_data['city'])
            ? $this->model->getCityId($u_data['city'],$u_data['country'])
            : 0;

        if ($city_id > 0) {
            $CMSuser['city'] = $city_id;
            $CMSuser['city_cache'] = $u_data['city'];
        }

        $users_model = cmsCore::getModel('users');
        $result = $users_model->addUser($CMSuser);

        //  см. system/controllers/auth/actions/register.php:187
        if ($result['success']){

            $CMSuser['id'] = $result['id'];

            cmsUser::addSessionMessage('Регистрация прошла успешно', 'success');

            cmsEventsManager::hook('user_registered', $CMSuser);

        } else {
            $this->sendMessage (array(
                'title' => "Ошибка при регистрации.",
                'msg' => "Произошла ошибка при регистрации пользователя.",
                'answerType' => 'error'
            ));
            return false;
        }

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

        $u_data = $this->u_data;

        $CMSuser = $this->model->getUser(array('id' => $user_id));

        if (empty($CMSuser['id'])) { return false; }

//		$this->getAvatar($CMSuser);
        // обновление данных
        if (
            empty($CMSuser['avatar'])
            || empty($CMSuser['birth_date'])
            || empty($CMSuser['site'])
            || empty($CMSuser['phone'])
            || empty($CMSuser['city'])
        ) {
            $users = cmsCore::getModel( 'users' );
            $CMSuser['avatar'] = empty( $CMSuser['avatar'] ) && isset( $u_data['photo_big'] ) ?  $this->getAvatar($CMSuser) : $CMSuser['avatar'];

            $CMSuser['site'] = empty( $CMSuser['site'] ) && isset( $u_data['profile'] ) ? $u_data['profile'] : $CMSuser['site'];
            $CMSuser['phone'] = empty( $CMSuser['phone'] ) && isset( $u_data['phone'] ) ? $u_data['phone'] : $CMSuser['phone'];

            if ((empty($CMSuser['birth_date']) || $CMSuser['birth_date'] == '0000-00-00 00:00:00') && isset($u_data['bdate'])) {
                $CMSuser['birth_date'] = date( "Y-m-d H:i:s", strtotime( $u_data['bdate'] ) );
            }

            if (!empty($CMSuser['city_id'])) {
                $CMSuser['city'] = $CMSuser['city_id'];
            } elseif (isset($u_data['city'])) {
                $CMSuser['city'] = $this->model->getCityId($u_data['city'],$u_data['country']);
            }

            $result = $users->updateUser( $CMSuser['id'], $CMSuser );

            if ( $result['errors'] ) {
                $this->sendMessage(
                    array(
                        'title' => '',
                        'msg' => 'Ошибка при обновлении дынных пользователя',
                        'answerType' => 'error',
                    )
                );
                return false;
            }
        }

        // вход
        // см. system/core/user.php:174
        $CMSuser = cmsEventsManager::hook('user_login', $CMSuser);

        cmsUser::sessionSet('user', array(
            'id' => $CMSuser['id'],
            'groups' => $CMSuser['groups'],
            'time_zone' => $CMSuser['time_zone'],
            'perms' => cmsUser::getPermissions($CMSuser['groups'], $CMSuser['id']),
            'is_admin' => $CMSuser['is_admin'],
        ));

        $users_model = cmsCore::getModel('users');
        $users_model->update('{users}', $CMSuser['id'], array(
            'ip' => cmsUser::getIp()
        ));


        // см. system/controllers/auth/actions/login.php:30
        if (!cmsConfig::get('is_site_on')){

            $userSession = cmsUser::sessionGet('user');

            if (!$userSession['is_admin']){
                cmsUser::logout();
                $this->sendMessage (
                    array(
                        'title' => '',
                        'msg' => 'Войти на отключенный сайт может только администратор',//LANG_LOGIN_ADMIN_ONLY,
                        'answerType' => 'error',
                        'redirect' => $this->redirectBack(),
                    )
                );
                return false;
            }
        }

        cmsEventsManager::hook('auth_login', $CMSuser['id']);

        return true;
    }



    /**
     * Создание аватара
     */
    protected function getAvatar($user) {

        // см. system/core/uploader.php
        // см. system/controllers/images/frontend.php:38

        $u_data = $this->u_data;
        $uploader = new cmsUploader;
        $config = cmsConfig::getInstance();
        $path = '';
        $dest_file = '';
        $dest_dir = '';
        $dest_dir0 = '';
        $dest_ext = '';

        $file_url = ( !empty( $u_data['photo_big'] ) )
            ? $u_data['photo_big']
            : ( !empty( $u_data['photo'] )  ? $u_data['photo'] : '' );

        $q = !empty( $file_url ) ? true : false;

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

            $dir_num_user   = sprintf('%03d', intval($user['id']/100));
            $dir_num_file   = sprintf('%03d', intval($user['files_count']/100));
            $dest_dir0      = "{$dir_num_user}/u{$user['id']}/{$dir_num_file}";
            $dest_dir       = $config->upload_path . $dest_dir0;

            @mkdir($dest_dir, 0777, true);

            $dest_file = substr( md5( $user['id'] . $user['files_count'] . microtime( true ) ), 0, 8 ) . '.' . $dest_ext;
            $path = $dest_dir . '/' . $dest_file;

            $q = @copy( $file_url, $path );
        }

        if ($q){
            if (!$uploader->isImage($path)){
                $uploader->remove($path);
                $msg = 'Файл имеет неподходящий формат';
                $q = false ;
            }
        }

        if (!$q) {
            return array(
                'msg' => isset($msg) ? $msg : $msg = 'Произошла ошибка при формировании аватара',
                'answerType' => 'error',
            );
        }

        $defaultPresets = array(
            'normal' 	=> array('width' => 256, 'height' => 256),
            'small' 	=> array('width' => 64, 'height' => 64),
            'micro' 	=> array('width' => 32, 'height' => 32),
        );
        $availablePresets = array_keys($defaultPresets);

        //достаем размеры изображений из настроек "загрузка изображений" (если он установлен) и индексируем их по именам
        if(cmsCore::isModelExists('images')){
            $images_model = cmsCore::getModel('images');
            $presets_tmp = $images_model->getPresets();
            $presets = array();
            foreach ($presets_tmp as $tmp) {
                if(in_array($tmp['name'], $availablePresets)) {
                    $presets[$tmp['name']] = $tmp;
                }
            }
        } else {
            $presets = $defaultPresets;
        }

        uasort($presets, function ($a, $b) {
            return $a['height'] > $b['height'] ? -1 : ($a['height'] < $b['height'] ? 1 : 0);
        });

        $result['paths'] = array();
        foreach ($presets as $name => $data) {
            $dest_file = substr( md5( $user['id'] . $user['files_count'] . microtime( true ) ), 0, 8 ) . '.' . $dest_ext;
            $path2 = $dest_dir . '/' . $dest_file;
            if ($uploader->imageCopyResized($path, $path2, $data['width'], $data['height'], false)) {
                $result['paths'][$name] = $dest_dir0 . '/' . $dest_file;
            }
        }

        $uploader->remove($path);

        unset($path);
        return $result['paths'];
    }


    /**
     * Проверка текущего пользователя
     * @param $user_id
     * @return bool
     */
    protected function checkCurrentUserId($user_id){
        $currentUserId = $this->currentUserId;
        if(cmsUser::isLogged()) {
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
        $token = $this->request->get('token');

        if (!$token) {
            $this->sendMessage (array(
                'title' => "Произошла ошибка при авторизации.",
                'msg' => "Не был получен токен uLogin.",
                'answerType' => 'error'
            ));
            return false;
        }

        $s = $this->getUserFromToken($token);

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

        return $this->u_data;
    }


    /**
     * "Обменивает" токен на пользовательские данные
     */
    protected function getUserFromToken($token = false)
    {
        $response = false;
        if ($token){

            $data = array(
                'cms' => 'instantcms',
                'version' => cmsCore::getVersion(),
            );

            $request = 'https://ulogin.ru/token.php?token=' . $token . '&host=' . $_SERVER['HTTP_HOST'] .
                '&data='.base64_encode(json_encode($data));

            if(in_array('curl', get_loaded_extensions())){
                $c = curl_init($request);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($c);
                curl_close($c);

            } elseif (function_exists('file_get_contents') && ini_get('allow_url_fopen')){
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
        return $first_name . " " . $last_name;
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
        if (!$this->model->getUser(array('nickname'=>$login))){
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
}