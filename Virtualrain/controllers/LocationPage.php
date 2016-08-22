<?php

require_once( 'Page.php' );
require_once( 'SessionCart.php' );
require_once( 'Util.php' );

class LocationPage extends Page {
	public function __construct( $template ) {
		parent::__construct( $template );
		
		$this->handlePost();
		
		//Load Locations
		$db = new VirtualRainDB();
		$locations = $db->getLocations( $_SESSION['dist_id'] );
		$this->smarty->assign( 'locations', $locations );
		
		//Load Preferred Locations
		$preferred = $db->getPreferredLocation( $_SESSION['user']['id'] );
		$this->smarty->assign( 'preferred', $preferred['preferred_location'] );
		
	}
	
	protected function handlePost() { 
		if( !empty( $_POST ) && isset( $_POST[ 'action' ]) ) {
			switch( $_POST[ 'action' ] ) {
				case 'create_order': {
					if( !empty($_POST['preferred']) ) {
						// TODO: This code is nearly identical to what is in ShippingPage.php.
						//       Should combine it somehow.
						
						// Create the order
						$poNum = '';
						if( isset( $_SESSION[ 'po_num' ] ) ) {
							$poNum = $_SESSION[ 'po_num' ];
						}
						$db = new VirtualRainDB();
						$cart = new SessionCart($_SESSION[ 'user' ]['id']);
						$comment = $_POST['comment'];
						
						// For pickup, the branch the order is assigned to is based
						// on the branch the location is assigned to.
						$branch = $db->getBranchFromLocationId($_POST['preferred']);
						if ($branch == null && isset($_SESSION['user']['branch'])) {
							// If the location does not have a branch set then get the 
							// branch from the user.
							$branch = $_SESSION['user']['branch'];
						}
						
						$branchId = null;
						if(!empty($branch)) {
							$branchId = $branch['id'];
						}
						
						$orderId = $db->createOrder( $_SESSION[ 'dist_id' ], $_SESSION[ 'user' ][ 'id' ], $cart, 
													 $poNum, $comment, $_POST[ 'preferred' ], 0, $branchId );
						
						// Export the order
						$db->exportOrder($orderId);
						
						// Email the order
						$user = $db->getUserById($_SESSION[ 'user' ][ 'id' ]);
						$dist = $db->getDistributer($_SESSION[ 'dist_id' ]);
						$extraAdmins = $db->getExtraAdminsById($_SESSION[ 'dist_id' ]);
						$cc = "";

						
						if($dist['send_new_order_emails']) {
							// The email will either be the dist or branch manger
							$to = $dist['email'] . "," . $user['email'];
							$cc = "";
							if($branch != null && isset($branch['manager_email'])) {
								// This will go to the user's branch manager or the location's branch manger
								// depending on the distributer's branch_managers_own_orders preference
								if($dist['branch_managers_own_orders']) {
									// Goes to the user's branch manager
									$cc .= " " . $user['branch']['manager_email'];
								}
								else {
									// Goes to the location's branch manager
									$cc .= " " . $branch['manager_email'];
								}
							}
							
							if($extraAdmins != null){
								
								foreach($extraAdmins as $admin){
									$emailSettings = $admin['email_setting'];
									if( $emailSettings == "all_emails" || $emailSettings == "new_order_emails_only" ){
										$cc .= " " . $admin['email'];
									}
								}
								
							}
							
							$order = $db->getOrder($orderId);
							
							Util::emailNewOrder($dist, $to, $order, $user, $cc);
						}
						
						// Empty the cart and redirect
						$cart->emptyCart();
						header( 'Location:/thankyou' );
						exit();
					}
					else{
						$this->smarty->assign( 'errorMsg', 'please select a pickup location' );
					}
				}
			}
		}
	}
}