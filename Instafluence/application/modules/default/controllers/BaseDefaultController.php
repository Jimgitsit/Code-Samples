<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . 'controllers/BaseController.php');

abstract class BaseDefaultController extends BaseController
{

	public function __construct()
	{
		$this->initSmarty('default');
		parent::__construct();
	}

}