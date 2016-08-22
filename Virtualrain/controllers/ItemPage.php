<?php

require_once( 'Page.php' );
require_once( 'classes/SessionCart.php' );

class ItemPage extends Page {
	public function __construct( $template ) {
		parent::__construct( $template );
		
		$this->handlePost();
		
		/* Get Product */
		$db = new VirtualRainDB();
		
		// Validation
		if( empty( $_GET ) || !isset( $_GET[ 'id' ] ) ) {
			header( "Location:categories" );
			exit();
		}
		
		$product = $db->getDistProductById( $_SESSION[ 'user' ][ 'dist_id' ], $_GET[ 'id' ], true );	
		if( empty( $product ) ){
			header( "Location:categories" );
			exit();
		}
		
		if (@file_get_contents($product['image_large']) === false) {
			$product['image_large'] = null;
		}
		
		$styles = $product[ 'styles' ];
		$this->smarty->assign( 'styles', $styles );
		$product[ 'default_style' ] = $styles[ 0 ];
		foreach( $styles as $style ) {
			if( $style[ 'default_style' ] == 1 ) {
				$product[ 'default_style' ] = $style;
			}
		}
		
		$product[ 'favorite' ] = $db->isUserFavorite( $_SESSION[ 'user' ][ 'id' ], $product[ 'sku' ] );
		
		$this->smarty->assign( 'product', $product );
		
		$dist = $db->getDistributer($_SESSION['dist_id']);
		$this->smarty->assign('showPnInstead', $dist['show_part_num_instead_of_sku']);
		
		if( isset( $_POST[ 'style_id' ] ) ) {
			$this->smarty->assign( 'currentlySelected', $_POST[ 'style_id' ] );
		}
		
		if( isset( $_GET[ 'item_index' ] ) ) {
			$this->smarty->assign( 'itemIndex', $_GET[ 'item_index' ] );
		}
	}
	
	protected function handlePost() { 
		if( !empty( $_POST ) && isset( $_POST[ 'action' ]) ) {
			switch( $_POST[ 'action' ] ) {
				case 'add_to_cart': {
					if( !empty( $_POST[ 'quantity' ] ) ) {
						$db = new VirtualRainDB();
						
						$product = $db->getDistProductById( $_SESSION[ 'dist_id' ], $_POST[ 'product_id' ] );
						$style = $db->getDistProductStyleById( $_SESSION[ 'dist_id' ], $_POST[ 'style_id' ] );
						
						$cart = new SessionCart($_SESSION[ 'user' ]['id']);
						$cart->addToCart( $product, $style, $_POST[ 'quantity' ] );
						$this->smarty->assign( 'showCartUpdate', true );
						$this->updateCart();
					}
					break;
				}
				case 'clear_cart_ajax': {
					$cart = new SessionCart($_SESSION[ 'user' ]['id']);
					$cart->emptyCart();
					$this->updateCart();
					echo( 'success' );
					exit();
				}
				case 'clear_selected_ajax':{
					if( isset($_POST['ids']) ){
						$cart = new SessionCart($_SESSION[ 'user' ]['id']);
						$cart->clearSelected( $_POST['ids'] );
						$response = array();
						$response[ 'success' ] = true;
						$response[ 'new_sub_total' ] = number_format($cart->totalPrice(), 2);
						$response[ 'new_count' ] = $cart->numItems();
						$this->updateCart();
						echo( json_encode( $response ) );
						exit();
					}
					break;
				}
				case 'update_cart_qty_ajax': {
					// Update the quantites in the cart
					$cart = new SessionCart($_SESSION[ 'user' ]['id']);
					foreach($_POST['items'] as $index => $newQty) {
						$cart->updateQuantity($index, $newQty);
					}
					
					// Return the new sub-total
					$newSub = $cart->totalPrice();
					echo(json_encode(array('new_sub_total' => number_format($newSub, 2))));
					exit();
				}
				case 'add_to_favorites': {
					$db = new VirtualRainDB();
					$db->addUserFavorite( $_SESSION[ 'user' ][ 'id' ], $_POST[ 'sku' ], $_POST[ 'style_num' ] );
					break;
				}
			}
		}
	}
}