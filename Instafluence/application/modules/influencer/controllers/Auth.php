<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Auth Controller used as a central location for API authorizations.
 * Initially called to begin the authorization processes, but also as
 * the callbacks used in the API app settings.
 *
 * @author Jason Maurer <jason@builtbyhq.com>
 */

require_once('BaseInfluencerController.php');

class Auth extends BaseInfluencerController
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('social_model');
		$this->social_model->setUser($this->user['email']);
	}

	// TODO: Need to catch exceptions in all of the functions below

	public function facebook()
	{
		$this->load->library('APIs/Facebook');

		if (!$this->facebook->connect()) {
			redirect('/profile/');
		}

		// Get Facebook profile info
		$data = $this->facebook->getProfileForDB();

		$newAccountIndex = $this->social_model->addOrUpdateAccount($data, 'facebook');

		// Redirect when process is complete
		//if (isset($data['accounts']) && count($data['accounts']) > 0) {
			// Show the page/account selection modal
			redirect('/profile/?fbm=1&ai=' . $newAccountIndex);
		//}
		//else {
		//	redirect('/profile/');
		//}
	}

	public function setFacebookPages() {
		$this->load->library('APIs/Facebook');
		$pages = $this->input->post('pages');
		$accountIndex = $this->input->post('accountIndex');

		$this->social_model->setUser($this->user['email']);

		if (is_array($pages)) {
			// Get the user's social account
			$fb = $this->social_model->getNetwork('facebook');

			foreach ($pages as $pageIndex) {
				if ($pageIndex == "-1") {
					$fb[$accountIndex]['include'] = true;
					$this->social_model->update($fb[$accountIndex], 'facebook', $accountIndex);
				}
				elseif (isset($fb[$accountIndex]['accounts'][$pageIndex])) {
					// Get the access token from the facebook account
					$token = $fb[$accountIndex]['accounts'][$pageIndex]['access_token'];

					// Get the page likes etc. and store in db
					$profile = $this->facebook->getPageProfile($fb[$accountIndex]['accounts'][$pageIndex]['id'], $token);

					$this->social_model->updateFacebookPage($profile, $accountIndex, $pageIndex);
				}
			}
		}
	}

	public function twitter()
	{
		$this->load->library('APIs/Twitter');

		// Don't try to connect again if user has already authorized us
		if (!isset($_GET['oauth_token'])) {
			if ($this->twitter->connect() == false) {
				// Connection error or user canceled.
				redirect('/profile/');
			}
		}

		$data = $this->twitter->getProfileForDB();

		$this->social_model->addOrUpdateAccount($data, 'twitter');

		// Redirect when process is complete
		if (isset($_GET['oauth_token'])) {
			redirect('/profile/');
		}
	}

	public function instagram()
	{
		$this->load->library('APIs/Instagram');

		// Authorize
		$this->instagram->connect();

		$data = $this->instagram->getProfileForDB();

		$this->social_model->addOrUpdateAccount($data, 'instagram');

		/* Redirect when process is complete */
		if (isset($_GET['code'])) {
			redirect('/profile/');
		}
	}

	public function vine()
	{
		$response = array("success" => true, "msg" => "");

		$this->load->library('APIs/Vine');

		$creds = $this->input->post();

		// Connect to Vine API
		$connect = $this->vine->connect($creds);
		if ($connect !== true) {
			$response['success'] = false;
			$response['msg'] = $connect;
			echo(json_encode($response));
			exit();
		}

		// Get Vine user info
		$data = $this->vine->getProfileForDB($creds);

		$this->social_model->addOrUpdateAccount($data, 'vine');

		echo(json_encode($response));
		exit();
	}

	public function youtube()
	{
		$this->load->library('APIs/YouTube');
		$this->youtube->connect();

		$data = $this->youtube->getProfileForDB();

		$this->social_model->addOrUpdateAccount($data, 'youtube');

		// Redirect when process is complete
		if (isset($_GET['code'])) {
			redirect('/profile/');
		}
	}

	public function googleplus()
	{
		$this->load->library('APIs/GooglePlus');
		$this->googleplus->connect();

		$data = $this->googleplus->getProfileForDB();

		$this->social_model->addOrUpdateAccount($data, 'googleplus');

		// Redirect when process is complete
		if (isset($_GET['code'])) {
			redirect('/profile/');
		}
	}

	public function linkedin()
	{
		try {
			$this->load->library('APIs/LinkedIn');

			// Authorize
			$this->linkedin->connect();

			// Get LinkedIn profile info
			$data = $this->linkedin->getProfileForDB();

			$this->social_model->addOrUpdateAccount($data, 'linkedin');

			// Redirect when process is complete
			if (isset($_GET['code'])) {
				redirect('/profile/');
			}
		}
		catch(Exception $e) {
			// TODO: ?
			redirect('/profile/');
		}
	}

	public function tumblr()
	{
		$this->load->library('APIs/Tumblr');

		// TODO: This need revising/fixed
		// Get access token from database, or request one if needed
		$token = $this->social_model->getTumblrToken($this->user['email']);
		if (!$token) {
			$token = $this->tumblr->connect();
		}

		$data = $this->tumblr->getProfileForDB();

		$this->social_model->addOrUpdateAccount($data, 'tumblr');

		redirect('/profile/');
	}

	public function wordpress()
	{
		$this->load->library('APIs/Wordpress');

		// Get access token from database, or request one if needed
		$token = $this->social_model->getWordpressAccessToken($this->user['email']);
		if (!$this->user['connected'] || !$token) {
			$token = $this->wordpress->connect();
		}
		else {
			$token = json_decode($token);
		}

		// Get Wordpress site info
		$data = $this->wordpress->getSiteForDB($token->access_token, $token->blog_id);

		$this->social_model->addOrUpdateAccount($data, 'wordpress');

		redirect('/profile/');
	}

	public function foursquare()
	{
		$this->load->library('APIs/Foursquare');

		// Authorize
		$this->foursquare->connect();

		$data = $this->foursquare->getProfileForDB();

		$this->social_model->addOrUpdateAccount($data, 'foursquare');

		redirect('/profile/');
	}

	public function pinterest() {
		$response = array('success' => true);

		try {
			$this->load->library('APIs/Pinterest');

			$username = trim($this->input->post('username'));

			$profile = $this->pinterest->getProfileForDB($username);

			$this->social_model->addOrUpdateAccount($profile, 'pinterest');
		}
		catch(Exception $e) {
			$response['success'] = false;
			$response['msg'] = $e->getMessage();
		}

		echo(json_encode($response));
	}

	// public function snapchat()
	// {
	// 	$data = $this->input->post();
	// 	$data['connected'] = true;
	//
	// 	$this->social_model->addOrUpdateAccount($data, 'snapchat');
	//
	// 	echo json_encode(array('success'=>true));
	// }

	/*
	public function setPinterestBoards() {
		$this->load->library('APIs/Facebook');
		$boards = $this->input->post('pages');
		$accountIndex = $this->input->post('accountIndex');

		$this->social_model->setUser($this->user['email']);

		if (is_array($boards)) {
			foreach ($boards as $board) {
				$boardInfo = $this->pinterest->getBoardInfo($board['url']);
			}
		}


	}
	*/
}
