<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('BaseInfluencerController.php');

class Deauth extends BaseInfluencerController
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('social_model');
		$this->social_model->setUser($this->user['email']);
	}

	public function platform($platform, $accountIndex)
	{
		$this->social_model->disconnect($platform, $accountIndex);

		redirect('/profile/');
	}

}