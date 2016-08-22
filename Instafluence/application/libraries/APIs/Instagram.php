<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Used for interacting with the Instagram API
 * 
 * @author Jason Maurer <jason@builtbyhq.com>
 */

require_once(APPPATH.'third_party/Unirest/Unirest.php');

class Instagram
{

	/** Instagram App ID */
	protected $client_id;

	/** Instagram App Secret */
	protected $client_secret;

	/** Instagram App Redirect URI */
	protected $redirect_uri;

	/** Instagram User ID */
	protected $user_id;

	/** Instagram Access Token */
	protected $access_token;
	
	public static $ERR_INSTAGRAM_BAD_USERNAME = -100;

	public function __construct()
	{
		/* Get CodeIgniter Instance */
		$ci =& get_instance();

		/* Set URI to redirect to after OAuth authentication */
		$this->redirect_uri = $ci->config->item('base_url').'auth/instagram/';

		/* Set Instagram Client ID/Secret based on current environment
			(For the purpose of setting a correct redirect URI) */
		switch ($ci->config->item('env')) {
			// TODO: Move bash to production before committing 
			case 'local':
				$this->client_id = 'e21e8d8c1c2a42489bed2b65ed3f0487';
				$this->client_secret = '05184dbb6eca48d2bffa8effa5a337e4';
				break;
			case 'dev':
				$this->client_id = 'e2e39420e06d4073876564fe7fcdaa6f';
				$this->client_secret = '278c92e6774d425f9fe03be645cad1c4';
				break;
			case 'bash':
			case 'prod':
				$this->client_id = 'b56b051dd92c425489c5ee6ab6aee4f8';
				$this->client_secret = 'f370972efd2c4f7281f41158948e6649';
				break;
		}
	}

	/**
	 * Connect to the Instagram API with OAuth, and receive
	 * an access token. Also gets the user's ID
	 */
	public function connect()
	{
		/* Initial OAuth connect */
		if (!isset($_GET['code'])) {
			$url = 'https://api.instagram.com/oauth/authorize/?client_id='
				. $this->client_id
				. '&redirect_uri='
				. $this->redirect_uri
				. '&response_type=code';
				
			redirect($url);
		}

		/* Send POST request */
		if (ENVIRONMENT == 'development') {
			Unirest::verifyPeer(false);
		}
		$auth = Unirest::post(
			'https://api.instagram.com/oauth/access_token',
			array('Accept' => 'application/json'),
			array(
				'client_id' => $this->client_id,
				'client_secret' => $this->client_secret,
				'grant_type' => 'authorization_code',
				'redirect_uri' => $this->redirect_uri,
				'code' => $_GET['code']
			)
		);

		/* Set variables so they can be accessed throughout object */
		$this->access_token = $auth->body->access_token;
		
		// TODO: User ID is requried for getProfile so we need to check if this 
		//       is set and if not don't error instead of adding to the db
		$this->user_id = $auth->body->user->id;
	}

	/**
	 * Gets Instagram profile from user ID and access token
	 * received when we connected to the API above
	 */
	public function getProfile($userId = null, $accessToken = null)
	{
		if ($accessToken == null) {
			$accessToken = $this->access_token;
		}
		
		if ($userId == null) {
			$userId = $this->user_id;
		}
		
		if (empty($userId)) {
			throw new Exception('Missing user id.');
		}

		$user = array();

		/* Send GET request */
		if (ENVIRONMENT == 'development') {
			Unirest::verifyPeer(false);
		}
		$profile = Unirest::get(
			'https://api.instagram.com/v1/users/' . $userId,
			null,
			array(
				'access_token' => $accessToken
			)
		);
		
		$respCode = $profile->__get('code');
		if ($respCode != 200) {
			
			if ($respCode == 404) {
				throw new Exception('Page not found.');
			}
			elseif ($respCode == 429) {
				throw new Exception("Rate limit exceeded.");
			}
			if (isset($profile->__get('body')->meta->error_message)) {
				throw new Exception($profile->__get('body')->meta->error_message);
			}
			else {
				throw new Exception('Unknown error.');
			}
		}

		$user['profile'] = $profile->body;
		$user['access_token'] = $accessToken;
		
		/* Return profile info */
		return $user;
	}

	public function getProfileForDB($userId = null, $accessToken = null) 
	{
		$user = $this->getProfile($userId, $accessToken);

		if (!isset($user['profile']->data)) {
			throw new Exception('Profile data not found.');
		}
		
		$data = array(
			'id' => $user['profile']->data->id,
			'username' => $user['profile']->data->username,
			'followers' => $user['profile']->data->counts->followed_by,
			'following' => $user['profile']->data->counts->follows,
			'posts' => $user['profile']->data->counts->media,
			'access_token' => $user['access_token'],
			'profile' => $user['profile']->data,
			'last_updated' => new MongoDate(),
			'connected' => true
		);
		
		return $data;
	}
	
	public function getPublicProfileForDB($id) {
		$profile = $this->getPublicProfile($id);
		if ($profile == null) {
			return null;
		}

		$data = array(
			'id' => $profile->id,
			'username' => $profile->username,
			'followers' => $profile->counts->followed_by,
			'following' => $profile->counts->follows,
			'posts' => $profile->counts->media,
			'picture' => $profile->profile_picture,
			'url' => "http://instagram.com/{$profile->username}",
			'profile' => $profile,
			'last_updated' => new MongoDate(),
			'connected' => true
		);

		return $data;
	}
	
	public function getPublicProfile($id) 
	{
		if (ENVIRONMENT == 'development') {
			Unirest::verifyPeer(false);
		}
		$response = Unirest::get(
			"https://api.instagram.com/v1/users/$id/",
			null,
			array(
				'client_id' => $this->client_id
			)
		);

		$respCode = $response->__get('code');
		if ($respCode != 200) {
			if ($respCode == 404) {
				throw new Exception('Page not found.');
			}
			elseif ($respCode == 429) {
				throw new Exception("Rate limit exceeded.");
			}
			if (isset($response->__get('body')->meta->error_message)) {
				throw new Exception($response->__get('body')->meta->error_message, $respCode);
			}
			else {
				throw new Exception('Unknown error.');
			}
		}
		
		$user = $response->body->data;
		
		return $user;
	}
	
	public function getId($username) {
		// Get user id from username
		if (ENVIRONMENT == 'development') {
			Unirest::verifyPeer(false);
		}
		$response = Unirest::get(
			'https://api.instagram.com/v1/users/search/',
			null,
			array(
				'q' => $username,
				'client_id' => $this->client_id
			)
		);

		$respCode = $response->__get('code');
		if ($respCode != 200) {
			if ($respCode == 404) {
				throw new Exception('Page not found.');
			}
			elseif ($respCode == 429) {
				throw new Exception("Rate limit exceeded.");
			}
			if (isset($response->__get('body')->error_message)) {
				throw new Exception($response->__get('body')->error_message);
			}
			else {
				throw new Exception('Unknown error.');
			}
		}

		$id = null;
		$search = $response->body->data;
		foreach ($search as $find) {
			// Look for an exact match
			if (strtolower($find->username) == strtolower($username)) {
				$id = $find->id;
				break;
			}
		}
		
		if ($id == null) {
			throw new Exception("Could not find Instagram user $username", self::$ERR_INSTAGRAM_BAD_USERNAME);
		}
		
		return $id;
	}
}