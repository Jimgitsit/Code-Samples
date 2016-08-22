<?php

require_once( 'Smarty.class.php' );
require_once( 'VirtualRainDB.php' );
require_once( 'SessionCart.php' );

/**
 * Base class for all pages.
 * Initializes Smarty and build the header and footer.
 *
 * @author Jim M
 *
 */
class Page {
	public $smarty = null;
	private $template = null;
	
	protected $searchTerm = '';
	protected $searchCatId = null;
	protected $searchCatName = '';
	protected $searchWithinCat = false;
	
	/**
	 * Constructor
	 *
	 * @param string $template - File name of the template.
	 */
	public function __construct( $template ) {
		$this->startSession();
		$this->checkLogin();

		// Start Smarty
		$this->initSmarty( $template );

		// Update the user info just in case it was changed since they logged in
		$this->updateUserSession();

		$this->initPageAccount();
		$this->initCart();
		$this->setHistory();
		$this->checkPickupLocations();
		$this->checkShippingLocations();
		$this->initSearchVars();

		$db = new VirtualRainDB();

		if( isset( $_SESSION['user']['id'] ) ){
			// Notifications count
			$_SESSION['notifications'] = $db->getNotificationsCount( $_SESSION['user']['id'] );
			$this->smarty->assign( 'notifications', $_SESSION['notifications'] );

			// Cart Items count
			$this->smarty->assign( 'cartNum', count( $_SESSION['cart'] ) );
		}
		
		if (isset($_GET['dist_id'])) {
			$_SESSION['dist_id'] = $_GET['dist_id'];
		}

		// Set Distributors Skin
		if( isset($_SESSION['dist_id']) ){
			// TODO: Save the distributer in the session so we don't have to get from the db for every page load
			$distributor = $db->getDistributer( $_SESSION['dist_id'] );
			$distDir = $distributor[ 'dir' ];
			$skin = "/dist/$distDir/skin/{$distDir}_skin.css"; 
			$logo = $distributor[ 'logo' ];
			$this->smarty->assign( 'distDir', $distDir );
			$this->smarty->assign( 'skin', $skin );
			$this->smarty->assign( 'distLogo', $logo );
		}
		
		$this->smarty->assign( 'hideBackButton', null );
	}

	public function startSession() {
		// Set the session and cookie lifetimes to 1 year
		$sessionExpires = time()+60*60*24*365;
		ini_set('session.gc_maxlifetime', $sessionExpires);
		session_set_cookie_params($sessionExpires);
		session_name("VRSESSION");
		@session_start();
		
		/*
		if (empty($_COOKIE['session_id'])) {
			@session_start();
			// Save the session id in a cookie
			setcookie( 'session_id', session_id(), pow(2,31)-1 );
		}
		else {
			// Recall the session id from the cookie
			session_id($_COOKIE['session_id']);
			@session_start();
		}
		*/
	}

	private function checkLogin() {
		$page = str_replace( '?' . $_SERVER[ 'QUERY_STRING' ], '', $_SERVER[ 'REQUEST_URI' ] );
		if( ( empty( $_SESSION[ 'user' ] ) || empty( $_SESSION[ 'user' ][ 'id' ] ) ) && $page != '/login' && $page != '/register' && $page != '/forgot' ) {
			header( 'Location:login'.(isset($_SESSION['dist_id']) ? "?dist_id=".$_SESSION['dist_id'] : "") );
			exit();
		}
	}

	// Set smarty variable if distributor has any pickup locations set
	private function checkPickupLocations(){
		if( isset($_SESSION['dist_id']) ){
			$db = new VirtualRainDB();
			$hasPickupLocation = $db->userHasPickupLocations( $_SESSION['dist_id'] );
			if( $hasPickupLocation == true ){
				$this->smarty->assign( 'hasPickupLocations', $hasPickupLocation );
			}
		}
	}

	// Set smarty variable if distributor has any shipping locations set
	private function checkShippingLocations(){
		if( isset($_SESSION['user']['id']) ){
			$db = new VirtualRainDB();
			$hasShippingLocation = $db->userHasShippingLocations( $_SESSION['user']['id'] );
			if( $hasShippingLocation == true ){
				$this->smarty->assign( 'hasShippingLocations', $hasShippingLocation );
			}
		}
	}
	
	/**
	 * Initializes the $this->search* members and smarty vars.
	 */
	private function initSearchVars() {
		// Search term. Found in order from GET, POST, or SESSION
		if (!empty($_GET['search_term'])) {
			$this->searchTerm = trim($_GET['search_term']);
			$_SESSION['client']['search_term'] = $this->searchTerm;
		}
		else if (!empty($_POST['search_term'])) {
			$this->searchTerm = trim($_POST['search_term']);
			$_SESSION['client']['search_term'] = $this->searchTerm;
		}
		else if (!empty($_SESSION['client']['search_term'])) {
			$this->searchTerm = $_SESSION['client']['search_term'];
		}
		
		// Search category ID. Found in order from GET['search_cat_id'], POST, or $_GET['parent_id']
		// This is never saved in the session
		if (!empty($_GET['search_cat_id'])) {
			$this->searchCatId = $_GET['search_cat_id'];
		}
		else if (!empty($_POST['search_cat_id'])) {
			$this->searchCatId = $_POST['search_cat_id'];
		}
		else if (!empty($_GET['parent_id'])) {
			$this->searchCatId = $_GET['parent_id'];
		}
		else if (!empty($_GET['cat_id'])) {
			$this->searchCatId = $_GET['cat_id'];
		}
		else {
			$this->searchCatId = null;
			$_SESSION['client']['search_cat_id'] = $this->searchCatId;
		}
		
		// Search within category name. Obtained if searchCatId != null
		if ($this->searchCatId != null) {
			// Search within category. Found in order from GET, POST, or SESSION
			if (!empty($_GET['search_within_cat'])) {
				$this->searchWithinCat = $_GET['search_within_cat'] == 'false' || $_GET['search_within_cat'] == false ? false : true;
				$_SESSION['client']['search_within_cat'] = $this->searchWithinCat;
			}
			else if (!empty($_POST['search_within_cat'])) {
				$this->searchWithinCat = $_POST['search_within_cat'] == 'false' || $_GET['search_within_cat'] == false ? false : true;
				$_SESSION['client']['search_within_cat'] = $this->searchWithinCat;
			}
			else if (isset($_SESSION['client']['search_within_cat'])) {
				$this->searchWithinCat = $_SESSION['client']['search_within_cat'];
			}
		
			$db = new VirtualRainDB();
			$this->searchCatName = $db->getDistCategoryName( $_SESSION[ 'dist_id' ], $this->searchCatId );
		}
		
		// Set smarty vars for search form in footer.html
		$this->smarty->assign('searchTerm', $this->searchTerm);
		$this->smarty->assign('searchCatId', $this->searchCatId);
		$this->smarty->assign('searchCatName', $this->searchCatName);
		$this->smarty->assign('searchWithinCat', $this->searchWithinCat);
	}

	/**
	 * Initializes Smarty
	 *
	 * @param string $template
	 */
	private function initSmarty( $template ) {
		$this->smarty = new Smarty();
		$this->template = $template;

		$this->smarty->setTemplateDir( 'templates/' );
		$this->smarty->setCompileDir( 'libs/smarty/templates_c/' );
		$this->smarty->setConfigDir( 'libs/smarty/configs/' );
		$this->smarty->setCacheDir( 'libs/smarty/cache/' );

		// TODO: *** For debugging, uncomment for release ***
		//$this->smarty->testInstall();
		$this->smarty->caching = 0;
		$this->smarty->clearAllCache();
		$this->smarty->force_compile = true;

		//** un-comment the following line to show the debug console
		//$this->smarty->debugging = true;
	}

	/**
	 * Creates header common to all pages and assigns it to the 'header' Smarty var.
	 */
	protected function initPageHeader() {
		// Load the header template based on if the user is logged in
		$this->smarty->assign( 'header', $this->smarty->fetch( 'header.html' ) );
	}

	/**
	 * Creates account section common to all pages and assigns it to the 'account' Smarty var.
	 */
	protected function initPageAccount() {
		$this->smarty->assign( 'account', $this->smarty->fetch( 'account.html' ) );
	}

	protected function updateCart() {
		$this->initCart();
	}

	/**
	 * Creates footer common to all pages and assigns it to the 'cart' Smarty var.
	 */
	protected function initCart() {
		if( !empty( $_SESSION[ 'user' ] ) ) {
			$sessionCart = new SessionCart($_SESSION[ 'user' ]['id']);

			$cartItems = $sessionCart->getItems();
			$this->smarty->assign( 'cartItems', $cartItems );
			
			$totalPrice = $sessionCart->totalPrice();
			$this->smarty->assign( 'cartSubtotal', $totalPrice );

			$this->smarty->assign( 'cartNum', count( $_SESSION['cart'] ) );
			
			if( $_SESSION[ 'user' ][ 'show_pricing' ] == 1 ) {
				$this->smarty->assign( 'showPricing', true );
			}
			else {
				$this->smarty->assign( 'showPricing', false );
			}
			
			$db = new VirtualRainDB();
			$dist = $db->getDistributer($_SESSION['dist_id']);
			$this->smarty->assign('showPNInsteadOfSKU', $dist['show_part_num_instead_of_sku']);
			$this->smarty->assign('showUnits', $dist['show_units']);
		}
	}

	/**
	 * Displays the Smarty template given in the constructor.
	 */
	public function display() {
		if( $this->template != null ) {
			$this->smarty->display( $this->template );
		}
		else {
			die( 'null template.' );
		}
	}
	
	public function resetHistory() {
		if (empty($_SESSION)) {
			$this->startSession();
		}
		
		$_SESSION[ 'history' ] = array();
		unset( $_GET[ 'dir' ] );
		$this->setHistory();
	}
	
	public function removeLastHistory() {
		if( empty( $_SESSION ) ) {
			throw new Exception( "removeLastHistory called before session was initialized." );
		}
		
		if( count( $_SESSION[ 'history' ] ) > 0 ) {
			unset( $_SESSION[ 'history' ][ count( $_SESSION[ 'history' ] ) - 1 ] );
		}
	}

	public function setHistory() {
		if( empty( $_SESSION[ 'history' ] ) ) {
			$_SESSION[ 'history' ] = array();
		}
		
		// Ignore ajax calls
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) && strstr( $_POST[ 'action' ], '_ajax' ) !== false ) {
			// Yes, ignore the call
			return;
		}

		// Did back button get pressed?
		if( !empty( $_GET ) && isset( $_GET[ 'dir' ] ) && $_GET[ 'dir' ] == 'back' ) {
			// yes, remove last item from history
			if( count( $_SESSION[ 'history' ] ) > 0 ) {
				unset( $_SESSION[ 'history' ][ count( $_SESSION[ 'history' ] ) - 1 ] );
			}
		}
		else {
			// no. Is this page the same as the last?
			if( count( $_SESSION[ 'history' ] ) == 0 || ( $_SERVER[ 'REQUEST_URI' ] != $_SESSION[ 'history' ][ count( $_SESSION[ 'history' ] ) - 1 ] ) ) {
				// No, is this the login page?
				if (strstr($_SERVER[ 'REQUEST_URI' ], '/login') == false) {
					// No, add this page to history
					$url = $_SERVER['REQUEST_URI'];
					$url = str_replace('&dir=back', '', $url);
					$url = str_replace('?dir=back', '', $url);
					$url = str_replace('?doreg=true', '', $url);
					if (strstr($url, 'gcm_reg_id=')) {
						$url = '/categories';
					}
					$_SESSION['history'][] = $url;
				}
			}
		}

		// Setup the back button url
		$backUrl = '';
		if (!LEGACY_HOST_NAME && ($_SERVER[ 'REQUEST_URI' ] == '/register' || $_SERVER[ 'REQUEST_URI' ] == '/forgot')) {
			$backUrl = $_SESSION[ 'history' ][ count( $_SESSION[ 'history' ] ) - 2 ];
		}
		else if( count( $_SESSION[ 'history' ] ) > 1 ) {
			$backUrl = $_SESSION[ 'history' ][ count( $_SESSION[ 'history' ] ) - 2 ];
			strstr($backUrl, '?') ? $backUrl .= '&dir=back' : $backUrl .= '?dir=back';
		}
		else {
			//echo('Error: no history for the back button!');
		}
		//echo( $backUrl . '<br/><br/>' );
		$this->smarty->assign( 'prev_request', $backUrl );
		//echo('<br/><br/><br/><br/><br/><br/>');
		//var_dump( $_SESSION[ 'history' ] );
	}

	private function updateUserSession() {
		if( !empty( $_SESSION[ 'user' ] ) && !empty( $_SESSION[ 'user' ][ 'id' ] ) ) {
			$db = new VirtualRainDB();
			$user = $db->getUserById( $_SESSION[ 'user' ][ 'id' ] );
			$_SESSION[ 'user' ] = $user;
			if(isset($_SESSION['branch'])) {
				unset($_SESSION['branch']);
			}
			if(isset($_SESSION['user']['branch_id']) && $_SESSION['user']['branch_id'] != 0) {
				$_SESSION['branch'] = $db->getBranch($_SESSION['user']['branch_id']);
			}
		}
	}
}









