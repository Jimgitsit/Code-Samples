<?php

require_once( 'AdminPage.php' );
require_once( 'VirtualRainDB.php' );
require_once( 'Util.php' );

class DistributersEditPage extends AdminPage {
	public function __construct( $template ) {
		parent::__construct( $template, 'Distributers' );

		$this->handlePost();

		if( empty( $_GET[ 'id' ] ) ) {
			$this->redirect( '../distributers' );
		}

		if( $_GET[ 'id' ] !== 'new' ) {
			$db = new VirtualRainDB();
			$dist = $db->getDistributer( $_GET[ 'id' ] );
			if( $dist == null ) {
				$this->redirect( '../distributers' );
			}
			$this->smarty->assign( 'dist', $dist );
		}
	}

	public function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			switch( $_POST[ 'action' ] ) {
				case 'save': {
					$distributer = array(
							'company_name' 	=> trim( $_POST[ 'company_name' ] ),
							'email' 		=> trim( $_POST[ 'email' ] ),
							'contact_name' 	=> trim( $_POST[ 'contact_name' ] ),
							'phone' 		=> trim( $_POST[ 'phone' ] ),
							'address' 		=> trim( $_POST[ 'address' ] ),
							'city' 			=> trim( $_POST[ 'city' ] ),
							'state' 		=> trim( $_POST[ 'state' ] ),
							'zip' 			=> trim( $_POST[ 'zip' ] ),
							'status' 		=> 0
					);

					// Validate empty fields
					if( $distributer[ 'company_name' ] == '' ||
						$distributer[ 'email' ] == '' ||
						$distributer[ 'contact_name' ] == '' ||
						$distributer[ 'phone' ] == '' ||
						$distributer[ 'address' ] == '' ||
						$distributer[ 'city' ] == '' ||
						$distributer[ 'state' ] == '' ||
						$distributer[ 'zip' ] == '' )
					{
						$this->smarty->assign( 'dist', $distributer );
						$this->setAlert( 'Error', 'Missing required fields. Please enter values for all fields.' );
						return;
					}

					// Validate email address
					if( !filter_var( $distributer[ 'email' ], FILTER_VALIDATE_EMAIL ) ) {
						$this->smarty->assign( 'dist', $distributer );
						$this->setAlert( 'Error', 'Invalid email address. Please enter a valid email address.' );
						return;
					}

					// Validate phone number
					if( !preg_match( "/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/", $distributer[ 'phone' ] ) &&
						!preg_match( "/^[0-9]{3}[0-9]{3}[0-9]{4}$/", $distributer[ 'phone' ] ) )
					{
						$this->smarty->assign( 'dist', $distributer );
						$this->setAlert( 'Error', 'Invalid phone number. Please enter a valid phone number in the format 123-456-7890.' );
						return;
					}

					// TODO: format the phone number

					// Validate email in use
					$db = new VirtualRainDB();
					if( $_POST[ 'id' ] == 'new' ) {
						if( $db->emailInUse( $distributer[ 'email' ] ) ) {
							$this->smarty->assign( 'dist', $distributer );
							$this->setAlert( 'Error', 'The email address you have entered is already in use. Please enter a different email address.' );
							return;
						}
					}

					// Get the value for status
					if( $_POST[ 'status' ] == 'on' ) {
						$distributer[ 'status' ] = 1;
					}
					
					// Create the distributers dir
					$distributer['dir'] = str_replace("'", "", str_replace(" ", "_", strtolower($distributer['company_name']) ) );
					$distDir = $_SERVER['DOCUMENT_ROOT'] . '/dist/' . $distributer['dir'];
					if(!file_exists($distDir)) {
						if( !mkdir($distDir, 0755) ) {
							$this->setAlert( 'Error', "An error occured creating the distributer directory. Please try again or contact support." );
							break;
						}
					}

					if( $_POST[ 'id' ] == 'new' ) {
						// Add new distributer
						$distributer['logo'] = $this->saveLogo($distributer, $distDir);
						if($distributer['logo'] !== false) {
							$distributer[ 'change_pass_token' ] = md5( uniqid( $distributer[ 'email' ], true ) );
							$distributer[ 'password' ] = md5( uniqid( $distributer[ 'email' ], true ) );
							$distributer['id'] = $db->addDistributer( $distributer, $distributer[ 'change_pass_token' ] );
							if( $distributer['id'] != null) {
								// Send email to new distributer
								$this->sendDistSignupEmail( $distributer[ 'email' ], $distributer[ 'change_pass_token' ] );
								
								header('Location:/admin/distributers');
								exit();
							}
							else {
								$this->setAlert( 'Error', "An error occured creating the new distributer. Please try again or contact support." );
							}
						}
						else {
							$this->setAlert( 'Error', "An error occured saving the distributer logo. Please try again or contact support." );
						}
					}
					else {
						// Update existing distributer
						if(isset($_FILES['logo']) && $_FILES['logo']['size'] > 0) {
							$distributer['logo'] = $this->saveLogo($distributer, $distDir);
							
							if($distributer['logo'] !== false) {
								$this->setAlert( 'Error', "An error occured saving the distributer logo. Please try again or contact support." );
							}
						}
								
						$distributer[ 'id' ] = $_POST[ 'id' ];
						$success = $db->updateDistributer( $distributer );
						if( $success) {
							$this->setAlert( 'Success', "Distributer '{$distributer[ 'company_name' ]}' updated. <a href=\"../distributers\">Return to list</a>" );
						}
						else {
							$this->setAlert( 'Error', "An error occured saving the distributer. Please try again or contact support." );
						}
					}

					break;
				}
				case 'reset_pw': {
					$db = new VirtualRainDB();

					$pw = md5( uniqid( $_POST[ 'email' ], true ) );
					$token = md5( uniqid( $_POST[ 'email' ], true ) );

					$sent = $this->sendDistPwResetEmail( $_POST[ 'email' ], $token );
					if( $sent == false ) {
						$this->setAlert( 'Error', "An error occured sending the email. Please try again. If the problem persists please contact the site admin." );
						break;
					}

					$db->setDistributerPassword( $_POST[ 'id' ], $pw, $token );

					$this->setAlert( 'Success', "The distributer has been sent an email with further instructions." );

					break;
				}
			}
		}
	}

	private function sendDistSignupEmail( $email, $token ) {
		$domain = DOMAIN;
		$protocol = PROTOCOL;

		$html = file_get_contents( 'templates/admin/res/new_dist_email.html' );
		$text  = file_get_contents( 'templates/admin/res/new_dist_email.txt' );

		$html = str_replace( '{link}', "$protocol://$domain/admin/login?t=$token", $html );
		$text = str_replace( '{link}', "$protocol://$domain/admin/login?t=$token", $text );

		$subject = "Virtualrain - Signup";

		return Util::sendEmail( $email, null, $subject, $html, $text, null, $_SESSION['distributer']['company_name'] );
	}

	private function sendDistPwResetEmail( $email, $token ) {
		$domain = DOMAIN;
		$protocol = PROTOCOL;

		$html = file_get_contents( 'templates/admin/res/pwreset_dist_email.html' );
		$text  = file_get_contents( 'templates/admin/res/pwreset_dist_email.txt' );

		$html = str_replace( '{link}', "$protocol://$domain/admin/login?t=$token", $html );
		$text = str_replace( '{link}', "$protocol://$domain/admin/login?t=$token", $text );

		$subject = "Virtualrain - Password Reset";

		return Util::sendEmail( $email, NULL, $subject, $html, $text, NULL, NULL);
	}

	private function saveLogo($distributer, $distDir){
		// Save logo if exists
		include_once($_SERVER['DOCUMENT_ROOT'].'/libs/resize-class.php');
		$db = new VirtualRainDB();
		if(isset($_FILES['logo']) && $_FILES['logo']['size'] > 0) {
			$file = $_FILES['logo'];
			if(preg_match("/^image\/.*/", $file['type'])) {
				$ext = explode(".",$file['name']);
				$ext = end($ext);
				$dest = str_replace(" ", "_", strtolower( $distributer['company_name'] ) );
				$dest = str_replace("'", "", $dest);
				$dest = 'logo_' . str_replace('"', "", $dest) . '.' . $ext;
				$dest = $db->escape($dest);
				if(move_uploaded_file($file['tmp_name'], $distDir . '/' . $dest)){
					$image = new resize($distDir . '/' . $dest);
					if($image->image == false) {
						$this->setAlert('Error','Sorry, the image you specified cannot be read');
						$this->setAlert('Success','');
						return false;
					}
					$image->resizeImage(400, 400, 'auto');
					$image->saveImage($distDir . '/' . $dest);
					
					return $dest;
				}
				else {
					return false;
				}
			}
			else {
				$this->setAlert('Error', 'The file you specified is not supported');
				$this->setAlert('Success','');
				return false;
			}
		}
		else {
			return false;
		}
	}
}









