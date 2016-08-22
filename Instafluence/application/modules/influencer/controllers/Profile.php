<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('BaseInfluencerController.php');

class Profile extends BaseInfluencerController
{
	public function init()
	{
		$this->forceSSL();
		
		$this->setActiveMenuItem('profile');
	}

	public function index()
	{
		$this->smarty->assign('newAccountIndex', '');
		if (isset($_GET['ai'])) {
			$this->smarty->assign('newAccountIndex', $_GET['ai']);
		}
		
		$this->load->model('social_model');
		$this->social_model->setUser($this->user['email']);
		
		$totalReach = $this->social_model->getTotalReach();
		$connectedCount = $this->social_model->getConnectedCount();
		$connectedNetworks = $this->social_model->getConnectedNetworkNames();

		// $this->smarty->assign('email', urlencode($this->user['email']));
		$this->smarty->assign('totalReach', $totalReach);
		$this->smarty->assign('connectedCount', $connectedCount);
		$this->smarty->assign('connectedNetworks', $connectedNetworks);
		
		$social = $this->social_model->getNetworks();
		$this->smarty->assign('social', $social);
		
		$this->load->model('profile_model');
		$profile = $this->profile_model->getProfile($this->user['email']);
		if ($profile !== null) {
			$this->smarty->assign('profile', $profile);
		}
		
		$this->load->model('payouts_model');
		$payouts = $this->payouts_model->getDoc();
		$thisPayouts = array();
		foreach ($social as $name => $s) {
			if (isset($payouts[$name]) && is_array($s) && isset($s['followers'])) {
				$index = (int)floor($s['followers'] / 1000000);
				$thisPayouts[$name] = $payouts[$name]['schedule'][$index] * $s['followers'] / 1000;
			}
		}
		$this->smarty->assign('payouts', $thisPayouts);
		
		$this->smarty->assign('hobbies', Profile_model::$HOBBIES);
		$this->smarty->assign('phoneTypes', Profile_model::$PHONE_TYPES);
		$this->smarty->assign('musicGenres', Profile_model::$MUSIC_GENRES);
		$this->smarty->assign('petTypes', Profile_model::$PET_TYPES);
		$this->smarty->assign('educationTypes', Profile_model::$EDUCATION_TYPES);
		$this->smarty->assign('tiers', Profile_model::$TIERS);
		$this->smarty->assign('promotionTypes', Profile_model::$PROMOTION_TYPES);
		$this->smarty->assign('contentRatings', Profile_model::$CONTENT_RATINGS);
		$this->smarty->assign('contentCategories', Profile_model::$CONTENT_CATEGORIES);
		$this->smarty->assign('heightOptions', Profile_model::$HEIGHT_OPTIONS);
		$this->smarty->assign('shirtSizes', Profile_model::$SHIRT_SIZES);
		$this->smarty->assign('enthicityTypes', Profile_model::$ETHNICITY_TYPES);
		$this->smarty->assign('eyeColors', Profile_model::$EYE_COLORS);
		$this->smarty->assign('hairColors', Profile_model::$HAIR_COLORS);
		$this->smarty->assign('countries', Profile_model::$COUNTRIES);

		$this->smarty->display('profile/index.html');
	}

	public function logout()
	{
		$this->load->library('Authenticate');
		$this->authenticate->logout();

		redirect('/');
	}

	public function saveProfile() {
		$response = array('success' => true);
		
		$post = $this->input->post();
		
		// Normalize the input
		foreach ($post as $key => &$value) {
			if ($value === 'true' || $value === 'on') {
				$value = true;
			}
			
			if ($value === 'false' || $value === 'off') {
				$value = false;
			}
			
			if ($key === 'dob') {
				$value = new MongoDate(strtotime($value['year'] . '/' . $value['month'] . '/' . $value['day']));
			}
			
			if (is_numeric($value)) {
				if (is_float($value + 1)) {
					$value = (float)$value;
				}
				else {
					$value = (int)$value;
				}
			}
			
			if (is_array($value)) {
				foreach ($value as &$value2) {
					if ($value2 === 'true' || $value2 === 'on') {
						$value2 = true;
					}

					if ($value2 === 'false' || $value2 === 'off') {
						$value2 = false;
					}
				}
			}
		}
		
		$this->load->model('profile_model');
		$this->profile_model->setUser($this->user['email']);
		
		if ($this->profile_model->profileCompleted()) {
			$response['showFinish'] = false;
		}
		else {
			$response['showFinish'] = true;
		}
		
		$this->profile_model->addOrUpdateProfile($this->user['email'], $post);
		
		//redirect('/account/?m=finish');
		
		echo(json_encode($response));
		exit();
	}
	
	public function saveInitialTotalFollowers() {
		$response = array('success' => true);
		
		$itf = $this->input->post('initial_total_followers');
		
		// Strip commas
		$itf = str_replace(',', '', $itf);
		
		// Make sure it's a number
		if (!is_numeric($itf)) {
			$response['success'] = false;
			$response['msg'] = 'Invalid value for initial total followers.';
			echo(json_encode($response));
			return;
		}
		
		$this->load->model('profile_model');
		$this->profile_model->updateInitalTotalFollowers($this->user['email'], $itf);
		
		$response['formattedNumber'] = number_format($itf, 0, '.', ',');

		echo(json_encode($response));
		return;
	}
}