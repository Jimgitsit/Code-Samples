<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Global controller for the entire application
 *
 * @author Jason Maurer <jason@builtbyhq.com>
 */

require_once(APPPATH . 'third_party/smarty/libs/Smarty.class.php');

abstract class BaseController extends MX_Controller
{

	protected $smarty;

	public function __construct()
	{
		parent::__construct();

		if (method_exists($this, 'init'))
			$this->init();
	}

	protected function initSmarty($moduleName)
	{
		$this->smarty = new Smarty();

		$this->smarty->caching = 0;
		$this->smarty->setTemplateDir(APPPATH . "modules/$moduleName/views");
		$this->smarty->setCompileDir(APPPATH . 'third_party/smarty/templates_c');
		//$this->smarty->setConfigDir(APPPATH . 'third_party/smarty/configs');
		$this->smarty->setCacheDir(APPPATH . 'cache');
	}

	/**
	 * Used for processing forms through some simple checks and CodeIgniter
	 * form validation.
	 *
	 * @param formName(string) Name of form (set in HTML) that is being processed
	 * @param function() Function to run after form is processed
	 * @param validation(bool) Determines whether or not form needs validation
	 */
	protected function processForm($formName, $function, $validation = true)
	{
		/* POST data exists */
		if ($this->input->post() !== false) {
			/* Form needs to be validated */
			if ($validation) {
				/* Form validation passed */
				if ($this->form_validation->run($formName) !== false) {
					/* Run success function */
					$function($this->input->post());
				}
			} else $function($this->input->post());
		}
	}

	/**
	 * Checks whether or not a user is logged in, and whether or not
	 * they are authorized to access the module they are requesting.
	 *
	 * @param account_type(string) Ex. influencer, campaigner, admin, sadmin
	 */
	protected function checkAuth($account_type = null)
	{
		$this->load->library('session');

		/* Get auth variable from session */
		$auth = $this->session->userdata('auth');

		/* Check if user is logged in */
		if (isset($auth) &&  $auth === true) {
			/* Get account type from session */
			$account_type_check = $this->session->userdata('account_type');

			/* Return true if user is authenticated, but no account type is being validated */
			if ($account_type === null)
				return true;

			/* Validate account type */
			if ($account_type === $this->config->item('accounts')[$account_type_check]['name'])
				return true;
			else
				return false;
		} else {
			/* User is not authenticated. Destroy temporary session created during check */
			// $this->session->sess_destroy();

			return false;
		}
	}

	/**
	 * Sets the active menu item. initSmarty must be called first.
	 *
	 * @param $activteItem
	 */
	protected function setActiveMenuItem($activteItem) {
		$this->smarty->assign('activeMenuItem', $activteItem);
	}

	function forceSSL() {
		if (strpos(base_url(), 'local.') === false && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")) {
			$url = "https://". $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
			redirect($url);
			exit;
		}
	}
}
