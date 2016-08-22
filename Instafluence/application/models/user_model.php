<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author Jason Maurer <jason@builtbyhq.com>
 */

require_once('BaseModel.php');

class User_model extends BaseModel
{
	public static $ACCOUNT_TYPE = array(
		"influencer"    => 1,
		"client"        => 2,
		"admin"         => 3
	);

	public function init()
	{
		$this->setCollection('user');
	}

	/**
	 * @param $data
	 * @param $account_type
	 *
	 * @return id of the new record.
	 */
	public function create($data, $account_type)
	{
		try {
			// A user must have an email
			if(empty($data['email'])) {
				return null;
			}
	
			$this->load->library('Cryptography');
	
			/* Make first letter of names uppercase */
			//$data['first_name'] = ucwords($data['first_name']);
			//$data['last_name'] = ucwords($data['last_name']);
	
			/* Hash password */
			$password = $this->cryptography->hash(
				$data['password'],
				$this->config->item('encryption_key')
			);
			$data['password'] = $password['hash'];
			$data['salt'] = $password['salt'];
	
			/* Set account type */
			$data['account_type'] = $this->config->item('accounts')[$account_type]['code'];
			
			/* Value doesn't go into the database */
			unset($data['password_confirm']);
	
			$this->collection->save($data);
	
			// Create row in social collection 
			$this->load->model('social_model');
			$this->social_model->create($data['email']);
	
			// Create row in social history collection 
			$this->load->model('social_history_model');
			$this->social_history_model->create($data['email']);
	
			// Create row in profile collection 
			$this->load->model('profile_model');
			$this->profile_model->create($data['email']);
	
			return $data['_id'];
		}
		catch(Exception $e) {
			return false;
		}
	}

	/**
	 * Deletes a user and all associated documents from other collections.
	 * 
	 * TODO: Protect against wildcards in $email?
	 * 
	 * @param $email
	 */
	public function deleteUser($email) {
		if (!empty($email)) {
			$this->load->model('notes_model');
			$this->notes_model->getCollection()->remove(array('email' => $email));
			
			$this->load->model('profile_model');
			$this->profile_model->getCollection()->remove(array('email' => $email));
			
			$this->load->model('social_history_model');
			$this->social_history_model->getCollection()->remove(array('email' => $email));
			
			$this->load->model('social_model');
			$this->social_model->getCollection()->remove(array('email' => $email));
			
			$this->load->model('social_public_history_model');
			$this->social_public_history_model->getCollection()->remove(array('email' => $email));
			
			$this->load->model('social_public_model');
			$this->social_public_model->getCollection()->remove(array('email' => $email));

			$this->collection->remove(array('email' => $email));
		}
	}

	/**
	 * Helper function to determine if an email address is 
	 * already in use.
	 * 
	 * @return bool
	 */
	public function emailInUse($email) {
		$user = $this->collection->findOne(
			array('email' => $email)
		);
		
		if ($user) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Updates the full document with $data.
	 * 
	 * @param $data
	 */
	public function updateFull($data) {
		$this->collection->update(array('email' => $data['email']), $data);
	}

	/**
	 * @param string
	 *
	 * @return Array, the user.
	 */
	public function getUser($email)
	{
		$exp = preg_quote($email);
		$user = $this->collection->findOne(
			array('email' => new MongoRegex("/^.*$exp.*$/i"))
		);

		return $user;
	}
	
	public function getUserByToken($token) {
		$user = $this->collection->findOne(
			array('pw_reset_token' => $token)
		);

		return $user;
	}
	
	public function setUserPwResetToken($email) {
		$user = $this->getUser($email);
		if ($user !== null) {
			$token = uniqid("");
			$this->collection->update(array('email' => $email), array('$set' => array('pw_reset_token' => $token)));
			return $token;
		}
		else {
			return null;
		}
	}
	
	public function setUserPassword($email, $pw) {
		$data = array();
		$data['password'] = $pw;
		$this->load->library('Cryptography');
		$password = $this->cryptography->hash(
			$data['password'],
			$this->config->item('encryption_key')
		);
		
		$data['password'] = $password['hash'];
		$data['salt'] = $password['salt'];
		$data['pw_reset_token'] = "";
		
		$this->collection->update(array('email' => $email), array('$set' => $data));
	}

	/**
	 * Changes the email on all docs with $oldEmail to $newEmail.
	 * Trims the input but assumes $newEmail is a properly formatted email addresses.
	 * 
	 * // TODO: Assumes $newEmail is unique and not already used by another user. Should do a sanity check here. (this function is in several other models too)
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
}