<?php

/**
 * Used for interacting with the Tumblr API
 * 
 * @author Jason Maurer <jason@builtbyhq.com>
 */

require_once(APPPATH.'third_party/APIs/Tumblr/lib/tumblrPHP.php');

class Tumblr
{

	protected $consumer_key;
	protected $consumer_secret;

	public function __construct()
	{
		$ci =& get_instance();

		switch ($ci->config->item('env')) {
			case 'local': /* Running on local.instafluence.com */
			case 'bash':
				$this->consumer_key = '3Gc1PNufxMWkpVdZb83CfbuYfE4eSJ0lOddM2M4qpexgJTZ8S9';
				$this->consumer_secret = 'x9ECtWqUJ5HDcLygfrE5ZxvI0ksLJuUVThFPCihEYV9KgmFUyy';
				break;
			case 'dev': /* Running on dev.instafluence.com */
				$this->consumer_key = '0gSXjEKfWKDxdJBOFjt3AgXjiwydae0Jg9vOOjPjVfp4I0akFs';
				$this->consumer_secret = 'sbt9GKyXowz7IXaTbh2i2s7lBMRMBmAZDjgbex9alC2cXhTpaP';
				break;
			case 'prod': /* Running on instafluence.com */
				$this->consumer_key = 'PWcsaIrPyx3ZEBHkKwYZK4gvzfdpfpplveClQogUIVQuHitQcf';
				$this->consumer_secret = 'JEi1AbgA2TJYKrDrBNTUKW0lxpAn1nagLrPFur7GPd7WGdt12R';
				break;
		}
	}

	/** 
	 * Connect to Tumblr API via OAuth, and set token creds
	 * in the session.
	 */
	public function connect()
	{
		session_start();

		if (!isset($_GET['oauth_token'])) {
			$tumblr = new TumblrAPI($this->consumer_key, $this->consumer_secret);
			$token = $tumblr->getRequestToken();

			$_SESSION['oauth_token'] = $token['oauth_token'];
			$_SESSION['oauth_token_secret'] = $token['oauth_token_secret'];

			$url = $tumblr->getAuthorizeURL($token['oauth_token']);
			redirect($url);
		}

		$tumblr = new TumblrAPI($this->consumer_key, $this->consumer_secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

		$oauth_verifier = $_GET['oauth_verifier'];
		$token = $tumblr->getAccessToken($oauth_verifier);

		session_destroy();

		return $token;
	}

	public function getProfile($token)
	{
		$tumblr = new TumblrAPI($this->consumer_key, $this->consumer_secret, $token['oauth_token'], $token['oauth_token_secret']);

		// TODO: THIS IS BROKEN! GETTING 401 NOT AUTHORIZED
		$profile = $tumblr->oauth_get('/user/info');

		return $profile->response;
	}

	public function getProfileForDB($token) 
	{
		$profile = $this->tumblr->getProfile($token);

		// Add followers for all blogs to total followers
		$followers = 0;
		foreach ($profile->user->blogs as $blog) {
			$followers += $blog->followers;
		}

		// Add posts for all blogs to total posts
		$posts = 0;
		foreach ($profile->user->blogs as $blog) {
			$posts += $blog->posts;
		}

		// Update data
		$data = array(
			'username' => $profile->user->name,
			'followers' => $followers,
			'following' => $profile->user->following,
			'posts' => $posts,
			'blogs' => count($profile->user->blogs),
			'oauth_token' => $token['oauth_token'],
			'oauth_token_secret' => $token['oauth_token_secret'],
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