<div class="ulogin_form">

	<?php $this->addJS( 'http://ulogin.ru/js/ulogin.js' ); ?>
	<?php $this->addJS( 'templates/default/js/ulogin.js' ); ?>
	<?php $this->addCSS( 'templates/default/css/ulogin.css' ); ?>
	<?php $this->addCSS( 'http://ulogin.ru/css/providers.css' ); ?>

	<div id="<?php html($id)?>" data-ulogin="display=panel;fields=first_name,last_name,email,photo,photo_big;providers=vkontakte,odnoklassniki,mailru,facebook;hidden=other;redirect_uri=<?php html($redirect);?>;callback=<?php html($callback);?>"></div>

</div>