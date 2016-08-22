<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('BaseDefaultController.php');

/**
 * Class DataImportGoogleDoc
 *
 * Used to import data from a csv exported from the google doc Other Links tab:
 * https://docs.google.com/a/builtbyhq.com/spreadsheets/d/1wh506gmFajj9t3JO8rIdpMdaFDKq5B8GygKkn4JIM5o/edit#gid=1608094688
 */
class DataImportGoogleDoc extends BaseDefaultController
{
	private static $CRLF = "\n";
	// private static $COLUMNS = array(
	// 	"ig_username","email","emails","ig_link","twitter","facebook","vine","youtube","pinterest","tumblr","blog","ig_price","notes","qa","austin","gender","blogger","fashion","gaming","male_fashion","mom","design_photography","food","fitness","animals","beauty","cars","travel","sports","entertainment_misc","spanish_speaking","dance","themed","lifestyle","outdoor"
	// );
	private static $COLUMNS = array(
		"ig_username","email","ig_link","twitter","facebook","vine","youtube","pinterest","tumblr","rate","notes","gender","ig_blast","blogger","fashion","gaming","male_fashion","mom","design_photography","food","fitness","animals","beauty","cars","travel","sports","music","entertainment_misc","spanish_speaking","dance","themed","lifestyle","outdoor"
	);

	private static $NETWORK_COLUMNS = array(
		"ig_username","twitter","facebook","vine","youtube","pinterest"
	);
	// private static $NETWORK_COLUMNS = array(
	// 	"Username","Twitter","Facebook","Vine","YouTube","Pinterest","Tumblr"
	// );

	private static $TAG_COLUMNS = array(
		'ig_blast','blogger','fashion','gaming','male_fashion','mom','design_photography','food','fitness','animals','beauty','cars','travel','sports','entertainment_misc','spanish_speaking','dance','themed','lifestyle','outdoor'
	);

	// private static $PATH = '/Applications/XAMPP/xamppfiles/htdocs/instafluence/public/';
	private static $PATH = '/Users/jdmaurer/Projects/HQ/Instafluence/data/';

	private $addedNetworks = 0;

	public function __construct() {
		parent::__construct();
		$this->load->model('user_model');
		$this->load->model('social_model');
		$this->load->model('social_public_model');
		$this->load->model('notes_model');
		$this->load->model('profile_model');
		$this->load->model('tags_model');

		$this->load->library('Cryptography');
		$this->load->helper('email');
	}

	public function index($fileName) {
		echo(self::$CRLF . "Importing from file $fileName...\n\n");

		set_time_limit(0);

		$newCount = 0;
		$updateCount = 0;

		if (($handle = @fopen(self::$PATH."$fileName", "r")) !== FALSE) {
			$first = true;
			while (($rowData = fgetcsv($handle, 0, ",")) !== FALSE) {
				// Skip the first row (header)
				if ($first) {
					$first = false;
					continue;
				}

				$row = $this->decodeRow($rowData);
				if ($row == null) {
					continue;
				}

				if ($row['email'] == 'a_karanikolaou@yahoo.com') {
					$break = true;
				}

				// if (($row['email'] == '' || $row['email'] == 'NULL') && count($row['emails']) > 0) {
				// 	$row['email'] = $row['emails'][0];
				// }

				// Blank or invalid email?
				if (!valid_email($row['email'])) {
					// TODO: Would be better to finish what's below to help avoid duplicate users
					/*
					// Try to find an existing user by matching usernames to social netowrks
					foreach (self::$NETWORK_COLUMNS as $column) {
						$username = $this->getUsernameFromUrl($row[$column]);
						$social = $this->social_model->findByUsername($username);
						$socialPublic = $this->social_public_model->findByUsername($username);
						if
					}
					*/

					$row['email'] = $this->generateRandomEmail();
				}

				// Look for an existing user
				$newUser = false;
				$user = $this->user_model->getUser($row["email"]);
				if ($user == null) {
					// Create a new user
					echo(" Creating user: {$row['email']}...\n");

					$user = array(
						'email' => $row['email']
					);
					$user = array_merge($user, $this->getFirstLastName($row));

					$userDoc = array(
						'email' => $user['email'],
						'first_name' => $user['first_name'],
						'last_name' => $user['last_name'],
						'password' => strtolower($this->cryptography->randomString(10)),
						'salt' => '',
						'account_type' => 1
					);
					$this->user_model->create($userDoc, 'influencer');

					$user = $this->user_model->getUser($row["email"]);
					$newCount++;
					$newUser = true;
				}
				else {
					// Update existing user
					echo(" Updating user: {$user['email']}...\n");
					$updateCount++;
				}

				$this->addNetwork('instagram', false, $row, $user, $newUser);
				$this->addNetwork('vine', false, $row, $user, $newUser);
				$this->addNetwork('twitter', true, $row, $user, $newUser);
				$this->addNetwork('facebook', true, $row, $user, $newUser);
				$this->addNetwork('youtube', true, $row, $user, $newUser);
				$this->addNetwork('pinterest', true, $row, $user, $newUser);
				//$this->addNetwork('tumblr', true, $row, $user, $newUser);
				//$this->addNetwork('blog', true, $row, $user, $newUser);

				$this->addNotes($row, $user);
				$this->addTags($row, $user);
				$this->setGender($row, $user);
			}
			fclose($handle);
		}
		else {
			echo("Error: Couldn't open '$fileName' for reading.\n");
		}

		echo("\n# of users added: $newCount, #of users updated: $updateCount\n");
		echo("\n# of networks added: $this->addedNetworks\n");

		echo("\nDone\n\n");
	}

	private function addNetwork($networkName, $showEst, $row, $user, $newUser) {
		// Translate column name
		$column = $networkName;
		if ($column == 'instagram') {
			$column = 'ig_username';
		}

		if (!empty($row[$column])) {
			$url = $row[$column];
			if ($networkName == 'instagram') {
				$url = $row['ig_link'];
			}

			if (stripos($url, 'http') != 0 && stripos($url, ' ') !== false && stripos($url, '/') !== false) {
				echo("    Got a bad url for $networkName: $url\n");
				return;
			}

			if ($user['email'] == 'Bookings@kaydenstephenson.com') {
				$break = true;
			}

			$username = $this->getUsernameFromUrl($url);

			if ($username == false && $column == 'ig_username') {
				$username = $row[$column];
			}

			if (empty($username) || $username == false) {
				echo("    Got a bad username for $networkName: $username\n");
				return;
			}

			$addNetwork = false;
			if ($newUser == false) {
				// Look for user connected networks
				$this->social_model->setUser($user['email']);
				//$account = $this->social_model->findAccountByUsername($networkName, $username);

				$accounts = $this->social_model->getNetwork($networkName);

				if ($accounts != null) {
					// Remove any that have errors
					foreach ($accounts as $index => $account) {
						// If there is an error remove the user connected account
						if ($account != null && isset($account['error']) && $account['error'] != '') {
							$this->social_model->disconnect($networkName, $index);
						}
					}

					$accounts = $this->social_model->getNetwork($networkName);
				}

				if ($accounts == null) {
					// Look for admin connected network
					$this->social_public_model->setUser($user['email']);
					$account = $this->social_public_model->findAccountByUsername($networkName, $username);
					if ($account == null) {
						// If we have neither create an admin connected network
						$addNetwork = true;
					}
				}
			}
			else {
				// Create an admin connected network
				$addNetwork = true;
			}

			if ($addNetwork) {
				echo("    Adding network $networkName\n");
				$this->addedNetworks++;

				$networkTemplate = array(
					'id' => '',
					'username' => '',
					'url' => $url,
					'show_est' => $showEst,
					'est_followers' => 0,
					'connected' => true
				);

				$network = $networkTemplate;
				$network['username'] = $username;

				// Special cases
				if ($networkName == 'instagram') {
					if (!empty($row['ig_link'])) {
						$network['url'] = $row['ig_link'];
					}

					if (!empty($row['ig_price'])) {
						$price = str_replace(array('$',',',' '), '', $row['ig_price']);
						$network['price'] = intval($price);
					}
				}
				elseif ($networkName == 'vine') {
					$network['id'] = $username;
				}

				$this->social_public_model->setUser($user['email']);
				$this->social_public_model->addOrUpdateAccount($network, $networkName);
			}
		}
	}

	private function addNotes($row, $user) {
		if (isset($row['notes']) && $row['notes'] != '') {
			echo("    Adding note '{$row['notes']}'\n");
			$this->notes_model->addOrUpdateNote(null, $user['email'], $row['notes'], 'Imported');
		}
	}

	private function addTags($row, $user) {
		echo("    Adding tags\n");
		//$this->load->model('profile_model');
		$newTags = array();
		foreach (self::$TAG_COLUMNS as $column) {
			if ($row[$column] != '') {
				$newTags[] = $column;
			}
		}

		$tags = $this->profile_model->getTags($user['email']);
		foreach ($newTags as $newTag) {
			if (!in_array($newTag, $tags)) {
				$tags[] = $newTag;
			}
		}
		$this->profile_model->setTags($user['email'], $tags);
		$this->tags_model->save($tags);
	}

	private function setGender($row, $user) {
		$profile = $this->profile_model->getProfile($user['email']);
		if ($profile == null || !isset($profile['email']) || $profile['email'] == null) {
			//throw new Exception(': ERROR! Got null profile');
			echo("      ERROR! Bad or missing profile. Recreating it.\n");
			$this->profile_model->getCollection()->remove(array('email' => $user['email']));
			$this->profile_model->create($user['email']);
			$profile = $this->profile_model->getProfile($user['email']);
		}

		if ($row['gender'] != '') {
			echo("    Setting gender\n");

			if ($row['gender'] == 'Male') {
				$profile['gender'] = 1;
			}
			else {
				if ($row['gender'] == 'Female') {
					$profile['gender'] = 0;
				}
				else {
					// if ($row['gender'] == 'O') {
						$profile['gender'] = 3;
					// }
				}
			}

			$this->profile_model->addOrUpdateProfile($user['email'], $profile);
		}
	}

	private function decodeRow($rowData) {
		$row = array();

		$empty = true;
		foreach (self::$COLUMNS as $colName) {
			$row[$colName] = trim($rowData[array_search($colName, self::$COLUMNS)]);
			if ($row[$colName] != '') {
				$empty = false;
			}

			if ($colName == 'email' && stripos($row[$colName], ',')) {
				$row[$colName] = substr($row[$colName], 0, strpos($row[$colName], ','));
			}

			if ($colName == 'email' && stripos($row[$colName], ':')) {
				$row[$colName] = trim(substr($row[$colName], strpos($row[$colName], ':') + 1));
			}

			if ($colName == 'email' && substr($row[$colName], -1) == '.') {
				$row[$colName] = substr($row[$colName], 0, -1);
			}

			if ($colName == 'emails') {
				$row[$colName] = json_decode($row[$colName]);
			}
		}

		if ($empty) {
			return null;
		}

		return $row;
	}

	private function getFirstLastName($row) {
		$result = array(
			'first_name' => '',
			'last_name' => ''
		);

		// Look for an instagram username
		if ($row['ig_username'] != '') {
			$result['first_name'] = $row['ig_username'];
		}
		else {
			// Get the first username we can find
			if ($row['ig_link'] != '') {
				$result['first_name'] = $this->getUsernameFromUrl($row['ig_link']);
			}
			elseif ($row['vine'] != '') {
				$result['first_name'] = $this->getUsernameFromUrl($row['vine']);
			}
			elseif ($row['twitter'] != '') {
				$result['first_name'] = $this->getUsernameFromUrl($row['twitter']);
			}
			elseif ($row['facebook'] != '') {
				$result['first_name'] = $this->getUsernameFromUrl($row['facebook']);
			}
			elseif ($row['youtube'] != '') {
				$result['first_name'] = $this->getUsernameFromUrl($row['youtube']);
			}
			elseif ($row['pinterest'] != '') {
				$result['first_name'] = $this->getUsernameFromUrl($row['pinterest']);
			}

			// If we still don't have one generate a random name
			if ($result['first_name'] == '') {
				$result['first_name'] = $this->getRandomString(8);
			}
		}

		return $result;
	}

	private function generateRandomEmail() {
		return $this->getRandomString(16) . '@random.com';
	}

	private function getRandomString($len) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $len; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
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
}
