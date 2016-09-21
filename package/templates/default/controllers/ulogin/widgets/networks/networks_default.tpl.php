<div class="ulogin_form">
<?php
	$this->addJS( 'https://ulogin.ru/js/ulogin.js' );
	$this->addJS( 'templates/default/js/ulogin.js' );
	$this->addCSS( 'templates/default/css/ulogin.css' );
	$this->addCSS( 'https://ulogin.ru/css/providers.css' );
?>

	<?php if ($add_str) { ?>
		<span class="add_str hint"><?php html($add_str);?></span>
	<?php }?>

	<div id="<?php html($id)?>" data-ulogin="display=panel;fields=first_name,last_name,email,photo,photo_big;providers=vkontakte,odnoklassniki,mailru,facebook;hidden=other;redirect_uri=<?php html($redirect);?>;callback=<?php html($callback);?>"></div>

	<?php if ($delete_str) { ?>
		<span class="delete_str hint"><?php html($delete_str);?></span>
	<?php }?>


	<?php

		$ulogin_accounts = '';
		if (is_array( $networks )) {
			foreach ( $networks as $network ) {
				$ulogin_accounts .= "<div data-ulogin-network='$network' " .
				                    "class=\"ulogin_provider big_provider " . $network . "_big\" " .
				                    "onclick=\"uloginDeleteAccount('$network')\"" .
				                    "></div>";
			}
		}
		$ulogin_accounts = '<div class="ulogin_accounts can_delete">' .
		                   $ulogin_accounts .
		                   '</div><div style="clear:both"></div>';
		echo $ulogin_accounts;

	?>

</div>