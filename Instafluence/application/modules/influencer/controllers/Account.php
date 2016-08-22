<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('BaseInfluencerController.php');

class Account extends BaseInfluencerController
{

	public function init()
	{
		$this->forceSSL();
		
		$this->setActiveMenuItem('account');
	}

	public function index()
	{
		$msg = $this->input->get('m');
		if ($msg != null) {
			switch ($msg) {
				case 'finish': {
					$this->smarty->assign('msg', "Great! Thanks for filling out the Influencer Profile<sup>&trade;</sup>");
					break;
				}
			}
		}
		
		$this->load->model('social_model');
		$this->load->model('profile_model');

		$profile = $this->profile_model->getProfile($this->user['email']);
		$social  = $this->social_model->getCollection()->findOne(array('email' => $this->user['email']));
		
		$this->smarty->assign('social', $social);
		$this->smarty->assign('profile', $profile);
		$this->smarty->assign('user', $this->user);
		$this->smarty->display('account/index.html');
	}

}