<?php

/**
 * Used for interacting with the Foursquare API
 * 
 * @author Jason Maurer <jason@builtbyhq.com>
 */

require_once(APPPATH.'third_party/Unirest/Unirest.php');

class Foursquare
{

	/** Foursquare Client ID */
	protected $clientId = '4CMCYYFUJZSGHQDWXSPR4WEADVSJEDRLSO3EHAR1DTLSZ11Y';

	/** Foursquare Client Secret */
	protected $clientSecret = 'RBTJAQTACFOCRTSUX540AQV1NISPAASEAZVZG5E0500FPKIN';

	/** Foursquare Redirect URI */
	protected $redirectUri;

	/** Foursquare user access token */
	protected $accessToken;

	public function __construct()
	{
		// Get CodeIgniter Instance
		$ci =& get_instance();

		// Set redirect URI
		$this->redirectUri = $ci->config->item('base_url') . 'auth/foursquare/';
	}

	/**
	 * Connect to the Foursquare API with OAuth, and receive
	 * an access token. Store in object.
	 */
	public function connect()
	{
		$data = array(
			'client_id' => $this->clientId,
			'response_type' => 'code',
			'redirect_uri' => $this->redirectUri
		);

		/* Authorize app */
		$url = 'https://foursquare.com/oauth2/authenticate?'.http_build_query($data);
		if (!isset($_GET['code'])) redirect($url);

		/* Make request for access token */
		if (ENVIRONMENT == 'development') {
			Unirest::verifyPeer(false);
		}
		$request = Unirest::get('https://foursquare.com/oauth2/access_token',
			array('Accept' => 'application/json'),
			array(
				'client_id' => $this->clientId,
				'client_secret' => $this->clientSecret,
				'grant_type' => 'authorization_code',
				'redirect_uri' => $this->redirectUri,
				'code' => $_GET['code']
			)
		);

		$this->accessToken = $request->body->access_token;
	}
	
	public function getProfile($accessToken = null)
	{
		if ($accessToken == null) {
			$accessToken = $this->accessToken;
		}

		if (ENVIRONMENT == 'development') {
			Unirest::verifyPeer(false);
		}
		$profile = Unirest::get('https://api.foursquare.com/v2/users/self',
			null,
			array(
				'oauth_token' => $accessToken,
				'v' => '20140711' /* API Version. Required by Foursquare API for some reason */
			)
		);

		$user['profile'] = $profile->body->response->user;
		$user['access_token'] = $accessToken;

		return $user;
	}

	public function getProfileForDB($accessToken = null) {
		$user = $this->getProfile($accessToken);

		// Update data
		$data = array(
			'id' => $user['profile']->id,
			'username' => $user['profile']->contact->email,
			'followers' => $user['profile']->friends->count,
			'following' => $user['profile']->friends->count,
			'posts' => $user['profile']->checkins->count,
			'access_token' => $user['access_token'],
			'profile' => $user['profile'],
			'last_updated' => new MongoDate(),
			'connected' => true
		);
		
		return $data;
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