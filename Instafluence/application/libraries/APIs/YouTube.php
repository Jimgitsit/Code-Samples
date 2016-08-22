<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Used for interacting with Google's YouTube API
 * 
 * @author Jason Maurer <jason@builtbyhq.com>, Jim McGowen <jim@builtbyhq.com>
 */

set_include_path(APPPATH.'third_party/APIs/Google/src');

require_once(APPPATH.'third_party/APIs/Google/src/Google/Client.php');
require_once(APPPATH.'third_party/APIs/Google/src/Google/Service/YouTube.php');
require_once(APPPATH.'third_party/APIs/Google/src/Google/Service/Oauth2.php');

class YouTube
{
	protected $client_id = '987970793731-rcacfqrrnpa71127ubmt5ltmepdq03kp.apps.googleusercontent.com';
	protected $client_secret = '6CYoqRyj4c8yapVlXgzNsP0o';

	protected $youtube;
	protected $client;
	protected $oauth;
	
	protected $accessToken;

	public function __construct()
	{
		$this->client = new Google_Client();
		$this->client->setClientId($this->client_id);
		$this->client->setClientSecret($this->client_secret);
		$this->client->setScopes(array(
			Google_Service_Oauth2::PLUS_LOGIN,
			Google_Service_Oauth2::PLUS_ME,
			Google_Service_Oauth2::USERINFO_EMAIL,
			Google_Service_Oauth2::USERINFO_PROFILE,
			Google_Service_YouTube::YOUTUBE
		));

		$ci =& get_instance();
		$this->client->setRedirectUri($ci->config->item('base_url').'auth/youtube/');

		$this->oauth = new Google_Service_Oauth2($this->client);

		$this->youtube = new Google_Service_YouTube($this->client);
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
		//$userInfo = $userInfo->toSimpleObject();

		$userInfo->accessToken = $this->accessToken;

		return $userInfo;
	}
	
	public function getProfileForDB($token = null) {
		$user = $this->getProfile($token);

		$channels = $this->getChannels();

		$subscriberCount = 0;
		foreach ($channels as $channel) {
			$subscriberCount += $channel['stats']->subscriberCount;
		}

		// Update data
		$data = array(
			'followers' => $subscriberCount,
			'id' => $user->getId(),
			'username' => $user->getEmail(),
			'picture' => $user->getPicture(),
			'first_name' => $user->getGivenName(),
			'last_name' => $user->getFamilyName(),
			'locale' => $user->getLocale(),
			'gender' => $user->getGender(),
			'link' => $user->getLink(),
			'access_token' => $user->accessToken,
			'channels' => $channels,
			'last_updated' => new MongoDate(),
			'connected' => true
		);
		
		return $data;
	}

	private function getChannels() {
		$cleanChannels = array();
		
		// TODO: Seems as though we only ever get one channel from this. The oauth login asks for an account THEN a channel.
		$response = $this->youtube->channels->listChannels('brandingSettings,contentDetails,contentOwnerDetails,id,invideoPromotion,snippet,statistics,status,topicDetails', array('mine' => true));
		$channels = $response->getItems();
		
		foreach ($channels as $channel) {
			$title = '';
			$brandingSettings = $channel->getBrandingSettings();
			if ($brandingSettings !== null) {
				$title = $brandingSettings->getChannel()->getTitle();
			}
			else {
				$snippet = $channel->getSnippet();
				if ($snippet !== null) {
					$title = $snippet->getTitle();
				}
			}
			
			if (trim($title) == '') {
				$title = 'Default';
			}
			
			$cleanChannels[] = array(
				'id' => $channel->getId(),
				'title' => $title,
				'stats' => $channel->getStatistics()->toSimpleObject(),
				'status' => $channel->getStatus()->toSimpleObject(),
				'last_updated' => new MongoDate()
			);
		}

		return $cleanChannels;
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
		
		/*
		if ($token != null) {
			$this->client->setAccessToken(json_encode($token));

			if ($this->client->isAccessTokenExpired()) {
				$this->client->refreshToken($token->refresh_token);
				$token = json_decode($this->client->getAccessToken());
			}

			return $token;
		}
		*/
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