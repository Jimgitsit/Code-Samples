<?php

require_once( 'Page.php' );
require_once( 'SessionCart.php' );
require_once( 'Util.php' );

class ShippingPage extends Page {
	public function __construct( $template ) {
		parent::__construct( $template );
		
		$this->handlePost();
		
		//Load Locations
		$db = new VirtualRainDB();
		$locations = $db->getShippingLocations( $_SESSION['user']['id'] );
		
		$this->smarty->assign( 'locations', $locations );
		//Load Preferred Locations
		$preferred = $db->getPreferredShippingLocation( $_SESSION['user']['id'] );
		$this->smarty->assign( 'preferred', $preferred['preferred_shipping_location'] );
		
	}
	
	protected function handlePost() { 
		if( !empty( $_POST ) && isset( $_POST[ 'action' ]) ) {
			switch( $_POST[ 'action' ] ) {
				case 'create_order': {
					if( !empty($_POST['preferred']) ) {
						// TODO: This code is nearly identical to what is in LocationPage.php.
						//       Should combine it somehow.
						
						// Create the order
						$poNum = '';
						if( isset( $_SESSION[ 'po_num' ] ) ) {
							$poNum = $_SESSION[ 'po_num' ];
						}
						$db = new VirtualRainDB();
						$cart = new SessionCart($_SESSION[ 'user' ]['id']);
						$comment = $_POST['comment'];
						
						// For shipping, the branch the order is assigned to is based 
						// on the banch the user is assigned to.
						$branchId = null;
						if(isset($_SESSION['branch'])) {
							$branchId = $_SESSION['branch']['id'];
						}
						$orderId = $db->createOrder( $_SESSION[ 'dist_id' ], $_SESSION[ 'user' ][ 'id' ], $cart, $poNum, $comment, 0, $_POST[ 'preferred' ], $branchId );
						
						// Export the order
						$db->exportOrder($orderId);
						
						// Email the order
						$dist = $db->getDistributer($_SESSION[ 'dist_id' ]);
						$extraAdmins = $db->getExtraAdminsById($_SESSION[ 'dist_id' ]);
						$cc = "";

						if($dist['send_new_order_emails']) {
							// The email will either be the dist or branch manger
							$to = $dist['email'];
							if(isset($_SESSION['branch']) && isset($_SESSION['branch']['manager_email'])) {
								$cc = $_SESSION['branch']['manager_email'];
							}
							$order = $db->getOrder($orderId);
							$user = $db->getUserById($order['user_id']);
							
							if($extraAdmins != null){
								
								foreach($extraAdmins as $admin){
									$emailSettings = $admin['email_setting'];
									if( $emailSettings == "all_emails" || $emailSettings == "new_order_emails_only" ){
										$cc .= " " . $admin['email'];
									}
								}	
								
							}
							
							Util::emailNewOrder($dist, $to, $order, $user, $cc);
						}
						
						
						
						// Empty the cart and redirect
						$cart->emptyCart();
						header( 'Location:/thankyou-shipping' );
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