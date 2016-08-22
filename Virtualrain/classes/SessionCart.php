<?php

require_once( 'VirtualRainDB.php' );

class SessionCart {
	
	private $cloneInDB = true;
	private $userId = null;
	
	public function __construct($userId, $cloneInDB = true) {
		if( !isset( $_SESSION ) ) {
			session_start();
		}
		
		if( !isset( $_SESSION[ 'cart' ] ) ) {
			$_SESSION[ 'cart' ] = array();
		}
		
		$this->userId = $userId;
		$this->cloneInDB = $cloneInDB;
		
		if($this->cloneInDB) {
			$this->restoreFromDB();
		}
	}
	
	public function restoreFromDB() {
		$vrdb = new VirtualRainDB();
		$db = $vrdb->getDB();
		$query = "SELECT cart FROM shopping_cart WHERE user_id=$this->userId";
		$result = $db->query( $query );
		if (!$result) {
			throw new Exception( "Error: " . $db->error );
		}
		
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$_SESSION['cart'] = json_decode($row['cart'], true);
		}
	}
	
	public function removeFromDB() {
		$vrdb = new VirtualRainDB();
		$db = $vrdb->getDB();
		$query = "DELETE FROM shopping_cart WHERE user_id=$this->userId";
		$result = $db->query( $query );
		if (!$result) {
			throw new Exception( "Error: " . $db->error );
		}
	}
	
	public function saveToDB() {
		$vrdb = new VirtualRainDB();
		$db = $vrdb->getDB();
		$fields = array('user_id' => $this->userId, 'cart' => json_encode($_SESSION['cart']));
		$query = $vrdb->createAddOrUpdateStatment('shopping_cart', 'user_id', $fields);
		$result = $db->query( $query );
		if (!$result) {
			throw new Exception( "Error: " . $db->error );
		}
	}
	
	public function addToCart( $product, $style, $quantity ) {
		assert( $product != null && $style != null && $quantity > 0 );
		
		// See if the product is already in the cart
		$found = false;
		foreach( $_SESSION[ 'cart' ] as &$item ) {
			if( $product[ 'id' ] == $item[ 'product' ][ 'id' ] && $style[ 'id' ] == $item[ 'style' ][ 'id' ] ) {
				// Update quantity and price
				$item[ 'quantity' ] += $quantity;
				$item[ 'price' ] = $item[ 'quantity' ] * round($style[ 'price' ], 2);
				$found = true;
			}
		}
		
		if( !$found ) {
			// Add the item
			$newItem = array(
				"product" => $product,
				"style" => $style,
				"quantity" => $quantity,
				"price" => $quantity * round($style[ 'price' ], 2)
			);
			
			$_SESSION[ 'cart' ][] = $newItem;
		}
		
		if($this->cloneInDB) {
			$this->saveToDB();
		}
	}
	
	public function totalQuantity(){
		$total = 0;
		foreach( $_SESSION[ 'cart' ] as $item ) {
			$total += $item[ 'quantity' ];
		}
		
		return $total;
	}
	
	public function totalPrice(){
		$total = 0;
		foreach( $_SESSION[ 'cart' ] as $item ) {
			$total += $item[ 'price' ];
		}
		
		return $total;
	}
	
	public function removeFromCart( $itemIndex ) {
		if( isset( $_SESSION[ 'cart' ][ $itemIndex ] ) ) {
			unset( $_SESSION[ 'cart' ][ $itemIndex ] );
		}
		
		if($this->cloneInDB) {
			$this->saveToDB();
		}
	}
	
	public function emptyCart() {
		$_SESSION[ 'cart' ] = array();
		
		if($this->cloneInDB) {
			$this->removeFromDB();
		}
	}
	
	public function clearSelected( $itemIndexes ) {
		foreach( $itemIndexes as $itemIndex ) {
			$this->removeFromCart( $itemIndex );
		}
		
		if($this->cloneInDB) {
			$this->saveToDB();
		}
	}
	
	public function getItem( $itemIndex ) {
		if( !empty( $_SESSION[ 'cart' ][ $itemIndex ] ) ) {
			return $_SESSION[ 'cart' ][ $itemIndex ];
		}
		else {
			return null;
		}
	}
	
	public function getItems() {
		return $_SESSION[ 'cart' ];
	}
	
	public function numItems() {
		return count( $_SESSION[ 'cart' ] );
	}
	
	public function updateQuantity($itemIndex, $newQty) {
		if( !empty( $_SESSION[ 'cart' ][ $itemIndex ] ) ) {
			$item = &$_SESSION[ 'cart' ][ $itemIndex ];
			$item['quantity'] = $newQty;
			$item[ 'price' ] = $item[ 'quantity' ] * round($item['style'][ 'price' ], 2);
		}
		
		if($this->cloneInDB) {
			$this->saveToDB();
		}
	}
}





