<?php

require_once( 'Page.php' );
require_once( 'SessionCart.php' );

class ReorderPage extends Page {
	public function __construct( $template ) {
		parent::__construct( $template );
		$this->handlePost();
		
		$db = new VirtualRainDB();
		
		if( !empty( $_GET['id'] ) ){	
			$reorder = $db->getOrder( $_GET[ 'id' ] );
			$this->smarty->assign( 'reorder', $reorder );
		}
		
	}
	
	protected function handlePost() {
		
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			$cart = new SessionCart($_SESSION[ 'user' ]['id']);
			$db = new VirtualRainDB();
			switch( $_POST[ 'action' ] ) {
				case 'reorder': {
					$cart->emptyCart();
					foreach( $_POST['quantity'] as $index => $quantity ){
						$quantity = trim($quantity);
						if( $quantity != '' && $quantity > 0 ) {
							$active = $db->distProductExists( $_SESSION[ 'dist_id' ], $_POST[ 'sku' ][ $index ], $_POST[ 'style_num' ][ $index ] );
							if($active == true) {
								$product = $db->getDistProductBySKU( $_SESSION[ 'dist_id' ], $_POST[ 'sku' ][ $index ] );
								$style = $db->getDistProductStyleByStyleNum( $_SESSION[ 'dist_id' ], $_POST[ 'style_num' ][ $index ] );
								
								$cart->addToCart( $product, $style, $quantity );
								$this->updateCart();
							}
						}
					}
					header('Location: /finalize');
					break;
				}
			}
		}
	}
}