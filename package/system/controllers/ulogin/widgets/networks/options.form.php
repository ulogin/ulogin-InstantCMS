<?php

class formWidgetUloginNetworksOptions extends cmsForm {

	public function init() {

		//cmsCore::loadControllerLanguage('ulogin');

		return array(

			array(
				'type' => 'fieldset',
				'title' => LANG_OPTIONS,
				'childs' => array(

					new fieldString('options:uloginid', array(
							'title' => 'Значение поля <b>uLogin ID</b>',
							'hint' => 'Заполните это поле, если хотите, чтобы значение было отличным от заданного в опциях компонента uLogin',
							'default' => '',
						)
					),

					new fieldString('options:add_str', array(
							'title' => 'Текст для добавления аккаунта',
							'default' => 'Привяжите аккаунты соцсетей:',
						)
					),

					new fieldString('options:delete_str', array(
							'title' => 'Текст для удаления аккаунта',
							'default' => 'Удалите привязку к аккаунту, кликнув по значку:',
						)
					),

					new fieldCheckbox('options:editable', array(
							'title' => 'Возможность добавлять/удалять аккаунты соцсетей',
							'default' => true,
						)
					),

					new fieldCheckbox('options:in_profile_only', array(
							'title' => 'Отображать виджет только в профиле пользователя',
							'default' => true,
						)
					),

				)
			),

		);

	}

}
