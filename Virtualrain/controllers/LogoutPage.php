<?php

require_once( 'Page.php' );

class LogoutPage extends Page {
	public function __construct( $template ) {
		$this->startSession();
		$this->logout();
	}

	protected function logout() {
		$url = 'login';
		
		// For backwards compat, before distributer url rewrite rule.
		if(LEGACY_HOST_NAME	&& isset($_SESSION['dist_id'])) {
			$url .= '?dist_id=' . $_SESSION[ 'dist_id' ];
		}
			
			// Unset all of the session variables.
		$_SESSION = array();
		
		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (ini_get("session.use_cookies")) {
			$name = session_name();
			$params = session_get_cookie_params();
			setcookie($name, '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		}
		
		// Finally, destroy the session.
		session_destroy();
		
		header( "Location:$url" );
		exit();
	}
}