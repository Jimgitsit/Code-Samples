<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Used for interacting with the Facebook API
 * 
 * @author Jason Maurer <jason@builtbyhq.com>
 */

require_once(APPPATH.'third_party/Unirest/Unirest.php');
require_once(APPPATH.'third_party/APIs/Facebook/autoload.php');

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use Facebook\FacebookRedirectLoginHelper;

class Facebook
{

	/** Stores API OAuth connection */
	protected $helper;

	public function __construct()
	{
		/* Get CodeIgniter Instance */
		$ci =& get_instance();

		/* Define environment variables */
		define('FACEBOOK_CONSUMER_KEY', '523462221091224');
		define('FACEBOOK_CONSUMER_SECRET', 'fd103f9505f359b289916131e6b060b5');
		define('FACEBOOK_OAUTH_CALLBACK', $ci->config->item('base_url').'auth/facebook/');

		/* Set application credentials for Facebook app */
		FacebookSession::setDefaultApplication(FACEBOOK_CONSUMER_KEY, FACEBOOK_CONSUMER_SECRET);
	}

	/**
	 * Connects to Facebook API via OAuth. If they have already
	 * authorized the app, it will redirect to the callback url,
	 * and if they haven't, it will ask them to authorize then
	 * redirect to the callback url.
	 */
	public function connect()
	{
		session_start();
		
		if (isset($_GET['error'])) {
			return false;
		}

		if (!isset($_GET['code'])) {
			/* Get permissions to view their profile and news feed */
			$this->helper = new FacebookRedirectLoginHelper(FACEBOOK_OAUTH_CALLBACK);
			//$loginUrl = $this->helper->getLoginUrl(array('manage_pages','public_profile','read_stream','email','user_about_me','user_activities','user_birthday','user_education_history','user_hometown','user_interests','user_likes','user_location','user_relationships','user_religion_politics','user_status','user_website','user_work_history'));
			$loginUrl = $this->helper->getLoginUrl(array('manage_pages','public_profile','email','user_about_me'));

			redirect($loginUrl);
		}
		
		return true;
	}
	
	public function getFacebookSession($token = null) 
	{
		if ($token == null) {
			// Get authorized session (won't work unless app is already authorized)
			$this->helper = new FacebookRedirectLoginHelper(FACEBOOK_OAUTH_CALLBACK);
			$session = $this->helper->getSessionFromRedirect();
		} 
		else {
			$session = new FacebookSession($token);
		}
		
		return $session;
	}

	public function getProfile($token = null)
	{
		$user = array();

		$session = $this->getFacebookSession($token);
		$user['access_token'] = $session->getAccessToken();
		
		// For safety, if we don't have an access token from the api 
		// for whatever reason we don't want to loose the one we have
		if (empty($user['access_token'])) {
			$user['access_token'] = $token;
		}

		// Get user's public profile
		$user['me'] = (new FacebookRequest(
			$session, 'GET', '/me'
		))->execute()->getGraphObject()->asArray();
		
		// Get their picture
		$user['me']['picture'] = (new FacebookRequest(
			$session, 
			'GET', '/me/picture', 
			array(
				'redirect' => false, 
				'type' => 'normal',
				'height' => 100,
				'width' => 100
			)
		))->execute()->getGraphObject()->asArray();

		/*
		$likes = (new FacebookRequest(
			$session, 'GET', '/' . $user['me']['id'] . '/insights/like'
		))->execute()->getGraphObject()->asArray();
		*/
		
		// TODO: fql is deprecated so we need to do this via the graph api only.
		// Get friend count
		$friend_count = (new FacebookRequest(
			$session, 'GET', '/fql?q=SELECT friend_count FROM user WHERE uid = '.$user['me']['id']
		))->execute()->getGraphObject()->asArray();
		$user['me']['friend_count'] = $friend_count[0]->friend_count;

		// Get post count
		$post_count = (new FacebookRequest(
			$session, 'GET', '/fql?q=SELECT time FROM status WHERE uid = '.$user['me']['id']
		))->execute()->getGraphObject()->asArray();
		$user['me']['post_count'] = count($post_count);

		// Get accounts
		$accounts = (new FacebookRequest(
			$session,
			'GET',
			'/me/accounts'
		))->execute()->getGraphObject()->asArray();
		if (isset($accounts['data'])) {
			foreach ($accounts['data'] as &$account) {
				$picture = (new FacebookRequest(
					$session,
					'GET',
					'/' . $account->id . '/picture',
					array (
						'redirect' => false,
						'height' => '50',
						'type' => 'normal',
						'width' => '50',
					)
				))->execute()->getGraphObject()->asArray();
				$account->picture = $picture->url;
			}
			$user['me']['accounts'] = $accounts['data'];
		}
		else {
			$user['me']['accounts'] = array();
		}

		return $user;
	}
	
	public function getProfileForDB($token = null) {
		$profile = $this->getProfile($token);
		
		$data = array(
			'id' => $profile['me']['id'],
			'username' => $profile['me']['email'],
			'followers' => $profile['me']['friend_count'],
			'following' => $profile['me']['friend_count'],
			'posts' => $profile['me']['post_count'],
			'access_token' => (string)$profile['access_token'],
			'accounts' => $profile['me']['accounts'],
			'profile' => $profile['me'],
			'picture' => $profile['me']['picture']->url,
			'last_updated' => new MongoDate(),
			'connected' => true
		);
		
		return $data;
	}

	public function getPageProfile($pageId, $token = null) {
		try {
			$session = $this->getFacebookSession($token);
			
			$request = new FacebookRequest(
				$session,
				'GET',
				'/' . $pageId
			);
			$response = $request->execute();
			$graphObject = $response->getGraphObject();
			$profile = $graphObject->asArray();
			return $profile;
		}
		catch (FacebookRequestException $e) {
			return null;
		}
		catch (\Exception $e) {
			return null;
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