<div class="ulogin_form">

	<?php $this->addJS( 'https://ulogin.ru/js/ulogin.js', null, false); ?>
	<?php $this->addCSS( 'https://ulogin.ru/css/providers.css', false); ?>

	<?php $this->addJS( 'templates/default/js/ulogin.js' ); ?>
	<?php $this->addCSS( 'templates/default/css/ulogin.css' ); ?>

	<div id="<?php html($id)?>" data-uloginid="<?php html($uloginid);?>" data-ulogin="redirect_uri=<?php html($redirect);?>;callback=<?php html($callback);?>"></div>

</div>