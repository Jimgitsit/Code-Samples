<?php

require_once('MySQL_DB.php');
require_once('Util.php');

/**
 * Model for the virtual rain database.
 *
 * Connection paramerter are defined in config/config_db.php.
 *
 * @author Jim McGowen
 * @see MySQL_DB
 */
class VirtualRainDB extends MySQL_DB
{

	public static $DEFAULT_SEARCH_FIELDS = array('product.title', 'product.manufacturer', //'product.manufacturer_description',
		//'product.sku',
		//'style.style_description',
		'style.meta');

	/**
	 * Returns the database instance.
	 *
	 * @return mysqli
	 */
	public static function getDB()
	{
		return parent::getDB();
	}

	/**
	 * Returns a user record or false if the user was not found or if
	 * the password is incorrect.
	 *
	 * @param string $email The users email (unique to each user)
	 * @param string $password The plain text password.
	 *
	 * @return boolean|array
	 */
	public function getUser($email, $password, $distId = null)
	{
		// Pull salt and re-hash password
		$db = self::getDB();
		$query = "SELECT salt FROM users WHERE email='$email'";
		if ($distId !== null) {
			$query .= " AND dist_id=$distId";
		}

		$result = $db->query($query);
		if ($result->num_rows == 0) {
			// User was not found
			return false;
		}

		$user = $result->fetch_assoc();
		$salt = $user['salt'];

		$pw = Util::encryptPassword($password, $salt);
		$currentPassword = $pw['encrypted_password'];

		// Check to see if both username and password exists
		$fromTz = ini_get('date.timezone');
		$toTz = $_SESSION['client_timezone'];
		$sql = "SELECT *, CONVERT_TZ(last_login, '$fromTz', '$toTz') AS local_last_login FROM users WHERE password='$currentPassword' AND email='$email'";
		if ($distId !== null) {
			$sql .= " AND dist_id=$distId";
		}

		$result = $db->query($sql);
		if ($result->num_rows == 1) {
			$user = $result->fetch_assoc();
			unset($user['salt']);
			unset($user['password']);

			return $user;
		}
		else {
			// Bad password or unknown user
			return false;
		}
	}

	/**
	 * Fetches a single user from the db given the user id.
	 *
	 * @param int $userId
	 *
	 * @return array|NULL
	 */
	public function getUserById($userId)
	{
		$db = self::getDB();
		$query = "SELECT * FROM users WHERE id=$userId";
		$result = $db->query($query);
		if ($result->num_rows == 0) {
			// User was not found
			return false;
		}

		$user = $this->getOne($query);

		if ($user['branch_id'] != null && $user['branch_id'] != 0) {
			$query = "SELECT * FROM branches WHERE id=" . $user['branch_id'];
			$branch = $this->getBranch($user['branch_id']);
			if ($branch != null) {
				$user['branch'] = $branch;
			}
		}

		return $user;
	}
	
	
	public function getUserByEmail($email)
	{
		$db = self::getDB();
		$query = "SELECT * FROM users WHERE email='$email'";
		$result = $db->query($query);
		if ($result->num_rows == 0) {
			// User was not found
			return false;
		}

		$user = $this->getOne($query);

		if ($user['branch_id'] != null && $user['branch_id'] != 0) {
			$query = "SELECT * FROM branches WHERE id=" . $user['branch_id'];
			$branch = $this->getBranch($user['branch_id']);
			if ($branch != null) {
				$user['branch'] = $branch;
			}
		}

		return $user;
	}
	

	/**
	 * Returns an array of users. If $distId is supplied, only users
	 * for that dist will be returned.
	 *
	 * @param int $distId
	 *
	 * @return array|boolean
	 */
	public function getUsers($distId = null, $branchId = null, $indexById = false, $sort = null)
	{
		
		$fromTz = ini_get('date.timezone');
		$toTz = $_SESSION['client_timezone'];
		$query = "SELECT *, CONVERT_TZ(last_login, '$fromTz', '$toTz') AS local_last_login FROM users";
		if ($distId !== null) {
			$query .= " WHERE dist_id=$distId";
		}
		if ($branchId !== null) {
			$query .= " AND branch_id=$branchId";
		}
		
		
		if ($sort !== null) {
			$query .= " ORDER BY";
			foreach ($sort as $by => $dir) {
				$query .= " $by $dir";
			}
		} else {
			$query .= " ORDER BY status DESC, first_name ASC, last_name ASC";
		}

		$users = $this->getAll($query);

		if ($branchId != null) {
			// We also need to look for any orders assigned to this branch, but from
			// users assigned to another branch and include them in this result
			$userIds = array();
			foreach ($users as $user) {
				$userIds[] = $user['id'];
			}

			$query = "SELECT user_id FROM orders WHERE branch_id = $branchId";
			$orders = $this->getAll($query);
			foreach ($orders as $order) {
				if (!in_array($order['user_id'], $userIds)) {
					$user = $this->getUserById($order['user_id']);
					$users[] = $user;
					$userIds[] = $order['user_id'];
				}
			}
		}

		//if($branchId == null) {
		// Add the branch for each user
		foreach ($users as &$userRef) {
			if ($userRef['branch_id'] != null && $userRef['branch_id'] != 0) {
				//$query = "SELECT * FROM branches WHERE id=" . $user['branch_id'];
				$branch = $this->getBranch($userRef['branch_id']);
				if ($branch != null) {
					$userRef['branch'] = $branch;
				}
			}
		}
		//}

		if ($indexById) {
			$usersById = array();
			foreach ($users as $user) {
				$usersById[$user['id']] = $user;
			}

			return $usersById;
		}

		return $users;
	}

	/**
	 * Returns an array of active users. If $distId is supplied, only users
	 * for that dist will be returned.
	 *
	 * @param int $distId
	 *
	 * @return array|boolean
	 */
	public function getActiveUsers($distId = null, $branchId = null)
	{
		$query = "SELECT * FROM users WHERE status = 1";
		if ($distId !== null) {
			$query .= " AND dist_id=$distId";
		}
		if ($branchId !== null) {
			$query .= " AND branch_id=$branchId";
		}

		$users = $this->getAll($query);

		if ($branchId != null) {
			// We also need to look for any orders assigned to this branch, but from
			// users assigned to another branch and include them in this result
			$userIds = array();
			foreach ($users as $user) {
				$userIds[] = $user['id'];
			}

			$query = "SELECT user_id FROM orders WHERE branch_id = $branchId";
			$orders = $this->getAll($query);
			foreach ($orders as $order) {
				if (!in_array($order['user_id'], $userIds)) {
					$user = $this->getUserById($order['user_id']);
					$users[] = $user;
					$userIds[] = $order['user_id'];
				}
			}
		}

		foreach ($users as &$user) {
			if ($user['branch_id'] != null && $user['branch_id'] != 0) {
				$query = "SELECT * FROM branches WHERE id=" . $user['branch_id'];
				$branch = $this->getBranch($user['branch_id']);
				if ($branch != null) {
					$user['branch'] = $branch;
				}
			}
		}

		return $users;
	}

	/**
	 * @param $userId
	 *
	 * @return bool
	 */
	public function updateUserLastLogin($userId)
	{
		$query = "UPDATE users SET last_login=NOW() WHERE id=$userId";
		$db = self::getDB();
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}
	}

	/**
	 * @param $userId
	 * @param $regId
	 */
	public function setUserGCMRegId($userId, $regId)
	{
		$query = "UPDATE users SET gcm_reg_id='$regId' WHERE id=$userId";
		$db = self::getDB();
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}
	}

	/**
	 * @param $userId
	 * @param $regId
	 */
	public function setUserAPNSId($userId, $regId)
	{
		$query = "UPDATE users SET apns_id='$regId' WHERE id=$userId";
		$db = self::getDB();
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}
	}

	/**
	 * @param $userIds
	 *
	 * @return array
	 */
	public function getGCMIds($userIds)
	{
		$query = "SELECT gcm_reg_id FROM users WHERE (gcm_reg_id IS NOT NULL AND gcm_reg_id != '') AND (";
		foreach ($userIds as $userId) {
			$query .= " id=$userId OR ";
		}
		$query = substr($query, 0, -4) . ')';
		$db = self::getDB();
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}

		$rows = $this->resultToArray($result);

		$ids = array();
		foreach ($rows as $row) {
			if (!empty($row['gcm_reg_id'])) {
				$ids[] = $row['gcm_reg_id'];
			}
		}

		return $ids;
	}

	/**
	 * @param $userIds
	 *
	 * @return array
	 */
	public function getAPNSIds($userIds)
	{
		$query = "SELECT apns_id, id FROM users WHERE (apns_id IS NOT NULL AND apns_id != '' AND apns_id != '(null)') AND (";
		foreach ($userIds as $userId) {
			$query .= " id=$userId OR ";
		}
		$query = substr($query, 0, -4) . ')';
		$db = self::getDB();
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}

		$rows = $this->resultToArray($result);

		$ids = array();
		foreach ($rows as $row) {
			if (!empty($row['apns_id'])) {
				$ids[$row['id']] = $row['apns_id'];
			}
		}

		return $ids;
	}
	

	/**
	 * @param $distId
	 * @param null $branchId
	 *
	 * @return array
	 */
	public function getCompanyNames($distId, $branchId = null)
	{
		$query = "SELECT company_name FROM users WHERE dist_id=$distId";
		if ($branchId !== null) {
			$query .= " AND branch_id=$branchId";
		}
		$query .= " GROUP BY company_name";

		return $this->getAll($query);
	}

	/**
	 * Updates a user in the db.
	 *
	 * @param array $user An array with all the user table fields.
	 *
	 * @return boolean
	 */
	public function updateUser($user)
	{
		if (!empty($user['password'])) {
			$pw = Util::encryptPassword($user['password']);
			$user['salt'] = $pw['salt'];
			$user['password'] = $pw['encrypted_password'];
		}
		else {
			unset($user['password']);
		}

		$db = self::getDB();
		$query = $this->createSingleUpdateStatement('users', 'id', $user);
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);

			return false;
		}

		return true;
	}


	/**
	 * Save users.
	 *
	 * @return boolean
	 */
	/*
	public function saveUser( $data ) {
		$user_id = $data[ "user_id" ];
		$first_name = $data[ "first_name" ];
		$last_name = $data[ "last_name" ];
		$account_num = $data[ "account_num" ];
		$email = $data[ "email" ];
		$cell_phone = $data[ "cell_phone" ];
		$status = $data[ "status" ];
		$company_name = $data[ "company_name" ];
		
		$db = self::getDB();
		$query = "UPDATE users SET
			first_name = '$first_name',
			last_name = '$last_name',
			account_num = '$account_num',
			company_name = '$company_name',
			email = '$email',
			cell_phone = '$cell_phone',
			status = $status
			WHERE  users.id = $user_id;";
		
		$result = $db->query( $query );
		if( $result === false ) {
			return false;
		}
		else {
			return true;
		}
	}
	*/

	/**
	 * Returns true if a user with the given email address exists.
	 * False if not.
	 *
	 * @param string $email The users email (unique to each user)
	 *
	 * @return boolean
	 */
	public function userExists($email)
	{
		$db = self::getDB();
		$query = "SELECT * FROM users WHERE email = '$email'";
		$result = $db->query($query);
		if ($result->num_rows == 1) {
			return true;
		}

		return false;
	}

	/**
	 * Returns true if a user with the given email address for the given distributer exists.
	 * False if not.
	 *
	 * @param $distId
	 * @param string $email The users email (unique to each user)
	 *
	 * @return bool
	 */
	public function distUserExists($distId, $email)
	{
		$db = self::getDB();
		$query = "SELECT * FROM users WHERE dist_id = $distId AND email = '$email'";
		$result = $db->query($query);
		if ($result->num_rows == 1) {
			return true;
		}

		return false;
	}

	/**
	 * @param boolean $removed
	 * @param int $userId
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function markDistUserRemoved($removed, $userId)
	{
		if ($removed === true) {
			$query = "UPDATE users SET removed=1,status=0 WHERE id=$userId";
		}
		else {
			$query = "UPDATE users SET removed=0 WHERE id=$userId";
		}

		return $this->query($query);
	}

	/**
	 * Creates a new user. Users are initally disabled. Once the user is created
	 * an email notification will be sent to the distributer who then must manually
	 * activate the user in the admin UI. This gives the distributer a chance to approve
	 * the new user and set a pricing category for them.
	 *
	 * @param string $email
	 * @param string $password
	 * @param int $distId
	 * @param string $firstName
	 * @param string $lastName
	 * @param string $cellPhone
	 *
	 * @return mixed The user as an array if successful, false if not.
	 */
	public function createUser($email, $password, $distId, $firstName, $lastName, $cellPhone, $accountNumber, $companyName, $showPricing)
	{
		$db = self::getDB();

		$email = $db->real_escape_string($email);
		$firstName = $db->real_escape_string($firstName);
		$lastName = $db->real_escape_string($lastName);
		$cellPhone = $db->real_escape_string($cellPhone);
		$accountNumber = $db->real_escape_string($accountNumber);
		$companyName = $db->real_escape_string($companyName);

		// Encrypt Password with Salt
		$pw = Util::encryptPassword($password);
		$salty = $pw['salt'];
		$newpass = $pw['encrypted_password'];

		// Insert new User
		$query = "INSERT INTO users (email, status, salt, password, dist_id, first_name, last_name, cell_phone, account_num, company_name, show_pricing) " . "VALUES ('$email', 3, '$salty','$newpass',$distId,'$firstName','$lastName','$cellPhone','$accountNumber','$companyName',$showPricing)";
		$result = $db->query($query);

		if ($result) {
			$user = $this->getUser($email, $password, $distId);

			return $user;
		}

		return $result;
	}

	/**
	 * Helper function to generate salt for passwords.
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	/*
	public function gensalt( $length ) {
		$characterList = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*?";
		$i = 0;
		$salt = "";
		while( $i < $length ) {
			$salt .= $characterList{ mt_rand( 0, ( strlen( $characterList ) - 1 ) ) };
			$i ++;
		}
		return $salt;
	}
	*/

	/**
	 *
	 *
	 * @param int $distId
	 * @param int $catId
	 *
	 * @return string
	 */
	public function getDistCategoryName($distId, $catId)
	{
		$query = "SELECT name FROM dist_categories WHERE id = $catId";

		$db = self::getDB();
		$result = $db->query($query);

		$cat = $result->fetch_assoc();

		return $cat['name'];
	}

	/**
	 * @param int $distId
	 * @param int $catId
	 *
	 * @return int
	 */
	public function getDistProductCount($distId, $catId = null)
	{
		if ($catId !== null) {
			$query = "SELECT COUNT(*) FROM dist_products AS dp INNER JOIN dist_product_category AS dpc ON dp.id = dpc.product_id";
			$query .= " WHERE dp.dist_id=$distId AND dpc.category_id=$catId";
		}
		else {
			$query = "SELECT COUNT(*) FROM dist_products AS dp INNER JOIN dist_product_category AS dpc ON dp.id = dpc.product_id";
			$query .= " WHERE dp.dist_id=$distId";
		}

		$db = self::getDB();
		$result = $db->query($query);

		$row = $result->fetch_array();

		return $row[0];
	}

	/**
	 * Helper function to return the index of the default style
	 * given an array of styles.
	 *
	 * @param array $styles
	 *
	 * @return int
	 */
	public function getDefaultStyleIndex($styles)
	{
		assert(is_array($styles) && count($styles) > 0);

		foreach ($styles as $index => $style) {
			if ($style['default_style'] == 1) {
				return $index;
			}
		}

		return 0;
	}

	/**
	 * Binary search for products. $products must be ordered by id.
	 *
	 * @param array $products
	 * @param int $id
	 *
	 * @return The position in the $products array of the product with the id of $id.
	 */
	private function findProductInArray($products, $id)
	{
		return $this->binaryProductSearch($products, $id, 0, count($products) - 1);
	}

	/**
	 * Helper function to find the index of the given style id in an
	 * array of styles.
	 *
	 * @param array $styles
	 * @param int $id
	 *
	 * @return int
	 */
	private function findStyleInArray($styles, $id)
	{
		foreach ($styles as $index => $style) {
			if ($style['style_id'] == $id) {
				return $index;
			}
		}

		return -1;
	}

	/**
	 * Recursive function for use by findProductInArray.
	 *
	 * @param array $products
	 * @param int $id
	 * @param int $min
	 * @param int $max
	 *
	 * @return int Index if matched element in the array $products
	 */
	private function binaryProductSearch($products, $id, $min, $max)
	{
		if ($max < $min) {
			return -1;
		}
		else {
			$mid = floor($min + (($max - $min) / 2));

			if ($products[$mid]['id'] > $id) {
				return $this->binaryProductSearch($products, $id, $min, $mid - 1);
			}
			elseif ($products[$mid]['id'] < $id) {
				return $this->binaryProductSearch($products, $id, $mid + 1, $max);
			}
			else {
				return $mid;
			}
		}
	}

	/**
	 * Be very afraid!
	 *
	 * @return mixed
	 */
	public function removeAllProducts()
	{
		$query = "TRUNCATE TABLE products";
		$db = self::getDB();
		$result = $db->query($query);

		return $result;
	}

	/**
	 *
	 *
	 * Returns all products that match the given category id and search term.
	 *
	 * // TODO: Remove subCatId
	 *
	 * @param string $subCatId
	 * @param string $orderBy
	 * @param string $direction
	 * @param string $search
	 * @param string $limit
	 *
	 * @return array
	 */
	/*
	public function getAllProducts( $subCatId = null, $orderBy = 'title', $direction = 'ASC', $search = "", $limit = "" ) {
		$query = "SELECT * FROM products ";

		if( $subCatId != null ) {
			$query .= " WHERE sub_cat_id = $subCatId";
		}

		if( !empty( $search ) ) {
			if( $subCatId == null ) {
				$query .= " WHERE title LIKE '%$search%' OR manufacturer_description LIKE '%$search%' OR part_num LIKE '%$search%'";
			}
			else {
				$query .= " AND title LIKE '%$search%' OR manufacturer_description LIKE '%$search%' OR part_num LIKE '%$search%'";
			}
		}

		$db = self::getDB();
		$colQuery = "SHOW COLUMNS FROM products";
		$result = $db->query( $colQuery );
		$colNames = array();
		while( $row = $result->fetch_assoc() ) {
			$colNames[] = $row[ 'Field' ];
		}

		if( in_array( $orderBy, $colNames ) ) {
			$query .= " ORDER BY $orderBy $direction";
		}

		if( !empty( $limit ) ) {
			$query .= " LIMIT $limit";
		}

		$result = $db->query( $query );
		if( $result == false ) {
			return false;
		}

		return $this->resultToArray( $result );
	}
	*/

	/**
	 * good
	 *
	 * Fetches a single product style from the db.
	 *
	 * @param int $styleId
	 *
	 * @return boolean|NULL
	 */
	public function getProductStyle($styleId)
	{
		$db = self::getDB();

		$query = "SELECT * FROM product_styles WHERE id = $styleId";
		$result = $db->query($query);
		if ($result == false) {
			return false;
		}

		if ($result->num_rows == 0) {
			return null;
		}

		return $result->fetch_assoc();
	}

	/**
	 * Returns an array of all the styles for the products in the array $products.
	 *
	 *
	 * @param array $products
	 * @param int $priceCatId
	 * @param boolean $applyOverrides
	 *
	 * @return array
	 */
	/*
	public function getProductStyles( $products ) {
		$db = self::getDB();
		
		$query = "SELECT * FROM product_styles";
		if( is_array( $products ) ) {
			$query .= " WHERE ";
			foreach( $products as $product ) {
				$query .= "product_id = {$product[ 'id' ]} OR ";
			}
			$query = trim( $query, ' OR ' );
		}
		
		$query .= " ORDER BY price ASC";
		
		$result = $db->query( $query );
		if( $result == false ) {
			return false;
		}
		
		$styles = $this->resultToArray( $result );
		
		$groupedStyles = array();
		$thisProdId = null;
		foreach( $styles as &$style ) {
			if( $style[ 'product_id' ] != $thisProdId ) {
				$groupedStyles[ $style[ 'product_id' ] ] = array();
				$thisProdId = $style[ 'product_id' ];
			}
		
			$groupedStyles[ $thisProdId ][] = $style;
		}
		
		return $groupedStyles;
	}
	*/

	/**
	 * good
	 *
	 * Adds or updates a product to the database. Does NOT create a default style.
	 * $values[ 'sku' ] must be set since it is the primary key we are
	 * using to identify existing products.
	 *
	 * @param array $values - Associative array <db_field_name> -> <value>
	 */
	/*
	public function addOrUpdateProduct( $values ) {
		$query = $this->createAddOrUpdateStatment( 'products', array( 'manufacturer', 'sku' ), $values );
		$db = self::getDB();
		$result = $db->query( $query );
		if( $result == false ) {
			return false;
		}

		$id = null;

		if( strstr( $query, 'INSERT' ) ) {
			// New product
			$id =  $db->insert_id;
		}
		else {
			// Existing product?
			$query = "SELECT id FROM products WHERE manufacturer = '{$values[ 'manufacturer' ]}' AND sku = '{$values[ 'sku' ]}'";
			$result = $db->query( $query );
			if( $result == false ) {
				return false;
			}
			$row = $result->fetch_assoc();

			$id = $row[ 'id' ];
		}

		return $id;
	}
	*/

	/**
	 * Adds or updates a style to database for the associated with the given product id.
	 * $values[ 'style_num' ] must be set since it is the primary key we are
	 * using to identify existing styles.
	 *
	 * @param array $values - Associative array [db_field_name] -> <[value]
	 *
	 * @return int|false
	 */
	public function addOrUpdateProductStyle($values)
	{
		$query = $this->createAddOrUpdateStatment('product_styles', 'style_num', $values);
		$db = self::getDB();
		$result = $db->query($query);
		if ($result == false) {
			return false;
		}

		if (strstr($query, 'INSERT')) {
			return $db->insert_id;
		}
		else {
			$query = "SELECT id FROM product_styles WHERE style_num = '{$values['style_num']}'";
			$result = $db->query($query);
			if ($result == false) {
				return false;
			}

			$row = $result->fetch_assoc();

			return $row['id'];
		}
	}

	/**
	 * Removes a product from the database AND all styles
	 * associated with it.
	 *
	 * @param unknown $id
	 */
	/*
	public function removeProduct( $id ) {
		$db = self::getDB();

		// Remove all styles associated with this product id
		$query = "DELETE FROM product_styles WHERE prodcut_id = $id";
		$result = $db->query( $query );
		if( $result == false ) {
			return false;
		}

		// Remove the product
		$query = "DELETE FROM products WHERE id = $id";
		$result = $db->query( $query );

		return $result;
	}
	*/

	/**
	 * Removes a product style from the database.
	 *
	 * If this is the default style then another style
	 * will be set as the default.
	 *
	 * If this is the last style for the product then
	 * this funciton will not remove the style and will
	 * return false.
	 *
	 * @param unknown $id
	 */
	/*
	public function removeProductStyle( $id ) {
		// Get this style
		$query = "SELECT id FROM product_sytles WHERE id = $id";
		$result = $db->query( $query );
		if( $result == false ) {
			return false;
		}
		$style = $result->fetch_assoc();

		// Get all styles for the product
		$query = "SELECT id FROM product_styles WHERE product_id = {$sytle[ 'product_id' ]}";
		$result = $db->query( $query );
		if( $result == false ) {
			return false;
		}

		// If this is the only style for the products then don't
		// delete it and just return false
		if( $result->num_rows <= 1 ) {
			return false;
		}

		// If this is the default style for the product set another style as default
		if( $style[ 'default' ] == 1 ) {
			$styles = $result->fetch_array();
			foreach( $styles as $otherStyle ) {
				if( $otherStyle[ 'id' ] != $id ) {
					$otherStyle[ 'default' ] = 1;
					$query = $this->createSingleInsertStatement( 'product_styles', $otherStyle );
					$result = $db->query( $query );
					if( $result == false ) {
						return false;
					}
					break;
				}
			}
		}

		// Delete this product style
		$query = "DELETE FROM product_styles WHERE id = $id";
		$result = $db->query( $query );

		return $result;
	}
	*/

	/**
	 * Returns either a distributer record or an admin_users record. Returns false
	 * if no admin user or distributer is found or if the password is incorrect.
	 *
	 * @param string $email
	 * @param string $pw The plain text password.
	 *
	 * @return array
	 */
	public function checkAdminLogin($email, $pw)
	{
		$db = self::getDB();

		$email = $db->real_escape_string($email);
		$pw = $db->real_escape_string($pw);

		// TODO: Hash the distributer password

		$query = "SELECT * FROM admin_users WHERE email='$email' AND password='$pw'";
		$result = $this->query($query);
		if ($result->num_rows == 1) {
			$adminUser = $result->fetch_assoc();

			return $adminUser;
		}
		else {
			$query = "SELECT * FROM distributers WHERE email='$email' AND password='$pw'";
			$result = $this->query($query);
			if ($result->num_rows == 1) {
				$distributer = $result->fetch_assoc();

				return $distributer;
			}
			else {
				$query = "SELECT * FROM dist_logins WHERE email='$email'";
				$result = $db->query($query);
				if ($result->num_rows == 1) {
					$user = $result->fetch_assoc();
					$salt = $user['salt'];

					$hashed = Util::encryptPassword($pw, $salt);
					$hashedPw = $hashed['encrypted_password'];

					$query = "SELECT * FROM dist_logins WHERE email='$email' AND password='$hashedPw'";
					$result = $this->query($query);
					if ($result->num_rows == 1) {
						$distLogin = $result->fetch_assoc();
						$query = "SELECT * FROM distributers WHERE id='{$distLogin['dist_id']}'";
						$result = $this->query($query);
						if ($result->num_rows == 1) {
							$distributer = $result->fetch_assoc();

							return $distributer;
						}
						else {
							return false;
						}
					}
				}
				else {
					$query = "SELECT * FROM branches WHERE manager_email='$email' AND manager_pw='$pw'";
					$result = $this->query($query);
					if ($result->num_rows == 1) {
						$branch = $this->resultToArray($result)[0];
						$distributer = $this->getDistributer($branch['dist_id']);
						$distributer['branch'] = $branch;

						return $distributer;
					}
					else {
						return false;
					}
				}
			}
		}
	}

	/**
	 * Returns a distributer record.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public function getDistributer($id)
	{
		$db = self::getDB();

		$id = $db->real_escape_string($id);

		$query = "SELECT * FROM distributers WHERE id = $id";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}
		$distributer = null;
		if ($result->num_rows == 1) {
			$distributer = $result->fetch_assoc();
		}

		return $distributer;
	}
	
	/**
	 * Returns a distributer record.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public function getExtraAdminsById($id)
	{
		$db = self::getDB();

		$id = $db->real_escape_string($id);

		$query = "SELECT * FROM dist_logins WHERE dist_id = $id";
		
		$result = $db->query($query);
		
		if ($result === false) {
			echo($db->error);
		}
		
		return $this->resultToArray($result);
	}

	/**
	 * Retrieves all additional distributer logins. Does NOT include
	 * the main distributer login which is in the distributers table.
	 *
	 * @param $distId
	 *
	 * @return array
	 */
	public function getDistributerLogins($distId)
	{
		$db = self::getDB();

		$distId = $db->real_escape_string($distId);

		$query = "SELECT * FROM dist_logins WHERE dist_id=$distId";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}

		return $this->resultToArray($result);
	}

	/**
	 * @param $distId
	 * @param $name
	 * @param $email
	 * @param $password
	 *
	 * @return bool
	 */
	public function addDistributerLogin($distId, $name, $email, $password, $emailPref)
	{
		$db = self::getDB();

		$name = trim($db->real_escape_string($name));
		$email = trim($db->real_escape_string($email));
		$password = trim($db->real_escape_string($password));

		$password = Util::encryptPassword($password);

		$query = "INSERT INTO dist_logins (name, email, dist_id, password, salt, email_setting) VALUES('$name', '$email', $distId, '{$password['encrypted_password']}', '{$password['salt']}', '$emailPref')";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);

			return false;
		}

		return true;
	}

	/**
	 * @param $id
	 * @param $name
	 * @param $email
	 * @param $password
	 *
	 * @return bool
	 */
	public function updateDistributerLogin($id, $name, $email, $password, $emailPref)
	{
		$db = self::getDB();

		$name = trim($db->real_escape_string($name));
		$email = trim($db->real_escape_string($email));
		$password = trim($db->real_escape_string($password));

		$query = "SELECT COUNT(id) as count FROM dist_logins WHERE id=$id";
		$count = $this->getOne($query);
		if ($count['count'] > 0) {
			$query = "UPDATE dist_logins SET name='$name', email='$email', email_setting='$emailPref'";
			if ($password !== '') {
				// Password will only be changed if it is set
				$password = Util::encryptPassword($password);
				$query .= ", password='{$password['encrypted_password']}', salt='{$password['salt']}'";
			}
			$query .= " WHERE id=$id";
			$result = $db->query($query);
			if ($result === false) {
				echo($db->error);

				return false;
			}
		}
		else {
			// Login not found
			return false;
		}

		return true;
	}

	/**
	 * @param $id
	 *
	 * @return boolean
	 */
	public function deleteDistributerLogin($id)
	{
		$query = "DELETE FROM dist_logins WHERE id=$id";
		$db = self::getDB();
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);

			return false;
		}

		return true;
	}

	/**
	 * good
	 *
	 * Returns the distributer with the given change_pass_token.
	 * For use with distributer registration.
	 *
	 * @param unknown $token
	 *
	 * @return NULL
	 */
	public function getDistributerFromToken($token)
	{
		$db = self::getDB();

		$token = $db->real_escape_string($token);

		$query = "SELECT * FROM distributers WHERE change_pass_token = '$token'";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}
		$distributer = null;
		if ($result->num_rows == 1) {
			$distributer = $result->fetch_assoc();
		}

		return $distributer;
	}

	/**
	 * good
	 *
	 * Returns an array of all distributers.
	 *
	 * @return array
	 */
	public function getAllDistributers()
	{
		$db = self::getDB();

		$query = "SELECT * FROM distributers";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}

		return $this->resultToArray($result);
	}

	/**
	 * good
	 *
	 * Return true if a distributer or admin user has the given
	 * email address, false if not.
	 *
	 * @param string $email
	 *
	 * @return boolean
	 */
	public function emailInUse($email)
	{
		$db = self::getDB();

		$email = $db->real_escape_string($email);

		$query = "SELECT id FROM distributers WHERE email = '$email'";
		$result = $this->query($query);
		if ($result->num_rows == 0) {
			$query = "SELECT id FROM admin_users WHERE email = '$email'";
			$result = $this->query($query);
			if ($result->num_rows == 0) {
				$query = "SELECT id FROM branches WHERE manager_email = '$email'";
				$result = $this->query($query);
				if ($result->num_rows == 0) {
					return false;
				}
				else {
					return true;
				}
			}
			else {
				return true;
			}
		}
		else {
			return true;
		}
	}

	/**
	 * good
	 *
	 * Add a distributer to the database. Also creates a pricing category named 'Default'
	 * and activates all products for the new distributer by adding records to the
	 * distributer_products table.
	 *
	 * @param unknown $distributer
	 *
	 * @return unknown|NULL
	 */
	public function addDistributer($distributer)
	{
		$adb = self::getDB();

		$query = $this->createSingleInsertStatement('distributers', $distributer);
		$result = $adb->query($query);
		if ($result === false) {
			echo($adb->error);
		}

		if ($result) {
			$distId = $adb->insert_id;

			$this->createDefaultDistOrderStatuses($distId);

			return $distId;
		}

		return null;
	}

	/**
	 * good
	 *
	 * Sets the password for a distributer. If you want to force the
	 * distributer to change their password on next login then let $pw
	 * be an empty string and pass a string into $token.
	 *
	 * @param int $id
	 * @param string $pw The plain text password.
	 * @param string $token
	 *
	 * @return boolean
	 */
	public function setDistributerPassword($id, $pw, $token = null)
	{
		$db = self::getDB();

		$id = $db->real_escape_string($id);
		$pw = $db->real_escape_string($pw);

		// TODO: Hash the password
		$query = "UPDATE distributers SET password = '$pw'";
		if ($token != null) {
			$query .= ", change_pass_token = '$token', change_pass = 1";
		}
		else {
			$query .= ", change_pass_token = '', change_pass = 0";
		}

		$query .= " WHERE id = $id";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}

		return $result;
	}

	/**
	 * good
	 *
	 * Updates the distributer record.
	 *
	 * @param array $distributer
	 *
	 * @return boolean
	 */
	public function updateDistributer($distributer)
	{
		$db = self::getDB();

		$query = $this->createSingleUpdateStatement('distributers', 'id', $distributer);
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}

		return $result;
	}

	/**
	 * @param $distId
	 *
	 * @return bool
	 */
	public function createDefaultDistOrderStatuses($distId)
	{
		$statuses = array('Back Ordered', 'Canceled', 'Placed', 'Processed', 'Ready for pickup', 'Received', 'Completed');

		$success = true;
		$db = self::getDB();
		foreach ($statuses as $status) {
			$query = "INSERT IGNORE INTO dist_order_statuses (dist_id,name) VALUES($distId,'$status')";
			$result = $db->query($query);
			if ($result === false) {
				echo($db->error);
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Creates an order in the database.
	 *
	 * @param int $distId
	 * @param int $userId
	 * @param SessionCart $cart
	 * @param int $locationId
	 *
	 * @return boolean|int The id of the new order record or false if anything went wrong.
	 */
	public function createOrder($distId, $userId, SessionCart $cart, $poNum, $comment, $locationId = 0, $shippingId = 0, $branchId = null)
	{
		$db = self::getDB();

		$order = array('dist_id' => $distId, 'user_id' => $userId, 'sub_total' => $cart->totalPrice(), 'tax' => 0, 'total' => $cart->totalPrice(), 'status' => 'Placed', 'pickup_location' => $locationId, 'shipping_location' => $shippingId, 'cart_data' => $db->real_escape_string(json_encode($cart->getItems())), 'order_date' => date('Y-m-d H:i:s'), 'status_date' => date('Y-m-d H:i:s'), 'order_comment' => $db->real_escape_string($comment), 'po_num' => $poNum, 'branch_id' => $branchId === null ? 'NULL' : $branchId);

		$query = "INSERT INTO orders (dist_id, user_id, sub_total, tax, total, status, pickup_location, shipping_location, cart_data, order_date, status_date, order_comment, po_num, branch_id) ";
		$query .= "VALUES( {$order['dist_id']}, {$order['user_id']}, {$order['sub_total']}, {$order['tax']}, {$order['total']}, '{$order['status']}', {$order['pickup_location']}, {$order['shipping_location']}, '{$order['cart_data']}', '{$order['order_date']}', '{$order['status_date']}', '{$order['order_comment']}', '{$order['po_num']}', {$order['branch_id']} )";

		$result = $this->query($query);

		$orderId = $db->insert_id;

		return $orderId;
	}

	/**
	 * For exporting orders as they are created for EDI.
	 *
	 * @param int $orderId
	 *
	 * @throws Exception
	 */
	public function exportOrder($orderId)
	{
		$order = $this->getOrder($orderId);
		$user = $this->getUserById($order['user_id']);
		$dist = $this->getDistributer($order['dist_id']);

		// OrderID	OrderDate	PONum	Total	Comment	UserName	UserCompany	UserPhone	UserAcc#	ShippingLocation	PickupLocation
		$tabDel = "{$order['id']}\t{$order['order_date']}\t{$order['po_num']}\t{$order['total']}\t{$order['order_comment']}\t";
		$tabDel .= "{$user['first_name']} {$user['last_name']}\t{$user['company_name']}\t{$user['cell_phone']}\t{$user['account_num']}\t";

		if (!empty($order['shipping_location'])) {
			$tabDel .= 'ship';
		}

		if (!empty($order['pickup_location'])) {
			$tabDel .= 'pickup';
		}

		$tabDel .= "\t{$order['location_info']['name']}\t{$order['location_info']['address1']}\t{$order['location_info']['address2']}\t{$order['location_info']['city']}\t{$order['location_info']['state']}\t{$order['location_info']['zip']}\n";

		foreach ($order['cart'] as $item) {
			// Product_ID	Product_SKU	Product_PartNumber	Product_Price	Quantity	Total
			if ($item['style']['price'] == null) {
				$item['style']['price'] = 0;
			}
			if (empty($item['style']['unit'])) {
				$item['style']['unit'] = '';
			}
			$tabDel .= "{$item['product']['id']}\t{$item['product']['sku']}\t{$item['product']['part_num']}\t{$item['style']['price']}\t{$item['style']['unit']}\t{$item['quantity']}\t{$item['price']}\n";
		}

		$outputDir = ORDERS_OUTPUT_DIR . '/' . $dist['dir'];
		if (!is_dir($outputDir)) {
			mkdir($outputDir, 0775, true);
		}

		$fileName = $outputDir . "/order_{$order['id']}.txt";

		if (file_put_contents($fileName, $tabDel) === false) {
			throw new Exception("Fatal Error: Could not write order file.");
		}
	}

	/**
	 * Returns the number of notifications a user has / Used on Page.php
	 *
	 * @param int $userId
	 *
	 * @return int
	 */
	public function getNotificationsCount($userId)
	{
		$query = "SELECT COUNT(*) AS count FROM notifications n, notifications_users nu WHERE nu.user_id=$userId AND active=1 AND nu.message_id = n.id";
		$count = $this->getOne($query);

		return $count['count'];
	}

	/**
	 * Returns an array of all the summarized users orders.
	 *
	 * @param int $userId
	 *
	 * @return array|boolean
	 */
	public function getUserOrders($userId, $limit = "")
	{
		$db = self::getDB();

		$fromTz = ini_get('date.timezone');
		$toTz = $_SESSION['client_timezone'];
		$query = "SELECT *, CONVERT_TZ(order_date, '$fromTz', '$toTz') AS local_order_date, CONVERT_TZ(status_date, '$fromTz', '$toTz') AS local_status_date FROM orders WHERE user_id = $userId ORDER BY order_date DESC";
		if (!empty($limit)) {
			$query .= " LIMIT $limit";
		}

		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);

			return false;
		}
		$orders = $this->resultToArray($result);
		foreach ($orders as &$order) {
			$order['cart'] = json_decode($order['cart_data'], true);
			unset($order['cart_data']);
		}

		return $orders;
	}

	/**
	 * good
	 *
	 * Returns a single order.
	 *
	 * @param int $orderId
	 *
	 * @return array|boolean
	 */
	public function getOrder($orderId)
	{
		$db = self::getDB();

		$fromTz = ini_get('date.timezone');
		$toTz = $_SESSION['client_timezone'];
		$query = "SELECT o.*, b.name AS branch_name, CONVERT_TZ(order_date, '$fromTz', '$toTz') AS local_order_date, CONVERT_TZ(status_date, '$fromTz', '$toTz') AS local_status_date FROM orders AS o LEFT JOIN branches AS b ON b.id = o.branch_id WHERE o.id = $orderId";
		$result = $db->query($query);
		if ($result === false) {
			return false;
		}

		if ($result->num_rows != 1) {
			return null;
		}

		$order = $result->fetch_array();

		$order['cart'] = json_decode($order['cart_data'], true);
		unset($order['cart_data']);

		if ($order['cart'] != null) {
			foreach ($order['cart'] as $index => $cartItem) {
				$active = $this->distProductExists($order['dist_id'], $cartItem['product']['sku'], $cartItem['style']['style_num']);
				$order['cart'][$index]['product']['active'] = $active;
			}
		}

		if ($order['pickup_location'] != 0) {
			$order['location_info'] = $this->getPickupLocationbyId($order['pickup_location']);
		}
		else {
			if ($order['shipping_location'] != 0) {
				$order['location_info'] = $this->getShippingLocationbyId($order['shipping_location']);
			}
		}

		return $order;
	}

	/**
	 * Retrieves all orders in the given date range for all distributers and branches.
	 *
	 * @param $startDate
	 * @param $endDate
	 *
	 * @return array
	 */
	public function getOrdersByDateRange($startDate, $endDate)
	{
		$db = self::getDB();
		$startDate = $db->real_escape_string($startDate);
		$endDate = $db->real_escape_string($endDate);

		$startDate = date('Y-m-d', strtotime($startDate));
		$endDate = date('Y-m-d', strtotime($endDate));

		$fromTz = ini_get('date.timezone');
		$toTz = $_SESSION['client_timezone'];
		$query = "SELECT *, CONVERT_TZ(order_date, '$fromTz', '$toTz') AS local_order_date, CONVERT_TZ(status_date, '$fromTz', '$toTz') AS local_status_date FROM orders WHERE order_date >= '$startDate' AND order_date <= '$endDate' ORDER BY order_date ASC";

		return $this->getAll($query);
	}

	/**
	 * Retrieves all orders in the given date range for the specified distributer and branch.
	 *
	 * @param $distId
	 * @param $branchId
	 * @param $startDate
	 * @param $endDate
	 *
	 * @return array
	 */
	/*
	public function getDistOrdersByDateRange($distId, $branchId, $startDate, $endDate) {
		$db = self::getDB();
		$startDate = $db->real_escape_string($startDate);
		$endDate = $db->real_escape_string($endDate);

		$startDate = date('Y-m-d', strtotime($startDate));
		$endDate = date('Y-m-d', strtotime($endDate));

		$fromTz = ini_get('date.timezone');
		$toTz = $_SESSION['client_timezone'];
		$query = "SELECT orders.*, branches.name AS branch_name, CONVERT_TZ(orders.order_date, '$fromTz', '$toTz') AS local_order_date, CONVERT_TZ(orders.status_date, '$fromTz', '$toTz') AS local_status_date FROM orders INNER JOIN branches ON branches.id = orders.branch_id WHERE orders.dist_id = $distId";
		if ($branchId !== null) {
			$query .= " AND branch_id = $branchId";
		}
		$query .= " AND order_date >= '$startDate' AND order_date <= '$endDate' ORDER BY order_date ASC";
		return $this->getAll($query);
	}
	*/

	/**
	 * Sets the hidden attribute for an order. $hidden should be either 1 or 0.
	 *
	 * @param int $orderId
	 * @param boolean $hidden
	 */
	public function setOrderHidden($orderId, $hidden)
	{
		$query = "UPDATE orders SET hidden=$hidden WHERE id=$orderId";
		$this->query($query);
	}

	/**
	 * good
	 *
	 * Sets the status for the given order.
	 *
	 * @param int $orderId
	 * @param string $newStatus
	 *
	 * @return mixed
	 */
	public function setOrderStatus($orderId, $newStatus)
	{
		$db = self::getDB();

		$query = "UPDATE orders SET status='$newStatus' WHERE id=$orderId";
		$result = $db->query($query);

		return $result;
	}

	/**
	 * Returns a count of all orders for the given distributer and optionaly the user.
	 *
	 * @param int $distId
	 * @param int $branchId
	 * @param array $filters
	 * @param array $sort
	 * @param boolean $countOnly
	 * @param int $offset
	 * @param int $max
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getDistributerOrders($distId, $branchId = null, $filters = null, $sort = null, $countOnly = false, $offset = null, $max = null)
	{
		$db = self::getDB();

		if ($countOnly) {
			$query = "SELECT COUNT(*) FROM orders AS o";
		}
		else {
			$fromTz = ini_get('date.timezone');
			$toTz = $_SESSION['client_timezone'];
			$query = "SELECT o.*, b.name AS branch_name, CONVERT_TZ(o.order_date, '$fromTz', '$toTz') AS local_order_date, CONVERT_TZ(o.status_date, '$fromTz', '$toTz') AS local_status_date FROM orders AS o";
		}

		$query .= " LEFT JOIN users AS u ON u.id = o.user_id";
		$query .= " LEFT JOIN branches AS b ON b.id = o.branch_id";

		$query .= " WHERE o.dist_id = $distId";

		if ($branchId !== null) {
			if ($_SESSION['distributer']['branch_managers_own_orders']) {
				// Manger sees order for their users despite what branch the order is for
				$query .= " AND u.branch_id = $branchId";
			}
			else {
				// Manager only sees orders for their branch
				$query .= " AND o.branch_id = $branchId";
			}

			$query .= " AND o.branch_id IS NOT NULL";
		}
		else {
			// TODO: Why doing this???
			//$query .= " AND o.branch_id IS NOT NULL";
		}

		if (isset($filters['start_date'])) {
			$startDate = date('Y-m-d', strtotime(str_replace('-', '/', $filters['start_date'])));
			$query .= " AND o.order_date >= '$startDate'";
		}

		if (isset($filters['end_date'])) {
			$endDate = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $filters['end_date'])));
			$query .= " AND o.order_date <= DATE(DATE_ADD('$endDate', INTERVAL 1 DAY))";
		}

		if (isset($filters['status'])) {
			$query .= " AND o.status = '{$filters['status']}'";
		}

		if (isset($filters['user_id'])) {
			$query .= " AND o.user_id = " . $filters['user_id'];
		}

		if (isset($filters['po'])) {
			$query .= " AND o.po_num = " . $filters['po'];
		}

		if (isset($filters['company'])) {
			$query .= " AND u.company_name = '{$filters['company']}'";
		}

		if (isset($filters['acc_num'])) {
			$query .= " AND u.account_num = '{$filters['acc_num']}'";
		}

		if (!isset($filters['hidden']) || $filters['hidden'] == false) {
			$query .= " AND hidden = 0";
		}

		if (!$countOnly) {
			if ($sort !== null) {
				$query .= " ORDER BY";
				foreach ($sort as $by => $dir) {
					$query .= " $by $dir";
				}
			}
			else {
				$query .= " ORDER BY o.id DESC";
			}

			if ($offset !== null) {
				$query .= " LIMIT $offset";
			}

			if ($max !== null) {
				$query .= ",$max";
			}

			$orders = $this->getAll($query);

			// Calculate total_price for each order
			foreach ($orders as &$order) {
				$order['cart'] = json_decode($order['cart_data'], true);
				//unset( $order[ 'cart_data' ] );

				$order['total_price'] = 0;
				$order['total_quantity'] = 0;
				if ($order['cart']) {
					foreach ($order['cart'] as $item) {
						$order['total_price'] += $item['price'];
						$order['total_quantity'] += $item['quantity'];
					}
				}
			}

			return $orders;
		}
		else {
			$result = $this->query($query);
			$row = $result->fetch_array();

			return $row[0];
		}
	}

	/**
	 * Rturns an array of all orders for the given distributer.
	 *
	 * @param unknown $distId
	 * @param string $limit
	 *
	 * @return NULL|unknown
	 */
	/*
	public function getDistributerOrders( $distId, $branchId = null, $limit = '', $searchParams = null ) {
		$db = self::getDB();

		$query = "SELECT * FROM orders WHERE dist_id = $distId";
		if($branchId !== null) {
			$query .= " AND branch_id = $branchId";
		}
		$query .= " ORDER BY id DESC";
		if( $limit != '' ) {
			$query .= " LIMIT $limit";
		}

		$orders = $this->getAll($query);

		foreach( $orders as &$order ) {
			$order[ 'cart' ] = json_decode( $order[ 'cart_data' ], true );
			//unset( $order[ 'cart_data' ] );

			$order[ 'total_price' ] = 0;
			if( $order[ 'cart' ] ) {
				foreach( $order[ 'cart' ] as $item ) {
					$order[ 'total_price' ] += $item[ 'price' ];
				}
			}
		}

		return $orders;
	}
	*/

	/**
	 * @param int $userId
	 * @param string $limit
	 *
	 * @return array|false
	 */
	public function getUserNotifications($userId, $limit = "")
	{
		$db = self::getDB();

		//$query = "SELECT * FROM notifications n, notifications_users nu WHERE nu.user_id = $userId AND nu.message_id = n.id ORDER BY nu.active DESC, n.date DESC";
		$fromTz = ini_get('date.timezone');
		$toTz = $_SESSION['client_timezone'];
		$query = "SELECT *, CONVERT_TZ(n.date, '$fromTz', '$toTz') AS local_date FROM notifications n, notifications_users nu WHERE nu.user_id = $userId AND nu.message_id = n.id ORDER BY nu.active DESC, n.date DESC";

		if (!empty($limit)) {
			$query .= " LIMIT $limit";
		}

		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);

			return false;
		}
		$notifications = $this->resultToArray($result);

		return $notifications;
	}

	/**
	 * Returns a Notification, and marks it as read
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public function getUserNotification($id)
	{
		$db = self::getDB();

		$fromTz = ini_get('date.timezone');
		$toTz = $_SESSION['client_timezone'];
		$query = "SELECT *, CONVERT_TZ(`date`, '$fromTz', '$toTz') AS local_date FROM notifications WHERE id = $id";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);

			return false;
		}
		$notification = $this->resultToArray($result);
		$userId = $_SESSION['user']['id'];
		// TODO: Why this query, Kalden???
		$query = "SELECT * FROM notifications_users WHERE message_id = $id AND user_id = $userId";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);

			return false;
		}
		$activeArray = $this->resultToArray($result);
		// TODO: Should be separate function setNotificationActive
		$active = $activeArray[0]['active'];
		if ($active == 1) {
			$query = "UPDATE notifications_users SET active='0' WHERE message_id = $id AND user_id = $userId";
			$result = $db->query($query);
			if ($result === false) {
				echo($db->error);

				return false;
			}
		}

		return $notification[0];
	}

	/**
	 * Returns an array of all notifications sent by distributor
	 *
	 * @param $distId
	 * @param null $branchId
	 *
	 * @return array
	 */
	public function getDistributorNotifications($distId, $branchId = null)
	{
		$db = self::getDB();

		//$query = "SELECT n.title, n.message, n.id, DATE_FORMAT(n.date, '%b %D, %Y') as date, SUM(nu.active) as unread, COUNT(*) as total FROM notifications n, notifications_users nu ";
		$fromTz = ini_get('date.timezone');
		$toTz = $_SESSION['client_timezone'];
		$query = "SELECT n.title, n.message, n.id, DATE_FORMAT(CONVERT_TZ(n.date, '$fromTz', '$toTz'), '%b %D, %Y') as date, DATE_FORMAT(n.date, '%b %D, %Y') AS local_date, SUM(nu.active) as unread, COUNT(*) as total FROM notifications n, notifications_users nu ";
		$query .= " WHERE nu.message_id = n.id AND n.dist_id = $distId";
		if ($branchId !== null) {
			$query .= " AND branch_id = $branchId";
		}
		$query .= " GROUP BY n.title, n.message, n.id, DATE_FORMAT(n.date, '%b %e, %Y') ORDER BY n.date DESC;";

		$notifications = $this->getAll($query);

		return $notifications;
	}

	/**
	 * Saves notification to the database
	 *
	 * @param $data
	 *
	 * @return bool|mixed
	 */
	public function saveNotification($data)
	{
		$db = self::getDB();

		// First insert message into notifications table, then insert relevant data into notifications_users
		$data['title'] = $db->real_escape_string($data['title']);
		$data['message'] = $db->real_escape_string($data['message']);

		$query = "INSERT INTO notifications (title, message, dist_id, date) VALUES ('" . $data['title'] . "', '" . $data['message'] . "', " . $data['dist_id'] . ", '" . date('Y-m-d H:i:s') . "')";
		$result = $db->query($query);
		if ($result === false) {
			return false;
		}
		$messageId = $db->insert_id;

		$allUsers = false;
		if ($data['users'][0] == "0") {
			$data['users'] = $this->getActiveUsers($_SESSION['distributer']['id']);
			$allUsers = true;
		}

		foreach ($data['users'] as $user) {
			if (!$allUsers) {
				$user = $this->getUserById($user);
			}
			if ($user['status'] == 1) {
				$query = "INSERT INTO notifications_users (user_id, message_id, active) VALUES (" . $user['id'] . ", $messageId, 1)";
				$result = $db->query($query);
				if ($result === false) {
					return false;
				}
			}
		}

		return $messageId;
	}

	/**
	 * Returns an array of all the active pickup locations
	 *
	 * @param $distId
	 *
	 * @return array
	 */
	public function getLocations($distId)
	{
		$db = self::getDB();

		$query = "SELECT * FROM pickup_locations WHERE active='1' AND dist_id=$distId";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}
		$locations = $this->resultToArray($result);

		return $locations;
	}

	/**
	 * @param $distId
	 * @param null $branchId
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getPickupLocations($distId, $branchId = null)
	{
		$query = "SELECT * FROM pickup_locations WHERE dist_id=$distId";
		if (!empty($branchId)) {
			$query .= " AND branch_id=$branchId";
		}
		$result = $this->query($query);

		return $this->resultToArray($result);
	}

	/**
	 * Returns an array of all pickup locations
	 *
	 * @param $distId
	 * @param array $orderBy
	 *
	 * @return array
	 */
	public function getAllLocations($distId, $orderBy = array('name' => 'ASC'))
	{
		$db = self::getDB();

		$query = "SELECT * FROM pickup_locations WHERE dist_id=$distId";
		if (is_array($orderBy)) {
			$query .= " ORDER BY";
			foreach ($orderBy as $field => $dir) {
				$query .= " `$field` $dir,";
			}
			$query = substr($query, 0, -1);
		}
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}
		$locations = $this->resultToArray($result);

		return $locations;
	}

	/**
	 * Returns the id of the preferred location
	 *
	 * @param $userId
	 *
	 * @return array|bool
	 */
	public function getPreferredLocation($userId)
	{
		$db = self::getDB();

		$query = "SELECT preferred_location FROM users WHERE id = $userId";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}
		$preferedLocation = $result->fetch_assoc();
		if ($result->num_rows == 0) {
			return false;
		}

		return $preferedLocation;
	}

	/**
	 * Saves the prefered pickup location
	 *
	 * @param $preferedLocation
	 * @param $userId
	 *
	 * @return bool
	 */
	public function savePreferred($preferedLocation, $userId)
	{
		$db = self::getDB();

		$query = "UPDATE users SET preferred_location = $preferedLocation WHERE id = $userId ";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}

		return true;
	}

	/**
	 * @param int $locationId
	 *
	 * @return array
	 */
	public function getPickupLocationbyId($locationId)
	{
		$db = self::getDB();

		$query = "SELECT * FROM pickup_locations WHERE id = $locationId";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}
		$location = $this->resultToArray($result);
		if (count($location) > 0) {
			$location = $location[0];
		}
		else {
			$location = "";
		}

		return $location;
	}

	/**
	 * @param int $locationId
	 *
	 * @return array
	 */
	public function getShippingLocationbyId($locationId)
	{
		$db = self::getDB();

		$query = "SELECT * FROM shipping_locations WHERE id = $locationId";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}

		if ($result->num_rows == 0) {
			return null;
		}

		$location = $this->resultToArray($result);

		return $location[0];
	}

	/**
	 * Sets the password for a user. If you want to force the
	 * user to change their password on next login then let $pw
	 * be an empty string and pass a string into $token.
	 *
	 * @param int $id
	 * @param string $pw The plain text password.
	 * @param string $token
	 *
	 * @return boolean
	 */
	public function setUserPassword($id, $pw, $token = null)
	{
		$db = self::getDB();

		//$id = $db->real_escape_string( $id );
		$pw = $db->real_escape_string($pw);

		if ($token != null) {
			$query = "UPDATE users SET password = '$pw'";
			$query .= ", change_pass_token = '$token', change_pass = 1";
		}
		else {
			$pw = Util::encryptPassword($pw);
			$salty = $pw['salt'];
			$newpass = $pw['encrypted_password'];

			$query = "UPDATE users SET password = '$newpass', salt = '$salty'";
			$query .= ", change_pass_token = '', change_pass = 0";
		}

		$query .= " WHERE id = $id";
		$result = $this->query($query);

		return $result;
	}

	/**
	 * @param string $email
	 *
	 * @return array
	 */
	public function getUserIdByEmail($email, $distId = null)
	{
		$db = self::getDB();

		$query = "SELECT id FROM users WHERE email='$email'";
		if ($distId) {
			$query .= " AND dist_id=$distId";
		}
		$user = $this->getOne($query);

		return $user == null ? false : (int)$user['id'];
	}

	/**
	 * Returns the user with the given change_pass_token.
	 * For use with user registration.
	 *
	 * @param string $token
	 *
	 * @return array
	 */
	public function getUserFromToken($token)
	{
		$db = self::getDB();

		$token = $db->real_escape_string($token);

		$query = "SELECT * FROM users WHERE change_pass_token = '$token'";
		$user = $this->getOne($query);

		return $user;
	}

	/**
	 * Saves Distributor Location
	 *
	 * @param array $locationData
	 *
	 * @return boolean
	 */
	public function saveLocation($locationData)
	{
		$db = self::getDB();

		$query = $this->createSingleUpdateStatement('pickup_locations', 'id', $locationData);

		return $this->query($query);

		/*
		$address1 = $locationData['address1'];
		$address2 = $locationData['address2'];
		$city = $locationData['city'];
		$state = $locationData['state'];
		$zip = $locationData['zip'];
		$name = $locationData['name'];
		$active = $locationData['active'];
		$id = $locationData['id'];

		if($stmt = $db->prepare("UPDATE pickup_locations SET address1 = ?, address2 = ?, city = ?, state = ?, zip = ?, name = ?, active = ? WHERE id = ?")){
			$stmt->bind_param("ssssssii",$address1, $address2, $city, $state, $zip, $name, $active, $id);
			$stmt->execute();
			return array("Error"=>false);
		}else{
			return array("Error"=>true);
		}
		*/
	}

	/**
	 * Deletes Distributor Location
	 *
	 * @param int $id
	 *
	 * @return array
	 * @author Kalden Ficklin
	 */
	public function deleteLocation($id)
	{
		$db = self::getDB();

		$query = "DELETE FROM pickup_locations WHERE id = ?";
		if ($stmt = $db->prepare($query)) {
			$stmt->bind_param("i", $id);
			$stmt->execute();

			return array("Error" => false);
		}
		else {
			return array("Error" => true);
		}
	}

	/**
	 * Creates Distributor Location
	 *
	 * @param array $location
	 *
	 * @return array
	 */
	public function createLocation($location)
	{
		$db = self::getDB();

		$address1 = $location['address1'];
		$address2 = $location['address2'];
		$city = $location['city'];
		$state = $location['state'];
		$zip = $location['zip'];
		$name = $location['name'];
		$active = $location['active'];
		$distId = $location['dist_id'];

		//TODO: adjust for SQL injection

		$query = "INSERT INTO pickup_locations (dist_id, address1, address2, city, state, zip, name, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

		if ($stmt = $db->prepare($query)) {
			$stmt->bind_param("issssssi", $distId, $address1, $address2, $city, $state, $zip, $name, $active);
			$stmt->execute();

			return array("Error" => false, "locationId" => $db->insert_id);
		}
		else {
			return array("Error" => true);
		}
	}

	/**
	 * good
	 *
	 * @param unknown $id
	 *
	 * @return boolean
	 */
	/*
	public function deleteNotification( $id ) {
		$db = self::getDB();

		$query = "DELETE FROM notifications WHERE id=$id";
		$result = $db->query( $query );
		if( $result === false ) {
			echo( $db->error );
			return false;
		}
		return true;
	}
	*/

	/**
	 * // TODO function sucks. it's miss-named and pretty useless.
	 *
	 * @param int $distId
	 *
	 * @return boolean
	 */
	public function userHasPickupLocations($distId)
	{
		$db = self::getDB();
		$query = "SELECT * FROM pickup_locations WHERE dist_id = $distId AND active = 1";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);

			return false;
		}
		$locations = $this->resultToArray($result);
		if (!empty($locations)) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * @param int $userId
	 *
	 * @return boolean
	 */
	public function userHasShippingLocations($userId)
	{
		$db = self::getDB();
		$query = "SELECT * FROM shipping_locations WHERE user_id = $userId AND active = 1";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);

			return false;
		}
		$locations = $this->resultToArray($result);
		if (!empty($locations)) {
			return true;
		}
		else {
			return false;
		}

	}

	/**
	 * Saves distributor logo
	 *
	 * @param array $fileInfo
	 *
	 * @return bool $success
	 */
	/*
	public function saveDistributorLogo($fileInfo){
		$db = self::getDB();

		$query = "UPDATE distributers SET logo = ? WHERE id = ?";
		if($stmt = $db->prepare($query)){
			$stmt->bind_param("si",$fileInfo['dest'],$fileInfo['dist_id']);
			$stmt->execute();
			return true;
		}else{
			return false;
		}
	}
	*/

	/**
	 * Returns an array of all the active shipping locations
	 *
	 * @param $userId
	 *
	 * @return array
	 */
	public function getShippingLocations($userId)
	{
		$db = self::getDB();

		$query = "SELECT * FROM shipping_locations WHERE active=1 AND user_id=$userId";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}
		$locations = $this->resultToArray($result);

		return $locations;
	}

	/**
	 * Returns the id of the preferred location
	 *
	 * @param $userId
	 *
	 * @return array|bool
	 */
	public function getPreferredShippingLocation($userId)
	{
		$db = self::getDB();

		$query = "SELECT preferred_shipping_location FROM users WHERE id = $userId";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}
		$preferredLocation = $result->fetch_assoc();
		if ($result->num_rows == 0) {
			return false;
		}

		return $preferredLocation;
	}

	/**
	 * @param $name
	 * @param $address1
	 * @param $address2
	 * @param $city
	 * @param $state
	 * @param $zip
	 * @param $userId
	 *
	 * @return bool
	 */
	public function saveNewLocation($name, $address1, $address2, $city, $state, $zip, $phone, $userId)
	{
		$db = self::getDB();

		$name = $db->real_escape_string($name);
		$address1 = $db->real_escape_string($address1);
		$address2 = $db->real_escape_string($address2);
		$city = $db->real_escape_string($city);
		$state = $db->real_escape_string($state);
		$zip = $db->real_escape_string($zip);

		$query = "INSERT INTO shipping_locations (name, address1, address2, city, state, zip, phone, user_id, active) VALUES ('$name', '$address1', '$address2', '$city', '$state', '$zip', '$phone', $userId, '1')";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}

		return true;
	}

	/**
	 * @param $name
	 * @param $address1
	 * @param $address2
	 * @param $city
	 * @param $state
	 * @param $zip
	 * @param $id
	 *
	 * @return bool
	 */
	public function updateLocation($name, $address1, $address2, $city, $state, $zip, $phone, $id)
	{
		$db = self::getDB();

		$query = "UPDATE shipping_locations SET name = '$name', address1 = '$address1', address2 = '$address2', city = '$city', state = '$state', zip = '$zip', phone='$phone' WHERE id = $id";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}

		return true;
	}

	/**
	 * Saves the preferred pickup location
	 *
	 * @param $preferredLocation
	 * @param $userId
	 *
	 * @return bool
	 */
	public function savePreferredShipping($preferredLocation, $userId)
	{
		$db = self::getDB();

		$query = "UPDATE users SET preferred_shipping_location = $preferredLocation WHERE id = $userId ";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}

		return true;
	}

	/**
	 * @param int $id
	 *
	 * @return boolean
	 */
	public function inactivateShippingLocation($id)
	{
		$db = self::getDB();

		$query = "UPDATE shipping_locations SET active=0 WHERE id=$id";
		$result = $db->query($query);
		if ($result === false) {
			echo($db->error);
		}

		return true;
	}

	/**
	 * Gets a list of admin users
	 *
	 * @return array
	 */
	public function getAdminUsers()
	{
		$db = self::getDB();

		$query = "SELECT id, email, full_name FROM admin_users";
		if (($result = $db->query($query)) === false) {
			echo $db->error;

			return false;
		}

		$admins = $this->resultToArray($result);

		return $admins;
	}

	/**
	 * Saves an admin's information
	 *
	 * @param array $user
	 *
	 * @return bool
	 */
	public function saveSuperAdmin($user)
	{
		$db = self::getDB();
		$query = "UPDATE admin_users SET full_name = ?, email = ?";
		if ($user['password'] !== null) {
			$query .= ", password = ?";
		}
		$query .= " WHERE id = ?";
		if (($stmt = $db->prepare($query)) !== false) {
			if ($user['password'] !== null) {
				$stmt->bind_param("sssi", $user['name'], $user['email'], $user['password'], $user['id']);

				return $stmt->execute();
			}
			$stmt->bind_param("ssi", $user['name'], $user['email'], $user['id']);

			return $stmt->execute();
		}

		return false;
	}

	/**
	 * Gets number of users that received a certain notification
	 *
	 * @param int $notificationId
	 *
	 * @return int $numUsers
	 */
	public function numUsersForNotification($notificationId)
	{
		$db = self::getDB();

		$query = "SELECT SUM(active) FROM notifications_users WHERE message_id = ?";
		if ($stmt = $db->prepare($query)) {
			$stmt->bind_param("i", $notificationId);
			$stmt->execute();
			$stmt->bind_result($result);
			$stmt->fetch();

			return $result;
		}
		else {
			return false;
		}
	}

	/**
	 * escapes strings
	 *
	 * @param string $string
	 *
	 * @return string $escapedString
	 */
	public function escape($string)
	{
		$db = self::getDB();

		return $db->real_escape_string($string);
	}

	/**
	 * Creates a new super admin
	 *
	 * @param array $user
	 *
	 * @return int|false Id of the new record.
	 */
	public function createSuperUser($user)
	{
		$db = self::getDB();

		$query = "INSERT INTO admin_users (full_name, email, password) VALUES (?, ?, ?)";
		if (($stmt = $db->prepare($query)) !== false) {
			$stmt->bind_param("sss", $user['name'], $user['email'], $user['password']);
			$stmt->execute();

			return $stmt->insert_id;
		}

		return false;
	}

	/**
	 * Deletes a super admin
	 *
	 * @param int $id
	 *
	 * @return bool $success
	 */
	public function deleteSuperUser($id)
	{
		$db = self::getDB();

		$query = "DELETE FROM admin_users WHERE id = ?";
		if (($stmt = $db->prepare($query)) !== false) {
			$stmt->bind_param("i", $id);

			return $stmt->execute();
		}

		return false;
	}

	/**
	 * @param int $distId
	 *
	 * @throws Exception
	 * @return boolean
	 */
	public function deleteAllDistProducts($distId)
	{
		$this->deleteAllDistProductStyles($distId);

		$db = self::getDB();

		$query = "DELETE FROM dist_products WHERE dist_id=$distId";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error deleting distributer products: " . $db->error);
		}

		$query = "DELETE FROM dist_product_styles WHERE dist_id=$distId";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error deleting distributer products: " . $db->error);
		}

		$query = "DELETE FROM dist_product_category WHERE dist_id=$distId";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error deleting distributer products: " . $db->error);
		}

		return $result;
	}

	/**
	 * @param int $distId
	 *
	 * @throws Exception
	 * @return mixed
	 */
	public function deleteAllDistProductStyles($distId)
	{
		$db = self::getDB();

		$query = "DELETE FROM dist_product_styles WHERE dist_id=$distId";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error deleting distributer styles: " . $db->error);
		}

		return $result;
	}

	/**
	 * @param int $distId
	 * @param string $catName
	 *
	 * @throws Exception
	 * @return int
	 */
	public function addDistCategory($distId, $catName, $level, $parentId, $imageURL)
	{
		$db = self::getDB();

		$catName = $db->real_escape_string($catName);

		if ($parentId == null) {
			$parentId = 'NULL';
		}

		$query = "INSERT IGNORE INTO dist_categories (dist_id,name,level,parent_id,image) VALUES($distId,'$catName',$level,$parentId,'$imageURL')";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error distributer category: " . $db->error);
		}

		if ($db->insert_id == 0) {
			$query = "SELECT id FROM dist_categories WHERE dist_id=$distId AND name='$catName' AND level=$level AND parent_id=$parentId";
			$result = $db->query($query);
			if (!$result) {
				throw new Exception("Error distributer category: " . $db->error);
			}

			if ($result->num_rows == 0) {
				throw new Exception("Error distributer category: " . $db->error);
			}

			$row = $result->fetch_array();

			return $row['id'];
		}

		return $db->insert_id;
	}

	/**
	 * Also removes sub-categories for the given category
	 *
	 * @param int $distId
	 * @param int $catId
	 *
	 * @throws Exception
	 */
	public function removeDistCategory($distId, $catId)
	{
		$db = self::getDB();

		$query = "DELETE FROM dist_categories WHERE dist_id=$distId AND id=$catId";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}

		$query = "DELETE FROM dist_product_category WHERE dist_id=$distId AND category_id=$catId";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error deleting distributer products: " . $db->error);
		}
	}

	/**
	 * Also removes all sub-categories
	 *
	 * @param int $distId
	 *
	 * @throws Exception
	 */
	public function removeAllDistCategories($distId)
	{
		$db = self::getDB();

		$query = "DELETE FROM dist_categories WHERE dist_id=$distId";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}

		$query = "DELETE FROM dist_product_category WHERE dist_id=$distId";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error deleting distributer products: " . $db->error);
		}
	}

	/**
	 * @param int $distId
	 * @param string $title
	 * @param int $catId
	 * @param string $mDesc
	 * @param string $manufacturer
	 * @param string $partNum
	 *
	 * @throws Exception
	 * @return int
	 */
	public function insertOrUpdateDistProduct($distId, $title, $catId, $mDesc, $manufacturer, $partNum, $sku, $image)
	{
		if (empty($distId) || empty($title) || empty($catId) || empty($manufacturer) || empty($sku)) {
			throw new Exception("Missing required data to VirtualRainDB::insertOrUpdateProduct.");
		}
		$db = self::getDB();

		$title = $db->real_escape_string($title);
		$mDesc = $db->real_escape_string($mDesc);
		$manufacturer = $db->real_escape_string($manufacturer);
		$partNum = $db->real_escape_string($partNum);
		$sku = $db->real_escape_string($sku);
		$image = $db->real_escape_string($image);

		$query = "INSERT INTO dist_products (dist_id,title,manufacturer_description,manufacturer,part_num,sku,image,update_dt) 
					VALUES($distId,'$title','$mDesc','$manufacturer','$partNum','$sku','$image',NOW())
					ON DUPLICATE KEY UPDATE 
					title='$title',manufacturer_description='$mDesc',part_num='$partNum',image='$image',update_dt=NOW()";

		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error inserting or updating product: " . $db->error);
		}

		$prodId = null;
		if ($db->insert_id == 0) {
			$query = "SELECT id FROM dist_products WHERE dist_id=$distId AND manufacturer='$manufacturer' AND sku='$sku'";
			$result = $db->query($query);
			if (!$result) {
				throw new Exception("Error locatiing distributer product: " . $db->error);
			}

			if ($result->num_rows == 0) {
				throw new Exception("Error locatiing distributer product: " . $db->error);
			}

			$row = $result->fetch_array();
			$prodId = $row['id'];
		}
		else {
			$prodId = $db->insert_id;
		}

		// Add the product category association
		$query = "INSERT INTO dist_product_category (dist_id, product_id, category_id) VALUES($distId, $prodId, $catId) ON DUPLICATE KEY UPDATE id=id";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error inserting or updating product: " . $db->error);
		}

		return $prodId;
	}

	/**
	 * good
	 *
	 * @param int $distId
	 * @param int $productId
	 * @param string $styleDesc
	 * @param string $styleNum
	 * @param string $upc
	 * @param float $price
	 * @param string $unit
	 * @param boolean $default
	 * @param array $meta
	 *
	 * @throws Exception
	 */
	public function insertOrUpdateDistProductStyle($distId, $productId, $styleDesc, $styleNum, $upc, $price, $unit, $default, $meta)
	{
		if (empty($distId) || empty($productId)) {
			throw new Exception("VirtualRainDB::insertOrUpdateDistProductStyle: Missing distributer id and/or product id.");
		}

		$db = self::getDB();

		$styleDesc = $db->real_escape_string($styleDesc);
		$styleNum = $db->real_escape_string($styleNum);
		$upc = $db->real_escape_string($upc);

		if (empty($price)) {
			$price = 'NULL';
		}

		if ($default === true || $default === '1' || $default === 'y' || $default == 'yes' || $default == 'YES' || $default == 'Yes') {
			$default = 1;
		}
		else {
			$default = 0;
		}

		$metaString = '';
		if (is_array($meta)) {
			foreach ($meta as $term) {
				$metaString .= $db->real_escape_string(trim($term)) . ' ';
			}
		}
		else {
			$metaString = $db->real_escape_string(trim($meta));
		}

		$metaString = trim($metaString);

		$query = "INSERT INTO dist_product_styles (dist_id,product_id,style_description,style_num,price,unit,upc,default_style,meta,updated_dt)
					VALUES($distId,$productId,'$styleDesc','$styleNum',$price,'$unit','$upc',$default,'$metaString',NOW())
					ON DUPLICATE KEY UPDATE
					style_description='$styleDesc',price=$price,unit='$unit',upc='$upc',default_style=$default,meta='$metaString',updated_dt=NOW()";

		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error inserting or updating product style: " . $db->error);
		}
	}

	/**
	 *
	 * @param int $distId
	 *
	 * @throws Exception
	 * @return int
	 */
	public function getDistMaxCategoryLevel($distId)
	{
		$query = "SELECT MAX(level) max_level FROM dist_categories";
		$db = self::getDB();
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error in VirtualRainDB::getDistMaxCategoryLevel: " . $db->error);
		}

		$row = $result->fetch_assoc();

		return $row['max_level'];
	}

	/**
	 * @param int $distId
	 * @param null $parentId
	 * @param null $level
	 * @param bool $indexById
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getDistCategories($distId, $parentId = null, $level = null, $indexById = false)
	{
		$db = self::getDB();
		$query = "SELECT * FROM dist_categories WHERE dist_id=$distId";

		if ($parentId != null) {
			$query .= " AND parent_id=$parentId";
		}

		if ($level != null) {
			$query .= " AND level=$level";
		}

		$query .= " ORDER BY id";

		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}

		$categories = array();

		if ($indexById) {
			while ($row = $result->fetch_assoc()) {
				$categories[$row['id']] = $row;
			}
		}
		else {
			$categories = $this->resultToArray($result);
		}

		return $categories;
	}

	private function setDistCategoryImage(&$category)
	{

	}

	/**
	 * @param $catId
	 * @param $count
	 *
	 * @throws Exception
	 */
	public function updateDistCategoryProductCount($catId, $count)
	{
		$db = self::getDB();
		$query = "UPDATE dist_categories SET product_count=$count WHERE id=$catId";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}
	}

	/**
	 * @param $distId
	 *
	 * @throws Exception
	 */
	public function removeUnusedDistCategories($distId)
	{
		$cats = $this->getDistCategories($distId);
		foreach ($cats as $cat) {
			$count = $this->getDistProductCount($distId, $cat['id']);
			if ($count === 0) {
				// The category has no products so delete it and it's sub-categories
				$this->removeDistCategory($distId, $cat['id']);
			}
		}
	}

	/**
	 * @param $distId
	 * @param $prodId
	 * @param bool $withStyles
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getDistProductById($distId, $prodId, $withStyles = false)
	{
		$db = self::getDB();
		$query = "SELECT * FROM dist_products WHERE dist_id=$distId AND id=$prodId";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}

		if ($result->num_rows == 0) {
			return null;
		}

		$product = $result->fetch_assoc();

		if (!empty($product) && $withStyles) {
			$product['styles'] = $this->getDistProductStyles($distId, $prodId);
		}

		$this->setProductImage($product);

		return $product;
	}

	/**
	 * good
	 *
	 * @param unknown $distId
	 * @param unknown $partNum
	 * @param string $withStyles
	 *
	 * @throws Exception
	 * @return multitype:unknown
	 */
	public function getDistProductBySKU($distId, $sku, $withStyles = false)
	{
		$db = self::getDB();
		$query = "SELECT * FROM dist_products WHERE dist_id=$distId AND sku='$sku'";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}

		if ($result->num_rows == 0) {
			return null;
		}

		$product = $result->fetch_assoc();

		if ($withStyles) {
			$product['styles'] = $this->getDistProductStyles($distId, $product['id']);
		}

		$this->setProductImage($product);

		return $product;
	}

	/**
	 * @param $distId
	 * @param $prodId
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getDistProductStyles($distId, $prodId)
	{
		$db = self::getDB();
		$query = "SELECT * FROM dist_product_styles WHERE dist_id=$distId AND product_id=$prodId";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}

		$styles = $this->resultToArray($result);

		/*
		if( $withImages ) {
			foreach( $styles as &$style ) {
				$imgs = $this->getStyleImageByStyleNum( $style[ 'style_num' ] );
				if( $imgs !== null ) {
					$style[ 'image_name' ] = $imgs[ 'image_name' ];
					$style[ 'large_image_name' ] = $imgs[ 'large_image_name' ];
				}
			}
		}
		*/

		return $styles;
	}

	/**
	 * @param $distId
	 * @param $styleId
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getDistProductStyleById($distId, $styleId)
	{
		$db = self::getDB();
		$query = "SELECT * FROM dist_product_styles WHERE dist_id=$distId AND id=$styleId";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}

		$style = $result->fetch_assoc();

		/*
		if( $withImages ) {
			$imgs = $this->getStyleImageByStyleNum( $style[ 'style_num' ] );
			if( $imgs !== null ) {
				$style[ 'image_name' ] = $imgs[ 'image_name' ];
				$style[ 'large_image_name' ] = $imgs[ 'large_image_name' ];
			}
		}
		*/

		return $style;
	}

	/**
	 * good
	 *
	 * @param unknown $distId
	 * @param unknown $styleNum
	 *
	 * @throws Exception
	 * @return NULL
	 */
	public function getDistProductStyleByStyleNum($distId, $styleNum)
	{
		$db = self::getDB();
		$query = "SELECT * FROM dist_product_styles WHERE dist_id=$distId AND style_num='$styleNum'";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}

		$style = $result->fetch_assoc();

		/*
		if( $withImages ) {
			$imgs = $this->getStyleImageByStyleNum( $style[ 'style_num' ] );
			if( $imgs !== null ) {
				$style[ 'image_name' ] = $imgs[ 'image_name' ];
				$style[ 'large_image_name' ] = $imgs[ 'large_image_name' ];
			}
		}
		*/

		return $style;
	}

	/**
	 * @param $countOnly
	 * @param $distId
	 * @param $searchString
	 * @param bool $withinCategoryIds
	 * @param string $limitClause
	 * @param bool $withImages
	 * @param array $searchFields
	 * @param string $orderBy
	 *
	 * @return array
	 */
	public function searchDistProducts($countOnly, $distId, $searchString, $withinCategoryIds = null, $limitClause = null, $withImages = false, $searchFields = null, $orderBy = 'sku ASC')
	{
		$db = self::getDB();

		$dist = $this->getDistributer($distId);
		$skuOrPN = 'product.sku';
		if ($dist['show_part_num_instead_of_sku']) {
			$skuOrPN = 'product.part_num';
			if ($orderBy == 'sku ASC') {
				$orderBy = 'part_num ASC';
			}
		}

		$searchString = trim($searchString);
		if ($searchString == '') {
			return array();
		}

		if ($searchFields == null) {
			$searchFields = self::$DEFAULT_SEARCH_FIELDS;
		}

		// Separate search terms grouping quoted terms
		preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $searchString, $searchTerms);
		$searchTerms = $searchTerms[0];
		foreach ($searchTerms as &$searchTerm) {
			$searchTerm = trim($searchTerm);

			// If it's a quoted phrase then remove the quotes
			if ($searchTerm[0] == '"' && $searchTerm[strlen($searchTerm) - 1] == '"') {
				$searchTerm = str_replace('"', '', $searchTerm);
			}

			// Add additional related sub search terms...
			$subTerms = array();

			// For inches
			if (strstr($searchTerm, '"')) {
				$subTerms = array_merge($subTerms, array($searchTerm, str_replace('"', '', $searchTerm), str_replace('"', ' inches', $searchTerm), str_replace('"', ' inch', $searchTerm), str_replace('"', ' in', $searchTerm)));
			}

			// For feet
			if (strstr($searchTerm, "'")) {
				$subTerms = array_merge($subTerms, array($searchTerm, str_replace("'", '', $searchTerm), str_replace("'", ' feet', $searchTerm), str_replace("'", ' foot', $searchTerm), str_replace("'", ' ft', $searchTerm)));
			}

			// For pounds
			if (strstr($searchTerm, 'pound') || strstr($searchTerm, 'lb')) {
				$subTerms = array_merge($subTerms, array('pound', 'pounds', str_replace($searchTerm, 'lb', $searchTerm), str_replace($searchTerm, 'lbs', $searchTerm)));
			}

			if (strstr($searchTerm, '-')) {
				// Also look for the term without the dash
				$subTerms = array_merge($subTerms, array($searchTerm, str_replace('-', '', $searchTerm)));
			}

			if (count($subTerms) > 0) {
				$searchTerm = $subTerms;
			}
		}

		// Construct the query
		if ($countOnly) {
			$query = 'SELECT COUNT(*) AS count FROM ((';
		}
		else {
			$query = 'SELECT * FROM ((';
		}

		// *** First Sub Query *** //
		// The first sub query is going to be on SKU
		$query .= "SELECT product.* FROM dist_product_styles AS style";
		$query .= " INNER JOIN dist_products AS product ON product.id = style.product_id";
		if ($withinCategoryIds !== null && is_array($withinCategoryIds)) {
			$query .= " INNER JOIN dist_product_category AS pc ON pc.product_id = product.id";
		}
		$query .= " WHERE style.dist_id=$distId";

		if ($withinCategoryIds !== null && is_array($withinCategoryIds) && count($withinCategoryIds) > 0) {
			$query .= " AND (";
			foreach ($withinCategoryIds as $id) {
				$query .= " pc.category_id=$id OR";
			}
			$query = substr($query, 0, -3);
			$query .= ")";
		}

		// We are only going to use the first search term to search on SKU (or PN).
		// May want to allow all search terms but for now this should do.
		$searchTerm = &$searchTerms[0];
		if (!is_array($searchTerm)) {
			$searchTerm = $db->real_escape_string($searchTerm);
			$query .= " AND $skuOrPN LIKE '%$searchTerm%'";
		}
		else {
			$searchTerm[0] = $db->real_escape_string($searchTerm[0]);
			$query .= " AND $skuOrPN LIKE '%{$searchTerm[0]}%'";
		}

		$query .= " ORDER BY $skuOrPN ASC) UNION ALL (";

		// *** Second Sub Query *** //
		// The second sub query will be on all other serach fields ignoring 
		// the rows returned in the first sub query
		$query .= "SELECT product.* FROM dist_product_styles AS style";
		$query .= " INNER JOIN dist_products AS product ON product.id = style.product_id";
		if ($withinCategoryIds !== null && is_array($withinCategoryIds)) {
			$query .= " INNER JOIN dist_product_category AS pc ON pc.product_id = product.id";
		}
		$query .= " WHERE style.dist_id=$distId";

		if ($withinCategoryIds !== null && is_array($withinCategoryIds) && count($withinCategoryIds) > 0) {
			$query .= " AND (";
			foreach ($withinCategoryIds as $id) {
				$query .= " pc.category_id=$id OR";
			}
			$query = substr($query, 0, -3);
			$query .= ")";
		}

		$trimAnd = false;
		$afterGroup = false;
		$query .= " AND (";
		foreach ($searchTerms as $term) {
			if ($term == '' || count($term) == 0) {
				continue;
			}

			if (!is_array($term)) {
				// Single terms will be compared to other term with AND
				if ($afterGroup) {
					$query .= " AND ";
				}
				$query .= "(CONCAT_WS(' ',";
				foreach ($searchFields as $field) {
					$query .= "$field,";
				}
				$query = substr($query, 0, -1);
				$query .= ")";

				$term = $db->real_escape_string($term);
				$query .= " REGEXP '([[:blank:]]|^)$term([[:blank:][:punct:]]|$)' AND $skuOrPN NOT LIKE '%$term%') AND ";
				$trimAnd = true;
				$afterGroup = false;
			}
			else {
				// An array of terms (sub-terms) will be an OR grouping
				$query .= " (";
				foreach ($term as $subTerm) {
					$query .= "(CONCAT_WS(' ',";
					foreach ($searchFields as $field) {
						$query .= "$field,";
					}
					$query = substr($query, 0, -1);
					$query .= ")";

					$subTerm = $db->real_escape_string($subTerm);
					$query .= " REGEXP '([[:blank:]]|^)$subTerm([[:blank:][:punct:]]|$)' AND $skuOrPN NOT LIKE '%$subTerm%') OR ";
				}
				$query = substr($query, 0, -4);
				$query .= ") ";
				$trimAnd = false;
				$afterGroup = true;
			}
		}
		if ($trimAnd) {
			$query = substr($query, 0, -5);
		}
		$query .= ")";

		// Group by sku (or part_num) since we want to return a list of products not styles
		if (!$countOnly) {
			$query .= " GROUP BY $skuOrPN";
			$query .= " ORDER BY $orderBy";

			$query .= ")) AS tmp ORDER BY $orderBy";

			if ($limitClause !== null) {
				$query .= " LIMIT $limitClause";
			}

			$products = $this->getAll($query);

			if ($withImages) {
				foreach ($products as &$product) {
					$this->setProductImage($product);
				}
			}

			return $products;
		}
		else {
			$query .= ")) AS tmp";
			$count = $this->getOne($query);

			return $count['count'];
		}
	}

	/**
	 * Returns the ids of all sub categories of the supplied parent category.
	 * This includes all nested sub categories below the parent.
	 *
	 * @param $parentId
	 *
	 * @return array
	 */
	public function getAllChildCategoryIds($parentId)
	{
		$catIdsTemp = $this->getAllChildCategoryIdsInternal($parentId);

		$catIds = array();
		foreach ($catIdsTemp as $cat) {
			$catIds[] = $cat['id'];
		}

		return $catIds;
	}

	/**
	 * Helper function for getAllChildCategoryIds.
	 *
	 * Recurses.
	 *
	 * @param $parentId
	 * @param null $catIds
	 *
	 * @return array|null
	 */
	private function getAllChildCategoryIdsInternal($parentId, &$catIds = null)
	{
		if ($catIds === null) {
			$catIds = array();
		}
		$query = "SELECT id FROM dist_categories WHERE parent_id = $parentId";
		$ids = $this->getAll($query);
		$catIds = array_merge($catIds, $ids);
		foreach ($ids as $cat) {
			$this->getAllChildCategoryIdsInternal($cat['id'], $catIds);
		}

		return $catIds;
	}

	/**
	 * @param $distId
	 * @param null $catId
	 * @param string $limit
	 * @param string $orderBy
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getDistProducts($distId, $catId = null, $limit = '', $orderBy = 'dp.id ASC')
	{
		$db = self::getDB();

		// TODO: Shouldn't we be using $distId in the query?
		$query = "SELECT dp.* FROM dist_products AS dp INNER JOIN dist_product_category AS dpc ON dp.id = dpc.product_id";
		if (!empty($catId)) {
			$query .= " WHERE dpc.category_id=$catId";
		}

		if (!empty($orderBy)) {
			$query .= " ORDER BY $orderBy";
		}

		if (!empty($limit)) {
			$query .= " LIMIT $limit";
		}

		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}

		$products = $this->resultToArray($result);
		foreach ($products as &$product) {
			$this->setProductImage($product);
		}

		return $products;
	}

	/**
	 * @param $product
	 */
	private function setProductImage(&$product)
	{
		if (empty($product['image'])) {
			$pattern = '/[^A-Za-z0-9-_~.\s]/';

			$manufacturer = preg_replace($pattern, '', $product['manufacturer']);
			$sku = preg_replace($pattern, '', $product['sku']);

			$url = IMAGES_BASE_URL . rawurlencode(strtoupper($manufacturer)) . '/' . rawurlencode(strtoupper($sku) . '.jpg');
			$test = file_get_contents($url);
			$product['image'] = $url;
			
			// TODO: Check here if the large image exists?
			$urlLg = IMAGES_BASE_URL . rawurlencode(strtoupper($manufacturer)) . '/' . rawurlencode(strtoupper($sku) . '_1K.jpg');
			//$test = @file_get_contents($urlLg);
			if ($test !== false) {
				$product['image_large'] = $urlLg;
			}
			else {
				$product['image_large'] = null;
			}
		}
	}

	/**
	 * @param $distId
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getDistProductsForExport($distId)
	{
		$db = self::getDB();

		$query = "SELECT dpc.category_id, dp.title, dp.manufacturer, dp.manufacturer_description, dp.sku, ps.style_description, 
					ps.price, ps.default_style, dp.part_num, dp.sku, ps.style_num, ps.upc, ps.meta, ps.updated_dt 
					FROM dist_product_styles AS ps
					INNER JOIN dist_products AS dp ON dp.id = ps.product_id
					INNER JOIN dist_product_category AS dpc ON dp.id = dpc.product_id
					WHERE dp.dist_id=$distId
					ORDER BY dpc.category_id, dp.title";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}

		return $this->resultToArray($result);
	}

	/**
	 * @param int $distId
	 * @param string $sku
	 * @param string $styleNum
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function distProductExists($distId, $sku, $styleNum = null)
	{
		$db = self::getDB();
		$query = "SELECT id FROM dist_products WHERE dist_id=$distId AND sku='$sku'";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}
		if ($result->num_rows > 0) {
			if ($styleNum !== null) {
				$query = "SELECT id FROM dist_product_styles WHERE dist_id=$distId AND style_num='$styleNum'";
				$result = $db->query($query);
				if (!$result) {
					throw new Exception("Error: " . $db->error);
				}
				if ($result->num_rows > 0) {
					return true;
				}
				else {
					return false;
				}
			}
			else {
				return true;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * @param int $distId
	 * @param int $userId
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getUserFavorites($distId, $userId)
	{
		$db = self::getDB();
		$query = "SELECT * FROM user_favorites WHERE user_id = $userId";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}

		$favorites = $this->resultToArray($result);
		foreach ($favorites as &$favorite) {
			$favorite['product'] = $this->getDistProductBySKU($distId, $favorite['sku']);
			if ($favorite['product'] == null) {
				// Found an invalid favorite (probably because the product is no longer available) so remove it from the db
				$this->removeUserFavorite($userId, $favorite['sku'], $favorite['style_num']);

				return $this->getUserFavorites($distId, $userId);
			}
			$favorite['style'] = $this->getDistProductStyleByStyleNum($distId, $favorite['style_num']);
		}

		return $favorites;
	}

	/**
	 * @param int $userId
	 * @param string $sku
	 * @param boolean
	 *
	 * @throws Exception
	 */
	public function addUserFavorite($userId, $sku, $styleNum = null)
	{
		$db = self::getDB();
		$query = "INSERT IGNORE INTO user_favorites (user_id,sku,style_num) VALUES($userId,'$sku','$styleNum')";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}
	}

	/**
	 * @param int $userId
	 * @param string $sku
	 * @param boolean
	 *
	 * @throws Exception
	 */
	public function removeUserFavorite($userId, $sku, $styleNum = null)
	{
		$db = self::getDB();
		$query = "DELETE FROM user_favorites WHERE user_id=$userId AND sku='$sku' AND style_num='$styleNum'";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}
	}

	/**
	 * TODO: Should eventually add style id as a param.
	 *
	 * @param int $userId
	 * @param string $sku
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function isUserFavorite($userId, $sku)
	{
		$db = self::getDB();
		$query = "SELECT * FROM user_favorites WHERE user_id=$userId AND sku='$sku'";
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}

		return $result->num_rows == 0 ? false : true;
	}

	/**
	 * @param int $distId
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getOrderStatuses($distId)
	{
		$query = "SELECT * FROM dist_order_statuses WHERE dist_id=$distId ORDER BY id";
		$db = self::getDB();
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}

		return $this->resultToArray($result);
	}

	/**
	 * @param int $distId
	 * @param string $name
	 *
	 * @throws Exception
	 */
	public function addOrderStatus($distId, $name)
	{
		$query = "INSERT IGNORE INTO dist_order_statuses (dist_id, name) VALUES($distId, '$name')";
		$db = self::getDB();
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}
	}

	/**
	 * @param int $id
	 *
	 * @throws Exception
	 */
	public function deleteOrderStatus($id)
	{
		$query = "DELETE FROM dist_order_statuses WHERE id=$id";
		$db = self::getDB();
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}
	}

	/**
	 * @param int $distId
	 *
	 * @throws Exception
	 */
	public function deleteAllOrderStatuses($distId)
	{
		$query = "DELETE FROM dist_order_statuses WHERE dist_id=$distId";
		$db = self::getDB();
		$result = $db->query($query);
		if (!$result) {
			throw new Exception("Error: " . $db->error);
		}
	}

	/**
	 * @param int $distId
	 *
	 * @return array
	 */
	public function getAllBranches($distId)
	{
		$query = "SELECT * FROM branches WHERE dist_id=$distId";

		return $this->getAll($query);
	}

	/**
	 * @param int $id
	 *
	 * @return array|null
	 */
	public function getBranch($id)
	{
		$query = "SELECT * FROM branches WHERE id=$id";

		return $this->getOne($query);
	}

	/**
	 * @param int $locationId
	 *
	 * @return array|null
	 */
	public function getBranchFromLocationId($locationId)
	{
		$query = "SELECT b.* FROM branches b INNER JOIN pickup_locations l ON b.id = l.branch_id WHERE b.id = l.branch_id AND l.id = $locationId";

		return $this->getOne($query);
	}

	/**
	 * @param array $branch
	 *
	 * @return bool|mysqli_result
	 * @throws Exception
	 */
	public function addOrUpdateBranch($branch)
	{
		$query = $this->createAddOrUpdateStatment('branches', 'id', $branch);

		return $this->query($query);
	}

	/**
	 * @param int $id
	 *
	 * @return bool|mysqli_result
	 * @throws Exception
	 */
	public function deleteBranch($id)
	{
		// First set all the locations assigned to this branch to null
		$query = "UPDATE pickup_locations SET branch_id=NULL WHERE branch_id=$id";
		if ($this->query($query)) {
			$query = "DELETE FROM branches WHERE id=$id";

			return $this->query($query);
		}

		return false;
	}
}








