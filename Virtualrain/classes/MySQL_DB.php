<?php

require_once( 'config/config.php' );

/**
 * Base call class for accessing a MySQL database.
 * Handles construction a single database connection.
 * Call self::getDB() from a subclass to get the single instance of the mysqli object.
 * 
 * The following defines are required in config.php:
 * 		DB_HOST
 * 		DB_USER
 * 		DB_PASS
 * 		DB_NAME
 * 
 * @author Jim McGowen
 *
 */
abstract class MySQL_DB {
	private static $db = null;
	private static $inTransaction = false;
	
	public function __construct() {
		if( !defined( 'DB_HOST' ) ) { die( 'Error in MySQL_DB: DB_HOST is undefined.' ); }
		if( !defined( 'DB_USER' ) ) { die( 'Error in MySQL_DB: DB_USER is undefined.' ); }
		if( !defined( 'DB_PASS' ) ) { die( 'Error in MySQL_DB: DB_PASS is undefined.' ); }
		if( !defined( 'DB_NAME' ) ) { die( 'Error in DB_NAME: DB_HOST is undefined.' ); }
	}
	
	protected static function getDB() {
		if( self::$db == null ) {
			self::$db = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );
		}
		
		return self::$db;
	}
	
	public function startTransaction() {
		$db = self::getDB();
	
		$db->begin_transaction();
		self::$inTransaction = true;
	}
	
	public function commitTransaction() {
		$db = self::getDB();
	
		if( self::$inTransaction ) {
			$db->commit();
			self::$inTransaction = false;
		}
	}
	
	public function rollbackTransaction() {
		$db = self::getDB();
	
		if( self::$inTransaction ) {
			$db->rollback();
			self::$inTransaction = false;
		}
	}
	
	public function saveRecord( $tableName, $id, $data ) {
		assert( is_array( $data ) );
		
		$query = "INSERT INTO $tableName SET(";
		foreach( $data as $field => $value ) {
			$query .= "$field=$value,";
		}
		$query = substr( $query, 0, strlen( $query ) - 1 );
		$query .= ") WHERE id=$recordId";
		
		$db = self::getDB();
		$result = $db->query( $query );
		return $result;
	}
	
	/**
	 * Helper funciton to create an array from a result set.
	 * The array returned an multi-dimensional array of the rows in the 
	 * result and the columns for each row. The array will be numerically 
	 * indexed even if there is only one row in the result.
	 */
	public function resultToArray( $result ) {
		$theArray = array();
		$result->data_seek( 0 );
		while( $row = $result->fetch_assoc() ) {
			$theArray[] = $row;
		}
		return $theArray;
	}
	
	public function getFieldTypes( $table ) {
		$db = self::getDB();
		
		$result = $db->query( "SHOW COLUMNS FROM $table" );
		if( $result == false ) {
			throw new Exception($this->getLastError());
		}
		
		$cols = $this->resultToArray( $result );
		
		$fieldTypes = array();
		foreach( $cols as $col ) {
			$fieldTypes[ $col[ 'Field' ] ] = $col[ 'Type' ];
		}
		
		return $fieldTypes;
	}
	
	public function createSingleInsertStatement( $tableName, $fieldValues ) {
		$db = self::getDB();
		
		$fieldTypes = $this->getFieldTypes( $tableName );
		
		$fields = '';
		$values = '';
		foreach( $fieldValues as $field => $value ) {
			if( is_array( $value ) ) {
				continue;
			}
			
			if( array_key_exists( $field, $fieldTypes ) ) {
				if( stripos( $fieldTypes[ $field ], 'varchar' ) !== false ||
					stripos( $fieldTypes[ $field ], 'text' ) !== false || 
					stripos( $fieldTypes[ $field ], 'datetime' ) !== false || 
					stripos( $fieldTypes[ $field ], 'blob' ) !== false ) 
				{
					$value = "'" . $db->real_escape_string( $value ) . "'";
				}
				else {
					$value = $db->real_escape_string( $value );
				}
				
				$field = $db->real_escape_string( $field );
				
				$fields .= $field . ',';
				$values .= $value . ',';
			}
		}
		$fields = trim( $fields, ',' );
		$values = trim( $values, ',' );
		
		$query = "INSERT INTO $tableName ($fields) VALUES($values)";
		
		return $query;
	}
	
	public function createSingleUpdateStatement( $tableName, $primaryKey, $fieldValues ) {
		$db = self::getDB();
		
		$fieldTypes = $this->getFieldTypes( $tableName );
		if( $fieldTypes == false ) {
			echo( "Error in createSingleUpdateStatement: Could not get field types for table $tableName" );
			return false;
		}
		
		$set = '';
		$pkValue = null;
		foreach( $fieldValues as $field => $value ) {
			if( is_array( $value ) ) {
				continue;
			}
			
			if( array_key_exists( $field, $fieldTypes ) ) {
				if( stripos( $fieldTypes[ $field ], 'varchar' ) !== false ||
					stripos( $fieldTypes[ $field ], 'text' ) !== false || 
					stripos( $fieldTypes[ $field ], 'datetime' ) !== false || 
					stripos( $fieldTypes[ $field ], 'blob' ) !== false ) 
				{
					$value = "'" . $db->real_escape_string( $value ) . "'";
				}
				else {
					$value = $db->real_escape_string( $value );
				}
				
				$field = $db->real_escape_string( $field );
					
				if( $field == $primaryKey ) {
					$pkValue = $value;
				}
				else {
					$set .= "`$field`=$value,";
				}
			}
		}
		$set = trim( $set, ',' );
		
		$query = "UPDATE $tableName SET $set WHERE $primaryKey = $pkValue";
		
		return $query;
	}
	
	public function createAddOrUpdateStatment( $tableName, $primaryKey, $fieldValues ) {
		$db = self::getDB();
		
		if( !empty( $fieldValues[ $primaryKey ] ) ) {
			$pkValue = $fieldValues[ $primaryKey ];
			if( !is_numeric( $fieldValues[ $primaryKey ] ) ) {
				$pkValue = "'$pkValue'";
			}
			$query = "SELECT $primaryKey FROM $tableName WHERE $primaryKey = $pkValue";
			$result = $db->query( $query );
			if( $result == false ) {
				throw new Exception($this->getLastError());
			}
		
			if( $result->num_rows > 0 ) {
				// Update
				$query = $this->createSingleUpdateStatement( $tableName, $primaryKey, $fieldValues );
				return $query;
			}
		}
		
		// Insert
		$query = $this->createSingleInsertStatement( $tableName, $fieldValues );
		return $query;
	}
	
	public function getLastError() {
		$db = self::getDB();
		return $db->errno . ': ' . $db->error;
	}
	
	public function query($query) {
		$db = self::getDB();
		$result = $db->query( $query );
		if( $result == false ) {
			throw new Exception($this->getLastError());
		}
		
		return $result;
	}
	
	public function getOne($query) {
		$result = $this->query($query);
		if ($result->num_rows > 0) {
			return $result->fetch_assoc();
		}
		else {
			return null;
		}
	}
	
	public function getAll($query) {
		$result = $this->query($query);
		return $this->resultToArray($result);
	}
}














