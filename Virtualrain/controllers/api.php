<?php

class api {
	public function __construct() {
		
		
		echo( $this->handleRest() );
	}
	
	private function checkAuthKey() {
		
	}
	
	private function handleRest() {
		if( !empty( $_GET ) && isset( $_GET[ 'action' ] ) ) {
			switch( $_GET[ 'action' ] ) {
				case 'test' :{
					return "test good";
					break;
				}
				case 'login': {
					
					break;
				}
				case 'logout': {
					
					break;
				}
				case 'register': {
					
					break;
				}
				case 'reset_pw': {
					
					break;
				}
			}
		}
	}
}