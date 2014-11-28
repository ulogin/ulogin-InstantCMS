<?php

class formWidgetUloginPanelOptions extends cmsForm {

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

				)
			),

		);

	}

}
