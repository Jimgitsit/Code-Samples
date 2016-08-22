<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author Jason Maurer <jason@builtbyhq.com>
 */

require_once(APPPATH . 'third_party/Unirest/Unirest.php');

class Aggregation
{

	protected $ci;
	protected $users = array();
	protected $newData;
	protected $ignoreRateLimits = false;
	protected $network = null;
	protected $type = null;
	
	protected static $INSTAGRAM_MAX_API_CALLS = 4000;
	
	protected $instagramCallCount = 0;
	protected $instagramLimitReached = false;

	public function __construct()
	{
		$this->ci =& get_instance();

		$this->ci->load->model('social_model');
		$this->ci->load->model('social_history_model');

		$this->ci->load->model('social_public_model');
		$this->ci->load->model('social_public_history_model');
	}
	
	public function setUser($email) 
	{
		$this->ci->social_model->setUser($email);
		$social = $this->ci->social_model->getDoc();
		$this->users[$social['email']]['social'] = $social;

		$this->ci->social_public_model->setUser($email);
		$socialPublic = $this->ci->social_public_model->getDoc();
		$this->users[$social['email']]['social_public'] = $socialPublic;
	}

	public function setAllUsers()
	{
		$allSocial = $this->ci->social_model->getAll();
		$allSocialPublic = $this->ci->social_public_model->getAll();

		$this->users = array();
		foreach ($allSocial as $social) {
			$this->users[$social['email']]['social'] = $social;
		}

		foreach ($allSocialPublic as $social) {
			$this->users[$social['email']]['social_public'] = $social;
		}
	}
	
	public function aggregate($echoOutput = true, $ignoreRateLimits = false, $network = null, $type = null)
	{
		set_time_limit(0);
		
		$this->ignoreRateLimits = $ignoreRateLimits;
		if (!empty($network) && $network != 'all') {
			$this->network = $network;
		}
		$this->type = $type;
		
		if ($echoOutput) {
			echo "\n*************** Aggregation Service Started ***************\n";
			$time = time();
			echo(strftime("------------------- %m/%d/%Y %H:%M:%S -------------------\n\n"));
		}

		if ($this->network) {
			echo("\n\tAggregating only " . $this->network . "\n\n");
		}

		if ($this->type !== null) {
			echo("\n\tAggregating only " . $this->type . "\n\n");
		}
		
		if ($this->type !== null && $this->type == 'connected') {
			$this->aggregateConnected($echoOutput);
		}
		else if ($this->type !== null && $this->type == 'public') {
			$this->aggregatePublic($echoOutput);
		}
		else if ($this->type === null || $this->type == 'both') {
			$this->aggregateConnected($echoOutput);
			$this->aggregatePublic($echoOutput);
		}

		if ($echoOutput) {
			echo "\nTotal Running Time: " . (time() - $time) . " seconds\n";
			echo "*************** Aggregation Service Completed ***************\n\n";
		}
	}
	
	private function aggregateConnected($echoOutput) 
	{
		$newData = array();
		
		if ($echoOutput) {
			echo "    *** Aggregating Connected Networks ***\n";
		}
		
		foreach ($this->users as $email => $user) {
			$social = $user['social'];
			
			if ($echoOutput) {
				echo "Updating ".$email."...\n";
			}

			foreach ($social['networks'] as $network => $accounts) {
				if (!is_array($accounts)) {
					continue;
				}
				
				if ($this->network !== null && $network != $this->network) {
					continue;
				}
				
				if ($network == 'instagram') {
					$this->instagramCallCount++;
					if ($this->instagramCallCount >= self::$INSTAGRAM_MAX_API_CALLS) {
						echo("\tInstagram...Skipping\n");
						continue;
					}
				}

				foreach ($accounts as $index => $account) {
					// Only want to update connected accounts
					if (isset($account['connected']) && $account['connected'] == true) {
						if ($echoOutput) {
							echo "\t".ucfirst($network)."...";
						}

						// *** Handle rate limits ***
						if (!$this->ignoreRateLimits) {

							// Instagram rate limit handler. About 5000 per day
							if ($network == 'instagram' && $this->instagramLimitReached) {
								echo("Skipping\n");
								continue;
							}

							// Twitter and Instagram rate limit handler. Update every two days
							if (($network == 'twitter' || $network == 'instagram') && isset($account['last_updated'])) {
								$updated = new DateTime();
								$updated->setTimestamp($account['last_updated']->sec);
								$interval = $updated->diff(new DateTime());
								if ($interval->days < 2) {
									echo("Skipping\n");
									continue;
								}
							}
						}

						$error = false;
						try {
							// TODO: Seems redundant to collect all the info in newData and then update the db.
							//       Would probably be faster to update the db as we go.

							$this->ci->social_model->setUser($email);

							$newData[$email][$network][$index] = $this->{$network}($account, $index);

							// If we made it this far then there was no error so clear any previous errors
							if (isset($newData[$email][$network][$index]['errors'])) {
								unset($newData[$email][$network][$index]['errors']);
							}
						}
						catch (Exception $e) {
							$error = true;

							// Save the error in the document
							$newData[$email][$network][$index] = $account;
							$msg = $e->getMessage();
							if (empty($msg)) {
								$msg = 'Unknown error';
							}
							
							if ($network == 'instagram' && $e->getCode() == 429) {
								$this->instagramLimitReached = true;
							}

							$msg = "An error has occurred accessing this users $network account. Please try again. If the problem persists please have the user reconnect their $network account. Error: $msg";
							$newData[$email][$network][$index]['error'] = $msg;

							// Output the error
							if ($echoOutput) {
								echo "\n\t\tERROR! " . $e->getMessage() . "\n";
							}
							else {
								log_message('error', "ERROR! " . $e->getMessage());
							}
						}

						if (!$error) {
							if ($echoOutput) {
								echo "Done\n";
							}
						}
					}
				}
			}
		}

		foreach ($newData as $email => $networks) {

			$this->ci->social_model->setUser($email);
			$this->ci->social_history_model->setUser($email);

			foreach ($networks as $network => $accounts) {
				$followers = 0;
				$following = 0;
				$posts = 0;

				foreach ($accounts as $accountIndex => $data) {
					// Update the social model
					$this->ci->social_model->update($data, $network, $accountIndex);

					// Add total for all accounts for social history
					if (isset($data['followers'])) {
						$followers += $data['followers'];
					}

					if (isset($data['following'])) {
						$following += $data['following'];
					}

					if (isset($data['posts'])) {
						$posts += $data['posts'];
					}

					// For Facebook add all the likes for connected pages
					if ($network == 'facebook' && isset($data['accounts'])) {
						foreach ($data['accounts'] as $page) {
							if (isset($page->connected) && $page->connected == true && isset($page->profile['likes'])) {
								$followers += $page->profile['likes'];
							}
						}
					}

					// For you tube add channel subscribers
					if ($network == 'youtube' && isset($data['channels'])) {
						foreach ($data['channels'] as $channel) {
							if (isset($channel['stats']->subscriberCount)) {
								$followers += $channel['stats']->subscriberCount;
							}
						}
					}

					// Update the social history model
					$data = array(
						'followers' => $followers,
						'following' => $following,
						'posts' => $posts
					);

					$this->ci->social_history_model->update($data, $network, $accountIndex);
				}
			}
		}
	}

	private function aggregatePublic($echoOutput) {
		$newData = array();

		if ($echoOutput) {
			echo "    *** Aggregating Public Networks ***\n";
		}

		foreach ($this->users as $email => $user) {
			if (!isset($user['social_public'])) {
				continue;
			}
			
			$social = $user['social_public'];
			if ($social == null) {
				continue;
			}

			if ($echoOutput) {
				echo "Updating public " . $email . "...\n";
			}

			foreach ($social['networks'] as $network => $accounts) {
				if (!is_array($accounts)) {
					continue;
				}

				if ($this->network !== null && $network != $this->network) {
					continue;
				}

				if ($network == 'instagram') {
					if ($this->instagramCallCount >= self::$INSTAGRAM_MAX_API_CALLS) {
						echo("\tInstagram...Skipping\n");
						continue;
					}
				}

				foreach ($accounts as $index => &$account) {
					if (isset($account['username'])) {
						if ($echoOutput) {
							echo "\t".ucfirst($network)."...";
						}
						
						// *** Handle rate limits ***
						if (!$this->ignoreRateLimits) {
							
							// Instagram rate limit handler. About 5000 per day
							if ($network == 'instagram' && $this->instagramLimitReached) {
								echo("Skipping for instagram daily limit reached.\n");
								continue;
							}
							
							// Twitter and Instagram rate limit handler. Update every two days
							if (($network == 'twitter' || $network == 'instagram') && isset($account['last_updated'])) {
								$updated = new DateTime();
								$updated->setTimestamp($account['last_updated']->sec);
								$interval = $updated->diff(new DateTime());
								if ($interval->days < 2) {
									echo("Skipping for day limiter.\n");
									continue;
								}
							}
							
							if ($network == 'instagram' && 
								(isset($account['bad_username']) && $account['bad_username'] == true || 
									isset($account['private_account']) && $account['private_account'] == true))
							{
								echo("Skipping for bad username.\n");
								continue;
							}
						}

						$error = false;
						try {
							$network = strtolower($network);
							$library = ucfirst($network);
							$library == 'Youtube' ? $library = 'YouTube' : null;
							$library == 'Linkedin' ? $library = 'LinkedIn' : null;
							$this->ci->load->library("APIs/$library");

							if (!method_exists($this->ci->{$network}, 'getPublicProfileForDB') ||
								!method_exists($this->ci->{$network}, 'getId')) {
								throw new Exception("getPublicProfile and/or getId not implemented for network: " . $network);
							}

							$account['id'] = $this->ci->{$network}->getId($account['username']);

							if ($account['id'] != 'unimplemented') {
								if (!empty($account['id'])) {
									$newData[$email][$network][$index] = $this->ci->{$network}->getPublicProfileForDB($account['id']);
									if ($newData[$email][$network][$index] == null) {
										throw new Exception("Got null from $network -> getPublicProfileForDB.");
									}
									
									if ($network == 'instagram') {
										$this->instagramCallCount++;
									}
								}
								else {
									throw new Exception('Could not determine user id for network ' . $network . ' and account index ' . $index);
								}
							}

							// If we made it this far then there was no error so clear any previous errors
							if (isset($newData[$email][$network][$index]['errors'])) {
								unset($newData[$email][$network][$index]['errors']);
							}
						}
						catch (Exception $e) {
							$error = true;

							// Save the error in the document
							$newData[$email][$network][$index] = $account;
							$newData[$email][$network][$index]['show_est'] = false;
							$msg = $e->getMessage();
							if (empty($msg)) {
								$msg = 'Unknown error';
							}

							if ($network == 'instagram') {
								if ($e->getCode() == 429) {
									// Reached API call limit
									$this->instagramLimitReached = true;
								}
								elseif ($e->getCode() == 400) {
									// User not found
									$newData[$email][$network][$index]['private_account'] = true;
								}
								elseif ($e->getCode() == Instagram::$ERR_INSTAGRAM_BAD_USERNAME) {
									// User not found
									$newData[$email][$network][$index]['bad_username'] = true;
								}
							}

							$msg = "An error has occurred accessing this users public $network account. Please try again. If the problem persists please contact support. Error: $msg";
							$newData[$email][$network][$index]['error'] = $msg;

							// Output the error
							if ($echoOutput) {
								echo "\t\tERROR! " . $e->getMessage() . "\n";
							}
							else {
								log_message('error', "ERROR! " . $e->getMessage());
							}
						}

						if (!$error) {
							if ($echoOutput) {
								echo "Done\n";
							}
						}
					}
				}
			}
		}

		foreach ($newData as $email => $networks) {

			$this->ci->social_public_model->setUser($email);
			$this->ci->social_public_history_model->setUser($email);

			foreach ($networks as $network => $accounts) {
				$followers = 0;
				$following = 0;
				$posts = 0;

				foreach ($accounts as $accountIndex => $data) {
					// Update the social model
					$this->ci->social_public_model->update($data, $network, $accountIndex);

					// Add total for all accounts for social history
					if (isset($data['followers'])) {
						$followers += $data['followers'];
					}

					if (isset($data['following'])) {
						$following += $data['following'];
					}

					if (isset($data['posts'])) {
						$posts += $data['posts'];
					}

					/*
					// For Facebook add all the likes for connected pages
					if ($network == 'facebook' && isset($data['accounts'])) {
						foreach ($data['accounts'] as $page) {
							if (isset($page->connected) && $page->connected == true && isset($page->profile['likes'])) {
								$followers += $page->profile['likes'];
							}
						}
					}

					// For you tube add channel subscribers
					if ($network == 'youtube' && isset($data['channels'])) {
						foreach ($data['channels'] as $channel) {
							if (isset($channel['stats']->subscriberCount)) {
								$followers += $channel['stats']->subscriberCount;
							}
						}
					}
					*/

					// Update the social history model
					$data = array(
						'followers' => $followers,
						'following' => $following,
						'posts' => $posts
					);

					try {
						$this->ci->social_public_history_model->update($data, $network, $accountIndex);
					}
					catch (Exception $e) {
						echo "\t\tERROR! Could not update social_public_hsitory for '$network':'$accountIndex'.\n" . $e->getMessage() . "\n";
					}
				}
			}
		}
	}

	public function facebook($row, $accountIndex)
	{
		$this->ci->load->library('APIs/Facebook');
		
		if (empty($row['access_token'])) {
			throw new Exception('Missing access token');
		}

		$data = $this->ci->facebook->getProfileForDB($row['access_token']);
		
		$pages = $this->ci->social_model->getFacebookPages($accountIndex);
		foreach ($pages as $page) {
			foreach ($data['accounts'] as &$account) {
				if ($page['id'] == $account->id) {
					if (isset($page['connected']) && $page['connected'] == true) {
						$account->connected = true;
						$account->profile = $this->ci->facebook->getPageProfile($account->id, $row['access_token']);
					}
				}
			}
		}

		return $data;
	}

	public function twitter($row, $accountIndex)
	{
		$this->ci->load->library('APIs/Twitter');
		
		$data = $this->ci->twitter->getProfileForDB(array(
			'oauth_token' => $row['oauth_token'],
			'oauth_token_secret' => $row['oauth_token_secret']
		));
		
		return $data;
	}

	public function instagram($row, $accountIndex)
	{
		$this->ci->load->library('APIs/Instagram');

		$data = $this->ci->instagram->getProfileForDB($row['id'], $row['access_token']);
		
		return $data;
	}

	public function vine($row, $accountIndex)
	{
		$this->ci->load->library('APIs/Vine');

		$creds = array(
			'username' => $row['username'],
			'password' => $this->ci->encrypt->decode($row['password'])
		);
		
		if (!$this->ci->vine->connect($creds)) {
			throw new Exception('Authentication failed');
		}

		$data = $this->ci->vine->getProfileForDB($creds);
		
		return $data;
	}

	public function googleplus($row, $accountIndex)
	{
		$this->ci->load->library('APIs/GooglePlus');
		
		// Backwards comapt
		if (!is_array($row['access_token'])) {
			$row['access_token'] = json_decode($row['access_token']);
		}

		$data = $this->ci->googleplus->getProfileForDB($row['access_token']);

		return $data;
	}

	public function youtube($row, $accountIndex)
	{
		$this->ci->load->library('APIs/YouTube');

		// Backwards comapt
		if (!is_array($row['access_token'])) {
			$row['access_token'] = json_decode($row['access_token']);
		}

		$data = $this->ci->youtube->getProfileForDB($row['access_token']);

		return $data;
	}

	public function linkedin($row, $accountIndex)
	{
		$this->ci->load->library('APIs/LinkedIn');

		$data = $this->ci->linkedin->getProfileForDB($row['access_token']);
		
		return $data;
	}

	public function tumblr($row, $accountIndex)
	{
		$this->ci->load->library('APIs/Tumblr');

		$token = $this->ci->social_model->getTumblrToken($accountIndex);

		$data = $this->ci->tumblr->getProfileForDB($token);

		return $data;
	}

	public function wordpress($row, $accountIndex)
	{
		$this->ci->load->library('APIs/Wordpress');

		$token = $this->ci->social_model->getWordpressAccessToken($accountIndex);

		$data = $this->ci->wordpress->getSiteForDB($token->access_token, $token->blog_id);

		return $data;
	}

	public function foursquare($row, $accountIndex)
	{
		$this->ci->load->library('APIs/Foursquare');

		$data = $this->ci->foursquare->getProfileForDB($row['access_token']);

		return $data;
	}

	public function pinterest($row, $accountIndex) {
		$this->ci->load->library('APIs/Foursquare');

		$data = $this->ci->foursquare->getProfileForDB($row['access_token']);

		return $data;
	}
}