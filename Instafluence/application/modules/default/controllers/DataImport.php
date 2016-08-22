<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('BaseDefaultController.php');

class DataImport extends BaseDefaultController
{

	protected $table;

	public function init()
	{
		$this->load->model('profile_model');
		$this->load->model('user_model');
		$this->load->model('social_model');
		$this->load->library('Cryptography');

		$this->load->database();
	}

	/*******************************************************
		RUN THIS AFTER YOU HAVE DONE THE FOLLOWING:
		1. Comment out profile_model->create in user model
	********************************************************/

	public function index()
	{
		$this->db->select('*');
		$this->db->limit(10000);
		$this->db->offset(1);
		$this->db->from('existing_data2');

		$data = $this->db->get();
		$data = $data->result_array();

		$users = array();
		foreach ($data as $row) {			
			$user = array(
				'first_name' => mb_convert_encoding(ucfirst(strtolower(trim($row['COL 2']))), 'ISO-8859-1', 'UTF-8'),
				'last_name' => mb_convert_encoding(ucfirst(strtolower(trim($row['COL 3']))), 'ISO-8859-1', 'UTF-8'),
				'email' => strtolower(trim($row['COL 12'])),
				'password' => strtolower($this->cryptography->randomString(10))
			);
			array_push($users, array(
				'email' => $user['email'],
				'password' => $user['password']
			));

			$profile = array(
				'email' => trim($row['COL 12']),
				//'dob' => (int)date('Ymd', strtotime($row['COL 4'])),
				'dob' => new MongoDate(strtotime($row['COL 4'])),
				'gender' => ($row['COL 5'] == 'Male' ? 1 : 0),
				'address' => trim($row['COL 6']),
				'address2' => trim($row['COL 7']),
				'city' => trim($row['COL 8']),
				'state' => trim($row['COL 9']),
				'zip' => trim($row['COL 10']),
				'country' => trim($row['COL 11']),
				'paypal' => trim($row['COL 13']),
				'phone' => trim($row['COL 14']),
				'avatar' => trim($row['COL 15']),
				'exclusive' => (trim($row['COL 16']) == 'Yes' ? true : false),
				'history' => (trim($row['COL 17']) == 'Yes' ? true : false),
				'initial_total_followers' => (trim($row['COL 19']) > 0 ? (int)trim($row['COL 19']) : 0),
				'categories' => array(
					'comedy' => ($row['COL 30'] != '' ? true : false),
					'sports' => ($row['COL 31'] != '' ? true : false),
					'art' => ($row['COL 32'] != '' ? true : false),
					'music' => ($row['COL 33'] != '' ? true : false),
					'dance' => ($row['COL 34'] != '' ? true : false),
					'travel' => ($row['COL 35'] != '' ? true : false),
					'beauty' => ($row['COL 36'] != '' ? true : false),
					'clothing' => ($row['COL 37'] != '' ? true : false),
					'food' => ($row['COL 38'] != '' ? true : false),
					'apps' => ($row['COL 39'] != '' ? true : false),
					'animals' => ($row['COL 40'] != '' ? true : false)
				),
				/* Height in inches */
				'height' => (substr($row['COL 41'], 0, strpos($row['COL 41'], '\'')) * 12) +
					(substr($row['COL 41'], strpos($row['COL 41'], '\'') + 1, (strlen($row['COL 41']) - strlen(substr($row['COL 41'], 0, strpos($row['COL 41'], '\'')))-1))),
				'height_unit' => 'in',
				'shoe_size' => (float)trim($row['COL 42']),
				'dress_size' => (float)trim($row['COL 43']),
				'pant_size' => (float)trim($row['COL 44']),
				'shirt_size' => trim($row['COL 45']),
				'pant_waist' => (float)trim($row['COL 46']),
				'pant_length' => (float)trim($row['COL 47']),
				'ethnicity' => trim($row['COL 48']),
				'glasses' => (trim($row['COL 49']) == 'Yes' ? true : false),
				'eye_color' => trim($row['COL 50']),
				'hair_color' => trim($row['COL 51']),
				'travel' => (trim($row['COL 52']) == 'Yes' ? true : false),
				'passport' => (trim($row['COL 53']) == 'Yes' ? true : false),
				'drivers_license' => (trim($row['COL 54']) == 'Yes' ? true : false),
				'married' => (trim($row['COL 55']) == 'Yes' ? true : false),
				'children' => (trim($row['COL 56']) == 'Yes' ? true : false),
				'phone_type' => trim($row['COL 57']),
				'music_genre' => array(
					'pop' => ($row['COL 58'] != '' ? true : false),
					'rap' => ($row['COL 59'] != '' ? true : false),
					'rock' => ($row['COL 60'] != '' ? true : false),
					'country' => ($row['COL 61'] != '' ? true : false),
					'dance' => ($row['COL 62'] != '' ? true : false),
					'indie' => ($row['COL 63'] != '' ? true : false),
					'alternative' => ($row['COL 64'] != '' ? true : false),
					'classical' => ($row['COL 65'] != '' ? true : false),
					'electronic' => ($row['COL 66'] != '' ? true : false),
					'greatest_hits' => ($row['COL 67'] != '' ? true : false),
					'metal' => ($row['COL 68'] != '' ? true : false),
					'jazz' => ($row['COL 69'] != '' ? true : false),
					'rb' => ($row['COL 70'] != '' ? true : false),
					'reggae' => ($row['COL 71'] != '' ? true : false)
				),
				'pet' => (trim($row['COL 72']) == 'Yes' ? true : false),
				'pet_type' => array(
					'dog' => ($row['COL 73'] != '' ? true : false),
					'cat' => ($row['COL 74'] != '' ? true : false),
					'other' => ($row['COL 75'] != '' ? true : false)
				),
				'education' => trim($row['COL 76']),
				'drink' => (trim($row['COL 77']) == 'Yes' ? true : false),
				'promoting' => array(
					'entertainment' => ($row['COL 78'] != '' ? true : false),
					'clothing' => ($row['COL 79'] != '' ? true : false),
					'movies' => ($row['COL 80'] != '' ? true : false),
					'sports' => ($row['COL 81'] != '' ? true : false),
					'food' => ($row['COL 82'] != '' ? true : false),
					'apps' => ($row['COL 83'] != '' ? true : false),
					'electronics' => ($row['COL 84'] != '' ? true : false),
					'beauty' => ($row['COL 85'] != '' ? true : false),
					'animals' => ($row['COL 86'] != '' ? true : false),
					'travel' => ($row['COL 87'] != '' ? true : false)
				),
				'hobbies' => array(
					'art' => ($row['COL 88'] != '' ? true : false),
					'clothing' => ($row['COL 89'] != '' ? true : false),
					'movies' => ($row['COL 90'] != '' ? true : false),
					'team_sports' => ($row['COL 91'] != '' ? true : false),
					'individual_sports' => ($row['COL 92'] != '' ? true : false),
					'extreme_sports' => ($row['COL 93'] != '' ? true : false),
					'fitness' => ($row['COL 94'] != '' ? true : false),
					'travel' => ($row['COL 95'] != '' ? true : false),
					'cooking' => ($row['COL 96'] != '' ? true : false),
					'reading' => ($row['COL 97'] != '' ? true : false),
					'photography' => ($row['COL 98'] != '' ? true : false),
					'dancing' => ($row['COL 99'] != '' ? true : false),
					'tv' => ($row['COL 100'] != '' ? true : false),
					'crafts' => ($row['COL 101'] != '' ? true : false),
					'writing' => ($row['COL 102'] != '' ? true : false),
					'outdoor_recreation' => ($row['COL 103'] != '' ? true : false),
					'hunting' => ($row['COL 104'] != '' ? true : false)
				),
				'content_rating' => trim($row['COL 105']),
				'brands' => trim($row['COL 106']),
				//'created' => date('Y-m-d H:i:s', strtotime($row['COL 107'])),
				'created' => new MongoDate(strtotime($row['COL 107'])),
				//'updated' => date('Y-m-d H:i:s', strtotime($row['COL 109'])),
				'updated' => new MongoDate(strtotime($row['COL 109'])),
				'ip_address' => trim($row['COL 111']),
				'completion' => (trim($row['COL 113']) == '1' ? true : false),
				'tier' => 4
			);
			
			if ($profile['education'] == 'Graduate of Professional Degree') {
				$profile['education'] = 'Graduate or Professional Degree';
			}

			$this->user_model->create($user, 'influencer');
			$this->profile_model->insert($profile);
		}

		/*
		$fh = fopen(APPPATH.'../data/generated_accounts.json', 'w');
		fwrite($fh, json_encode($users));
		fclose($fh);
		*/
	}

}