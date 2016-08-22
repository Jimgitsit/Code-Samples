<?php

require_once( 'Page.php' );
require_once( 'classes/SessionCart.php' );

class GridPage extends Page {
	public function __construct( $template ) {
		parent::__construct( $template );
	}
}