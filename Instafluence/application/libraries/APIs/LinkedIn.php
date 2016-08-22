<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Used for interacting with the LinkedIn API
 * 
 * @author Jason Maurer <jason@builtbyhq.com>
 */

require_once(APPPATH.'third_party/Unirest/Unirest.php');

class LinkedIn
{

	//protected $api_key = '75z1m8i8udjk9k';
	//protected $secret_key = 'glR5QjOt6j1QB1cY';
	protected static $api_key = '75k9qelih28iag';
	protected static $secret_key = 'uhRMcgdg7GLKErCZ';

	protected $redirect_uri;
	protected $access_token;

	public function __construct()
	{
		/* Get CodeIgniter Instance */
		$ci =& get_instance();

		$this->redirect_uri = urlencode($ci->config->item('base_url').'auth/linkedin/');
	}

	/** 
	 * Connect to LinkedIn API via OAuth, and set token creds
	 * in the session.
	 */
	public function connect()
	{
		if (!isset($_GET['code'])) {
			$url = 'https://www.linkedin.com/uas/oauth2/authorization?response_type=code&client_id='
				. $this->api_key
				. '&scope=r_fullprofile%20r_emailaddress%20r_network&state='
				. mt_rand()
				. '&redirect_uri='
				. $this->redirect_uri;
			
			redirect($url);
			return;
		}

		$url_step2 = 'https://www.linkedin.com/uas/oauth2/accessToken';
		$parans = array(
			'grant_type' => 'authorization_code',
			'code' => $_GET['code'],
			'redirect_uri' => $this->redirect_uri,
			'client_id' => $this->api_key,
			'client_secret' => $this->secret_key
		);

		// TODO: This is broken! Getting a 400 error response. API broken or what?
		Unirest::verifyPeer(false);
		$result = Unirest::get($url_step2, array(), $parans);
		
		
		//$ch = curl_init($url_step2);
		//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//$result = curl_exec($ch);
		//curl_close($ch);

		$result = json_decode($result);
		
		if ($result != false && isset($result->access_token)) {
			$this->access_token = $result->access_token;
		}
	}

	public function getProfile($accessToken = null)
	{
		if ($accessToken == null) {
			$accessToken = $this->access_token;
		}

		$fields = '(id,first-name,last-name,num-connections,industry,interests,languages,date-of-birth,following,network,public-profile-url)?scope=self';

		$result = Unirest::get('https://api.linkedin.com/v1/people/~:'.$fields,
			null,
			array(
				'format' => 'json',
				'oauth2_access_token' => $accessToken
			)
		);
		
		$code = $result->__get('code');
		$body = $result->__get('body');
		if ($code != 200) {
			throw new Exception('LinkedIn: ' . var_export($body, true));
		}
		
		$profile = $body;
		$profile->url = substr(strrchr($profile->publicProfileUrl, '/'), 1);
		$profile->access_token = $accessToken;
		
		return $profile;
	}

	public function getProfileForDB($accessToken = null) {
		// TODO: This refuses to work!
		$profile = $this->linkedin->getProfile($accessToken);

		// Update data
		$data = array(
			'id' => $profile->id,
			'username' => 'blah', // TODO
			'url' => $profile->url,
			'followers' => $profile->numConnections,
			'following' => $profile->numConnections,
			'posts' => $profile->network->updates->_total,
			'industry' => $profile->industry,
			'access_token' => $profile->access_token,
			'profile' => $profile,
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