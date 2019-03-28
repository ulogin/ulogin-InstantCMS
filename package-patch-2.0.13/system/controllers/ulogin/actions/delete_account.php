<?php

class actionUloginDeleteAccount extends cmsAction {

	public function run() {
		if(!cmsUser::isLogged()) {exit;}

		$user_id = cmsUser::getInstance()->id;
		$network = $this->request->has('network') ? $this->request->get('network') : '';

		if ($user_id > 0 && !empty($network)) {
			try {
				$u_users = $this->model->getUloginUsers(array('user_id' => $user_id, 'network' => $network));
				if (!empty($u_users) && is_array($u_users)) {
					foreach ( $u_users as $u_user ) {
						$this->model->deleteUloginUser( $u_user['id'] );
					}
				}
				echo json_encode(array(
					'title' => '',
					'msg' => "Удаление аккаунта $network успешно выполнено",
					'answerType' => 'success'
				));
				exit;
			} catch (Exception $e) {
				echo json_encode(array(
					'title' => 'Ошибка при удалении аккаунта',
					'msg' => 'Exception: ' . $e->getMessage(),
					'answerType' => 'error'
				));
				exit;
			}
		}
		exit;
	}
}
