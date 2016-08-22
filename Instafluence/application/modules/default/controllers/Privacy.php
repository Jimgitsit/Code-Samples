<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('BaseDefaultController.php');

class Privacy extends BaseDefaultController
{

	public function init()
	{
		
	}

	public function index()
	{
		$this->smarty->display('home/privacy.html');
	}

}