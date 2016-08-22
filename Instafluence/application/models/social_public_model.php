<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('BaseModel.php');

/**
 * Class Social_public_model
 *
 * @author Jim McGowen <jim@builtbyhq.com>
 */
class Social_public_model extends BaseModel
{
	// The current in user's email
	protected $email;

	// The entire object from the social collection for the current user
	protected $doc;

	/**
	 *
	 */
	public function init()
	{
		$this->setCollection('social_public');
	}

	/**
	 * Fetch user's social info from the database,
	 * and store in object for use by other methods.
	 * Also stores the user's email.
	 *
	 * @param string
	 */
	public function setUser($email)
	{
		$this->email = $email;
		$this->doc = $this->collection->findOne(array('email' => $email));

		if ($this->doc == null) {
			//throw new Exception('Social_history_model->setUser: User ' . $email . ' not found.', self::$USER_NOT_FOUND);
			$this->create($email);
		}
	}

	/**
	 *
	 */
	public function getSocialPublic($email)
	{
		$row = $this->collection->findOne(array('email' => $email));
		return $row;
	}

	/**
	 * Creates a new row with empty social data.
	 *
	 * @param string
	 */
	public function create($email)
	{
		$data = array(
			'email' => $email,
			'networks' => array()
		);

		$this->collection->save($data);

		// Set the current user
		$this->setUser($email);
	}

	/**
	 * @return array
	 */
	public static function getAllNetworkNames()
	{
		return Social_model::$networks;
	}

	/**
	 * Returns the social document for the user set with setUser.
	 *
	 * @return social doc or null.
	 * @throws Exception
	 */
	public function getDoc()
	{
		if (!isset($this->email) || !isset($this->doc)) {
			throw new Exception('User not set');
		}

		return $this->doc;
	}

	/**
	 * @param $networkName
	 *
	 * @return null
	 * @throws Exception
	 */
	public function getNetwork($networkName)
	{
		if (!isset($this->email) || !isset($this->doc)) {
			throw new Exception('User not set');
		}

		if (isset($this->doc['networks'][$networkName])) {
			return $this->doc['networks'][$networkName];
		}

		// Invalid network
		return null;
	}

	/**
	 * Returns all networks for the current user.
	 * Must call setUser first.
	 *
	 * @param $computeTotalReach
	 *
	 * @return array|null
	 * @throws Exception
	 */
	public function getNetworks($computeTotalReach = true, $sort = true)
	{
		if (!isset($this->email) || !isset($this->doc)) {
			throw new Exception('User not set');
		}

		if (isset($this->doc['networks'])) {

			$networks = $this->doc['networks'];

			if ($computeTotalReach) {
				foreach ($networks as $networkName => &$netowrk) {
					// Some networks will be null
					if (is_array($netowrk)) {
						foreach ($netowrk as $index => &$account) {
							$account['total_reach'] = $this->getReach($networkName, $index);
						}
					}
				}

				if ($sort) {
					$networkTotals = array();
					foreach ($networks as $networkName => &$network) {
						// Sort the accounts
						usort($network, function ($a, $b) {
							if (isset($a['connected']) && isset($b['connected'])) {
								if ($a['connected'] && $b['connected']) {
									if (isset($a['show_est']) && $a['show_est'] == true) {
										if ($a['est_followers'] < $b['est_followers']) {
											return 1;
										}
										else {
											return -1;
										}
									}
									else {
										if ($a['followers'] < $b['followers']) {
											return 1;
										}
										else {
											return -1;
										}
									}
								}
								else {
									if (!$a['connected'] && $b['connected']) {
										return 1;
									}
									else {
										return -1;
									}
								}
							}
							else {
								return 0;
							}
						});

						// Store the total for this network
						$networkTotals[$networkName] = $this->getTotalReach($networkName);
						$networkTotals[$networkName] += $this->getEstTotalReach($networkName);
					}

					// Sort the networks
					uksort($networks, function($a, $b) use ($networks, $networkTotals) {
						if ($networkTotals[$a] < $networkTotals[$b]) {
							return 1;
						}
						else if ($networkTotals[$a] > $networkTotals[$b]) {
							return -1;
						}
						else {
							return 0;
						}
					});
				}
			}

			return $networks;
		}

		return null;
	}

	/**
	 * Updates a users social info for a specific social network. Will overwrite the entire doc with $data.
	 *
	 * @param $data array
	 * @param $network string
	 * @param $accountIndex int defaults to 0
	 *
	 * @throws Exception if user is not set with setUser()
	 */
	public function update($data, $network, $accountIndex = 0)
	{
		if (!isset($this->email) || !isset($this->doc)) {
			throw new Exception('User not set');
		}

		// Keep price if not set in $data
		if (!isset($data['price']) && isset($this->doc['networks'][$network][$accountIndex]['price'])) {
			$data['price'] = $this->doc['networks'][$network][$accountIndex]['price'];
		}

		$this->doc['networks'][$network][$accountIndex] = $data;

		$this->collection->update(
			array('email' => $this->email),
			$this->doc
		);
	}

	/**
	 * Adds or updates an account for the given network based on
	 * the id field. If an account with the same id
	 * as what is given in $data exists then that account will be
	 * updated, otherwise a new account will be added.
	 *
	 * @param $data
	 * @param $network
	 *
	 * @return int
	 *
	 * @throws Exception
	 */
	public function addOrUpdateAccount($data, $network)
	{
		if (!isset($this->email) || !isset($this->doc)) {
			throw new Exception('User not set');
		}

		if (!isset($data['id'])) {
			throw new Exception('Bad parameters');
		}

		if (!isset($this->doc['networks'][$network])) {
			$this->doc['networks'][$network] = array();
		}

		$accountIndex = count($this->doc['networks'][$network]);
		foreach ($this->doc['networks'][$network] as $index => $account) {
			if (isset($account['id']) && $account['id'] == $data['id']) {
				$accountIndex = $index;
			}
		}

		$this->update($data, $network, $accountIndex);

		return $accountIndex;
	}

	/**
	 * Removes the network account from the db.
	 *
	 * @param $network
	 * @param $accountIndex
	 *
	 * @throws Exception
	 */
	public function remove($network, $accountIndex) {
		if (!isset($this->email) || !isset($this->doc)) {
			throw new Exception('User not set');
		}

		if (isset($this->doc['networks'][$network][$accountIndex])) {
			unset($this->doc['networks'][$network][$accountIndex]);
			if (count($this->doc['networks'][$network]) == 0) {
				unset($this->doc['networks'][$network]);
			}
			$this->collection->save($this->doc);
		}
	}

	/**
	 * Gets total reach by adding together the reach
	 * of every social network in the database.
	 *
	 * @return int
	 * @throws Exception
	 */
	public function getTotalReach($networkName = null)
	{
		if (!isset($this->email) || !isset($this->doc)) {
			throw new Exception('User not set');
		}

		$reach = 0;

		// Loop through each network
		foreach ($this->doc['networks'] as $name => $network) {
			if ($networkName == null || $name == $networkName) {
				// Loop through each account
				if (is_array($network)) {
					foreach ($network as $accountIndex => $account) {
						$reach += $this->getReach($name, $accountIndex);
					}
				}
			}
		}

		return $reach;
	}

	public function getEstTotalReach($networkName = null) {
		if (!isset($this->email) || !isset($this->doc)) {
			throw new Exception('User not set');
		}

		$reach = 0;

		// Loop through each network
		foreach ($this->doc['networks'] as $name => $network) {
			if ($networkName == null || $name == $networkName) {
				// Loop through each account
				if (is_array($network)) {
					foreach ($network as $accountIndex => $account) {
						$reach += $this->getEstReach($name, $accountIndex);
					}
				}
			}
		}

		return $reach;
	}

	/**
	 * Returns the number of connected social networks.
	 *
	 * @return int
	 * @throws Exception
	 */
	public function getCount()
	{
		if (!isset($this->email) || !isset($this->doc)) {
			throw new Exception('User not set');
		}

		$connected = 0;
		foreach ($this->doc['networks'] as $network) {
			if (is_array($network)) {
				$connected += count($network);
			}
		}

		return $connected;
	}

	/**
	 * Returns a list of connected networks.
	 * Networks may appear multiple times if multiple accounts are connected.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getConnectedNetworkNames($networks = null)
	{
		if ($networks === null && (!isset($this->email) || !isset($this->doc))) {
			throw new Exception('User not set');
		}

		if ($networks === null) {
			if (isset($this->doc['networks'])) {
				$networks = $this->doc['networks'];
			}
			else {
				throw new Exception('Bad parameter.');
			}
		}

		$connectedNetworks = array();
		foreach ($networks as $networkName => $network) {
			if (is_array($network)) {
				foreach ($network as $account) {
					if (isset($account['connected']) && $account['connected'] == true) {
						$connectedNetworks[] = $networkName;
					}
				}
			}
		}

		return $connectedNetworks;
	}

	/**
	 * Retrieves/decodes the user's Vine credentials from
	 * the database.
	 *
	 * @param $email string
	 * @param $accountIndex int
	 * @return array ('username' => [username], 'password' => [password])
	 */
	/*
	public function getVineCreds($email, $accountIndex = 0)
	{
		$doc = $this->collection->findOne(array('email'=>$email));

		$creds = array(
			'username' => $doc['vine'][$accountIndex]['username'],
			'password' => $this->encrypt->decode($doc['vine'][$accountIndex]['password'])
		);

		return $creds;
	}
	*/

	/**
	 * Retrieves the user's Tumblr OAuth tokens from the
	 * database so they don't have to authorize the app
	 * every time we make a call for their profile information.
	 *
	 * @param $accountIndex int
	 * @return array ('oauth_token' => [oauth_token], 'oauth_token_secret' => [oauth_token_secret])
	 * @throws Exception
	 */
	/*
	public function getTumblrToken($accountIndex = 0)
	{
		if (!isset($this->email) || !isset($this->doc)) {
			throw new Exception('User not set');
		}

		if (!isset($this->doc['networks']['tumblr'][$accountIndex])) {
			return null;
		}

		// Return false if there are no tokens set yet
		if ($this->doc['networks']['tumblr'][$accountIndex]['connected'] == false || !isset($doc['tumblr'][$accountIndex]['oauth_token'])) {
			return null;
		}

		$token = array(
			'oauth_token' => $this->doc['networks']['tumblr'][$accountIndex]['oauth_token'],
			'oauth_token_secret' => $this->doc['networks']['tumblr'][$accountIndex]['oauth_token_secret']
		);

		return $token;
	}
	*/

	/**
	 * Retrieves the user's Wordpress access token from the
	 * database so they don't have to authorize the app
	 * every time we make a call for their profile information.
	 *
	 * @param $accountIndex int
	 * @return array
	 * @throws Exception
	 */
	/*
	public function getWordpressAccessToken($accountIndex = 0)
	{
		if (!isset($this->email) || !isset($this->doc)) {
			throw new Exception('User not set');
		}

		if (!isset($this->doc['networks']['tumblr'][$accountIndex])) {
			return null;
		}

		if (!isset($doc['wordpress'][$accountIndex]['access_token'])) {
			return false;
		}

		$token = array(
			'access_token' => $doc['wordpress'][$accountIndex]['access_token'],
			'blog_id' => $doc['wordpress'][$accountIndex]['id']
		);

		return json_encode($token);
	}
	*/

	/**
	 * Updates Facebook page data for the given account. Always sets connected to true for the page.
	 *
	 * @param $pageProfile
	 * @param $accountIndex
	 * @param $pageIndex
	 * @throws Exception
	 */
	/*
	public function updateFacebookPage($pageProfile, $accountIndex, $pageIndex)
	{
		if (!isset($this->email) || !isset($this->doc)) {
			throw new Exception('User not set');
		}

		if (!isset($this->doc['networks']['facebook'][$accountIndex])) {
			throw new Exception('Bad account index');
		}

		if (!isset($this->doc['networks']['facebook'][$accountIndex]['accounts'])) {
			$this->doc['networks']['facebook'][$accountIndex]['accounts'] = array();
		}

		if (!isset($this->doc['networks']['facebook'][$accountIndex]['accounts'][$pageIndex])) {
			$this->doc['networks']['facebook'][$accountIndex]['accounts'][$pageIndex] = array();
		}

		if (isset($this->doc['networks']['facebook'][$accountIndex]['accounts'][$pageIndex])) {
			$this->doc['networks']['facebook'][$accountIndex]['accounts'][$pageIndex]['connected'] = true;
			$this->doc['networks']['facebook'][$accountIndex]['accounts'][$pageIndex]['profile'] = $pageProfile;

			$this->collection->update(
				array('email' => $this->email),
				$this->doc
			);
		}
	}
	*/

	/**
	 * Returns all the Facebook pages for a given account.
	 *
	 * @param $accountIndex
	 *
	 * @return null
	 * @throws Exception
	 */
	/*
	public function getFacebookPages($accountIndex) {
		if (!isset($this->email) || !isset($this->doc)) {
			throw new Exception('User not set');
		}

		if (isset($this->doc['networks']['facebook'][$accountIndex]['accounts'])) {
			return $this->doc['networks']['facebook'][$accountIndex]['accounts'];
		}

		return null;
	}
	*/

	/**
	 * Returns the total followers for the given network and account index.
	 * If $account is null $this->doc[$networkName][$accountIndex] is used instead.
	 *
	 * Factors in special cases like Facebook pages and YouTube channels.
	 *
	 * @param $networkName
	 * @param $accountIndex
	 * @param null $account If set this account data will be used instead of the internal doc.
	 *
	 * @return int
	 * @throws Exception
	 */
	public function getReach($networkName, $accountIndex, $account = null)
	{
		if ($account == null && (!isset($this->email) || !isset($this->doc))) {
			throw new Exception('User not set');
		}

		if ($account == null) {
			if (isset($this->doc['networks'][$networkName][$accountIndex])) {
				$account = $this->doc['networks'][$networkName][$accountIndex];
			}
			else {
				//throw new Exception('Bad parameter.');
			}
		}

		$followers = 0;
		if (isset($account['followers'])) {
			$followers += $account['followers'];

			/*
			// Include connected Facebook pages
			if ($networkName == 'facebook' && isset($account['accounts'])) {
				if (!isset($account['include']) || $account['include'] == false) {
					// Do not include their personal account
					$followers -= $account['followers'];
				}

				foreach ($account['accounts'] as $page) {
					if (isset($page['connected']) && $page['connected'] == true && isset($page['profile']['likes'])) {
						$followers += $page['profile']['likes'];
					}
				}
			}
			// Include YouTube channels
			// TODO: Eventually want to only check connected channels
			elseif ($networkName == 'youtube' && isset($account['channels'])) {
				foreach ($account['channels'] as $channel) {
					if (isset($channel['stats']['subscriberCount'])) {
						$followers += $channel['stats']['subscriberCount'];
					}
				}
			}
			*/
		}

		return $followers;
	}

	public function getEstReach($networkName, $accountIndex, $account = null)
	{
		if ($account == null && (!isset($this->email) || !isset($this->doc))) {
			throw new Exception('User not set');
		}

		if ($account == null) {
			if (isset($this->doc['networks'][$networkName][$accountIndex])) {
				$account = $this->doc['networks'][$networkName][$accountIndex];
			}
			else {
				throw new Exception('Bad parameter.');
			}
		}

		$followers = 0;
		if (isset($account['show_est']) && isset($account['est_followers'])) {
			$followers += $account['est_followers'];
		}

		return $followers;
	}

	public function savePrice($network, $accountIndex, $price) {
		if (!isset($this->email) || !isset($this->doc)) {
			throw new Exception('User not set');
		}

		if (isset($this->doc['networks'][$network][$accountIndex])) {
			$this->doc['networks'][$network][$accountIndex]['price'] = $price;
		}

		$this->collection->save($this->doc);
	}

	public function saveEstFollowers($network, $accountIndex, $estFollowers) {
		if (!isset($this->email) || !isset($this->doc)) {
			throw new Exception('User not set');
		}

		if (isset($this->doc['networks'][$network][$accountIndex])) {
			$this->doc['networks'][$network][$accountIndex]['est_followers'] = $estFollowers;
		}

		$this->collection->save($this->doc);
	}

	/**
	 * Changes the email on all docs with $oldEmail to $newEmail.
	 * Trims the input but assumes $newEmail is a properly formatted email addresses.
	 *
	 * @param $oldEmail
	 * @param $newEmail
	 */
	public function changeEmail($oldEmail, $newEmail) {
		$oldEmail = trim($oldEmail);
		$newEmail = trim($newEmail);

		$this->collection->update(
			array(
				'email' => $oldEmail
			),
			array(
				'$set' => array(
					'email' => $newEmail,
				)
			)
		);
	}

	/**
	 * @param $network
	 * @param $username
	 *
	 * @return null | array
	 * @throws Exception
	 */
	public function findAccountByUsername($network, $username) {
		$network = $this->getNetwork($network);
		if ($network != null) {
			foreach ($network as $account) {
				if (isset($account['connected']) && $account['connected'] == true) {
					if (isset($account['username']) && $account['username'] == $username) {
						// Found it
						return $account;
					}
				}
			}
		}

		// Not found
		return null;
	}

	public static function getUsernameFromUrl($url) {
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
}
