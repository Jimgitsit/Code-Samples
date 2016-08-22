<?php

require_once( 'Page.php' );
require_once( 'VirtualRainDB.php' );

class LoginPage extends Page {
	public function __construct( $template ) {
		parent::__construct( $template );
		$this->smarty->assign( 'errMsg', '' );
		$this->checkLoggedIn();
		$this->handlePost();
		$this->smarty->assign( 'signup', false );
		
		$dist = null;
		
		// Save the distributer id in the session
		if( !empty( $_GET[ 'dist_id' ] ) ) {
			$_SESSION[ 'dist_id' ] = $_GET[ 'dist_id' ];
			
			// Load the graphic for the given distributer
			$db = new VirtualRainDB();
			$dist = $db->getDistributer( $_SESSION[ 'dist_id' ] );
			$this->smarty->assign( 'distId', $dist[ 'id' ] );
		}
		else if( isset( $_GET[ 't' ] ) ) {
			// Password reset via token
			$db = new VirtualRainDB();
			$user = $db->getUserFromToken( $_GET[ 't' ] );
			if( $user == null ) {
				// Redirect to login page (without query string, so normal login)
				header( 'Location:login' );
				exit();
			}
			
			$this->smarty->assign( 'signup', true );
			$this->smarty->assign( 'current_user', $user );
			
			$dist = $db->getDistributer( $user[ 'dist_id' ] );
		}
		else if (!empty($_SESSION['dist_id'])) {
			// Load the graphic for the given distributer
			$db = new VirtualRainDB();
			$dist = $db->getDistributer( $_SESSION[ 'dist_id' ] );
			$this->smarty->assign( 'distId', $dist[ 'id' ] );
		}
		
		$logo = '';
		$name = '';
		if( $dist != null ) {
			if( !empty( $dist[ 'company_name' ] ) ) {
				$name = $dist[ 'company_name' ];
			}
			
			if( !empty( $dist[ 'logo' ] ) ) {
				$logo = 'dist/' . $dist[ 'dir' ] . '/' . $dist[ 'logo' ];
			}
		}
		else {
			$logo = '../templates/img/vr_logo_clear_transparent.jpg';
		}
		$this->smarty->assign( 'distName', $name );
		$this->smarty->assign( 'distLogo', $logo );
		
		if(isset($_COOKIE['user_email'])) {
			$this->smarty->assign('userEmail', $_COOKIE['user_email']);
		}
	}
	
	protected function checkLoggedIn() {
		if( !empty( $_SESSION[ 'user' ] ) && !empty( $_SESSION[ 'user' ][ 'id' ] ) ) {
			header( 'Location:categories' );
			exit();
		}
	}
	
	protected function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			switch( $_POST[ 'action' ] ) {
				case 'login': {
					// Save the email address as a cookie set to expire after 1 year
					setcookie("user_email", $_POST[ 'email' ], time()+60*60*24*365);

					// Save the client time zone
					if (!empty($_POST['client_timezone'])) {
						$_SESSION['client_timezone'] = $_POST['client_timezone'];
					}
					else {
						// If we can't determine the client's time zone then use the server's
						$_SESSION['client_timezone'] = ini_get('date.timezone');
					}
					
					$db = new VirtualRainDB();
					$this->smarty->assign('email', $_POST[ 'email' ]);
					$distId = null;
					if(isset($_GET['dist_id'])) {
						$distId = $_GET['dist_id'];
					}
					$user = $db->getUser( $_POST[ 'email' ], $_POST[ 'password' ], $distId );
					if( $user == false ) {
						$this->smarty->assign( 'errMsg', 'Email not found or invalid password' );
						break;
					}
					$db->updateUserLastLogin($user['id']);
								  
					if( isset( $_SESSION[ 'dist_id' ] ) ) {
						// If we have a dist_id from the session then check it against the user's dist_id
						if( $user[ 'dist_id' ] == $_SESSION[ 'dist_id' ] ) {
							// Check if the user account is active
							if( $user[ 'status' ] == 1 ) {
								// Good to go so save the user to the session
								$_SESSION[ 'user' ] = $user;
								if($user['branch_id'] != null && $user['branch_id'] != 0) {
									$_SESSION['branch'] = $db->getBranch($user['branch_id']);
								}
							
								// Redirect to the categories page
								header( 'Location:categories?doreg=true' );
								exit();
							}
							else {
								$this->smarty->assign( 'errMsg', 'Your account is inactive. Please contact your distributer.' );
							}
						}
						else {
							// This user does not match the distributer
							$this->smarty->assign( 'errMsg', 'Email not found or invalid password' );
						}
					}
					else {
						// Set the dist_id in the session from the user's dist_id
						$_SESSION[ 'dist_id' ] = $user[ 'dist_id' ];
						if( $user[ 'status' ] == 1 ) {
							// Save the user to the session
							$_SESSION[ 'user' ] = $user;
							if($user['branch_id'] != null && $user['branch_id'] != 0) {
								$_SESSION['branch'] = $db->getBranch($user['branch_id']);
							}
						
							// Redirect to the categories page
							header( 'Location:categories?doreg=true' );
							exit();
						}
						else {
							$this->smarty->assign( 'errMsg', 'Your account is inactive. Please contact your distributer.' );
						}
					}
					
					break;
				}
				case 'signup': {
					$pw1 = trim( $_POST[ 'pass1' ] );
					$pw2 = trim( $_POST[ 'pass2' ] );
					
					if( ( $pw1 == '' || $pw2 == '' ) || $pw1 != $pw2 ) {
						$this->smarty->assign( 'errMsg', 'Empty password or passwords do not match.' );
					}
					
					if( strlen( $pw1 ) < 8 ) {
						$this->smarty->assign( 'errMsg', 'Your password must be at least 8 characters in length.' );
					}
					
					$db = new VirtualRainDB();
					// TODO: Need to get user by email AND dist id
					$userId = $db->getUserIdByEmail( $_POST[ 'email' ] );
					
					$success = $db->setUserPassword( $userId, $pw1 );
					if( $success ) {
						$user = $db->getUserById( $userId );
						$_SESSION[ 'user' ] = $user;
						$_SESSION[ 'dist_id' ] = $user[ 'dist_id' ];
						if($user['branch_id'] != null && $user['branch_id'] != 0) {
							$_SESSION['branch'] = $db->getBranch($user['branch_id']);
						}
						header('Location:/categories');
					}
					
					break;
				}
			}
		}
	}
}