<?php

require_once( 'Page.php' );

class AccountPage extends Page {
	public function __construct( $template ) {
		parent::__construct( $template );
		$this->handlePost();
		
		$db = new VirtualRainDB();
		$accountInfo = $db->getUserById( $_SESSION['user']['id'] );
		$this->smarty->assign( 'account_info', $accountInfo );
		
	}
	
	protected function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			switch( $_POST[ 'action' ] ) {
				case 'save': {
					$db = new VirtualRainDB();
					//Validate
					if( !isset( $_POST[ 'company_name' ] ) || trim( $_POST[ 'company_name' ] ) == '' ||
						!isset( $_POST[ 'first_name' ] ) || trim( $_POST[ 'first_name' ] ) == '' || 
						!isset( $_POST[ 'last_name' ] ) || trim( $_POST[ 'last_name' ] ) == '' || 
						!isset( $_POST[ 'email' ] ) || trim( $_POST[ 'email' ] ) == '' || 
						!isset( $_POST[ 'password' ] ) || trim( $_POST[ 'password' ] ) == '' || 
						!isset( $_POST[ 'password2' ] ) || trim( $_POST[ 'password2' ] ) == '' || 
						!isset( $_POST[ 'cell_phone' ] ) || trim( $_POST[ 'cell_phone' ] ) == '' ) 
					{
						$this->smarty->assign( 'errMsg', 'Missing requried information.' );
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
					$user = $db->getUserById( $_SESSION['user']['id'] );
					if( $_POST[ 'email' ] != $user['email'] )	{
						$exists = $db->userExists( $_POST[ 'email' ] );
						if( $exists == true ) {
							$this->smarty->assign( 'errMsg', 'Email is already taken.' );
							break;
						}
					}
					
					//Update User
					$user = $db->getUserById( $_SESSION['user']['id'] );
					
					$user['email'] = trim($_POST['email']);
					$user['first_name'] = trim($_POST['first_name']);
					$user['last_name'] = trim($_POST['last_name']);
					$user['cell_phone'] = trim($_POST['cell_phone']);
					$user['company_name'] = trim($_POST['company_name']);
					if(isset($_POST['status_emails']) && $_POST['status_emails'] == 'on') {
						$user['status_emails'] = 1;
					}
					else {
						$user['status_emails'] = 0;
					}
					
					if( $_POST['password'] != '7DVX9lFUC3' ){
						$salty = $db->gensalt( 5 );
						$newpass = md5( md5( trim($_POST['password']) ) . md5( $salty ) );
						$user['salt'] = $salty;
						$user['password'] = $newpass;
					}
					$this->smarty->assign( 'saved', 1 );
					$db->updateUser( $user );
					
					break;
				}
			}
		}
	}
}