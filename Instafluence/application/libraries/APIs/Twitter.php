<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Used for interacting with the Twitter API
 * 
 * @author Jason Maurer <jason@builtbyhq.com>
 */

require_once(APPPATH.'third_party/APIs/Twitter/twitteroauth/twitteroauth.php');
require_once(APPPATH.'third_party/Unirest/Unirest.php');

class Twitter
{
	private $bearerToken = null;

	public function __construct()
	{
		// Get CodeIgniter instance
		$ci =& get_instance();

		// Define environment variables
		define('TWITTER_CONSUMER_KEY', 'uYdMK1qllSoHiKOyKlcoXhJT6');
		define('TWITTER_CONSUMER_SECRET', 'fxl2Dyw11RjPjKQInZUKowDx9Lx2kuqxyA3wXigQVz0WUXLuzz');
		
		define('TWITTER_OAUTH_CALLBACK', $ci->config->item('base_url').'auth/twitter/');
	}

	/** 
	 * Connect to Twitter API via OAuth, and set token creds
	 * in the session.
	 */
	public function connect()
	{
		if (isset($_GET['denied'])) {
			// User canceled auth
			return false;
		}
		
		session_start();

		/* Build TwitterOAuth object with client credentials. */
		$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
		/* Get temporary credentials. */
		$requestToken = $connection->getRequestToken(TWITTER_OAUTH_CALLBACK);

		/* Save temporary credentials to session. */
		$_SESSION['oauth_token'] = $token = $requestToken['oauth_token'];
		$_SESSION['oauth_token_secret'] = $requestToken['oauth_token_secret'];
		 
		/* If last connection failed don't display authorization link */
		switch ($connection->http_code) {
			case 200: {
				/* Build authorize URL and redirect user to Twitter */
				$url = $connection->getAuthorizeURL($token);
				redirect($url); 
				break;
			}
			default: {
				// Show notification if something went wrong
				//echo 'Could not connect to Twitter. Refresh the page or try again later.';
				//throw new Exception('Twitter: ' . var_export($requestToken, true));
				return false;
			}
		}
		
		return true;
	}
	
	public function getProfile($tokens = null)
	{
		$user = array();

		if ($tokens == null) {
			session_start();
			$tokens = $_SESSION;

			/* Connect with temporary tokens */
			$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $tokens['oauth_token'], $tokens['oauth_token_secret']);
			
			/* Get access token */
			$user['tokens'] = $connection->getAccessToken($_REQUEST['oauth_verifier']);
		} 
		else {
			$user['tokens'] = $tokens;
		}

		/* Connect with access token */
		$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $user['tokens']['oauth_token'], $user['tokens']['oauth_token_secret']);

		/* Get public profile info */
		$user['profile'] = $connection->get('account/verify_credentials');
		
		if (isset($user['profile']->errors) && count($user['profile']->errors) > 0) {
			throw new Exception($user['profile']->errors[0]->message);
		}

		/* Get user's current Twitter feed */
		$user['feed'] = $connection->get('statuses/home_timeline');

		if (isset($user['feed']->errors) && count($user['feed']->errors) > 0) {
			throw new Exception($user['feed']->errors[0]->message);
		}

		return $user;
	}
	
	public function getPublicProfile($username) 
	{
		$token = $this->getBearerToken();

		if (ENVIRONMENT == 'development') {
			Unirest::verifyPeer(false);
		}
		
		$result = Unirest::get(
			'https://api.twitter.com/1.1/users/show.json',
			array('Authorization' => "Bearer $token"),
			array('screen_name' => $username)
		);
		
		if ($result->code == 429) {
			throw new Exception("Rate limit exceeded.", $result->code);
		}

		if ($result->code != 200) {
			throw new Exception("Error getting public profile for '$username'.", $result->code);
		}
		
		return json_decode($result->raw_body, true);
	}

	public function getProfileForDB($tokens) 
	{
		$profile = $this->getProfile($tokens);

		// Update data
		$data = array(
			'id' => $profile['profile']->id_str,
			'username' => $profile['profile']->screen_name,
			'followers' => $profile['profile']->followers_count,
			'following' => $profile['profile']->friends_count,
			'posts' => $profile['profile']->statuses_count,
			'oauth_token' => $profile['tokens']['oauth_token'],
			'oauth_token_secret' => $profile['tokens']['oauth_token_secret'],
			'profile' => $profile['profile'],
			'last_updated' => new MongoDate(),
			'connected' => true
		);
		
		return $data;
	}

	public function getId($url) 
	{
		return Social_public_model::getUsernameFromUrl($url);
	}

	public function getPublicProfileForDB($id) 
	{
		$profile = $this->getPublicProfile($id);

		$data = array(
			'id' => $profile['id'],
			'username' => $id,
			'followers' => $profile['followers_count'],
			'following' => 0,
			'posts' => $profile['statuses_count'],
			'url' => "https://twitter.com/$id",
			'picture' => $profile['profile_image_url'],
			'profile' => $profile,
			'last_updated' => new MongoDate(),
			'connected' => true
		);

		return $data;
	}

	/**
	 * Gets a bearer token from Twitter for application-only api calls.
	 * 
	 * @return mixed
	 * @throws Exception
	 */
	private function getBearerToken()
	{
		if ($this->bearerToken != null) {
			return $this->bearerToken;
		}
		
		if (ENVIRONMENT == 'development') {
			Unirest::verifyPeer(false);
		}

		$encodedConsumerKey = urlencode(TWITTER_CONSUMER_KEY);
		$encodedConsumerSecret = urlencode(TWITTER_CONSUMER_SECRET);
		$basicAuth = base64_encode($encodedConsumerKey . ':' . $encodedConsumerSecret);
		

		$result = Unirest::post('https://twitter.com/oauth2/token',
			array(
				'Host' => 'api.twitter.com',
				'User-Agent' => 'My Twitter App v1.0.23',
				'Authorization' => "Basic $basicAuth",
				'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8',
				'Content-Length' => '29',
				'Accept-Encoding' => 'gzip'
			),
			'grant_type=client_credentials'
		);
		
		if ($result->code != 200) {
			throw new Exception('Could not obtain bearer token from Twitter.');
		}
		
		$this->bearerToken = $result->body->access_token;
		return $this->bearerToken;
	}
}