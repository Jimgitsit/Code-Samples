<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Used for interacting with the Vine API
 * 
 * @author Jason Maurer <jason@builtbyhq.com>
 */

require_once(APPPATH.'third_party/Unirest/Unirest.php');

class Vine
{

	protected $ci;
	protected $userId;
	protected $key;

	// Connect must always be called first for Vine
	public function connect($creds)
	{
		// Clean input data
		foreach ($creds as &$value) {
			$value = htmlentities($value);
		}

		// No SSL for dev
		if (ENVIRONMENT == 'development') {
			Unirest::verifyPeer(false);
		}

		// Send POST request with credentials
		$result = Unirest::post('https://api.vineapp.com/users/authenticate',
			array('Accept' => 'application/json'),
			$creds
		);
		// var_dump($result);

		if ($result->body->success == false) {
			// Authenticate failed
			return false;
		}

		$this->userId = $result->body->data->userId;
		$this->key = $result->body->data->key;

		return true;
	}

	public function getProfile()
	{
		return $this->request('https://api.vineapp.com/users/profiles/'.$this->userId);
	}
	
	public function getProfileForDB($creds) 
	{
		$this->ci =& get_instance();
		
		$user = $this->getProfile();

		$data = array(
			'id' => (string)$user->data->userId,
			'user' => $user->data->username,
			'username' => $creds['username'],
			'password' => $this->ci->encrypt->encode($creds['password']),
			'followers' => $user->data->followerCount,
			'following' => $user->data->followingCount,
			'posts' => $user->data->authoredPostCount,
			'profile' => $user->data,
			'last_updated' => new MongoDate(),
			'connected' => true
		);
		
		return $data;
	}

	public function getPublicProfile($id)
	{
		$profile = $this->request("https://api.vineapp.com/users/profiles/$id");

		if ($profile->success == false) {
			// Try vanity url
			$profile = $this->request("https://api.vineapp.com/users/profiles/vanity/$id");
		}
		
		if ($profile->success == false) {
			return null;
		}
		
		return $profile->data;
	}

	public function getPublicProfileForDB($id) {
		$profile = $this->getPublicProfile($id);
		if ($profile == null) {
			return null;
		}

		$data = array(
			'id' => $profile->userId,
			'username' => $this->getUsernameFromUrl($profile->shareUrl),
			'followers' => $profile->followerCount,
			'following' => $profile->followingCount,
			'posts' => $profile->postCount,
			'picture' => $profile->avatarUrl,
			'url' => $profile->shareUrl,
			'profile' => $profile,
			'last_updated' => new MongoDate(),
			'connected' => true
		);

		return $data;
	}

	private function getUsernameFromUrl($url) {
		if (strpos($url, '?') != false) {
			$url = substr($url, 0, strpos($url, '?'));
		}

		if (substr($url, -1) == '/') {
			$url = substr($url, 0, -1);
		}

		$username = $url;

		if (strstr($url, '/') != false) {
			$username = substr($url, strrpos($url, '/') + 1);
		}

		return $username;
	}
	
	public function getId($url) {
		$id = $this->getUsernameFromUrl($url);
		
		return $id;
	}

	public function getTimeline()
	{
		return $this->request('https://api.vineapp.com/timelines/users/'.$this->userId);
	}
	
	private function request($url) 
	{
		Unirest::defaultHeader('user-agent', 'com.vine.iphone/1.0.3 (unknown, iPhone OS 6.1.0, iPhone, Scale/2.000000)');
		Unirest::defaultHeader('accept-language', 'en');
		Unirest::defaultHeader('vine-session-id', $this->key);

		if (ENVIRONMENT == 'development') {
			Unirest::verifyPeer(false);
		}
		
		$result = Unirest::get($url);
		return $result->body;
	}
}