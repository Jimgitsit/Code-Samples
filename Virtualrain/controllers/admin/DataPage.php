<?php

require_once( 'AdminPage.php' );
require_once( 'DataImportService.php' );
require_once( 'VirtualRainDB.php' );
require_once( 'PHPExcel.php' );

class DataPage extends AdminPage {
	
	private $response = array( 'success' => true, 'msg' => '', 'file_name' => '', 'errors' =>array(), 'warnings' => array() );
	
	public function __construct( $template ) {
		parent::__construct( $template, 'Admin Users' );

		$this->handlePost();
	}

	private function handlePost(){
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			
			switch( $_POST[ 'action' ] ) {
				case 'export_dist_product_data': {
					$fileName = 'Products_' . date( 'd-m-Y_H-i-s' ) . '.xlsx';
					$this->response[ 'file_name' ] = $fileName;
					
					$this->export();
					
					echo( json_encode( $this->response ) );
					exit();
				}
				case 'import_dist_product_data': {
					try {
						if( empty( $_FILES[ 'import_file' ] ) ) {
							$this->response[ 'success' ] = false;
							$this->response[ 'msg' ] = "Server did not get file.";
							$this->response[ 'errors' ][] = "Server did not get file.";
						}
						else if( $_FILES[ 'import_file' ][ 'type' ] !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ) {
							$this->response[ 'success' ] = false;
							$this->response[ 'msg' ] = "Invalid file type: " . $_FILES[ 'import_file' ][ 'type' ] . ". Looking for application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
							$this->response[ 'errors' ][] = "Invalid file type.";
						}
						else {						
							// Copy uploaded file
							$dataDir = SITE_DIR . "/" . DATA_DEST_DIR;
							$excelFile = $_FILES[ 'import_file' ][ 'name' ];
							if( move_uploaded_file( $_FILES[ 'import_file' ][ 'tmp_name' ], $dataDir . '/' . $excelFile ) ) {
								
								// Open the file and get the 1st worksheet
								$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_discISAM;
								PHPExcel_Settings::setCacheStorageMethod( $cacheMethod );
								$inputFileType = PHPExcel_IOFactory::identify( $dataDir . '/' . $excelFile );
								$reader = PHPExcel_IOFactory::createReader($inputFileType);
								$reader->setReadDataOnly( true );
								$wb = @$reader->load( $dataDir . '/' . $excelFile );
								
								// Validate the spreadsheet
								if( !empty( $_POST[ 'ignore_warnings' ] ) && $_POST[ 'ignore_warnings' ] == 'no' ) {
									$this->validateData( $wb );
								}
								
								if( $this->response[ 'success' ] == true ) {
									// Do the import
									$this->import( $wb );
								}
								
								unlink( $dataDir . '/' . $excelFile );
							}
							else {
								$this->response[ 'success' ] = false;
								$this->response[ 'msg' ] = "Error: Could not copy uploaded file.";
								$this->response[ 'errors' ][] = "Could not copy uploaded file.";
							}
						}
					}
					catch( Exception $e ) {
						$this->response[ 'success' ] = false;
						$this->response[ 'msg' ] = "An exception occured.";
						$this->response[ 'errors' ][] = $e->getMessage();
					}
					
					echo( json_encode( $this->response ) );
					exit();
				}
			}
		}
	}
	
	private function export() {
		$prefix = 'Products_' . $_SESSION[ 'distributer' ][ 'id' ] . '_';
		
		// Remove any previous files
		$dataDir = SITE_DIR . "/" . DATA_DEST_DIR;
		$files = scandir( $dataDir );
		if( $files == false ) {
			throw new Exception( "Error reading data directory. " . $dataDir );
		}
		foreach( $files as $file ) {
			if( strstr( $file, $prefix ) ) {
				unlink( $dataDir . "/" . $file );
			}
		}
		
		$fileName = $prefix . date( 'Ymd_His' ) . '.xlsx';
			
		try {
			$db = new VirtualRainDB();
			$distId = $_SESSION[ 'distributer' ][ 'id' ];
			
			$maxCatLevel = $db->getDistMaxCategoryLevel( $distId );
			
			// Create a new spreadsheet
			$excel = new PHPExcel();
			$prodSheet = $excel->getSheet();
			$prodSheet->setTitle( "Products" );
			$col = 0;
			
			// Add the categories
			$prodSheet->setCellValueByColumnAndRow( $col, 1, 'Category1' );
			$col++;
			for( $level = $col + 1; $level <= $maxCatLevel; $level++ ) {
				$prodSheet->setCellValueByColumnAndRow( $col, 1, 'Category' . $level );
				$col++;
			}
			
			// Build the header row
			foreach( DataImportService::$exportHeaders as $header ) {
				$prodSheet->setCellValueByColumnAndRow( $col, 1, $header );
				$col++;
			}
			
			// Get products and categories from the database
			$products = $db->getDistProductsForExport( $distId );
			$categories = $db->getDistCategories( $distId, null, null, true );
			
			// Export the data rows
			$currRow = 2;
			foreach( $products as $product ) {
				// Add the categories (in reverse order)
				$prodCat = $categories[ $product[ 'category_id' ] ];
				for( $level = $maxCatLevel; $level >= 1; $level-- ) {
					if( $prodCat[ 'level' ] == $level ) {
						$prodSheet->setCellValueByColumnAndRow( $level - 1, $currRow, $prodCat[ 'name' ] );
						$parentId = $prodCat[ 'parent_id' ];
						$parentLevel = $level - 1;
						while( $parentId !== null && $parentLevel > 0 ) {
							$parentCat = $categories[ $parentId ];
							$prodSheet->setCellValueByColumnAndRow( $parentLevel - 1, $currRow, $parentCat[ 'name' ] );
							$parentId = $parentCat[ 'parent_id' ];
							$parentLevel--;
						}
						break;
					}
				}
				
				$col = $maxCatLevel;
				
				$prodSheet->setCellValueByColumnAndRow( $col, $currRow, $product[ 'title' ] );
				$prodSheet->setCellValueByColumnAndRow( $col + 1, $currRow, $product[ 'manufacturer' ] );
				$prodSheet->setCellValueByColumnAndRow( $col + 2, $currRow, $product[ 'manufacturer_description' ] );
				$prodSheet->setCellValueByColumnAndRow( $col + 3, $currRow, $product[ 'style_description' ] );
				$prodSheet->setCellValueByColumnAndRow( $col + 4, $currRow, $product[ 'price' ] );
				if( $product[ 'default_style' ] == 1 ) {
					$prodSheet->setCellValueByColumnAndRow( $col + 5, $currRow, 'yes' );
				}
				else {
					$prodSheet->setCellValueByColumnAndRow( $col + 5, $currRow, 'no' );
				}
				$prodSheet->setCellValueByColumnAndRow( $col + 6, $currRow, $product[ 'sku' ] );
				$prodSheet->setCellValueByColumnAndRow( $col + 7, $currRow, $product[ 'part_num' ] );
				$prodSheet->setCellValueByColumnAndRow( $col + 8, $currRow, $product[ 'style_num' ] );
				$prodSheet->setCellValueByColumnAndRow( $col + 9, $currRow, $product[ 'upc' ] );
				$prodSheet->setCellValueByColumnAndRow( $col + 10, $currRow, $product[ 'upc' ] );
				
				$currRow++;
			}
			
			$write = PHPExcel_IOFactory::createWriter( $excel, "Excel2007" );
			$write->save( SITE_DIR . "/data/" . $fileName );
			
			$this->response[ 'file_name' ] = $fileName;
		
			// Cleanup
			$excel->disconnectWorksheets();
			unset( $excel );
		}
		catch( Exception $e ) {
			$this->response[ 'success' ] = false;
			$this->response[ 'errors' ][] = $e->getMessage();
		}
			
		echo( json_encode( $this->response ) );
		exit();
	}
	
	private function validateData( $wb ) {
		$headers = DataImportService::$importHeaders;
		$headerPos = null;
		
		$sheet = $wb->getSheet( 0 );
		$test = $sheet->getHighestRowAndColumn();
		foreach( $sheet->getRowIterator() as $row ) {
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells( false );
			
			$rowIndex = $row->getRowIndex();
			if( $rowIndex == 1 ) {
				// Check the header row for the required columns
				$headerPos = $this->getHeaderPositions( $cellIterator );
				
				// Make sure we have all the required columns
				$missingHeaders = false;
				foreach( $headers as $header => $required ) {
					if( $required == true && !array_key_exists( $header, $headerPos ) ) {
						// Missing required column
						$missingHeaders = true;
						$this->response[ 'success' ] = false;
						$this->response[ 'msg' ] = "An error was detected in the spreadsheet. The spreadsheet can not be imported.";
						$this->response[ 'errors' ][] = "Missing required column '$header'";
					}
				}
		
				if( $missingHeaders ) {
					break;
				}
			}
			else {
				// Validate that each row has a value for each required column
				foreach( $headers as $header => $required ) {
					if( $required ) {
						$value = trim( $sheet->getCellByColumnAndRow( $headerPos[ $header ], $rowIndex ) );
						if( $value == '' ) {
							$this->response[ 'success' ] = false;
							$this->response[ 'errors' ][] = "Missing '$header' for row $rowIndex.";
						}
					}
				}
			}
		}
	}
	
	private function getHeaderPositions( $cellIterator ) {
		$headerPos = array();
		$col = 0;
		foreach( $cellIterator as $cell ) {
			$value = $cell->getValue();
			$headerPos[ $value ] = $col;
			$col++;
		}
		return $headerPos;
	}
	
	private function import( $wb ) {
		$db = new VirtualRainDB();
		
		try {
			// Start a db transaction
			$db->startTransaction();
			
			$distId = $_SESSION[ 'distributer' ][ 'id' ];
			
			//if( !empty( $_POST[ 'opt_remove_deleted' ] ) && $_POST[ 'opt_remove_deleted' ] == 'on' ) {
				// Delete all products, styles, and categories for this distributer
				$db->deleteAllDistProducts( $distId );
				$db->removeAllDistCategories( $distId );
			//}
			
			$headers = DataImportService::$importHeaders;
			$headerPos = null;
			
			$lastSKU = null;
			$lastProductId = null;
			
			$addedCategories = array();
			
			$sheet = $wb->getSheet( 0 );
			foreach( $sheet->getRowIterator() as $row ) {
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells( false );
			
				$rowIndex = $row->getRowIndex();
				// Skip the header row
				if( $rowIndex == 1 ) {
					$headerPos = $this->getHeaderPositions( $cellIterator );
				}
				else {
					// Get the row data for all possible columns
					$rowData = $headers;
					foreach( $rowData as &$r ) {
						// Reset all data values first
						$r = '';
					}
					$col = 0;
					foreach( $cellIterator as $cell ) {
						$rowData[ array_search( $col, $headerPos ) ] = trim( $cell->getValue() );
						
						$col++;
						if( $col > count( $headerPos ) ) {
							break;
						}
					}
					
					// Add the categories
					$catId = null;
					$catLevel = 1;
					$parentCatId = null;
					$catImageURL = '';
					while( !empty( $rowData[ 'Category' . $catLevel ] ) ) {
						$catName = $rowData[ 'Category' . $catLevel ];
						
						// Determine the image url for this category
						$pattern = '/[^A-Za-z0-9-_~.\s]/';
						$catImageURL .= '/' . rawurlencode(strtoupper(preg_replace($pattern, '', $catName)));
						
						$catId = null;
						
						$addCat = true;
						foreach( $addedCategories as $addedCat ) {
							if( $distId == $addedCat[ 'dist_id' ] && $catName == $addedCat[ 'name' ] && $catLevel == $addedCat[ 'level' ] && $parentCatId == $addedCat[ 'parent_id' ] ) {
								$addCat = false;
								$catId = $addedCat[ 'id' ];
								break;
							}
						}
						
						if( $addCat ) {
							$catId = $db->addDistCategory( $distId, $catName, $catLevel, $parentCatId, $catImageURL . '.jpg' );
							
							// Cache the list of added categories so we can tell in the next 
							// itteration if the category has already beed added.
							$addedCategories[$catId] = array( 'dist_id' => $distId, 'name' => $catName, 'level' => $catLevel, 'id' => $catId, 'parent_id' => $parentCatId, 'product_count' => 0 );
						}
						
						$addedCategories[$catId]['product_count'] += 1;
						
						$parentCatId = $catId;
						$catLevel++;
					}
					
					// Combine the meta data
					$rowData['meta'] = ''; 
					$metaLevel = 1;
					while (!empty($rowData['Meta' . $metaLevel])) {
						$rowData['meta'] .= $rowData['Meta' . $metaLevel] . ' ';
						$metaLevel++;
					}
					
					// Insert or update the product
					$productId = $lastProductId;
					if( $rowData[ 'SKU' ] != $lastSKU ) {
						$productId = $db->insertOrUpdateDistProduct( 
								$distId, 
								$rowData[ 'Product Title' ], 
								$catId,
								$rowData[ 'Product Description' ], 
								$rowData[ 'Manufacturer' ], 
								$rowData[ 'Product Number' ],
								$rowData[ 'SKU' ],
								$rowData[ 'Image' ]
						);
					}
					
					// Insert or update the style
					$styleNum = $rowData[ 'Style Number' ];
					if( empty( $styleNum ) ) {
						// If we don't have a style number then use the product SKU for the style
						$styleNum = $rowData[ 'SKU' ];
					}
					
					$db->insertOrUpdateDistProductStyle(
							$distId,
							$productId,
							$rowData[ 'Style Description' ],
							$styleNum,
							$rowData[ 'UPC' ],
							$rowData[ 'Style Price' ],
							$rowData[ 'Unit' ],
							$rowData[ 'Default Style' ],
							$rowData['meta']
					);
					
					$lastSKU 		= $rowData[ 'SKU' ];
					$lastProductId	= $productId;
				}
			}
			
			// Purge unused categories
			$db->removeUnusedDistCategories( $distId );
			
			// Save the product counts for all categories
			foreach ($addedCategories as $cat) {
				$db->updateDistCategoryProductCount($cat['id'], $cat['product_count']);
			}
			
			// End the transaction
			$db->commitTransaction();
		}
		catch( Exception $e ) {
			// Rollback the transaction
			$db->rollbackTransaction();
			
			throw $e;
		}
	}
}











