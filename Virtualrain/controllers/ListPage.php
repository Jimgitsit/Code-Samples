<?php

require_once( 'Page.php' );
require_once( 'classes/SessionCart.php' );

class ListPage extends Page {
	const PRODUCTS_PER_PAGE = 15;
	
	public function __construct( $template ) {
		parent::__construct( $template );
		
		$this->smarty->assign( 'productsPerPage', self::PRODUCTS_PER_PAGE );
		
		$this->handlePost();
		
		if( isset( $_GET[ 'cat_id' ] ) ) {
			$this->handleGet();
		}
		else {
			header( 'Location:categories' );
			exit();
		}
	}
	
	private function handleGet() {
		if( !empty( $_GET ) ) {
			$this->initProducts();
			
			if( !empty( $_GET[ 'cat_id' ] ) ) {
				$parentId = $_GET[ 'cat_id' ];
				$this->smarty->assign('parentId', $parentId);
					
				$db = new VirtualRainDB();
				$catName = $db->getDistCategoryName( $_SESSION[ 'dist_id' ], $parentId );
				$this->smarty->assign( 'catName', $catName );
			}
		}
	}
	
	private function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			switch( $_POST[ 'action' ] ) {
				case 'more_products_ajax': {
					$json = $this->initProducts( true );
					echo( $json );
					exit();
				}
			}
		}
	}
	
	private function initProducts( $returnJSON = false ) {
		$limit = self::PRODUCTS_PER_PAGE;
		$offset = 0;
		
		if( !empty( $_GET[ 'item_index' ] ) ) {
			$offset = 0;
			//if( $_GET[ 'item_index' ] > self::PRODUCTS_PER_PAGE ) {
				$limit = $_GET[ 'item_index' ] + 1;
				while($limit % self::PRODUCTS_PER_PAGE != 0) {
					$limit +=1;
				}
				$limit += self::PRODUCTS_PER_PAGE;
			//}
		}
		else if( isset( $_POST[ 'offset' ] ) ) {
			$offset = $_POST[ 'offset' ];
		}
		
		$limitClause = $offset . ',' . $limit;
		
		if( isset( $_GET[ 'cat_id' ] ) ) {
			$db = new VirtualRainDB();
			
			$products = $db->getDistProducts( $_SESSION[ 'dist_id' ], $_GET[ 'cat_id' ], $limitClause );
			
			if( $returnJSON ) {
				$json = json_encode( $this->generateListItems( $products, $offset ) );
				return $json;
			}
			else {
				$categoryTitle = $db->getDistCategoryName( $_SESSION[ 'dist_id' ], $_GET[ 'cat_id' ] );
				$this->smarty->assign( 'listItems', $this->generateListItems( $products, $offset ) );
				$this->smarty->assign( 'catId', $_GET[ 'cat_id' ] );
				$this->smarty->assign( 'categoryTitle', $categoryTitle );
			}
		}
	}
	
	private function generateListItems( $products, $offset ) {
		$db = new VirtualRainDB();
		$dist = $db->getDistributer($_SESSION['dist_id']);
		
		$listItems = array();
		foreach( $products as $index => $prod ) {
			$skuOrPN = $prod['sku'];
			if($dist['show_part_num_instead_of_sku']) {
				$skuOrPN = $prod['part_num'];
			}
			
			$html = '';
			
			$prodIndex = $offset + $index;
			
			$html .= '<a data-index="' . $prodIndex . '" href="/item?id=' . $prod[ 'id' ] . '&item_index=' . $prodIndex . '"><li class="item">';
			$html .= '<img class="product-image" src="' . $prod[ 'image' ] . '"/>';
			$html .= '<div class="left"><div class="title">' . $prod[ 'title' ] . '</div><div class="part-wrap"><div class="part-num">' . $skuOrPN . '</div><div class="manufacturer">' . $prod[ 'manufacturer' ] . '</div></div></div>';
			$html .= '<br class="clear"/></li></a>';
			
			$listItems[] = $html;
		}
		
		return $listItems;
	}
}





