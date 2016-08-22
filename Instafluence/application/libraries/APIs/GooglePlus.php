<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Used for interacting with Google's G+ API
 * 
 * @author Jason Maurer <jason@builtbyhq.com>, Jim McGowen <jim@builtbyhq.com>
 */

set_include_path(APPPATH.'third_party/APIs/Google/src');

require_once(APPPATH.'third_party/APIs/Google/src/Google/Client.php');
require_once(APPPATH.'third_party/APIs/Google/src/Google/Service/Plus.php');
require_once(APPPATH.'third_party/APIs/Google/src/Google/Service/Oauth2.php');

class GooglePlus
{
	protected $client_id = '987970793731-rcacfqrrnpa71127ubmt5ltmepdq03kp.apps.googleusercontent.com';
	protected $client_secret = '6CYoqRyj4c8yapVlXgzNsP0o';

	protected $plus;
	protected $client;
	protected $oauth;

	protected $accessToken;

	public function __construct()
	{
		$this->client = new Google_Client();
		$this->client->setClientId($this->client_id);
		$this->client->setClientSecret($this->client_secret);
		$this->client->setScopes(
			Google_Service_Oauth2::PLUS_LOGIN,
			Google_Service_Oauth2::PLUS_ME,
			Google_Service_Oauth2::USERINFO_EMAIL,
			Google_Service_Oauth2::USERINFO_PROFILE
		);
		
		$ci =& get_instance();
		
		$this->client->setRedirectUri($ci->config->item('base_url').'auth/googleplus/');

		$this->oauth = new Google_Service_Oauth2($this->client);
		
		$this->plus = new Google_Service_Plus($this->client);
	}

	public function connect()
	{
		session_start();

		if (isset($_GET['code'])) {
			$this->client->authenticate($_GET['code']);
			$this->accessToken = json_decode($this->client->getAccessToken());
			$this->accessToken = $this->checkAccessToken($this->accessToken);
		}
		else {
			$this->client->setApprovalPrompt('force');
			$this->client->setAccessType('offline');
			$authUrl = $this->client->createAuthUrl();

			redirect($authUrl);
		}
	}

	public function getProfile($token = null)
	{
		if ($token == null) {
			$token = $this->accessToken;
		}
		$this->accessToken = $this->checkAccessToken($token);
		
		$userInfo = $this->oauth->userinfo->get();
		$userInfo = $userInfo->toSimpleObject();

		$person = $this->plus->people->get('me')->toSimpleObject();
		$userInfo->profile = $person;

		$userInfo->accessToken = $this->accessToken;

		return $userInfo;
	}

	public function getProfileForDB($token = null)
	{
		$user = $this->getProfile($token);

		// Update data
		$data = array(
			'id' => $user->id,
			'access_token' => $user->accessToken,
			'profile' => $user->profile,
			'last_updated' => new MongoDate(),
			'connected' => true
		);
		
		// TODO: Not sure under what condition circledByCount isn't set but it happens
		if (isset($user->profile->circledByCount)) {
			$data['followers'] = $user->profile->circledByCount;
		}
		else {
			$data['followers'] = 0;
		}

		// We don't get email from the api for some reason (works fine for youtube so it's a mystery)
		if (isset($user->email)) {
			$data['username'] = $user->email;
		}
		else {
			$data['username'] = $user->id;
		}
		
		return $data;
	}
	
	private function checkAccessToken($token) {
		if ($token != null) {
			$this->client->setAccessToken(json_encode($token));

			if ($this->client->isAccessTokenExpired()) {
				$this->client->setAccessType('offline');
				
				// $token can be either and array (if it came from the db) or an object (if it came from google)
				if (is_array($token)) {
					$this->client->refreshToken($token['refresh_token']);
				}
				else {
					$this->client->refreshToken($token->refresh_token);
				}
				$token = json_decode($this->client->getAccessToken());
			}

			return $token;
		}
	}

	public function getId($url) {
		// TODO: Implement

		return 'unimplemented';
	}

	public function getPublicProfileForDB($id) {
		// TODO: Implement

		$username = Social_public_model::getUsernameFromUrl($id);

		$data = array(
			'id' => '',
			'username' => $username,
			'url' => $id,
			'show_est' => true,
			'last_updated' => new MongoDate(),
			'connected' => true
		);

		return $data;
	}
}