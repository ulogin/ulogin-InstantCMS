<?php

class formUloginOptions extends cmsForm {

    public function init() {

	    $model = cmsCore::getModel('ulogin');
	    $group_id = $model->getUloginGroupId();

        return array(

            array(
                'type' => 'fieldset',
                'childs' => array(

                    new fieldString('uloginid', array(
                        'title' => 'Значение поля <b>uLogin ID</b>',
                        'default' => '',
                    )),

	                new fieldList('group_id', array(
			                'title' => 'Группа для новых пользователей',
			                'default' => $group_id,
			                'generator' => function () {
				                $users_model = cmsCore::getModel('users');
				                $groups_list = $users_model->getGroups();
				                $groups = array_collection_to_list($groups_list, 'id', 'title');
				                return $groups;
			                }
		                )
	                ),

                )
            ),

        );

    }

}
