<?php

require_once('BaseAdminController.php');
require_once(APPPATH . 'third_party/SendMail_SMTP.php');

class InfluencerEdit extends BaseAdminController
{
	public function init() {
		$this->forceSSL();

		$this->setActiveMenuItem('influencers');
	}

	public function index()
	{
		$get = $this->input->get();

		if(!isset($get['id'])) {
			redirect('/influencers');
		}

		$email = $get['id'];

		$this->load->model('profile_model');
		$this->load->model('user_model');
		$this->load->model('social_model');
		$this->load->model('social_public_model');
		$this->load->model('social_history_model');

		$this->social_model->setUser($email);
		$this->social_public_model->setUser($email);
		$this->social_history_model->setUser($email);

		$user    = $this->user_model->getUser($email);
		$profile = $this->profile_model->getProfile($email);
		$social  = $this->social_model->getNetworks();
		$socialPublic = $this->social_public_model->getNetworks();
		$socialHistory = $this->social_history_model->getNetworks();

		// Set up the URLs to the user's social pages and determine the profile pic
		$profilePic = '';
		foreach ($social as $name => &$account) {
			if (is_array($account)) {
				foreach ($account as &$s) {
					if (isset($s['connected']) && $s['connected'] == true) {
						switch ($name) {
							case 'facebook': {
								$s['anchor'] = "http://facebook.com/" . $s['id'];

								if (empty($profilePic) && isset($s['profile']['picture']['url'])) {
									$profilePic = $s['profile']['picture']['url'];
								}
								break;
							}
							case 'twitter': {
								if (isset($s['profile']['screen_name'])) {
									$s['anchor'] = "https://twitter.com/" . $s['profile']['screen_name'];
								}

								if (empty($profilePic) && isset($s['profile']['profile_image_url'])) {
									$profilePic = $s['profile']['profile_image_url'];
								}
								break;
							}
							case 'instagram': {
								if (isset($s['profile']['username'])) {
									$s['anchor'] = "http://instagram.com/" . $s['profile']['username'];
								}

								if (empty($profilePic) && isset($s['profile']['profile_picture'])) {
									$profilePic = $s['profile']['profile_picture'];
								}
								break;
							}
							case 'vine': {
								if (isset($s['profile']['shareUrl'])) {
									$s['anchor'] = $s['profile']['shareUrl'];
								}

								if (empty($profilePic) && isset($s['profile']['avatarUrl'])) {
									$profilePic = $s['profile']['avatarUrl'];
								}
								break;
							}
							case 'foursquare': {
								$s['anchor'] = "http://foursquare.com/user/" . $s['id'];
								break;
							}
							case 'youtube': {
								if (isset($s['channels'])) {
									foreach ($s['channels'] as &$channel) {
										$channel['anchor'] = "https://www.youtube.com/channel/" . $channel['id'];
									}
								}
								else {
									// for legacy support
									$s['anchor'] = "https://www.youtube.com/channel/" . $s['id'];
								}

								if (empty($profilePic) && isset($s['picture'])) {
									$profilePic = $s['picture'];
								}
								break;
							}
							case 'googleplus': {
								$s['anchor'] = "https://plus.google.com/{$s['id']}/posts";
								break;
							}
							/*
							case 'tumblr': {

								break;
							}
							case 'wordpress': {

								break;
							}
							case 'linkedin': {

								break;
							}
							*/
						}
					}
				}
			}
		}

		if ($profilePic == '') {
			foreach ($socialPublic as $name => &$account) {
				if (is_array($account)) {
					foreach ($account as &$s) {
						if (isset($s['connected']) && $s['connected'] == true) {
							switch ($name) {
								case 'facebook': {
									if (empty($profilePic) && isset($s['profile']['picture']['url'])) {
										$profilePic = $s['profile']['picture']['url'];
									}
									break;
								}
								case 'twitter': {
									if (empty($profilePic) && isset($s['profile']['profile_image_url'])) {
										$profilePic = $s['profile']['profile_image_url'];
									}
									break;
								}
								case 'instagram': {
									if (empty($profilePic) && isset($s['profile']['profile_picture'])) {
										$profilePic = $s['profile']['profile_picture'];
									}
									break;
								}
								case 'vine': {
									if (empty($profilePic) && isset($s['profile']['avatarUrl'])) {
										$profilePic = $s['profile']['avatarUrl'];
									}
									break;
								}
								case 'foursquare': {
									break;
								}
								case 'youtube': {
									if (isset($s['channels'])) {
										foreach ($s['channels'] as &$channel) {
											$channel['anchor'] = "https://www.youtube.com/channel/" . $channel['id'];
										}
									}
									else {
										// for legacy support
										$s['anchor'] = "https://www.youtube.com/channel/" . $s['id'];
									}

									if (empty($profilePic) && isset($s['picture'])) {
										$profilePic = $s['picture'];
									}
									break;
								}
								case 'googleplus': {
									break;
								}
								/*
								case 'tumblr': {

									break;
								}
								case 'wordpress': {

									break;
								}
								case 'linkedin': {

									break;
								}
								*/
							}
						}
					}
				}
			}
		}

		if (isset($profile['tags']) && count($profile['tags']) > 0) {
			$profile['tags_str'] = join(',', $profile['tags']);
		}

		$this->smarty->assign('user', $user);
		$this->smarty->assign('profile', $profile);
		$this->smarty->assign('social', $social);
		$this->smarty->assign('socialPublic', $socialPublic);
		$this->smarty->assign('socialHistory', $socialHistory);
		$this->smarty->assign('profilePic', $profilePic);

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

		$actualTotalReach = $this->social_model->getTotalReach();
		$actualTotalReach += $this->social_public_model->getTotalReach();
		$estTotalReach = $this->social_public_model->getEstTotalReach();
		$totalReach = $actualTotalReach + $estTotalReach;

		$this->smarty->assign('actualTotalReach', $actualTotalReach);
		$this->smarty->assign('estTotalReach', $estTotalReach);
		$this->smarty->assign('totalReach', $totalReach);
		$this->smarty->assign('socialConnectedCount', $this->social_model->getConnectedCount());
		$this->smarty->assign('socialPublicConnectedCount', $this->social_public_model->getCount());

		$this->smarty->assign('allTags', $this->getAllTags());

		$this->load->model('notes_model');
		$notes = iterator_to_array($this->notes_model->getUserNotes($user['email']));
		$this->smarty->assign('notes', $notes);
		$this->smarty->assign('adminName', $this->session->userdata('name'));

		$this->smarty->display('influencer_edit.html');
	}

	public function saveProfile() {
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
				$value = new MongoDate(strtotime($value));
			}

			if (is_numeric($value)) {
				if ((int)$value == $value) {
					$value = (int)$value;
				}
				else {
					$value = (float)$value;
				}
			}

			if (is_array($value)) {
				$newValues = array();
				foreach ($value as $key2 => $value2) {
					$newValues[$value2] = true;
				}
				$post[$key] = $newValues;
			}
		}

		$this->load->model('profile_model');
		$this->profile_model->addOrUpdateProfile($post['email'], $post);

		redirect("/influenceredit?id=" . $post['email']);
	}

	/*
	public function saveSocialIds() {
		$this->load->model('social_model');

		$data = $this->input->post();
		$this->social_model->setUser($data['email']);
		unset($data['email']);

		$cleanData = array();
		foreach ($data as $key => $value) {
			$temp = explode('_', $key);
			$cleanData[$temp[0] . '.' . $temp[1]] = $value;
		}

		$this->social_model->updateUserNames($cleanData);

		redirect($_SERVER['HTTP_REFERER']);
	}
	*/

	public function updateSocial() {
		$response = array('success' => true);

		$this->load->library('Aggregation');
		$this->aggregation->setUser($this->input->post('email'));
		$this->aggregation->aggregate(false, true);

		echo(json_encode($response));
	}

	public function saveNote($exit = true) {
		$response = array('success' => true);
		$post = $this->input->post();

		$this->load->model('notes_model');
		$id = $this->notes_model->addOrUpdateNote($post['noteId'], $post['email'], $post['markup'], $this->session->userdata('name'));
		$response['id'] = (string)$id;

		if ($exit) {
			echo(json_encode($response));
			exit();
		}
	}

	public function deleteNote() {
		$response = array('success' => true);
		$post = $this->input->post();

		$this->load->model('notes_model');
		$result = $this->notes_model->deleteNote($post['noteId']);

		echo(json_encode($response));
		exit();
	}

	public function sendInvite() {
		$email = $this->input->post('email');
		$text = $this->input->post('text');

		$response = array('success' => true);

		$this->sendInviteEmail($email, $text);

		$response['email'] = $email;

		// Update the user doc with the timestamp of when this invite was sent
		$this->load->model('user_model');
		$user = $this->user_model->getUser($email);
		$user['invite_sent_date'] = new MongoDate();
		$this->user_model->updateFull($user);

		/*
		$this->load->model('user_model');
		$token = $this->user_model->setUserPwResetToken($email);
		if ($token != null) {
			$this->sendInviteEmail($email, $token);

			$response['email'] = $email;

			// Update the user doc with the timestamp of when this invite was sent
			$user = $this->user_model->getUser($email);
			$user['invite_sent_date'] = new MongoDate();
			$this->user_model->updateFull($user);
		}
		else {
			$response['success'] = false;
		}
		*/

		echo(json_encode($response));
		exit();
	}

	public function getInviteEmailText() {
		$email = $this->input->get('email');

		$this->load->model('user_model');
		$token = $this->user_model->setUserPwResetToken($email);

		$email = "Recently you created an Instafluence profile that is now accessible for you to view! Follow the steps below to use this new tool to be invited to upcoming brand promotions:\n\n1. Set a password - Click this link to set you password: ";
		$email .= $this->config->item('base_url_ssl') . "setpassword/?t=$token\n\n";
		$email .= "2. Connect Your Accounts - This shows you your total influential reach and tracks your social growth. *The more accounts you connect the more promotional opportunities you have\n\n3. Complete Your Profile - The more complete your profile, the better chance you have of receiving brand opportunities from Instafluence\n\nSoon we will have features to help you keep track of current campaigns, payment history, as well as show you cool stats about your profile. Going forward all of our campaigns will be run through our new platform and only influencers with profiles will receive invites to campaigns.\n\n";
		$email .= "Thanks,\n\nMichael Gordon\nInfluencer Coordinator\nmichael.gordon@instafluence.com\n(509) 868-3118\n" . $this->config->base_url();

		$emailHtml = str_replace("\n", "<br/>", $email);

		echo(json_encode(array('text' => $email, 'html' => $emailHtml)));
		exit();
	}

	private function sendInviteEmail($toEmail, $text) {
		$html = str_replace("\n", "<br/>", $text);

		$from = "support@instafluence.com";
		$fromName = "Instafluence";

		$mailer = new SendMail_SMTP();
		$mailer->sendEmail($toEmail, '', "Instafluence Profile", $html, $text, $from, $fromName);
	}

	public function addPublicNetwork($exit = true) {
		$response = array('success' => true);

		try {
			$post = $this->input->post();
			if (!isset($post['email']) || !isset($post['network']) || !isset($post['url_or_username'])) {
				throw new Exception('Missing input.');
			}

			$this->load->model('social_public_model');
			$this->social_public_model->setUser($post['email']);

			$network = $post['network'];
			$this->load->library("APIs/$network");
			$network = strtolower($post['network']);
			if (!method_exists($this->{$network}, 'getId') || !method_exists($this->{$network}, 'getPublicProfileForDB')) {
				throw new Exception("getPublicProfile not implemented for network: " . $post['network']);
			}

			$id = $this->{$network}->getId($post['url_or_username']);
			if ($id == null) {
				throw new Exception($post['network'] . " user not found: " . $post['url_or_username']);
			}

			if ($id == 'unimplemented') {
				$id = $post['url_or_username'];
			}

			$publicProfile = $this->{$network}->getPublicProfileForDB($id);

			if (isset($post['rate']))
				$publicProfile['price'] = $post['rate'];

			if ($publicProfile == null) {
				throw new Exception($post['network'] . " profile not found for user: " . $post['url_or_username']);
			}

			$this->social_public_model->addOrUpdateAccount($publicProfile, $network);
		}
		catch (Exception $e) {
			$response['success'] = false;
			$response['msg'] = "An exception has occurred: " . $e->getMessage();
		}

		if ($exit) {
			echo(json_encode($response));
			exit();
		}
	}

	public function removePublicNetwork() {
		$response = array('success' => true);

		$post = $this->input->post();
		if (!isset($post['email']) || !isset($post['network']) || !isset($post['account_index'])) {
			$response['success'] = false;
			$response['msg'] = 'Missing input.';
			echo(json_encode($response));
			exit();
		}

		$this->load->model('social_public_model');
		$this->social_public_model->setUser($post['email']);
		$this->social_public_model->remove($post['network'], $post['account_index']);

		$this->load->model('social_public_history_model');
		$this->social_public_history_model->setUser($post['email']);
		$this->social_public_history_model->remove($post['network'], $post['account_index']);

		echo(json_encode($response));
		exit();
	}

	public function getAllTags() {
		$this->load->model('tags_model');
		$tags = $this->tags_model->getAllTags();
		$tags = join(',', $tags);
		return $tags;
	}

	public function saveTags() {
		$post = $this->input->post();
		if (!isset($post['tags'])) {
			$post['tags'] = array();
		}

		// Save the tags
		$this->load->model('tags_model');
		$this->tags_model->save($post['tags']);

		if (isset($post['email'])) {
			// Save the tags to the user's profile
			$this->load->model('profile_model');
			$this->profile_model->setTags($post['email'], $post['tags']);
		}
	}

	public function savePrice() {
		$post = $this->input->post();

		if ($post['type'] == 'social') {
			$this->load->model('social_model');
			$this->social_model->setUser($post['email']);
			$this->social_model->savePrice($post['network'], $post['accountIndex'], $post['price']);
		}
		else {
			$this->load->model('social_public_model');
			$this->social_public_model->setUser($post['email']);
			$this->social_public_model->savePrice($post['network'], $post['accountIndex'], $post['price']);
		}
	}

	public function saveEstFollowers() {
		$post = $this->input->post();

		$this->load->model('social_public_model');
		$this->social_public_model->setUser($post['email']);
		$this->social_public_model->saveEstFollowers($post['network'], $post['accountIndex'], $post['est_followers']);
	}

	public function changeEmail() {
		$post = $this->input->post();
		$post['oldEmail'] = trim($post['oldEmail']);
		$post['newEmail'] = trim($post['newEmail']);

		$response = array('success' => true);
		if (empty($post['oldEmail']) || empty($post['newEmail'])) {
			$response['success'] = false;
			$response['msg'] = "Missing input.";
			echo(json_encode($response));
			exit();
		}

		// Validate email format
		$this->load->helper('email');
		if (!valid_email($post['newEmail'])) {
			$response['success'] = false;
			$response['msg'] = "Invlid email address.";
			echo(json_encode($response));
			exit();
		}

		$this->load->model('user_model');
		if ($this->user_model->emailInUse($post['newEmail'])) {
			$response['success'] = false;
			$response['msg'] = "That email is already in use by another user.";
			echo(json_encode($response));
			exit();
		}

		$models = array(
			'notes_model',
			'profile_model',
			'social_model',
			'social_history_model',
			'social_public_model',
			'social_public_history_model',
			'tags_model',
			'user_model'
		);

		foreach ($models as $model) {
			$this->load->model($model);
			if (method_exists($this->{$model}, 'changeEmail')) {
				$this->{$model}->changeEmail($post['oldEmail'], $post['newEmail']);
			}
		}

		echo(json_encode($response));
		exit();
	}

	public function deleteInfluencer() {
		$email = $this->input->post('email');
		if ($email != null) {
			$this->load->model('user_model');
			$this->user_model->deleteUser($email);
		}

		echo(json_encode(array('success' => true)));
		exit();
	}
}
