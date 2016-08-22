<?php

require_once( 'Page.php' );
require_once( 'classes/SessionCart.php' );

class ThankyouPage extends Page {
	public function __construct( $template ) {
		parent::__construct( $template );
		
		$db = new SessionCart($_SESSION[ 'user' ]['id']);
		$db->emptyCart();
			
	}
	
	
	
}