<?php
	
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

/**
 * This is the main entry point for all requests.
 * 
 * To add a page
 * 	1) Create a template which is an html file in the templates folder.
 * 	2) Create a controller which is a class that extends the base Page class.
 * 	3) Create an entry for the page in the config/pages.json file.
 */

require_once( 'config/config.php' );

// *** Parse the request URL and load the appropriate page ***
if( is_file( 'config/pages.json' ) ) {
	$pages = json_decode( file_get_contents( 'config/pages.json' ), true );
	
	$reqPage = $_SERVER[ 'REQUEST_URI' ];
	$query = '?' . $_SERVER[ 'QUERY_STRING' ];
	$reqPage = str_replace( $query, '', $reqPage );
	if( substr( $reqPage, -1 ) == '/' ) {
		$reqPage = substr( $reqPage, 0, strlen( $reqPage ) - 1 );
	}
	
	if( $reqPage == '' ) {
		// If no page was requested redirect to the admin login
		header( 'Location:http://' . DOMAIN . '/login' );
		exit();
	}
	
	foreach( $pages as $page ) {
		if( $page[ 'url' ] == $reqPage ) {
			if( !empty( $page[ 'redirect' ] ) ) {
				header( 'Location:' . $page[ 'redirect' ] );
				exit();
			}
			
			if( !empty( $page[ 'controller' ] ) ) {
				if( !is_file( 'controllers/' . $page[ 'controller' ] ) ) {
					die( "Controller 'controllers/{$page[ 'controller' ]} for page '{$page->url}' could not be found." );
				}
				require_once( 'controllers/' . $page[ 'controller' ] );
				$className = '';
				if( strstr( $page[ 'controller' ], '/' ) ) {
					$className = str_replace( '.php', '', substr( $page[ 'controller' ], strrpos( $page[ 'controller' ], '/' ) + 1 ) );
				}
				else {
					$className = str_replace( '.php', '', $page[ 'controller' ] );
				}
				
				if( isset( $page[ 'template' ] ) ) {
					$controller = new $className( $page[ 'template' ] );
				}
				else {
					// The controller does not have a template (most likely an API controller)
					$controller = new $className();
				}
			}
			
			// Call the display method in the controller if there is one
			if( method_exists( $controller, 'display' ) ) {
				$controller->display();
			}
			
			exit();
		}
	}
	
	// If we got here then the page is not defined in pages.json
	die( "Error 404: Page not found." );
}
else {
	die( "Site Error: Missing config/pages.json" );
}