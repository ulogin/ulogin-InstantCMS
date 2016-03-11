<?php

if(!class_exists('onUloginUsersProfileView')) {
	class onUloginUsersProfileView extends cmsAction
	{

		public function run($profile)
		{

			$user = cmsUser::getInstance();

			if (!$user->is_logged) {
				ulogin::$is_profile = false;
				return $profile;
			}

			if ($user->id == $profile['id']) {
				ulogin::$is_profile = true;
			} else {
				ulogin::$is_profile = false;
			}

			return $profile;

		}

	}
}