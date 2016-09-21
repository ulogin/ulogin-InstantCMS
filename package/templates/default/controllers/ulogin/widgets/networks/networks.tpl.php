<div class="ulogin_form">
<?php
	$this->addCSS( 'templates/default/css/ulogin.css' );
	$this->addCSS( 'https://ulogin.ru/css/providers.css' );
?>

	<?php

		$ulogin_accounts = '';
		if (is_array( $networks )) {
			foreach ( $networks as $network ) {
				$ulogin_accounts .= "<div data-ulogin-network='$network' " .
				                    "class=\"ulogin_provider big_provider " . $network . "_big\"></div>";
			}
		}
		$ulogin_accounts = '<div class="ulogin_accounts">' .
		                   $ulogin_accounts .
		                   '</div><div style="clear:both"></div>';
		echo $ulogin_accounts;

	?>

</div>