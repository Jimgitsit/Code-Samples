<?php

require_once( 'Page.php' );

class FinalizePage extends Page {
	public function __construct( $template ) {
		parent::__construct( $template );
		
		$this->handlePost();
	}
	
	protected function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ]) ) {
			switch( $_POST[ 'action' ] ) {
				case 'select_pickup_location': {
					if( isset( $_POST[ 'po_num' ] ) ) {
						$_SESSION[ 'po_num' ] = $_POST[ 'po_num' ];
					}
					
					header( "Location: /location" );
					exit();
				}
				case 'select_shipping_location': {
					if( isset( $_POST[ 'po_num' ] ) ) {
						$_SESSION[ 'po_num' ] = $_POST[ 'po_num' ];
					}
					
					header( "Location: /shipping-locations" );
					exit();
				}
			}
		}
	}
}