<?php

require_once( 'Page.php' );
require_once( 'VirtualRainDB.php' );

class AdminPage {
	protected $smarty = null;
	protected $requstedPage = '';
	
	private $template = null;
	
	public $alertInfo = '';
	public $alertError = '';
	public $alertSuccess = '';
	
	public function __construct( $template, $pageTitle ) {
		$this->template = $template;
		
		$this->requstedPage = str_replace( '?' . $_SERVER[ 'QUERY_STRING' ], '', $_SERVER[ 'REQUEST_URI' ] );
		
		if( empty( $_SESSION ) ) {
			session_start();
		}
		
		$this->checkLogin();
		
		// Start Smarty
		$this->initSmarty();
		
		if( $this->requstedPage != '/admin/login' ) {
			// Set the admin_user smarty var
			if( isset( $_SESSION[ 'admin_user' ] ) ) {
				$this->smarty->assign( 'admin_user', $_SESSION[ 'admin_user' ] );
			}
			else if($_SESSION[ 'distributer' ]) {
				$this->smarty->assign( 'distributer', $_SESSION[ 'distributer' ] );
				if(isset($_SESSION['distributer']['branch'])) {
					$this->smarty->assign( 'branch', $_SESSION[ 'distributer' ]['branch'] );
				}
			}
			
			// Set the brand name according to the admin type
			if( isset( $_SESSION[ 'distributer' ] ) ) {
				$this->smarty->assign( 'brand', $_SESSION[ 'distributer' ][ 'company_name' ] );
				/*
				if(isset($_SESSION['distributer']['branch'])) {
					$this->smarty->assign( 'brand', $_SESSION[ 'distributer' ][ 'company_name' ] . '<br/>' . $_SESSION['distributer']['branch']['name'] );
				}
				*/
				$this->smarty->assign( 'brandLogo', '/dist/' . $_SESSION[ 'distributer' ][ 'dir' ] . '/' . $_SESSION[ 'distributer' ][ 'logo' ] );
			}
			else {
				$this->smarty->assign( 'brand', 'Virtualrain, Inc' );
				$this->smarty->assign( 'brandLogo', "/templates/img/vrain_logo.png" );
			}
		}
	}
	
	private function checkLogin() {
		if( $this->requstedPage != '/admin/login' && ( empty( $_SESSION[ 'admin_user' ] ) && empty( $_SESSION[ 'distributer' ] ) ) ) {
			$this->redirect( 'login' );
		}
	}
	
	/**
	 * Initializes Smarty
	 *
	 * @param string $template
	 */
	private function initSmarty() {
		$this->smarty = new Smarty();
	
		$this->smarty->setTemplateDir( 'templates/' );
		$this->smarty->setCompileDir( 'libs/smarty/templates_c/' );
		$this->smarty->setConfigDir( 'libs/smarty/configs/' );
		$this->smarty->setCacheDir( 'libs/smarty/cache/' );
	
		// TODO: *** For debugging, uncomment for release ***
		//$this->smarty->testInstall();
		//$this->smarty->caching = 0;
		//$this->smarty->clearAllCache();
		$this->smarty->force_compile = true;
	
		//** un-comment the following line to show the debug console
		//$smarty->debugging = true;
	}
	
	/**
	 * Set an alert to be shown on the page.
	 * 
	 * $type can be 'Error', 'Info', or 'Success'.
	 * 
	 * @param unknown $type
	 * @param unknown $msg
	 */
	public function setAlert( $type, $msg ) {
		switch( $type ) {
			case 'Error': {
				$this->alertError = $msg;
				break;
			}
			case 'Info': {
				$this->alertInfo = $msg;
				break;
			}
			case 'Success': {
				$this->alertSuccess = $msg;
				break;
			}
			default: {
				throw new Exception( "Invalid alert type '$type' in AdminPage::setAlert" );
			}
		}
	}
	
	public function redirect( $url ) {
		header( "Location: $url" );
		exit();
	}

	public function display() {
		$this->smarty->assign( 'alertInfo', $this->alertInfo );
		$this->smarty->assign( 'alertError', $this->alertError );
		$this->smarty->assign( 'alertSuccess', $this->alertSuccess );
		
		if( $this->template != null ) {
			$this->smarty->display( $this->template );
		}
		else {
			die( 'null template.' );
		}
	}
}