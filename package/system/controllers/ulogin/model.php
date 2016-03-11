<?php

class modelUlogin extends cmsModel {

	public function checkUloginUserId ($u_id) {
		$users_model = cmsCore::getModel('users');
		$users_model->filterEqual('id', $u_id);
		$ids = $users_model->getUsersIds();

		if (!$ids) { return false; }
		else { return true; }
	}


//--------------------
	public function getUser ($fields = array()) {
		if (is_array($fields) && !empty($fields)) {
			$users = cmsCore::getModel('users');
			foreach ($fields as $field => $value) {
				$users->filterEqual($field, $value);
			}
			return $users->getUser();
		}
		return false;
	}


//--------------------
	public function getUloginUserItem ($fields = array()) {
		if (is_array($fields) && !empty($fields)) {
			$this->resetFilters();
			foreach ($fields as $field => $value) {
				$this->filterEqual($field, $value);
			}
			return $this->getItem('ulogin_user');
		}
		return false;
	}


//--------------------
	public function getUloginUsers ($fields = array()) {
		if (is_array($fields) && !empty($fields)) {
			$this->resetFilters();
			foreach ($fields as $field => $value) {
				$this->filterEqual($field, $value);
			};
			return $this->get('ulogin_user');
		}
		return false;

	}


//--------------------
	public function getUloginUserNetworks ($user_id = 0) {
		$this->resetFilters();
		$this->filterEqual('user_id', $user_id);
		$q = $this->get('ulogin_user');

		$result = array();

		if(is_array($q))
			foreach ($q as $q0) {
				$result[] = $q0['network'];
			}

		return $result;
	}


//--------------------
	public function deleteUloginUser ($user_id = 0) {
		$this->resetFilters();
		return $this->delete('ulogin_user', $user_id);
	}


//--------------------
	public function addUloginAccount ($data = array()) {
		return $this->insert('ulogin_user', $data);
	}


//--------------------
	public function getCityId ($city = '', $country = '') {
		$city_id = 0;
		$country_id = 0;

		if ($country) {
			$this->resetFilters();
			$this->filterEqual('name', trim($country));
			$country_id = $this->getFieldFiltered('geo_countries', 'id');
		}

		if ($city) {
			$this->resetFilters();
			if ($country_id > 0) $this->filterEqual('country_id', $country_id);
			$this->filterEqual('name', trim($city));
			$city_id = $this->getFieldFiltered('geo_cities', 'id');
		}

		return $city_id;
	}


//--------------------
	public function getUloginGroupId() {

		$this->resetFilters();
		$this->filterEqual('name', 'ulogin');
		return $this->getFieldFiltered('{users}_groups', 'id');

	}

}