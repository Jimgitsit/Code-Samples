<?php

/**
 * Used for interacting with the Wordpress API
 * 
 * @author Jason Maurer <jason@builtbyhq.com>
 */

require_once(APPPATH.'third_party/Unirest/Unirest.php');

class Wordpress
{

	protected $client_id;
	protected $client_secret;
	protected $redirect_uri;
	protected $token;

	public function __construct()
	{
		$ci =& get_instance();

		$this->redirect_uri = $ci->config->item('base_url').'auth/wordpress/';
		
		switch ($ci->config->item('env')) {
			// TODO: Move bash to production before committing 
			case 'local':
				$this->client_id = '36379';
				$this->client_secret = 'v5yPaTKtOyjJcVwfwTWrTaBXS2tiCeRSya5adSbKB1VN5T47PLDZar2cNCoKTcfx';
				break;
			case 'dev':
				$this->client_id = '36378';
				$this->client_secret = 'PFEckUujvTB0LX6oXxGMASh9nDD7p8I6VYtvxHNeKtLNwvcqc59QfJVbAWe9zEzA';
				break;
			case 'bash':
			case 'prod':
				$this->client_id = '36377';
				$this->client_secret = 'PHYvwFZ4T0yP0z2aBUnh65t1gpWd8MWJ4T44Pz7pczcRk1UtAQmwuL9cDmYW2Jmd';
				break;
		}
	}

	/** 
	 * Connect to Wordpress API via OAuth, and set token creds
	 * in the session.
	 */
	public function connect()
	{
		if (!isset($_GET['code'])) {
			$data = array(
				'client_id' => $this->client_id,
				'redirect_uri' => $this->redirect_uri,
				'response_type' => 'code'
			);

			$url = 'https://public-api.wordpress.com/oauth2/authorize?'.http_build_query($data);
			redirect($url);
		}

		if (ENVIRONMENT == 'development') {
			Unirest::verifyPeer(false);
		}
		$token = Unirest::post('https://public-api.wordpress.com/oauth2/token',
			array('Accept' => 'application/json'),
			array(
				'client_id' => $this->client_id,
				'client_secret' => $this->client_secret,
				'redirect_uri' => $this->redirect_uri,
				'code' => $_GET['code'],
				'grant_type' => 'authorization_code'
			)
		);

		return $token->body;
	}
	
	// TODO: Need a getProfile function that embeds all sites for the user

	public function getSite($accessToken, $blogId)
	{
		// $profile = Unirest::get('https://public-api.wordpress.com/rest/v1/me',
		// 	array('Authorization' => 'Bearer '.$token->body->access_token),
		// 	null
		// );
		// var_dump($profile);

		$site = Unirest::get('https://public-api.wordpress.com/rest/v1/sites/'.$blogId,
			array('Authorization' => 'Bearer '.$accessToken),
			null
		);

		$site->body->access_token = $accessToken;
		return $site;
	}

	public function getSiteForDB($accessToken, $blogId) {
		$site = $this->wordpress->getSite($accessToken, $blogId);

		// Update data
		$data = array(
			'id' => (string)$site->body->ID,
			'username' => 'blah', // TODO
			'website_name' => $site->body->name,
			'url' => $site->body->URL,
			'followers' => $site->body->subscribers_count,
			'following' => $site->body->subscribers_count,
			'posts' => $site->body->post_count,
			'access_token' => $site->body->access_token,
			'profile' => $site->body,
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