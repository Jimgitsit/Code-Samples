<?php

require_once( 'Page.php' );
require_once( 'VirtualRainDB.php' );
require_once( 'Util.php' );

class RegisterPage extends Page {
	public function __construct( $template ) {
		parent::__construct( $template );
		
		// Must have a distributer id
		if( empty( $_SESSION[ 'dist_id' ] ) ) {
			header( "Location:login" );
			exit();
		}
		
		$this->smarty->assign( 'distId', $_SESSION[ 'dist_id' ] );
		
		$this->handlePost();
	}
	
	protected function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			switch( $_POST[ 'action' ] ) {
				case 'register': {
					$db = new VirtualRainDB();
					
					// Validation
					if( !isset( $_POST[ 'company_name' ] ) || trim( $_POST[ 'company_name' ] ) == '' ||
						!isset( $_POST[ 'first_name' ] ) || trim( $_POST[ 'first_name' ] ) == '' || 
						!isset( $_POST[ 'last_name' ] ) || trim( $_POST[ 'last_name' ] ) == '' || 
						!isset( $_POST[ 'email' ] ) || trim( $_POST[ 'email' ] ) == '' || 
						!isset( $_POST[ 'password' ] ) || trim( $_POST[ 'password' ] ) == '' || 
						!isset( $_POST[ 'password2' ] ) || trim( $_POST[ 'password2' ] ) == '' || 
						!isset( $_POST[ 'cell_phone' ] ) || trim( $_POST[ 'cell_phone' ] ) == '' ) 
					{
						$this->smarty->assign( 'errMsg', 'Missing required information.' );
						break;
					}
					
					$email = trim( $_POST[ 'email' ] );
					if( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
						$this->smarty->assign( 'errMsg', 'Invalid email address.' );
						break;
					}
					
					$pass = trim( $_POST[ 'password' ] );
					$pass2 = trim( $_POST[ 'password2' ] );
					
					if( strlen( $pass ) < 6 ) {
						$this->smarty->assign( 'errMsg', 'Password must be at least 6 characters.' );
						break;
					}
					
					if( $pass != $pass2 ) {
						$this->smarty->assign( 'errMsg', 'Passwords do not match.' );
						break;
					}
					
					// Check if User exists
					$exists = $db->distUserExists( $_POST[ 'dist_id' ], $_POST[ 'email' ] );
					if( $exists == true ) {
						$this->smarty->assign( 'errMsg', 'The email address is already in use.' );
						break;
					}
					
					// Set show pricing from dist prefs
					$distributor = $db->getDistributer( $_POST[ 'dist_id' ] );
					$showPricing = $distributor['show_pricing_default'];
					
					// Create User
					$user = $db->createUser( $email, $pass, $_POST[ 'dist_id' ], 
											 trim( $_POST[ 'first_name' ] ), trim( $_POST[ 'last_name' ] ), 
											 trim( $_POST[ 'cell_phone' ] ), trim( $_POST[ 'account_number' ] ), 
											 trim( $_POST['company_name'] ), $showPricing );
					if( $user == false ) {
						$this->smarty->assign( 'errMsg', 'Error Creating User.' );
						break;
					}
					
					// Show the success message
					$this->smarty->assign( 'userCreated', true );
					
					//Email the Distributor
					$email = $distributor['email'];
					$subject = 'New user registration';
					
					$html = file_get_contents( 'templates/admin/res/new_user_email.html' );
					$text  = file_get_contents( 'templates/admin/res/new_user_email.txt' );
					
					$domain = DOMAIN;
					$protocol = PROTOCOL;
					
					
					
					$link = PROTOCOL . '://' . DOMAIN . '/admin/login';
					
					$html = str_replace( '{link}', $link, $html );
					$html = str_replace( '{companyName}', $user['company_name'], $html );
					$html = str_replace( '{userName}', $user['first_name'] . ' ' . $user['last_name'], $html );
					$html = str_replace( '{userEmail}', $user['email'], $html );
					$html = str_replace( '{phoneNumber}', $user['cell_phone'], $html );
					$html = str_replace( '{accountNumber}', $user['account_num'], $html );
					
					$text = str_replace( '{link}', $link, $text );
					$text = str_replace( '{companyName}', $user['company_name'], $text );
					$text = str_replace( '{userName}', $user['first_name'] . ' ' . $user['last_name'], $text );
					$text = str_replace( '{userEmail}', $user['email'], $text );
					$text = str_replace( '{phoneNumber}', $user['cell_phone'], $text );
					$text = str_replace( '{accountNumber}', $user['account_num'], $text );
					
					$extraAdmins = $db->getExtraAdminsById($_SESSION[ 'dist_id' ]);
					$cc = "";
					
					if($extraAdmins != null){
						foreach($extraAdmins as $admin){
							$emailSettings = $admin['email_setting'];
							if( $emailSettings == "all_emails" || $emailSettings == "new_user_emails_only" ){
								$cc .= " " . $admin['email'];
							}
						}
					}
					
					Util::sendEmail( $email, $cc, $subject, $html, $text, null, $distributor['company_name'] );
					
					break;
				}
			}
		}
	}
}








