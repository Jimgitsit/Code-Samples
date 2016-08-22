<?php

require_once('BaseAdminController.php');

class Influencers extends BaseAdminController
{
	public function init() {
		$this->forceSSL();
		$this->setActiveMenuItem('influencers');
	}

	public function index()
	{
		// Handle saved filters
		$this->load->model('filters_model');

		$currentSavedFilter = $this->input->get('f');
		if($currentSavedFilter != null) {
			$savedFilter = $this->session->userdata('currentSavedFilter');
			//if($currentSavedFilter != $this->session->userdata('currentSavedFilter')) {
				// Load the saved filters
				$savedFilters = $this->filters_model->getSavedFilters($currentSavedFilter);
				$this->smarty->assign('filters', $savedFilters);
				$this->session->set_userdata(array('filters' => $savedFilters));
				$this->session->set_userdata(array('currentSavedFilter' => $currentSavedFilter));
			//}
			//else if($this->session->userdata('filters') != null) {
			//	$this->smarty->assign('filters', $this->session->userdata('filters'));
			//}
		}
		else {
			$this->session->unset_userdata(array('currentSavedFilter' => ''));
			if($this->session->userdata('filters') != null) {
				$this->smarty->assign('filters', $this->session->userdata('filters'));
			}
		}

		$savedFilterNames = $this->filters_model->getSavedFilterNames();

		$this->smarty->assign('savedFilterNames', $savedFilterNames);
		$this->smarty->assign('currentSavedFilter', $currentSavedFilter);

		// Tell the browser not to cache this page (especially chrome)
		header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");

		$this->load->model('profile_model');
		$this->smarty->assign('countries', Profile_model::$COUNTRIES);
		$this->smarty->assign('states', Profile_model::$STATES);

		$this->load->model('tags_model');
		$this->smarty->assign('allTags', $this->tags_model->getAllTags());

		$this->smarty->display('influencers.html');
	}

	public function exportData()
	{
		set_time_limit(0);

		$response = json_decode($this->getDataNew(true, 0));

		$csv = '';
		$values = array();
		foreach ($_POST as $key => $value)
		{
			if ($value == 'on') {
				$csv .= $key.',';
				array_push($values, $key);
			}
		}
		$csv = rtrim($csv, ',');
		$csv .= "\n";

		function getValue($value, $user, $social, $social_public, $profile)
		{
			switch($value)
			{
				case 'Name':
					return $user['first_name'] . " " . $user['last_name'];
					break;
				case 'Email':
					return $user['email'];
					break;
				case 'PayPal':
					if (isset($profile['paypal']))
						return $profile['paypal'];
					else
						return '';
					break;
				case 'Vine_Link':
					if (isset($social['networks']['vine'][0]['profile']['shareUrl']))
						return $social['networks']['vine'][0]['profile']['shareUrl'];
					elseif (isset($social_public['networks']['vine'][0]['profile']['shareUrl']))
						return $social_public['networks']['vine'][0]['profile']['shareUrl'];
					else
						return '';
					break;
				case 'Vine_Following':
					if (isset($social['networks']['vine'][0]['followers']))
						return $social['networks']['vine'][0]['followers'];
					elseif (isset($social_public['networks']['vine'][0]['followers']))
						return $social_public['networks']['vine'][0]['followers'];
					else
						return '';
					break;
				case 'Vine_Price':
					if (isset($social['networks']['vine'][0]['price']))
						return $social['networks']['vine'][0]['price'];
					elseif (isset($social_public['networks']['vine'][0]['price']))
						return $social_public['networks']['vine'][0]['price'];
					else
						return '';
					break;
				case 'Instagram_Link':
					if (isset($social['networks']['instagram'][0]['username']))
						return 'https://instagram.com/'.$social['networks']['instagram'][0]['username'];
					elseif (isset($social_public['networks']['instagram'][0]['username']))
						return 'https://instagram.com/'.$social_public['networks']['instagram'][0]['username'];
					else
						return '';
					break;
				case 'Instagram_Following':
					if (isset($social['networks']['instagram'][0]['followers']))
						return $social['networks']['instagram'][0]['followers'];
					elseif (isset($social_public['networks']['instagram'][0]['followers']))
						return $social_public['networks']['instagram'][0]['followers'];
					else
						return '';
					break;
				case 'Instagram_Price':
					if (isset($social['networks']['instagram'][0]['price']))
						return $social['networks']['instagram'][0]['price'];
					elseif (isset($social_public['networks']['instagram'][0]['price']))
						return $social_public['networks']['instagram'][0]['price'];
					else
						return '';
					break;
				case 'Twitter_Link':
					if (isset($social['networks']['twitter'][0]['username']))
						return 'https://twitter.com/'.$social['networks']['twitter'][0]['username'];
					elseif (isset($social_public['networks']['twitter'][0]['username']))
						return 'https://twitter.com/'.$social_public['networks']['twitter'][0]['username'];
					else
						return '';
					break;
				case 'Twitter_Following':
					if (isset($social['networks']['twitter'][0]['followers']))
						return $social['networks']['twitter'][0]['followers'];
					elseif (isset($social_public['networks']['twitter'][0]['followers']))
						return $social_public['networks']['twitter'][0]['followers'];
					else
						return '';
					break;
				case 'Twitter_Price':
					if (isset($social['networks']['twitter'][0]['price']))
						return $social['networks']['twitter'][0]['price'];
					elseif (isset($social_public['networks']['twitter'][0]['price']))
						return $social_public['networks']['twitter'][0]['price'];
					else
						return '';
					break;
			}
		};

		$this->load->model('user_model');
		$this->load->model('social_model');
		$this->load->model('profile_model');

		foreach ($response->data as $profile)
		{
			$user = $this->user_model->getUser($profile->email);
			$social = $this->social_model->getSocial($profile->email);
			$social_public = $this->social_public_model->getSocialPublic($profile->email);
			$profile = $this->profile_model->getProfile($profile->email);

			foreach ($values as $value)
			{
				$csv .= getValue($value, $user, $social, $social_public, $profile) . ",";
			}

			$csv = rtrim($csv, ',');
			$csv .= "\n";
		}

		$timestamp = time();
		$filename = 'instafluence-export_'.$timestamp.'.csv';
		$fh = fopen('../data/exports/'.$filename, 'w');
		fwrite($fh, $csv);
		fclose($fh);

		echo '/admin/downloadExport/'.$timestamp;
	}

	public function downloadExport($timestamp)
	{
		$filename = 'instafluence-export_'.$timestamp.'.csv';
		$file_url = '../data/exports/'.$filename;

		header('Content-Type: application/octet-stream');
		header("Content-Transfer-Encoding: Binary");
		header("Content-disposition: attachment; filename=\"" . $filename . "\"");

		readfile($file_url);
		unlink($file_url);
	}

	/**
	 * The new way.
	 *
	 * Called by the DataTable via ajax.
	 */
	public function getDataNew($return=false, $limit = null) {
		// Get the filters (either from the request or the session)

		$filters = $this->getFilters();

		if (!isset($filters['limit']))
			$filters['limit'] = 500;
		if (!isset($filters['offset']))
			$filters['offset'] = 0;

		if ($limit !== null)
			$filters['limit'] = $limit;

		/*
		$get = $this->input->get();
		$order = $get['order'][0]['column'];

		if ($order == 1) {
			// Get users first
			// Get the filtered users
			$users = $this->getUsers('first_name');
			usort($users, array($this, 'compareEmail2'));
		}
		*/

		// Get the filtered profiles
		$profiles = $this->getProfiles($filters);
		usort($profiles, array($this, 'compareEmail2'));

		$users = $this->getUsers();
		usort($users, array($this, 'compareEmail2'));

		// Get the filters social networks
		$social = $this->getSocial($filters);
		usort($social, array($this, 'compareEmail2'));

		// Get the filtered social public networks
		$socialPublic = $this->getSocialPublic($filters);
		usort($socialPublic, array($this, 'compareEmail2'));

		// Form the json response
		$response = $this->createDataTableResponse($filters, $users, $profiles, $social, $socialPublic, $filters['limit'], $filters['offset']);

		if ($return !== false) {
			return $response;
		} else {
			echo($response);
			exit();
		}
	}

	private function createDataTableResponse($filters, $users, $profiles, $social, $socialPublic, $maxResults=500, $offset=0) {
		$this->load->model('social_model');
		$this->load->model('social_public_model');
		$this->load->model('social_history_model');

		$tableData = array();

		$totalInfluencers = 0;
		$totalReach = 0;
		foreach($profiles as $index => $profile) {
			$data = array();

			$userIndex = $this->binarySearchArray($users, 0, count($users) - 1, $profile['email'], 'Influencers::compareEmail');
			if ($userIndex === null) {
				// Profile is filtered out
				continue;
			}

			$socialIndex = $this->binarySearchArray($social, 0, count($social) - 1, $profile['email'], 'Influencers::compareEmail');
			$socialPublicIndex = $this->binarySearchArray($socialPublic, 0, count($socialPublic) - 1, $profile['email'], 'Influencers::compareEmail');

			if (isset($filters['socialFilters'])) {
				if ($socialIndex === null && $socialPublicIndex === null) {
					// Social filtered out
					continue;
				}
			}

			$data['email'] = $profile['email'];
			$data['name'] = $users[$userIndex]['first_name'] . ' ' . $users[$userIndex]['last_name'];
			$data['rating'] = 0;
			if (isset($profile['rating'])) {
				$data['rating'] = $profile['rating'];
			}

			$reach = 0;
			// Total reach from ALL social networks
			$this->social_model->setUser($profile['email']);
			$socialAll = $this->social_model->getNetworks(false, false);
			foreach ($socialAll as $name => $network) {
				if (is_array($network)) {
					foreach ($network as $accountIndex => $account) {
						if (isset($account['connected']) && $account['connected'] == true) {
							if (isset($filters['socialFilters']) && isset($filters['onlySelectedAccountFollowers']) && $filters['onlySelectedAccountFollowers'] == 'true') {
								if (array_key_exists($name, $filters['socialFilters'])) {
									$reach += $this->social_model->getReach($name, $accountIndex, $account);
								}
							}
							else {
								$reach += $this->social_model->getReach($name, $accountIndex, $account);
							}
						}
					}
				}
			}

			// Total reach from public social networks
			$this->social_public_model->setUser($profile['email']);
			$socialPublicAll = $this->social_public_model->getNetworks(false, false);
			foreach ($socialPublicAll as $name => $network) {
				if (is_array($network)) {
					foreach ($network as $accountIndex => $account) {
						if (isset($account['connected']) && $account['connected'] == true) {
							if (isset($filters['socialFilters']) && isset($filters['onlySelectedAccountFollowers']) && $filters['onlySelectedAccountFollowers'] == 'true') {
								if (array_key_exists($name, $filters['socialFilters'])) {
									$reach += $this->social_public_model->getReach($name, $accountIndex, $account);
									$reach += $this->social_public_model->getEstReach($name, $accountIndex, $account);
								}
							}
							else {
								$reach += $this->social_public_model->getReach($name, $accountIndex, $account);
								$reach += $this->social_public_model->getEstReach($name, $accountIndex, $account);
							}
						}
					}
				}
			}

			if (isset($filters['followers'])) {
				if ($reach < $filters['followers']['min'] || $reach > $filters['followers']['max']) {
					continue;
				}
			}

			$data['followers'] = $reach;
			$totalReach += $data['followers'];

			$this->social_history_model->setUser($profile['email']);
			$socialHistory = $this->social_history_model->getDoc();
			if (isset($socialHistory['networks']['growth_rate_week_avg']) && isset($socialHistory['networks']['growth_rate_week_avg_percent'])) {
				//$avgDiff = round($socialHistory['growth_rate_week_avg'], 2);
				$avgPercent = round($socialHistory['networks']['growth_rate_week_avg_percent'], 0);
				// We don't want to see -0
				if ($avgPercent == 0) {
					$avgPercent = abs($avgPercent);
				}
				$data['delta_count'] = $avgPercent;
			}
			else {
				// -1000 means an unknown quantity (we use -1000 so sorting works properly)
				$data['delta_count'] = -1000;
			}

			$socialNetworks = $this->social_model->getConnectedNetworkNames();
			$socialPublicNetworks = $this->social_public_model->getConnectedNetworkNames();
			$data['social'] = array_merge($socialNetworks, $socialPublicNetworks);

			$data['created'] = date('m/d/Y', $profile['created']->sec);

			$tableData[] = $data;
			$totalInfluencers++;
		}

		// Sort the results
		$get = $this->input->get();
		$sortBy = $get['columns'][$get['order'][0]['column']]['data'];
		$direction = $get['order'][0]['dir'];
		$tableData = $this->sortTableData($tableData, $sortBy, $direction);
		//$tableData = array_slice($tableData, $get['start'], $get['length']);

		// $maxResults = 500;
		$truncated = false;
		if (count($tableData) > $maxResults && $maxResults != 0) {
			$tableData = array_slice($tableData, $offset, $maxResults);
			$truncated = true;
		}

		// Format the json response
		$response = array(
			'total_influencers' => $totalInfluencers,
			'total_reach' => $totalReach,
			'data' => $tableData,
			'truncated' => $truncated,
			'max_results' => $maxResults,
			'results_offset' => $offset
			//'draw' => intval($get['draw']),
			//'recordsTotal' => $totalInfluencers,
			//'recordsFiltered' => $totalInfluencers
		);

		$response = json_encode($response);
		return $response;
	}

	private static function compareEmail($a, $b) {
		return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
	}

	private static function compareEmail2($a, $b) {
		return ($a['email'] < $b['email']) ? -1 : (($a['email'] > $b['email']) ? 1 : 0);
	}

	private function binarySearchCursor($cursor, $first, $last, $key, $compare) {
		$lo = $first;
		$hi = $last;

		while ($lo <= $hi) {
			$mid = (int)(($hi - $lo) / 2) + $lo;
			$cursor->reset();
			$cursor->skip($mid);
			$cmp = call_user_func($compare, $cursor->getNext()['email'], $key);

			if ($cmp < 0) {
				$lo = $mid + 1;
			}
			elseif ($cmp > 0) {
				$hi = $mid - 1;
			}
			else {
				// Found
				return $mid;
			}
		}

		// Not found
		//return -($lo + 1);
		return null;
	}

	private function binarySearchArray($array, $first, $last, $key, $compare) {
		$lo = $first;
		$hi = $last;

		while ($lo <= $hi) {
			$mid = (int)(($hi - $lo) / 2) + $lo;
			$cmp = call_user_func($compare, $array[$mid]['email'], $key);

			if ($cmp < 0) {
				$lo = $mid + 1;
			}
			elseif ($cmp > 0) {
				$hi = $mid - 1;
			}
			else {
				// Found
				return $mid;
			}
		}

		// Not found
		//return -($lo + 1);
		return null;
	}

	public function getProfiles($filters, $sortBy = null) {
		$profileFilters = array();
		$projections = array('email' => 1, 'initial_total_followers' => 1, 'created' => 1, 'rating' => 1);
		if (isset($filters['profileFilters'])) {
			$this->load->model('profile_model');
			$profileFilters['$and'] = array();
			foreach ($filters['profileFilters'] as $name => $value) {
				$projections[$name] = 1;

				// Country should be case insensitive
				if ($name == 'country') {
					$newValue = array();
					foreach ($value as $v) {
						$exp = preg_quote($v);
						$newValue[] = new MongoRegex("/^.*$exp.*$/i");
					}
					$value = $newValue;
				}

				// State can be ether full name or abbreviation and case insensitive.
				if ($name == 'state') {
					$newValue = array();
					foreach ($value as $v) {
						$v = trim($v);
						$exp = preg_quote($v);
						$newValue[] = new MongoRegex("/^.*$exp.*$/i");
						$newValue[] = new MongoRegex("/^.*" . Profile_model::$STATES[$v] . ".*$/i");
					}
					$value = $newValue;
				}

				// Zip comes in as comma deliminated, need to convert to an array
				if ($name == 'zip' && !is_array($value)) {
					$value = explode(',', $value);
					$newValue = array();
					foreach ($value as $v) {
						$v = trim($v);
						$newValue[] = $v;
						$newValue[] = (int)$v;
					}
					$value = $newValue;
				}

				if($name == 'age') {

					// Create the projection for "age"
					$projections['age'] = array(
						'$divide' => array(
							array(
								'$multiply' => array(
									-1,
									array(
										'$subtract' => array(
											'$dob',
											new MongoDate()
										)
									)
								)
							),
							31556952000
						)
					);

					$profileFilters['$and'][] = array('age' => array('$gte' => (int)$value['min']));
					$profileFilters['$and'][] = array('age' => array('$lte' => (int)$value['max']));
				}
				else if (is_array($value) && isset($value['min']) && isset($value['max'])) {
					// RANGE
					$profileFilters['$and'][] = array($name => array('$gte' => (float)$value['min'], '$lte' => (float)$value['max']));
				}
				// Check if it is an assoc array
				else if (is_array($value) && $value !== array_values($value)) {
					// MULTI
					$orFilter = array('$or' => array());
					foreach ($value as $key => $val) {
						if($val == "true" || $val == "false") {
							$val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
						}
						$orFilter['$or'][] = array($name.'.'.$key => $val);
					}
					$profileFilters['$and'][] = $orFilter;
				}
				// Numbered array
				else if (is_array($value)) {
					// MULTI
					$orFilter = array('$or' => array());
					foreach ($value as $val) {
						if($val == "true" || $val == "false") {
							$val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
						}

						if(is_numeric($val) && $name != 'zip') {
							$val = (double)$val;
						}
						$orFilter['$or'][] = array($name => $val);
					}
					$profileFilters['$and'][] = $orFilter;
				}
				else {
					// SINGLE
					if($value == "true" || $value == "false") {
						$value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
					}

					if(is_numeric($value)) {
						$value = (double)$value;
					}

					$profileFilters['$and'][] = array($name => $value);
				}
			}
		}

		$query = array(
			//array('$skip' => intval($get['skip'])),
			//array('$limit' => 50),
			//array('$sort' => array( $sortBy => $direction))
		);
		$match = null;
		if (count($projections) > 0) {
			$project = array('$project' => array());
			foreach ($projections as $key => $value) {
				$project['$project'][$key] = $value;
			}
			$query[] = $project;
		}
		if (count($profileFilters) > 0) {
			$match = array('$match' => $profileFilters);
		}

		if ($match) {
			$query[] = $match;
		}

		if (!empty($sortBy)) {
			$query[]['$sort'] = array( $sortBy => 1);
		}

		//$test = json_encode($query, JSON_PRETTY_PRINT);
		$this->load->model('profile_model');
		$profiles = $this->profile_model->getCollection()->aggregate($query);

		return $profiles['result'];
	}

	/**
	 * Find all users given the search term.
	 *
	 * @return mixed
	 */
	private function getUsers($sortBy = null) {
		$get = $this->input->get();

		$query = array(array('$match' => array(
				'$and' => array(
					array('account_type' => 1)
				)
			)
		));

		if (isset($get['searchTerm']))
			$searchTerm = trim($get['searchTerm']);
		else
			$searchTerm = '';

		if (!empty($searchTerm)) {
			if (strstr($searchTerm, ' ')) {
				// Probably a first and last name
				$name = explode(' ', $searchTerm);

				//$exp = preg_quote($searchTerm);
				$firstName = preg_quote($name[0]);
				$lastName = preg_quote($name[1]);
				$rFirst = new MongoRegex("/^$firstName$/i");
				$rLast = new MongoRegex("/^$lastName.*$/i");

				// Handle search on user doc
				$query[0]['$match']['$and'] = array();
				$query[0]['$match']['$and'][]['first_name'] = array('$regex' => $rFirst);
				$query[0]['$match']['$and'][]['last_name'] = array('$regex' => $rLast);
			}
			else {
				// Search by any of first name, last name, or email
				$exp = preg_quote($searchTerm);
				$regex = new MongoRegex("/^.*$exp.*$/i");

				// Handle search on user doc
				$query[0]['$match']['$or'] = array();
				$query[0]['$match']['$or'][]['first_name'] = array('$regex' => $regex);
				$query[0]['$match']['$or'][]['last_name'] = array('$regex' => $regex);
				$query[0]['$match']['$or'][]['email'] = array('$regex' => $regex);
			}
		}

		if (!empty($sortBy)) {
			$query[1]['$sort'] = array( $sortBy => 1);
			//$query[2]['$limit'] = 500;
		}

		$this->load->model('user_model');
		$users = $this->user_model->getCollection()->aggregate($query);

		return $users['result'];
	}

	private function getSocial($filters, $sortBy = null) {
		$this->load->model('social_model');


		$socialFilters = array('$match' => array());
		$socialFilters['$match'] = array('$and' => array());


		// if (isset($filters['socialFilters']['instagram_price'])) {
		// 	// $socialFilters['$match']['networks.instagram.price'] = array('$exists'=>true);
		// 	$socialFilters['$project']['$exists'][] = 'networks.instagram.price';
		// 	// $socialFilters['$ifNull'][] = array('networks.instagram.price'=>false);

			// $socialFilter['$match']['$and'][]
			// unset($filters['socialFilters']['instagram_price']);
		// }

		// {"$match":{"$and":[{"$or":[{"networks.instagram.connected":true}]}],"networks.instagram.price":{"$gt":10}}}



		if (isset($filters['socialFilters'])) {
			$index = count($socialFilters['$match']['$and']);
			$socialFilters['$match']['$and'][$index]['$or'] = array();
			foreach ($filters['socialFilters'] as $name => $value) {
				$socialFilters['$match']['$and'][$index]['$or'][] = array('networks.' . $name . '.connected' => true);

				if (isset($filters['onlyWithPrice']) && $filters['onlyWithPrice'] == 'true') {
					$socialFilters['$match']['$and'][$index+1]['$and'][] = array('networks.' . $name . '.price' => array('$exists'=>true, '$gt'=>0));
				}
			}

			if (empty($socialFilters['$match']['$and'][$index]['$or']))
				unset($socialFilters['$match']['$and'][$index]);
		}

		$get = $this->input->get();
		if (!empty($get['searchTerm'])) {
			// Handle username search
			$exp = preg_quote($get['searchTerm']);
			$regex = new MongoRegex("/^.*$exp.*$/i");

			$networks = $this->social_model->getAllNetworkNames();
			$index = count($socialFilters['$match']['$and']);
			$socialFilters['$match']['$and'][$index]['$or'] = array();
			foreach ($networks as $network) {
				$socialFilters['$match']['$and'][$index]['$or'][]['networks.' . $network . '.username'] = array('$regex' => $regex);
			}
		}

		if (empty($socialFilters['$match']['$and'])) {
			unset($socialFilters['$match']['$and']);
		}

		if (!empty($sortBy)) {
			$socialFilters[]['$sort'] = array( $sortBy => 1);
		}

		if (count($socialFilters['$match']) == 0) {
			unset($socialFilters['$match']);
		}


// echo json_encode($socialFilters);die();
		$this->load->model('social_model');
		$social = $this->social_model->getCollection()->aggregate($socialFilters);

		return $social['result'];
	}

	private function getSocialPublic($filters, $sortBy = null) {
		$this->load->model('social_public_model');

		$socialFilters = array('$match' => array());
		$socialFilters['$match'] = array('$and' => array());
		if (isset($filters['socialFilters'])) {
			$index = count($socialFilters['$match']['$and']);
			$socialFilters['$match']['$and'][$index]['$or'] = array();
			foreach ($filters['socialFilters'] as $name => $value) {
				$socialFilters['$match']['$and'][$index]['$or'][] = array('networks.' . $name . '.connected' => true);

				if (isset($filters['onlyWithPrice']) && $filters['onlyWithPrice'] == 'true') {
					$socialFilters['$match']['$and'][$index+1]['$and'][] = array('networks.' . $name . '.price' => array('$exists'=>true, '$gt'=>0));
				}
			}
		}

		$get = $this->input->get();
		if (!empty($get['searchTerm'])) {
			// Handle username search
			$exp = preg_quote($get['searchTerm']);
			$regex = new MongoRegex("/^.*$exp.*$/i");

			$networks = $this->social_public_model->getAllNetworkNames();
			$index = count($socialFilters['$match']['$and']);
			$socialFilters['$match']['$and'][$index]['$or'] = array();
			foreach ($networks as $network) {
				$socialFilters['$match']['$and'][$index]['$or'][]['networks.' . $network . '.username'] = array('$regex' => $regex);
			}
		}

		if (empty($socialFilters['$match']['$and'])) {
			unset($socialFilters['$match']['$and']);
		}

		if (!empty($sortBy)) {
			$socialFilters[]['$sort'] = array( $sortBy => 1);
		}

		if (count($socialFilters['$match']) == 0) {
			unset($socialFilters['$match']);
		}

		$this->load->model('social_model');
		$social = $this->social_public_model->getCollection()->aggregate($socialFilters);

		return $social['result'];
	}

	private function getFilters() {
		$get = $this->input->get();

		$filters = null;
		if(isset($get['filters'])) {
			$filters = $get['filters'];
			if(isset($filters['isReset']) && $filters['isReset'] == "true") {
				$this->session->unset_userdata(array('filters' => ''));
			}
			else if (!isset($filters['isApply'])) {
				if($this->session->userdata('filters') != null) {
					$filters = $this->session->userdata('filters');
				}
			}
			else {
				$this->session->set_userdata(array('filters' => $filters));
			}
		}
		else {
			if($this->session->userdata('filters') != null) {
				$filters = $this->session->userdata('filters');
			}
		}

		return $filters;
	}

	/*
	 * The old way...
	 *
	public function getData() {
		// Load the models that we will need
		$this->load->model('profile_model');
		$this->load->model('user_model');
		$this->load->model('social_model');
		$this->load->model('social_public_model');
		$this->load->model('social_history_model');

		$get = $this->input->get();

		// Get the filters
		$filters = null;
		if(isset($get['filters'])) {
			$filters = $get['filters'];
			if(isset($filters['isReset']) && $filters['isReset'] == "true") {
				$this->session->unset_userdata(array('filters' => ''));
			}
			else if (!isset($filters['isApply'])) {
				if($this->session->userdata('filters') != null) {
					$filters = $this->session->userdata('filters');
				}
			}
			else {
				$this->session->set_userdata(array('filters' => $filters));
			}
		}
		else {
			if($this->session->userdata('filters') != null) {
				$filters = $this->session->userdata('filters');
			}
		}

		// Parse the profile filters
		$profileFilters = array();
		$projections = array('email' => 1, 'initial_total_followers' => 1, 'created' => 1, 'rating' => 1);
		if (isset($filters['profileFilters'])) {
			$profileFilters['$and'] = array();
			foreach ($filters['profileFilters'] as $name => $value) {
				$projections[$name] = 1;

				// Country should be case insensitive
				if ($name == 'country') {
					$newValue = array();
					foreach ($value as $v) {
						$exp = preg_quote($v);
						$newValue[] = new MongoRegex("/^$exp/i");
					}
					$value = $newValue;
				}

				// State can be ether full name or abbreviation and case insensitive.
				if ($name == 'state') {
					$newValue = array();
					foreach ($value as $v) {
						$v = trim($v);
						$exp = preg_quote($v);
						$newValue[] = new MongoRegex("/^$v/i");
						$newValue[] = new MongoRegex("/^" . Profile_model::$STATES[$v] . "/i");
					}
					$value = $newValue;
				}

				// Zip comes in as comma deliminated, need to convert to an array
				if ($name == 'zip' && !is_array($value)) {
					$value = explode(',', $value);
					$newValue = array();
					foreach ($value as $v) {
						$v = trim($v);
						$newValue[] = $v;
						$newValue[] = (int)$v;
					}
					$value = $newValue;
				}

				if($name == 'age') {

					// Create the projection for "age"
					$projections['age'] = array(
						'$divide' => array(
							array(
								'$multiply' => array(
									-1,
									array(
										'$subtract' => array(
											'$dob',
											new MongoDate()
										)
									)
								)
							),
							31556952000
						)
					);

					$profileFilters['$and'][] = array('age' => array('$gte' => (int)$value['min']));
					$profileFilters['$and'][] = array('age' => array('$lte' => (int)$value['max']));
				}
				else if (is_array($value) && isset($value['min']) && isset($value['max'])) {
					// RANGE
					$profileFilters['$and'][] = array($name => array('$gte' => (float)$value['min'], '$lte' => (float)$value['max']));
				}
				// Check if it is an assoc array
				else if (is_array($value) && $value !== array_values($value)) {
					// MULTI
					$orFilter = array('$or' => array());
					foreach ($value as $key => $val) {
						if($val == "true" || $val == "false") {
							$val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
						}
						$orFilter['$or'][] = array($name.'.'.$key => $val);
					}
					$profileFilters['$and'][] = $orFilter;
				}
				// Numbered array
				else if (is_array($value)) {
					// MULTI
					$orFilter = array('$or' => array());
					foreach ($value as $val) {
						if($val == "true" || $val == "false") {
							$val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
						}

						if(is_numeric($val) && $name != 'zip') {
							$val = (double)$val;
						}
						$orFilter['$or'][] = array($name => $val);
					}
					$profileFilters['$and'][] = $orFilter;
				}
				else {
					// SINGLE
					if($value == "true" || $value == "false") {
						$value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
					}

					if(is_numeric($value)) {
						$value = (double)$value;
					}

					$profileFilters['$and'][] = array($name => $value);
				}
			}
		}

		// Parse the social filters
		$socialFilters = array('$and' => array());
		if (isset($filters['socialFilters'])) {
			$socialFilters['$and'][0]['$or'] = array();
			foreach ($filters['socialFilters'] as $name => $value) {
				$socialFilters['$and'][0]['$or'][] = array('networks.' . $name . '.connected' => true);
			}
		}

		// Set user filters (only need to set account type for now)
		$userFilters    = array();
		$userFilters['account_type'] = User_model::$ACCOUNT_TYPE['influencer'];

		// Get the collections we will need to query
		$profileCol = $this->profile_model->getCollection();
		$userCol    = $this->user_model->getCollection();
		$socialCol  = $this->social_model->getCollection();
		$socialPublicCol = $this->social_public_model->getCollection();
		$socialHistoryCol  = $this->social_history_model->getCollection();

		/*
		$sortBy = $get['columns'][$get['order'][0]['column']]['data'];
		$direction = $get['order'][0]['dir'];
		if ($direction == 'asc') {
			$direction = 1;
		}
		else {
			$direction = -1;
		}
		*

		// Get the filtered profiles
		$query = array(
			//array('$skip' => intval($get['skip'])),
			//array('$limit' => 50),
			//array('$sort' => array( $sortBy => $direction))
		);
		$match = null;
		if (count($projections) > 0) {
			$project = array('$project' => array());
			foreach ($projections as $key => $value) {
				$project['$project'][$key] = $value;
			}
			$query[] = $project;
		}
		if (count($profileFilters) > 0) {
			$match = array('$match' => $profileFilters);
		}

		if ($match) {
			$query[] = $match;
		}

		//$test = json_encode($query, JSON_PRETTY_PRINT);
		$profiles = $profileCol->aggregate($query);

		// Form the table data
		$totalInfluencers = 0;
		$totalReach = 0;
		$tableData = array();
		$profiles = $profiles['result'];
		foreach($profiles as $profile) {
			if (!isset($profile['email'])) {
				// TODO: Why does this echo?
				//log_message('debug', "Got profile with no email: " . var_export($profile));
				continue;
			}

			// User filters for this user
			$thisUserFilters = $userFilters;
			$thisUserFilters['email'] = $profile['email'];

			// Social filters for this user
			$thisSocialFilters = $socialFilters;
			$thisSocialFilters['$and'][]['email'] = $profile['email'];

			if (!empty($get['searchTerm'])) {
				$exp = preg_quote($get['searchTerm']);
				$regex = new MongoRegex("/^$exp/i");

				// Handle search on user doc
				$thisUserFilters['$or'] = array();
				$thisUserFilters['$or'][]['first_name'] = array('$regex' => $regex);
				$thisUserFilters['$or'][]['last_name'] = array('$regex' => $regex);
				$thisUserFilters['$or'][]['email'] = array('$regex' => $regex);

				// Handle username search
				$networks = $this->social_model->getAllNetworkNames();
				$thisSocialFilters['$and'][1]['$or'] = array();
				foreach ($networks as $network) {
					$thisSocialFilters['$and'][1]['$or'][]['networks.' . $network . '.username'] = array('$regex' => $regex);
				}
			}

			// Apply the filters to get the user and social docs
			$userFilteredOut = false;
			$socialFilteredOut = false;

			$user = $userCol->findOne($thisUserFilters);
			if ($user == null) {
				// User was filtered out
				$userFilteredOut = true;
			}

			$social = $socialCol->findOne($thisSocialFilters);
			$socialPublic = $socialPublicCol->findOne($thisSocialFilters);
			if ($social == null && $socialPublic == null) {
				// Social requirements were filtered out
				$socialFilteredOut = true;
			}

			if ($userFilteredOut && $socialFilteredOut) {
				continue;
			}

			else {
				$thisUserFilters = $userFilters;
				$thisUserFilters['email'] = $profile['email'];
				$user = $userCol->findOne($thisUserFilters);

				$thisSocialFilters = $socialFilters;
				$thisSocialFilters['$and'][]['email'] = $profile['email'];
				$social = $socialCol->findOne($thisSocialFilters);
				$socialPublic = $socialPublicCol->findOne($thisSocialFilters);
			}

			if ($user == null || ($social == null && $socialPublic == null)) {
				continue;
			}


			// User has passed all filters so far
			$data = array();
			$data['name'] = $user['first_name'] . ' ' . $user['last_name'];
			$data['email'] = $user['email'];
			if (isset($profile['created']->sec)) {
				$data['created'] = date('m/d/Y', $profile['created']->sec);
			}
			else {
				$data['created'] = '?';
			}

			//$data['following'] = 0;
			$data['followers'] = 0;
			//$data['total_posts'] = 0;
			$data['social'] = array();

			$socialNetworksConnected = 0;

			// Get totals for social networks
			if ($social != null) {
				foreach ($social['networks'] as $name => $network) {

					if (is_array($network)) {

						foreach ($network as $index => $account) {
							if (isset($account['connected']) && $account['connected'] == true) {
								$socialNetworksConnected++;

								if (!in_array($name, $data['social'])) {
									$data['social'][] = $name;
								}

								if (!empty($account['followers'])) {
									if (isset($filters['socialFilters']) && isset($filters['onlySelectedAccountFollowers']) && $filters['onlySelectedAccountFollowers'] == 'true') {
										if (array_key_exists($name, $filters['socialFilters'])) {
											$data['followers'] += $this->social_model->getReach($name, $index, $account);
										}
									}
									else {
										$data['followers'] += $this->social_model->getReach($name, $index, $account);
									}
								}

								// TODO: Followers and post are not updated correctly but are not used anywhere so maybe get rid of them
								/*
								if (!empty($account['following'])) {
									$data['following'] += $account['following'];
								}

								if (!empty($account['posts'])) {
									$data['total_posts'] += $account['posts'];
								}
								*
							}
						}
					}
				}
			}

			// Get totals for public social networks
			if ($socialPublic != null) {
				foreach ($socialPublic['networks'] as $name => $network) {

					if (is_array($network)) {

						foreach ($network as $index => $account) {
							if (isset($account['connected']) && $account['connected'] == true) {
								$socialNetworksConnected++;

								if (!in_array($name, $data['social'])) {
									$data['social'][] = $name;
								}

								//if (!empty($account['followers'])) {
									if (isset($filters['socialFilters']) && isset($filters['onlySelectedAccountFollowers']) && $filters['onlySelectedAccountFollowers'] == 'true') {
										if (array_key_exists($name, $filters['socialFilters'])) {
											if (isset($account['show_est'])) {
												$data['followers'] += $this->social_public_model->getEstReach($name, $index, $account);
											}
											else {
												$data['followers'] += $this->social_public_model->getReach($name, $index, $account);
											}
										}
									}
									else {
										if (isset($account['show_est'])) {
											$data['followers'] += $this->social_public_model->getEstReach($name, $index, $account);
										}
										else {
											$data['followers'] += $this->social_public_model->getReach($name, $index, $account);
										}
									}
								//}

								// TODO: following and post are not updated correctly but are not used anywhere so maybe get rid of them
								/*
								if (!empty($account['following'])) {
									$data['following'] += $account['following'];
								}

								if (!empty($account['posts'])) {
									$data['total_posts'] += $account['posts'];
								}
								*
							}
						}
					}
				}
			}

			// Apply min / max followers filter
			if (isset($filters['followers']['min']) && $data['followers'] < $filters['followers']['min']) {
				// User is filtered out
				continue;
			}

			if (isset($filters['followers']['max']) && $data['followers'] > $filters['followers']['max']) {
				// User is filtered out
				continue;
			}

			$totalReach += $data['followers'];

			// Get growth rate
			$socialHistory = $socialHistoryCol->findOne(array('email' => $profile['email']));
			if (isset($socialHistory['networks']['growth_rate_week_avg']) && isset($socialHistory['networks']['growth_rate_week_avg_percent'])) {
				//$avgDiff = round($socialHistory['growth_rate_week_avg'], 2);
				$avgPercent = round($socialHistory['networks']['growth_rate_week_avg_percent'], 0);
				// We don't want to see -0
				if ($avgPercent == 0) {
					$avgPercent = abs($avgPercent);
				}
				$data['delta_count'] = $avgPercent;
			}
			else {
				// -1000 means an unknown quantity (we use -1000 so sorting works properly)
				$data['delta_count'] = -1000;
			}

			// Get rating
			$data['rating'] = isset($profile['rating']) ? $profile['rating'] : 0;

			$tableData[] = $data;

			$totalInfluencers++;
		}

		// Sort the results
		$sortBy = $get['columns'][$get['order'][0]['column']]['data'];
		$direction = $get['order'][0]['dir'];
		$tableData = $this->sortTableData($tableData, $sortBy, $direction);
		$recordsTotal = count($tableData);
		//$tableData = array_slice($tableData, 0, $get['length']);

		// Format the json response
		$response = array(
			'total_influencers' => $totalInfluencers,
			'total_reach' => $totalReach,
			'data' => $tableData,
			'draw' => intval($get['draw']),
			'recordsTotal' => $recordsTotal,
			'recordsFiltered' => $recordsTotal
		);
		$response = json_encode($response);

		echo($response);
		exit();
	}
	*/

	public function saveFilterSet() {
		$response = array('success' => true, 'errorCode' => 0, 'error' => '');

		$filters = $this->input->post('filters');
		if($filters == null) {
			$response['success'] = false;
			$response['errorCode'] = 1;
			$response['error'] = "No filters to save";
			echo(json_encode($response));
			exit();
		}

		$name = $this->input->post('name');

		$this->load->model('filters_model');
		$this->filters_model->createOrUpdate($name, $filters);

		if($this->input->get('f') == null || $this->input->get('f') != $name) {
			$response['redirect'] = '/influencers/?f=' . urlencode($name);
		}

		echo(json_encode($response));
		exit();
	}

	public function saveRating() {
		$email = $this->input->post('email');
		$rating = $this->input->post('rating');

		$this->load->model('profile_model');
		$this->profile_model->setInfluencerRating($email, $rating);

		echo(json_encode(array('success' => true)));
		exit();
	}

	public function emailInUse() {
		$email = $this->input->get('email');
		$this->load->model('user_model');
		if ($this->user_model->emailInUse($email)) {
			echo 'false';
		}
		else {
			echo 'true';
		}

		exit();
	}

	public function addInfluencer($exit = true) {
		$response = array('success' => true);

		$data = $this->input->post();
		$data['password'] = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') , 0 , 10 );
// var_dump($data);die();
		$this->load->model('user_model');
		$success = $this->user_model->create($data, 'influencer');
		if ($success == false) {
			$response['success'] = false;
			$response['msg'] = 'That email address is already in use.';
		}

		if ($exit) {
			echo(json_encode($response));
			exit();
		}
	}

	private function sortTableData($tableData, $sortBy, $direction) {
		usort($tableData, function ($a, $b) use ($sortBy, $direction) {
			if (!isset($a[$sortBy]) || !isset($b[$sortBy]))
				return 0;

			if ($a[$sortBy] == $b[$sortBy]) {
				return 0;
			}
			else {
				// -1000 is an unknown quantity so always push them to the bottom
				if ($a[$sortBy] == -1000) {
					return 1;
				}
				elseif ($b[$sortBy] == -1000) {
					return -1;
				}

				if ($a[$sortBy] > $b[$sortBy]) {
					return ($direction == 'asc') ? 1 : -1;
				}
				else {
					return ($direction == 'asc') ? -1 : 1;
				}
			}
		});

		return $tableData;
	}

	public function logout()
	{
		$this->load->library('Authenticate');
		$this->authenticate->logout();

		redirect('/');
	}


}
