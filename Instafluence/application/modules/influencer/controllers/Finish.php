<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('BaseInfluencerController.php');

class Finish extends BaseInfluencerController
{

	public function init()
	{
		$this->forceSSL();

		$this->setActiveMenuItem('profile');
	}

	public function index()
	{
		$this->smarty->display('profile/finish.html');
	}

}