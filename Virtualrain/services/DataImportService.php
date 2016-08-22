<?php

if( empty( $paths ) ) {
	require_once( '../config/config.php' );
}
require_once( 'import_config.php' );
require_once( 'simplexlsx.class.php' );
require_once( 'VirtualRainDB.php' );

ini_set( 'memory_limit', '-1' );

/**
 * This class handles looking for and importing product data from Virtual Rain.
 * A log file is kept called vrain_data_import.log in the root of the web site.
 * 
 * Usage:
 * 
 * $service = new DataImportService();
 * 
 * // Check for a new data file with today's date
 * $newData = $service->checkForNewData();
 * 
 * // If a new data file was found import the data
 * if( $newData ) {
 * 		$success = $service->importData();
 * }
 * 
 * @author jim
 *
 */
class DataImportService {
	private $filePrefix = "VirtualRainData-";
	private $fileExt = '.xlsx';
	
	private $data = null;
	private $cols = null;
	
	private $categories = null;
	
	private $echoLog = false;
	private $htmlLogOutput = false;
	
	// True if requried, false for optional
	public static $importHeaders = array(
			'Category1' 			=> true,
			'Product Title' 		=> true,
			'Manufacturer' 			=> true,
			'Product Description' 	=> false,
			'Style Description' 	=> false,
			'Style Price' 			=> false,
			'Default Style' 		=> false,
			'Product Number' 		=> false,
			'Style Number' 			=> false,
			'SKU' 					=> true,
			'UPC' 					=> false,
			'Image'					=> false,
			'Meta 1' 				=> false
	);
	
	public static $exportHeaders = array(
			'Product Title',
			'Manufacturer',
			'Product Description',
			'Style Description',
			'Style Price',
			'Default Style',
			'SKU',
			'Product Number',
			'Style Number',
			'UPC',
			'Image',
			'Meta 1'
	);
	
	public static $masterExportHeaders = array(
			'Product Title',
			'Manufacturer',
			'Product Description',
			'Style Description',
			'Style Price',
			'Default Style',
			'Product Number',
			'Style Number',
			'SKU',
			'UPC',
			'Image',
			'Meta 1',
			'Meta 2',
			'Meta 3'
	);
	
	public function checkForNewData( $date ) {
		$this->log( '================ ' . date( 'm-d-Y H:i:s' ) . ' ================' );
		$this->log( 'Checking for new data file...' );
		
		if( !defined( 'LOCAL_DATA_PATH' ) || LOCAL_DATA_PATH == '' ) {
			$this->log( 'Error: LOCAL_DATA_PATH is not defined in import_config.php' );
		}
		
		if( DATA_LOCATION == 'local' ) {
			$result = $this->checkLocal( $date );
		}
		else if( DATA_LOCATION == 'ftp' ) {
			$result = $this->checkFTP( $date );
		}
		else {
			$this->log( 'Error: invalid DATA_LOCATION in import_config.php' );
		}
		
		if( $result == false ) {
			$this->log( "No new data file found. Done." );
		}
		
		return $result;
	}
	
	private function checkFTP( $date ) {
		// Connect to the ftp server
		$conn = ftp_connect( FTP_HOST );
		if( !$conn ) {
			$this->log( "Failed to connect to FTP server '" . FTP_HOST );
			return false;
		}
		
		$result = ftp_login( $conn, FTP_USER, FTP_PASS );
		if( $result == false ) {
			$this->log( "Authentication failed for FTP server '" . FTP_HOST );
			return false;
		}
		
		// Look for a data file with the given date
		$files = ftp_nlist( $conn, '.' );
		foreach( $files as $file ) {
			if( strstr( $file, $date ) ) {
				$this->log( "  Found file '$file'." );
				
				if( !is_dir( DATA_DEST_DIR ) ) {
					mkdir( DATA_DEST_DIR );
				}
		
				// Found one so lets download it
				$success = ftp_get( $conn, DATA_DEST_DIR . '/' . $file, $file, FTP_BINARY );
				if( $success ) {
					$this->log( "  Retrieved file '$file'." );
					ftp_close( $conn );
					return true;
				}
				else {
					$this->log( "  Error downloading file '$file'." );
					ftp_close( $conn );
					return false;
				}
			}
		}
		
		ftp_close( $conn );
		return false;
	}
	
	private function checkLocal( $date ) {
		$this->log( "Checking for local data files" );
		
		$files = scandir( LOCAL_DATA_PATH, SCANDIR_SORT_DESCENDING );
		if( $files == false ) {
			$this->log( "  Error reading local directory " . LOCAL_DATA_PATH );
			return false;
		}
		
		foreach( $files as $file ) {
			if( strstr( $file, $date ) ) {
				$this->log( "  Found file '$file'." );
		
				if( !is_dir( DATA_DEST_DIR ) ) {
					mkdir( DATA_DEST_DIR );
				}
				
				// Found one so copy it
				$copied = copy( LOCAL_DATA_PATH . '/' . $file, SITE_DIR . '/' . DATA_DEST_DIR . '/' . $file );
				if( $copied == false ) {
					$this->log( "  Error copying data file from " . LOCAL_DATA_PATH . '/' . $file . ' to ' . DATA_DEST_DIR . '/' . $file );
					return false;
				}
				
				$this->log( "  File copied to " . DATA_DEST_DIR );
				return true;
			}
		}
		
		return false;
	}
	
	public function importData( $date = null, $removeMissingData = false ) {
		$this->log( 'Import started at: ' . date( 'm-d-Y H:i:s' ) );
		
		// Import the data from the file based on the date
		if( $date == null ) {
			$date = date( 'm-d-Y' );
		}
		
		$dataFile = DATA_DEST_DIR . '/' . $this->filePrefix . $date . $this->fileExt;
		if( !file_exists( $dataFile ) ) {
			$this->log( "  Error could not find file $dataFile for import." );
			return;
		}
		
		$this->log( "Importing data from file '$dataFile'");
		
		// Get the data from the 
		$xlsx = new SimpleXLSX( $dataFile );
		$this->data = $xlsx->rows();
		
		// Get the column indexes
		$this->cols = array();
		foreach( $this->data[ 0 ] as $index => $name ) {
			$this->cols[ $name ] = $index;
		}
		
		/*
		if( !$this->importCategories( $removeMissingData ) ) {
			$this->log( '  Error importing categories.' );
		}
		*/
		
		if( !$this->importProducts( $removeMissingData ) ) {
			$this->log( '  Error importing products.' );
		}
		
		unlink( $dataFile );
		
		$this->log( "Finished importing data from file '$dataFile'" );
		$this->log( 'Import finished at: ' . date( 'm-d-Y H:i:s' ) . "\n" );
	}
	
	/*
	private function importCategories( $removeMissingData ) {
		assert( $this->data != null );
		
		// Get the categories
		$this->categories = array();
		foreach( $this->data as $index => $row ) {
			if( $index > 0 ) {
				$catName = $row[ $this->cols[ 'MainCategory' ] ];
				$found = false;
				foreach( $this->categories as $category ) {
					if( $category[ 'name' ] == $catName ) {
						$found = true;
						break;
					}
				}
		
				if( $found == false ) {
					$this->categories[] = array();
					$this->categories[ count( $this->categories ) - 1 ][ 'name' ] = $catName;
					$this->categories[ count( $this->categories ) - 1 ][ 'sub_categories' ] = array();
					
					/*
					// The image file name is based on the category name.
					// Example: If the category name is "Water Gardening" then the image name will be "category-water_gardening.png".
					// Spaces and slashes are replaced with underscores.
					$imageName = "category-" . strtolower( str_replace( array( ' ', '/' ), '_', $catName ) ) . '.png';
					$cwd = getcwd();
					if( !file_exists( "templates/img/$imageName" ) ) {
						// If the image file doesn't exist then use the default image.
						$imageName = 'category-default.png';
					}
					$this->categories[ count( $this->categories ) - 1 ][ 'image' ] = $imageName;
					*
				}
			}
		}
		
		// Get the sub-categories
		foreach( $this->data as $index => $row ) {
			if( $index > 0 ) {
				$catName = $row[ $this->cols[ 'MainCategory' ] ];
				$subCatName = $row[ $this->cols[ 'Category' ] ];
		
				// Special exception
				if( $subCatName == 'Image Not Available' ) {
					continue;
				}
		
				foreach( $this->categories as &$category ) {
					if( $category[ 'name' ] === $catName ) {
						$found = false;
						foreach( $category[ 'sub_categories' ] as $subCategory ) {
							if( $subCategory[ 'name' ] === $subCatName ) {
								$found = true;
								break;
							}
						}
		
						if( !$found ) {
							$category[ 'sub_categories' ][] = array();
							$category[ 'sub_categories' ][ count( $category[ 'sub_categories' ] ) - 1 ][ 'name' ] = $subCatName;
							break;
						}
					}
				}
			}
		}
		
		$db = new VirtualRainDB();
		
		// Get ids for existing categories
		$dbCats = $db->getAllCategories();
		foreach( $dbCats as $dbCat ) {
			$found = false;
			foreach( $this->categories as &$category ) {
				if( $category[ 'name' ] === $dbCat[ 'name' ] ) {
					$category[ 'id' ] = $dbCat[ 'id' ];
					$found = true;
					break;
				}
			}
				
			if( !$found ) {
				if( $removeMissingData ) {
					// Remove any categories from the db that are not in the data
					$db->removeCategory( $dbCat[ 'id' ] );
				}
			}
		}
		
		// Add any categories that are in the data and not in the db
		foreach( $this->categories as &$category ) {
			if( empty( $category[ 'id' ] ) && count( $category[ 'sub_categories' ] ) > 0 ) {
				// Add the category
				$id = $db->addCategory( $category[ 'name' ], $category[ 'image' ] );
				$category[ 'id' ] = $id;
			}
		}
		
		// Get ids for existing sub-categories
		foreach( $this->categories as &$category ) {
			// If the category does not have an id by now then we didn't add it
			// (probably because it has no sub-categories)
			if( isset( $category[ 'id' ] ) ) {
				$subCats = $db->getSubCategories( $category[ 'id' ] );
				foreach( $subCats as $subCat ) {
					$found = false;
					foreach( $category[ 'sub_categories' ] as &$subCategory ) {
						if( $subCategory[ 'name' ] == $subCat[ 'name' ] ) {
							$subCategory[ 'id' ] = $subCat[ 'id' ];
							$found = true;
							break;
						}
					}
						
					if( !$found ) {
						if( $removeMissingData ) {
							// Remove any sub-categories from the db that are not in the data
							$db->removeSubCategory( $subCat[ 'id' ] );
						}
					}
				}
		
				// Add any sub-categories that are in the data and not in the db
				foreach( $category[ 'sub_categories' ] as &$subCategory ) {
					if( empty( $subCategory[ 'id' ] ) ) {
						// Add the sub-category
						$id = $db->addSubCategory( $category[ 'id' ], $subCategory[ 'name' ] );
						$subCategory[ 'id' ] = $id;
					}
				}
			}
		}
		
		return true;
	}
	*/
	
	/**
	 * Imports the products and styles from the data in $this->data.
	 * 
	 * Products are determined by a unique PartNum value and styles 
	 * are determined by unique StyleNum values.
	 * 
	 * Every product will have at least 1 style. If a product has only 
	 * 1 style 'single' will be true. One of the styles for and individual 
	 * product will have a 'default' value of true (and the rest should be false).
	 * 
	 */
	private function importProducts( $removeMissingData ) {
		assert( $this->data != null );
		
		$db = new VirtualRainDB();
		
		$db->removeAllProducts();
		
		// Assemble the array of products and styles
		$products = array();
		
		foreach( $this->data as $index => $row ) {
			if( $index > 0 ) {
				$productIndex = null;
				$product = null;
				$partNum  = $row[ $this->cols[ 'PartNum' ] ];
				$manufaturer = $row[ $this->cols[ 'ManufacturerName' ] ];
				
				// We need to append the manufaturer name to the part number because some manufacturers
				// use the same part numbers
				//$partNum = $row[ $this->cols[ 'ManufacturerName' ] ] . '__' . $partNum;
				
				// Check if this product has already been added
				for( $i = count( $products ) - 1; $i >= 0; $i-- ) {
					if( $products[ $i ][ 'part_num' ] === $partNum && $products[ $i ][ 'manufacturer' ] === $manufaturer ) {
						$productIndex = $i;
						break;
					}
				}
				
				if( $productIndex === null ) {
					// Add the product
					$product = array();
					
					$product[ 'manufacturer' ] 		= $row[ $this->cols[ 'ManufacturerName' ] ];
					$product[ 'part_num' ] 			= $partNum;
					$product[ 'title' ] 			= $row[ $this->cols[ 'ProductTitle' ] ];
					if( isset( $row[ $this->cols[ 'Manufacturer_Description' ] ] ) ) {
						$product[ 'manufacturer_description' ] = $row[ $this->cols[ 'Manufacturer_Description' ] ];
					}
					//$product[ 'show_manual' ] 		= 1;
					//$product[ 'show_video' ] 		= 1;
					//$product[ 'show_specs' ] 		= 1;
					//if( isset( $row[ $this->cols[ 'Family' ] ] ) ) {
					//	$product[ 'family' ] 			= $row[ $this->cols[ 'Family' ] ];
					//}
					
					/*
					// Get the sub-category id
					$subCatId = null;
					foreach( $this->categories as $category ) {
						if( $category[ 'name' ] == $row[ $this->cols[ 'MainCategory' ] ] ) {
							foreach( $category[ 'sub_categories' ] as $subCategory ) {
								if( $subCategory[ 'name' ] === $row[ $this->cols[ 'Category' ] ] ) {
									$subCatId = $subCategory[ 'id' ];
								}
							}
						}
					}
					if( $subCatId == null ) {
						$this->log( "  Failed to load product. Sub-cat '{$row[ $this->cols[ 'Category' ] ]}' could not be found." );
						continue;
					}
					$product[ 'sub_cat_id' ] 		= $subCatId;
					*/
				}
				
				// Add the style
				$style = array();
				
				$style[ 'style_num' ] 			= $row[ $this->cols[ 'StyleNum' ] ];
				if( isset( $row[ $this->cols[ 'Style_Description' ] ] ) ) {
					$style[ 'style_description' ] 	= $row[ $this->cols[ 'Style_Description' ] ];
				}
				$style[ 'full_sku' ] 			= $row[ $this->cols[ 'FullSku' ] ];
				$style[ 'image_name' ] 			= $row[ $this->cols[ 'image_name' ] ];
				$style[ 'large_image_name' ] 	= $row[ $this->cols[ 'Large_image_name' ] ];
				$style[ 'cost' ] 				= $row[ $this->cols[ 'Cost' ] ];
				$style[ 'price' ] 				= $row[ $this->cols[ 'Price' ] ];
				isset( $row[ $this->cols[ 'UPC' ] ] ) ? $style[ 'upc' ] = $row[ $this->cols[ 'UPC' ] ] : null;
				$productIndex === null ? $style[ 'default_style' ] = 1 : $style[ 'default_style' ] = 0;
				
				if( $productIndex === null ) {
					assert( is_array( $product ) );
					//$style[ 'single' ] = 1;
					$product[ 'styles' ] = array();
					$product[ 'styles' ][] = $style;
					$products[] = $product;
				}
				else {
					//$style[ 'single' ] = 0;
					$products[ $productIndex ][ 'styles' ][] = $style;
					/*
					$styleCount = count( $products[ $productIndex ][ 'styles' ] );
					if( $styleCount > 1 ) {
						if( $styleCount == 2 ) {
							$products[ $productIndex ][ 'styles' ][ $styleCount - 2 ][ 'single' ] = 0;
						}
					}
					*/
				}
			}
		}
		
		$this->log( "  Found " . count( $products ) . " products in the new data." );
		
		/*
		if( $removeMissingData ) {
			$this->log( "  Removing missing data..." );
			// Remove any products in the db that are not in the $products array
			$dbProducts = $db->getAllProducts();
			$this->log( "  There is now a total of " . count( $dbProducts ) . " in the database." );
			foreach( $dbProducts as $dbProduct ) {
				
				$found = false;
				foreach( $products as $product ) {
					if( $dbProduct[ 'part_num' ] === $product[ 'part_num' ] ) {
						$found = true;
						
						// Remove any product styles in the db for this product that are not in the $product[ 'styles' ] array
						$dbStyles = $db->getProductStyles( array( $dbProduct[ 'id' ] ) );
						foreach( $dbStyles as $dbStyle ) {
							
							$foundStyle = false;
							foreach( $product[ 'styles' ] as $style ) {
								if( $dbStyle[ 'style_num' ] === $style[ 'style_num' ] ) {
									$foundStyle = true;
									break;
								}
							}
							
							if( !$foundStyle ) {
								$result = $db->removeProductStyle( $dbStyle[ 'id' ] );
								if( $result == false ) {
									$this->log( "  Failed to delete product style '{$dbStyle[ 'id' ]}'" );
								}
							}
						}
						
						break;
					}
				}
				
				if( !$found ) {
					$result = $db->removeProduct( $dbProduct[ 'id' ] );
					if( $result == false ) {
						$this->log( "  Failed to delete product '{$dbProduct[ 'id' ]}'" );
					}
				}
			}
		}
		*/
		
		// Import the products and styles into the database
		foreach( $products as $product ) {
			// Create or update the products record
			$productId = $db->addOrUpdateProduct( $product );
			if( $productId === false ) {
				$this->log( "  Error adding or updating product '{$product[ 'manufacturer' ]} : {$product[ 'part_num' ]}'. " . $db->getLastError() );
			}
			
			foreach( $product[ 'styles' ] as &$style ) {
				$style[ 'product_id' ] = $productId;
				
				// Create or update the style record
				$styleId = $db->addOrUpdateProductStyle( $style );
				if( $styleId === false ) {
					$this->log( "  Error adding or updating product style '{$product[ 'part_num' ]}'-'{$style[ 'style_num' ]}'. " . $db->getLastError() );
				}
				$style[ 'id' ] = $styleId;
			}
		}
		
		return true;
	}
	
	private function log( $msg ) {
		$logFileName = LOG_FILE;
		
		$output = $msg . "\n";
		if( file_put_contents( $logFileName, $output, FILE_APPEND ) == false ) {
			throw new Exception( "Could not write to log file '$logFileName'." );
		}
		
		if( $this->echoLog ) {
			$output = $msg . '<br/>';
			echo( $output );
		}
	}
}










