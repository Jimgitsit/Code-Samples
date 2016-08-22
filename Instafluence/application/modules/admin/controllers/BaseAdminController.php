<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . 'controllers/BaseController.php');

abstract class BaseAdminController extends BaseController
{

	public function __construct()
	{
		$this->initSmarty('admin');
		parent::__construct();

		if (!$this->checkAuth('admin'))
			redirect('/login/');
	}

}
