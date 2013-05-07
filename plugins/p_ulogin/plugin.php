<?php

class p_ulogin extends cmsPlugin
{

    public function __construct()
    {

        parent::__construct();

        $this->info['plugin'] = 'p_ulogin';
        $this->info['title'] = 'Авторизация Ulogin';
        $this->info['type'] = 'Auth';
        $this->info['description'] = 'Авторизация с помощью социальных сетей';
        $this->info['author'] = 'uLogin Team';
        $this->info['version'] = '1.0';

        $this->config['Providers'] = 'vkontakte,odnoklassniki,mailru,facebook';
        $this->config['Hidden'] = 'other';
        $this->config['Verify'] = 1;

        $this->events[] = 'ULOGIN_BUTTON';
        $this->events[] = 'ULOGIN_BUTTON_SMALL';
        $this->events[] = 'ULOGIN_SYNC_PANEL';
        $this->events[] = 'ULOGIN_AUTH';
        $this->events[] = 'ULOGIN_SYNC';
        $this->events[] = 'ULOGIN_DETTACH';

    }

    public function install()
    {

        if (!ini_get('allow_url_fopen')) {
            $error = '<h4>Ошибка установки плагина</h4>';
            $error .= '<p>Для работы плагина на вашем хостинге директива <b>allow_url_fopen</b> в php.ini должна быть включена</p>';
            die($error);
        }

        $inDB = cmsDatabase::getInstance();

        if (!$inDB->isFieldExists('cms_users', 'ulogin_id')) {

            $inDB->query("ALTER TABLE `cms_users` ADD `ulogin_id` VARCHAR( 250 ) NULL, ADD INDEX ( `ulogin_id` )");

        }

        if (!$inDB->isFieldExists('cms_users', 'main_id')) {

            $inDB->query("ALTER TABLE `cms_users` ADD `main_id` INT");

        }

        return parent::install();

    }

    public function upgrade()
    {

        return parent::upgrade();

    }

    public function execute($event, $item)
    {

        parent::execute();

        switch ($event) {
            case 'ULOGIN_BUTTON':
                $item = $this->showUloginButton();
                break;
            case 'ULOGIN_BUTTON_SMALL':
                $item['small'] = TRUE;
                $item['id'] = isset($item['id'])?$item['id']:'uLoginSmall';
                $item = $this->showUloginButton($item);
                break;
            case 'ULOGIN_SYNC_PANEL':
                $item = $this->showUloginSyncPanel();
                break;
            case 'ULOGIN_AUTH':
                $item = $this->uloginAuth();
                break;
            case 'ULOGIN_SYNC':
                $item = $this->uloginSync();
                break;
            case 'ULOGIN_DETTACH':
                $item = $this->uloginDettach();
                break;
        }

        return true;

    }

    private function showUloginButton($item = array())
    {
        
        $small = (isset($item['small']) && $item['small'])?true:false;
        $id = isset($item['id'])?$item['id']:'uLogin';

        $token_url = urlencode(HOST . '/plugins/p_ulogin/auth.php');
        $providers = $this->config['Providers'];
        $hidden = $this->config['Hidden'];
        $verify = $this->config['Verify'];


        $html = '<div id="'.$id.'" x-ulogin-params="display='.($small?'small':'panel').'&verify='.$verify.
                '&fields=first_name,last_name,nickname,city,photo,photo_big,bdate,sex,email&providers='. $providers .
                '&hidden=' . $hidden . '&redirect_uri=' . $token_url . '"></div>';

        echo $html;

        return;

    }


    private function showUloginSyncPanel($item = array()){

        $small = (isset($item['small']) && $item['small'])?true:false;
        $id = isset($item['id'])?$item['id']:'uLogin';

        $token_url = urlencode(HOST . '/plugins/p_ulogin/sync.php');
        $providers = $this->config['Providers'];
        $hidden = $this->config['Hidden'];

        $user_id = $_SESSION['user']['id'];

        $users = $this->getAttachedUsers($user_id);

        $html = "<div class='field'><div class='title'>Подключенные профили</div></div>";

        foreach($users as  $user){

            $html.="<div class='field'><div class='value'><a href='".$user['identity']."'>".$user['identity']."</a></div><div class='value' style='float:right;'><a href='/plugins/p_ulogin/dettach.php?id=".$user['id']."'>[Отключить]</a></div></div>";

        }

        $html.= "<div class='field'><div class='title'>Подключить профиль:</div></div>";

        $html .= '<div id="'.$id.'" x-ulogin-params="display='.($small?'small':'panel').
            '&fields=first_name,last_name,nickname,city,photo,photo_big,bdate,sex,email&providers='. $providers .
            '&hidden=' . $hidden . '&redirect_uri=' . $token_url . '"></div>';

        echo $html;

        return;

    }

    private function uloginAuth()
    {

        $inCore = cmsCore::getInstance();

        $token = $inCore->request('token', 'str', '');

        if (!$token) {
            exit;
        }

        $ulogin_token_url = 'http://ulogin.ru/token.php';
        $ulogin_token_url .= '?token=' . $token . '&host=' . $_SERVER['HTTP_HOST'];

        // получение профиля


        if(in_array('curl', get_loaded_extensions())){

            $request = curl_init('http://ulogin.ru/token.php?token=' . $token . '&host=' . $_SERVER['HTTP_HOST']);
            curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
            $json_string = curl_exec($request);

            if ($json_string)
                $profile = json_decode($json_string, true);

        }elseif (function_exists('file_get_contents') && ini_get('allow_url_fopen')){

        $json_string = file_get_contents($ulogin_token_url);
        $profile = json_decode($json_string, true);

        }else{

            $profile = array('error'=>'curl or fopen wrapper is required');

        }



        foreach ($profile as $key => $el)
        {
            $profile[$key] = iconv('UTF-8', 'Windows-1251', $el);
        }

        if (empty($profile) || isset($profile['error'])) {
            exit;
        }

        $user_id = $this->getUserByIdentity($profile['identity']);
		$inDB = cmsDatabase::getInstance();
        if (!$user_id) {
			if ($profile['verified_email'] == '1'){
				$email_id = $inDB->get_field('cms_users', "email='{$profile['email']}' AND is_deleted=0 AND main_id=0", 'id');
				if ($email_id){
					$this->createUser($profile, 1);
					$user_id = $this->getUserByIdentity($profile['identity']);
					$sql = "UPDATE cms_users SET main_id=".$email_id.", email='".$profile['email']."' WHERE id=".$user_id;
					$inDB->query($sql);
					
				}else{
					$user_id = $this->createUser($profile);
				}	
			}else{
				$user_id = $this->createUser($profile);
			}	
        }else{
 
            // проверяем аккаунт на подключение к другому аккаунту
            
            $main_id  = $inDB->get_field('cms_users', "id={$user_id}", 'main_id');

            if ($main_id == null) {

                $profile_id  = $inDB->get_field('cms_user_profiles', "user_id={$user_id}", 'id');

                // если аккаунт не был отключен от другого и имеет свой профиль
                if ($profile_id){

                    if ($profile['verified_email'] == '1'){ //пытаемся подключить по email

                        $email_id  = $inDB->get_field('cms_users', "email='{$profile['email']}' AND is_deleted=0 AND main_id=0", 'id');

                        if ($email_id){ //аккаунт с таким емейлом найден

                            //подключаем аккаунт
                            $sql = "UPDATE cms_users SET main_id=".$email_id.", email='".$profile['email']."' WHERE id=".$user_id;
                            $inDB->query($sql);

                            //удаляем профиль
                            $sql = "DELETE FROM cms_user_profiles WHERE id=".$profile_id;
                            $inDB->query($sql);

                        }else{ //аккаунт не найден, делаем текущий аккаунт основным

                            $sql = "UPDATE cms_users SET main_id=0, email='".$profile['email']."' WHERE id=".$user_id;
                            $inDB->query($sql);

                        }

                    }

                }else{ //аккаунт был отключен от основного аккаунта и без своего профиля

                    //пытаемся подключить по email
                    if ($profile['verified_email'] == '1'){

                        $email_id = $inDB->get_field('cms_users', "email='{$profile['email']}' AND is_deleted=0 AND main_id=0", 'id');

                        if ($email_id){ //аккаунт с таким емейлом найден

                            //подключаем аккаунт
                            $sql = "UPDATE cms_users SET main_id=".$email_id.", email='".$profile['email']."' WHERE id=".$user_id;
                            $inDB->query($sql);

                        }else{//профиль с таким email не найден

                            //удаляем аккаунт
                            $sql = "DELETE FROM cms_users WHERE id=".$user_id;
                            $inDB->query($sql);

                            //и создаем заново с профилем
                            $user_id = $this->createUser($profile);

                        }

                    }else{

                        //удаляем аккаунт
                        $sql = "DELETE FROM cms_users WHERE id=".$user_id;
                        $inDB->query($sql);

                        //и создаем заново с профилем
                        $user_id = $this->createUser($profile);

                    }
                }

            }
        }

        // если пользователь уже был или успешно создан, авторизуем
		 
        if ($user_id) {
            $this->loginUser($user_id);
        }

        // если авторизация не удалась, редиректим на сообщение об ошибке
        $inCore->redirect('/auth/error.html');
        exit;

    }

    private function loginUser($user_id)
    {

        $inCore = cmsCore::getInstance();
        $inDB = cmsDatabase::getInstance();

        if (isset($_SESSION['auth_back_url'])) {
            $back = $_SESSION['auth_back_url'];
            $is_sess_back = true;
            unset($_SESSION['auth_back_url']);
        } else {
            $is_sess_back = false;
        }

        $sql = "SELECT *
                FROM cms_users
                WHERE id = '{$user_id}'";

        $result = $inDB->query($sql);

        if ($inDB->num_rows($result) == 1) {

            $current_ip = $_SERVER['REMOTE_ADDR'];
            $user = $inDB->fetch_assoc($result);

            if (!cmsUser::isBanned($user['id'])) {

                while (isset($user['main_id']) && $user['main_id'] !=0 ){

                    $main_id = $user['main_id'];
                    $sql = "SELECT *
                            FROM cms_users
                            WHERE id = '{$main_id}'";

                    $result = $inDB->query($sql);

                    if ($inDB->num_rows($result) == 1) {

                        $main_user = $inDB->fetch_assoc($result);

                        if (!cmsUser::isBanned($user['id'])) {

                            $user = $main_user;
                            $user_id = $main_id;

                        }else{

                            $inDB->query("UPDATE cms_banlist SET ip = '$current_ip' WHERE user_id = " . $user['id'] . " AND status = 1");
                            break;

                        }
                    }else{

                        break;

                    }

                }
				 
            } else {
                $inDB->query("UPDATE cms_banlist SET ip = '$current_ip' WHERE user_id = " . $user['id'] . " AND status = 1");
            }
		 	
			$inUser = cmsUser::getInstance();
			$back_url = $inUser->signInUser($user['login'], $user['password'], 1, 1);
			$inCore->redirect($back_url);
            exit;

        }

        return false;

    }

    private function createUser($profile, $sync = false)
    {

        $inCore = cmsCore::getInstance();
        $inDB = cmsDatabase::getInstance();

        $nickname = (isset($profile['nickname']) && $profile['nickname']) ? $profile['nickname'] : '';

        if (!$nickname) {
            if (isset($profile['first_name']) && $profile['first_name']) {
                $nickname = $profile['first_name'];
                if (isset($profile['last_name']) && $profile['last_name']) {
                    $nickname .= ' ' . $profile['last_name'];
                }
            }
        }

        if (!$nickname) {
            // не указано вообще ничего
            $max = $inDB->get_fields('cms_users', 'id>0', 'id', 'id DESC');
            $nickname = 'user' . ($max['id'] + 1);
        }else{

            $nickname = $this->checkNickname($nickname);
        }

        $login = $this->makeLogin($nickname);
        $pass_orig = substr(md5(session_id()), rand(0, 20), 8);
        $pass = md5($pass_orig);

        $city = $inDB->escape_string(isset($profile['city']) ? $profile['city'] : '');
        $email = $inDB->escape_string($profile['email']);

        $already = $inDB->get_fields('cms_users', "login='{$login}' AND is_deleted=0", 'login');
        $email_id = $inDB->get_field('cms_users', "email='{$email}' AND is_deleted=0", 'id');

        if ($already['login'] == $login) {
            $max = $inDB->get_fields('cms_users', 'id>0', 'id', 'id DESC');
            $login .= ($max['id'] + 1);
        }
        $login = $inDB->escape_string($login);

        $main_id = 0;

        if ($email && $email_id){

            list($mailName,$mailDomain) = explode('@', $email);
            $email = $mailName.$profile['network'].'@'.$mailDomain;

            if ($profile['verified_email'] == '1'){

                $main_id = $email_id;
                $email = $mailName.'@'.$mailDomain;

            }

        }
        

        /**
         *  День рождения 
         */
        if (isset($profile['bdate'])) 
        {
            list($day,$month,$year) = explode('.', $profile['bdate']);
            $birthdate = $year.'-'.$month.'-'.$day;
        }
        else
        {
            $birthdate = '';
        }
        $birthdate = $inDB->escape_string($birthdate);


        /**
         * Пол 
         */
        if (isset($profile['sex']) && $profile['sex']) 
        {
            $sex = $profile['sex']==2?'m':'f';
        }
        else
        {
            $sex = '';
        }
        
        
        $user_array = array(
            'login' => $login,
            'nickname' => $nickname
        );

        $sql = "INSERT INTO cms_users (login, nickname, password, regdate, ulogin_id, birthdate, email, main_id)
                VALUES ('$login', '$nickname', '$pass', NOW(), '" . $inDB->escape_string($profile["identity"]) . 
                "' , '".$birthdate."', '".$email."',".$main_id.")";

        $inDB->query($sql);

        $user_id = $inDB->get_last_id('cms_users');

        
        // создаем профиль пользователя
        if ($user_id && !$sync) {

            $sql = "INSERT INTO cms_user_profiles (user_id, city, description, showmail, showbirth, showicq, karma, allow_who, gender)
                    VALUES ('{$user_id}', '{$city}', '', '0', '0', '1', '0', 'all' ,'".$sex."')";
            $inDB->query($sql);

            $user_array['id'] = $user_id;
            cmsCore::callEvent('USER_REGISTER', $user_array);

            $this->getAvatar($profile,$user_id);

            $this->sendGreetsMessage($user_id, $login, $pass_orig);

            return $user_id;

        }

        return false;

    }

    private function uloginSync(){

        $inCore = cmsCore::getInstance();

        $token = $inCore->request('token', 'str', '');

        if (!$token) {
            exit;
        }

        $ulogin_token_url = 'http://ulogin.ru/token.php';
        $ulogin_token_url .= '?token=' . $token . '&host=' . $_SERVER['HTTP_HOST'];

        // получение профиля
        $json_string = file_get_contents($ulogin_token_url);
        $profile = json_decode($json_string, true);

        foreach ($profile as $key => $el)
        {
            $profile[$key] = iconv('UTF-8', 'Windows-1251', $el);
        }

        if (empty($profile) || isset($profile['error'])) {

            exit;
        }

        $ids = $this->getMainUserProfileByIdentity($profile['identity']);

        $current_id = $_SESSION['user']['id'];
        $login = $_SESSION['user']['login'];

        if ($ids['id'] != $current_id && $ids['main_id'] != $current_id){

            $user_id = $ids['id'] ? $ids['id'] : $this->createUser($profile, true);

            if ($user_id){

                $this->attachUser($user_id, $current_id);

            }

        }

        $inCore->redirect('/users/'.$login);

    }

    private function uloginDettach(){

        if ($_SESSION['user']['id']){

            $inCore = cmsCore::getInstance();

            $id = $inCore->request('id', 'str', '');
            $login = $_SESSION['user']['login'];

            if ($id) {

                $this->dettachUser($id);

            }

            $inCore->redirect('/users/'.$login);
        }

        $inCore->redirect('/');

    }

    private function attachUser($user_id, $main_id){

        $inDB = cmsDatabase::getInstance();
        $sql = "UPDATE cms_users SET main_id=".$main_id." WHERE id=".$user_id;
        $inDB->query($sql);

        $p_id = $inDB->get_field('cms_user_profiles', "user_id='{$user_id}'", 'id');

        if($p_id){

            $sql = "DELETE FROM cms_user_profiles WHERE id=".$p_id;
            $inDB->query($sql);

        }


    }

    private function dettachUser($user_id){

        $inDB = cmsDatabase::getInstance();
        $sql = "UPDATE cms_users SET main_id=null WHERE id=".$user_id;
        $inDB->query($sql);

    }

    private function getAttachedUsers($user_id){

        $users = array();

        if ($user_id){

            $inDB = cmsDatabase::getInstance();

            $sql = "SELECT ulogin_id, id
                    FROM cms_users
                    WHERE main_id = '{$user_id}'";

            $result = $inDB->query($sql);

            if ($inDB->num_rows($result) > 0) {

                while($user = $inDB->fetch_assoc($result)){

                    $users[] = array('identity' => $user['ulogin_id'], 'id' => $user['id']);

                    $attached = $this->getAttachedUsers($user['id']);

                    if (count($attached) > 0){

                        $users = array_merge($attached, $users);

                    }

                }
            }

        }

        return $users;

    }

    private function getUserByIdentity($identity)
    {

        $inDB = cmsDatabase::getInstance();

        return $inDB->get_field('cms_users', "ulogin_id='{$identity}'", 'id');

    }

    private function getMainUserProfileByIdentity($identity){

        $inDB = cmsDatabase::getInstance();

        return $inDB->get_fields('cms_users', "ulogin_id='{$identity}'", 'id,main_id');

    }


    private function makeLogin($string)
    {

        $string = trim($string);
        $string = mb_strtolower($string, 'cp1251');
        $string = preg_replace('/[^a-zA-Zа-яА-Я0-9]/i', '', $string);

        while (strstr($string, '--')) {
            $string = str_replace('--', '-', $string);
        }

        $ru_en = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z',
            'и' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's',
            'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y',
            'ь' => '', 'э' => 'ye', 'ю' => 'yu', 'я' => 'ja'
        );

        foreach ($ru_en as $ru => $en) {
            $string = preg_replace('/([' . $ru . ']+)/i', $en, $string);
        }

        if (!$string) {
            
			$inDB = cmsDatabase::getInstance();
		    $max = $inDB->get_fields('cms_users', 'id>0', 'id', 'id DESC');
            $string = 'user'. ($max['id'] + 1);
        }

        return $string;

    }

    private function checkNickname($string){

        $inDB = cmsDatabase::getInstance();

        $nickname = mb_strtolower($string, 'cp1251');
        $exist = $inDB->get_fields('cms_users', "LOWER(nickname)='{$nickname}' AND is_deleted=0", 'nickname');
        if (isset($exist['nickname'])){
            $max = $inDB->get_fields('cms_users', 'id>0', 'id', 'id DESC');
            $string.= ($max['id'] + 1);
        }

        return $string;
    }


    private function sendGreetsMessage($user_id, $login, $pass)
    {

        $msg = "<div>Здравствуйте. Благодарим за регистрацию на нашем сайте.</div>";
        $msg .= "<div>Ваши реквизиты для входа на сайт:</div>";
        $msg .= "<strong>Логин:</strong> {$login}<br />";
        $msg .= "<strong>Пароль:</strong> {$pass}<br />";

        $msg .= '<div>Вы можете сменить никнейм, пароль и email в <a href="/users/' . $user_id . '/editprofile.html">настройках</a> вашего профиля</div>';

        cmsUser::sendMessage(USER_MASSMAIL, $user_id, $msg);

        return true;

    }

    private function getAvatar($profile,$id)
    {
        $inCore = cmsCore::getInstance();
        $inDB = cmsDatabase::getInstance();

        $profile_photo = $profile['photo'];
        if (isset($profile['photo_big']) &&  $profile['photo_big']!='http://ulogin.ru/img/photo_big.png')
            $profile_photo = $profile['photo_big'];


        if (!$profile_photo) return;

        $cfg = $inCore->loadComponentConfig('users');

        $tmpName = rand(100000,10000000).'.'.strtolower(substr($profile_photo,-3));


        $inCore->includeGraphics();

        $filecontents = file_get_contents($profile_photo);
        $uploaddir = PATH . '/images/users/avatars/';
        file_put_contents($uploaddir.$tmpName,$filecontents);


        $realfile = $tmpName;


        $path_parts = pathinfo($realfile);
        $ext = strtolower($path_parts['extension']);
        $realfile = md5($realfile . '-' . time()) . '.' . $ext;

            $filename = md5($realfile . '-' . $id . '-' . time()) . '.jpg';
            $uploadfile = $uploaddir . rand(100000,10000000).'.'.$path_parts['extension'];
            $uploadavatar = $uploaddir . $filename;
            $uploadthumb = $uploaddir . 'small/' . $filename;
            $source = $uploaddir.$tmpName;

        if (copy($source,$uploadfile)) {

            $sql = "SELECT imageurl FROM cms_user_profiles WHERE user_id = '$id'";
            $result = $inDB->query($sql);
            if ($inDB->num_rows($result)) {
                $old = $inDB->fetch_assoc($result);
                if ($old['imageurl'] && $old['imageurl'] != 'nopic.jpg') {
                    @unlink(PATH . '/images/users/avatars/' . $old['imageurl']);
                    @unlink(PATH . '/images/users/avatars/small/' . $old['imageurl']);
                }
            }

            //CREATE THUMBNAIL
            if (isset($cfg['smallw'])) {
                $smallw = $cfg['smallw'];
            } else {
                $smallw = 64;
            }
            if (isset($cfg['medw'])) {
                $medw = $cfg['medw'];
            } else {
                $medw = 200;
            }
            if (isset($cfg['medh'])) {
                $medh = $cfg['medh'];
            } else {
                $medh = 200;
            }

            @img_resize($uploadfile, $uploadavatar, $medw, $medh);
            @img_resize($uploadfile, $uploadthumb, $smallw, $smallw);

            //DELETE ORIGINAL
            @unlink($uploadfile);

            //MODIFY PROFILE
            $sql = "UPDATE cms_user_profiles
					SET imageurl = '$filename'
					WHERE user_id = '$id'
					LIMIT 1";
            $inDB->query($sql);

        }

        @unlink($source);
    }

}
