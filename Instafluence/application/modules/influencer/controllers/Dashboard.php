<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('BaseInfluencerController.php');

class Dashboard extends BaseInfluencerController
{
	public function init()
	{
		$this->forceSSL();

		$this->setActiveMenuItem('dashboard');
	}

	public function index()
	{
		$this->smarty->display('dashboard/index.html');
	}

}
