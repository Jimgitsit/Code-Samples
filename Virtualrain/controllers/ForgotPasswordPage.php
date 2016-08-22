<?php

require_once( 'Page.php' );
require_once( 'VirtualRainDB.php' );
require_once( 'Util.php' );

class ForgotPasswordPage extends Page {
	public function __construct( $template ) {
		parent::__construct( $template );
		$this->handlePost();
		
		if(isset($_SESSION['dist_id'])) {
			$this->smarty->assign('distId', $_SESSION['dist_id']);
		}
	}

	public function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			switch( $_POST[ 'action' ] ) {
				case 'reset_pw': {
					
					$db = new VirtualRainDB();

					$pw = md5( uniqid( $_POST[ 'email' ], true ) );
					$token = md5( uniqid( $_POST[ 'email' ], true ) );
					
					// Validate email
					if( !filter_var( $_POST[ 'email' ], FILTER_VALIDATE_EMAIL ) ) {
						$this->smarty->assign( 'errMsg', 'Invalid email address.' );
						break;
					}
					
					$distId = null;
					if(isset($_SESSION['dist_id'])) {
						$distId = $_SESSION['dist_id'];
					}
					else {
						$user = $db->getUserByEmail($_POST[ 'email' ]);
						$_SESSION['dist_id'] = $user['dist_id'];
					}
					
					$userId = $db->getUserIdByEmail( $_POST[ 'email' ], $distId );
					if($userId === false) {
						$this->smarty->assign( "error", "Distributor " . $distId . "doesn't have any record of " . $_POST[ 'email' ] . " in the database" );
						break;
					}

					$db->setUserPassword( $userId, $pw, $token );

					$sent = $this->sendUserPwResetEmail( $_POST[ 'email' ], $token );
					if( $sent == false ) {
						$this->smarty->assign( "error", "An error occurred sending the email. Please try again. If the problem persists please contact the site admin." );
						break;
					}
					$userEmail = $_POST[ 'email' ];
					$this->smarty->assign( "success", "An email has been sent to $userEmail with further instructions. Please check your spam folder if you do not see an email within 15 minutes." );

					break;
				}
			}
		}
	}


	private function sendUserPwResetEmail( $email, $token ) {
		$domain = DOMAIN;
		$protocol = PROTOCOL;
		
		$db = new VirtualRainDB();
		
		$distributor = $db->getDistributer($_SESSION['dist_id']);

		$subject = $distributor['company_name'] . " - Password Reset";

		$html = file_get_contents( 'templates/admin/res/pwreset_user_email.html' );
		$text  = file_get_contents( 'templates/admin/res/pwreset_user_email.txt' );

		$html = str_replace( '{link}', "$protocol://vrmobileapp.com/login?t=$token", $html );
		$html = str_replace( '{distName}', $distributor['company_name'], $html );
		
		$text = str_replace( '{link}', "$protocol://$domain/login?t=$token", $text );
		$text = str_replace( '{distName}', $distributor['company_name'], $text );

		return Util::sendEmail( $email, null, $subject, $html, $text, null, $distributor['company_name'] );
	}
}