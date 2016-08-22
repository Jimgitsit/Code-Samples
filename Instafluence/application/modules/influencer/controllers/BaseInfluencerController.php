<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Base controller for the Influencer controllers
 * 
 * @author Jason Maurer <jason@builtbyhq.com>
 */

require_once(APPPATH . 'controllers/BaseController.php');

abstract class BaseInfluencerController extends BaseController
{

	protected $user;

	public function __construct()
	{
		$this->initSmarty('influencer');
		parent::__construct();

		if (!$this->checkAuth('influencer'))
			redirect('/login/');

		$this->user = $this->session->all_userdata();

		$this->smarty->assign('avatar', md5($this->user['email']));
		$this->smarty->assign('default_avatar', urlencode($this->config->item('base_url').'img/default_avatar.png'));
	}
}