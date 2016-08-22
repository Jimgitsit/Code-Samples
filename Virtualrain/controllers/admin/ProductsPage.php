<?php

require_once( 'AdminPage.php' );
require_once( 'VirtualRainDB.php' );
require_once( 'resize-class.php' );

class ProductsPage extends AdminPage {
	const PRODUCT_PER_PAGE = 25;
	
	private $query;
	
	public function __construct( $template ) {
		parent::__construct( $template, 'Products' );
		
		$this->smarty->assign( 'currPriceCat', null );
		
		$this->query = $_SERVER[ 'QUERY_STRING' ];
		
		$this->handlePost();
		$this->handleGet();
		
		$this->smarty->assign( 'query', $this->query );
	}
	
	/**
	 * Query Params:
	 * 		'c' 		category id
	 * 		's' 		sub-category id
	 * 		'p' 		page number
	 * 		'search' 	the search term
	 * 
	 */
	private function handleGet() {
		$db = new VirtualRainDB();
		
		if( empty( $_GET ) || ( !isset( $_GET[ 'c' ] ) && !isset( $_GET[ 'search' ] ) ) ) {
			$db = new VirtualRainDB();
			$categories = $db->getAllCategories( true );
			$this->smarty->assign( 'categories', $categories );
			return;
		}
		
		if( !empty( $_GET ) ) {
			if( isset( $_GET[ 'search' ] ) ) {
				$term = trim( $_GET[ 'search' ] );
				$this->smarty->assign( 'term', $term );
				
				if( strlen( $term ) > 0 ) {
					$priceCategories = $db->getPricingCategories( $_SESSION[ 'distributer' ][ 'id' ] );
					$this->smarty->assign( 'priceCategories', $priceCategories );
					$currPriceCatId = $this->setCurrentPriceCategory( $priceCategories );
					$products = $db->getProducts( $_SESSION[ 'distributer' ][ 'id' ], $currPriceCatId, null, 'title', 'ASC', $term );
				}
				else {
					$products = array();
				}
				$this->smarty->assign( 'products', $products );
			}
			
			if( isset( $_GET[ 'c' ] ) ) {
				$category = $db->getCategory( $_GET[ 'c' ] );
				if( $category != null ) {
					$this->smarty->assign( 'category', $category );
				}
				
				if( !isset( $_GET[ 's' ] ) ) {
					$subCategories = $db->getSubCategories( $_GET[ 'c' ], true, $_SESSION[ 'distributer' ][ 'id' ] );
					$this->smarty->assign( 'subCategories', $subCategories );
				}
			}
			
			if( isset( $_GET[ 's' ] ) ) {
				$priceCategories = $db->getPricingCategories( $_SESSION[ 'distributer' ][ 'id' ] );
				$this->smarty->assign( 'priceCategories', $priceCategories );
				$currPriceCatId = $this->setCurrentPriceCategory( $priceCategories );
				
				$subCategory = $db->getSubCategory( $_GET[ 's' ] );
				if( $subCategory != null ) {
					$this->smarty->assign( 'subCategory', $subCategory );
					
					$total = $db->getProductCount( $_SESSION[ 'distributer' ][ 'id' ], $subCategory[ 'id' ] );
					$this->smarty->assign( 'totalProducts', $total );
					
					$offest = 0;
					$count = self::PRODUCT_PER_PAGE;
					if( isset( $_GET[ 'p' ] ) ) {
						$page = $_GET[ 'p' ];
						$offest = $count * ( $page - 1 );
						
						$prevPage = $page - 1;
						$nextPage = $page + 1;
							
						$pageEnd = $offest + $count;
						if( $pageEnd > $total ) {
							$pageEnd = $total;
							$nextPage = 0;
						}
							
						$this->smarty->assign( 'pageStart', $offest + 1 );
						$this->smarty->assign( 'pageEnd', $pageEnd );
						$this->smarty->assign( 'prevPage', $prevPage );
						$this->smarty->assign( 'nextPage', $nextPage );
						
						$products = $db->getProducts( $_SESSION[ 'distributer' ][ 'id' ], $currPriceCatId, $subCategory[ 'id' ], 'title', 'ASC', null, "$offest, $count" );
						$this->smarty->assign( 'products', $products );
					}
					else {
						$products = $db->getProducts( $_SESSION[ 'distributer' ][ 'id' ], $currPriceCatId, $subCategory[ 'id' ] );
						$this->smarty->assign( 'products', $products );
					}
				}
			}
		}
	}
	
	private function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			switch( $_POST[ 'action' ] ) {
				case 'create_price_cat': {
					$copyFrom = null;
					if( isset( $_POST[ 'price_category_copy' ] ) && $_POST[ 'price_category_copy' ] != -1 ) {
						$copyFrom = $_POST[ 'price_category_copy' ];
					}
					
					$db = new VirtualRainDB();
					$newId = $db->createPricingCategory( $_SESSION[ 'distributer' ][ 'id' ], $_POST[ 'new_price_cat_name' ], false, $copyFrom );
					if( $newId !== false ) {
						$index = strpos( $this->query, '&pc=' );
						if( $index ) {
							$this->query = substr( $this->query, 0, $index );
						}
						$this->query .= "&pc=$newId";
						
						// Redirect to prevent re-posting
						header( "Location:products?$this->query" );
						exit();
						
						// TODO: Need msg
					}
					else {
						// TODO: Need msg
					}
					
					break;
				}
				case 'rename_price_cat_ajax': {
					if( isset( $_POST[ 'price_cat_id' ] ) && isset( $_POST[ 'new_price_cat_name' ] ) && strlen( $_POST[ 'new_price_cat_name' ] ) >= 1 ) {
						$db = new VirtualRainDB();
						$db->renamePricingCategory( $_POST[ 'price_cat_id' ], $_POST[ 'new_price_cat_name' ] );
					}
					
					exit();
				}
				case 'delete_price_cat_ajax': {
					if( isset( $_POST[ 'price_cat_id' ] ) ) {
						$db = new VirtualRainDB();
						$db->deletePricingCategory( $_POST[ 'price_cat_id' ] );
					}
					
					exit();
				}
				case 'set_defalt_price_cat_ajax': {
					if( isset( $_POST[ 'price_cat_id' ] ) ) {
						$db = new VirtualRainDB();
						$db->setDefaultPriceCategory( $_SESSION[ 'distributer' ][ 'id' ], $_POST[ 'price_cat_id' ] );
					}
						
					exit();
				}
				case 'save_product_ajax': {
					if( isset( $_POST[ 'product' ] ) && isset( $_POST[ 'styles' ] ) ) {
						$product = $_POST[ 'product' ];
						$styles = $_POST[ 'styles' ];
						
						// Validate product
						if( trim( $product[ 'title' ] ) == '' ) {
							echo( 'invalid' );
							exit();
						}
						
						// Validate styles
						foreach( $styles as $style ) {
							if( trim( $style[ 'style_description' ] ) == '' || !is_numeric( $style[ 'price' ] ) ) {
								echo( 'invalid' );
								exit();
							}
						}
					
						$db = new VirtualRainDB();
						
						$success = $db->addOrUpdateProductOverride( $_SESSION[ 'distributer' ][ 'id' ], $product );
						if( $success ) {
							foreach( $styles as $style ) {
								$success = $db->addOrUpdateStyleOverride( $_SESSION[ 'distributer' ][ 'id' ], $style );
								if( !$success ) {
									echo( 'failed' );
									exit();
								}
							}
						}
						else {
							echo( 'failed' );
							exit();
						}
						
						echo( 'success' );
						exit();
					}
					else {
						echo( 'failed' );
						exit();
					}
					
					break;
				}
				case 'upload_new_product_image_ajax': {
					if( !empty( $_FILES[ 'new_product_image' ] ) ) {
						$tempDest = SITE_DIR . '/custom_product_images/temp/' . $_FILES[ 'new_product_image' ][ 'name' ];
						//chmod( $_FILES[ 'new_product_image' ][ 'tmp_name' ], 0775 );
						$moved = move_uploaded_file( $_FILES[ 'new_product_image' ][ 'tmp_name' ], $tempDest );
						if( $moved ) {
							@$resizeObj = new resize( $tempDest );
							if( $resizeObj->image !== false ) {
								$resizeObj->resizeImage( 200, 200, 'auto' );
								$newImgName = time() . '.png';
								$resizeObj->saveImage( SITE_DIR . '/custom_product_images/' . $newImgName );
								
								unlink( $tempDest );
								echo( '/custom_product_images/' . $newImgName );
								exit();
							}
						}
						unlink( $tempDest );
						echo( 'error' );
					}
					
					exit();
				}
				case 'save_new_product_ajax': {
					if( isset( $_POST[ 'product' ] ) && isset( $_POST[ 'styles' ] ) && count( $_POST[ 'styles' ] ) > 0 ) {
						$db = new VirtualRainDB();
						
						$product = $_POST[ 'product' ];
						$styles = $_POST[ 'styles' ];
						
						// Validate product
						if( trim( $product[ 'title' ] ) == '' ) {
							echo( 'invalid' );
							exit();
						}
						
						// Validate styles
						foreach( $styles as $style ) {
							if( trim( $style[ 'style_description' ] ) == '' || !is_numeric( $style[ 'price' ] ) ) {
								echo( 'invalid' );
								exit();
							}
						}
						
						$product[ 'custom' ] = 1;
						// Fix: part_num is now sku
						$product[ 'part_num' ] = $product[ 'title' ] . '_' . time();
						$prodId = $db->addOrUpdateProduct( $product );
						
						if( $prodId ) {
							foreach( $styles as $style ) {
								$style[ 'product_id' ] = $prodId;
								$style[ 'style_num' ] = $product[ 'title' ] . '_' . $style[ 'style_description' ] . '_' . time();
								$styleId = $db->addOrUpdateProductStyle( $style );
							}
							
							if( isset( $_POST[ 'active' ] ) && $_POST[ 'active' ] == 1 ) {
								$db->activateProduct( $_SESSION[ 'distributer' ][ 'id' ], $prodId );
							}
							else {
								$db->deactivateProduct( $_SESSION[ 'distributer' ][ 'id' ], $prodId );
							}
						}
						else {
							echo( 'error' );
							exit();
						}
					}
					else {
						echo( 'error' );
						exit();
					}
					
					echo( 'success' );
					exit();
				}
				case 'set_hide_prices_ajax': {
					$db = new VirtualRainDB();
					$success = $db->setPriceCategoryShowPrices( $_POST[ 'price_cat_id' ], $_POST[ 'show_prices' ] );
					echo( $success );
					exit();
				}
			}
		}
	}
	
	private function setCurrentPriceCategory( $priceCategories, $id = null ) {
		$db = new VirtualRainDB();
		
		if( $id != null ) {
			// Current price category comes from the parameter
			foreach( $priceCategories as &$priceCat ) {
				if( $priceCat[ 'id' ] == $id ) {
					$this->smarty->assign( 'currPriceCat', $priceCat );
					
					$users = $db->getPriceCategoryUsers( $priceCat[ 'id' ] );
					$this->smarty->assign( 'userCount', count( $users ) );
					
					$_SESSION[ 'currentPriceCat' ] = $id;
					
					return $id;
				}
			}
		}
		else if( isset( $_GET[ 'pc' ] ) ) {
			// Current price category comes from the query
			foreach( $priceCategories as &$priceCat ) {
				if( $priceCat[ 'id' ] == $_GET[ 'pc' ] ) {
					$this->smarty->assign( 'currPriceCat', $priceCat );
					
					$users = $db->getPriceCategoryUsers( $priceCat[ 'id' ] );
					$this->smarty->assign( 'userCount', count( $users ) );
					
					$_SESSION[ 'currentPriceCat' ] = $priceCat[ 'id' ];
					
					return $priceCat[ 'id' ];
				}
			}
		}
		else if( isset( $_SESSION[ 'currentPriceCat' ] ) ) {
			// Current price category comes from the session
			foreach( $priceCategories as &$priceCat ) {
				if( $priceCat[ 'id' ] == $_SESSION[ 'currentPriceCat' ] ) {
					$this->smarty->assign( 'currPriceCat', $priceCat );
						
					$users = $db->getPriceCategoryUsers( $priceCat[ 'id' ] );
					$this->smarty->assign( 'userCount', count( $users ) );
						
					return $priceCat[ 'id' ];
				}
			}
		}
		else {
			// Current price category gets set to the default
			$this->smarty->assign( 'currPriceCat', $priceCategories[ 0 ] );
			foreach( $priceCategories as &$priceCat ) {
				if( $priceCat[ 'default' ] ) {
					$this->smarty->assign( 'currPriceCat', $priceCat );
			
					$users = $db->getPriceCategoryUsers( $priceCat[ 'id' ] );
					$this->smarty->assign( 'userCount', count( $users ) );
					
					$_SESSION[ 'currentPriceCat' ] = $priceCat[ 'id' ];
			
					return $priceCat[ 'id' ];
				}
			}
		}
	}
}










