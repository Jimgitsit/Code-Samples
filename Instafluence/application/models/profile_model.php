<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author Jason Maurer <jason@builtbyhq.com>
 */

require_once('BaseModel.php');

class Profile_model extends BaseModel
{
	public static $HOBBIES = array(
		'art' => 'Art',
		'movies' => 'Movies',
		'individual_sports' => 'Individual Sports (Tennis, Golf, etc.)',
		'fitness' => 'Fitness',
		'cooking' => 'Cooking',
		'photography' => 'Photography',
		'tv' => 'Watching TV',
		'writing' => 'Writing',
		'hunting' => 'Hunting / Fishing',
		'clothing' => 'Clothing / Fashon',
		'team_sports' => 'Team Sports (Basketball, Football, etc.)',
		'extreme_sports'=> 'Extreme Sports (Skateboarding, Snowboarding, etc.)',
		'travel' => 'Travel',
		'reading' => 'Reading',
		'dancing' => 'Dancing',
		'crafts' => 'Crafts',
		'outdoor_recreation' => 'Outdoor Recreation (Hiking, Rock Climbing, etc.)'
	);

	public static $PHONE_TYPES = array(
		'iPhone' => 'iPhone',
		'Android' => 'Android',
		'Windows' => 'Windows',
		'Other' => 'Other'
	);

	public static $MUSIC_GENRES = array(
		'pop' => 'Pop',
		'rap' => 'Rap',
		'rock' => 'Rock',
		'country' => 'Country',
		'dance' => 'Dance',
		'indie' => 'Indie',
		'alternative' => 'Alternative',
		'classical' => 'Classical',
		'electronic' => 'Electronic',
		'greatest_hits' => 'Greatest Hits',
		'metal' => 'Metal',
		'jazz' => 'Jazz',
		'rb' => 'R & B',
		'reggae' => 'Reggae'
	);

	public static $PET_TYPES = array(
		'dog' => 'Dog',
		'cat' => 'Cat',
		'other' => 'Other'
	);

	public static $EDUCATION_TYPES = array(
		'Some High School' => 'Some High School',
		'High School Graduate or Equivalent' => 'High School Graduate or Equivalent',
		'Trade or Vocational Degree' => 'Trade or Vocational Degree',
		'Some College' => 'Some College',
		'Associate Degree' => 'Associate Degree',
		"Bachelor's Degree" => "Bachelor's Degree",
		'Graduate or Professional Degree' => 'Graduate or Professional Degree',
		'Prefer not to answer' => 'Prefer not to answer'
	);

	public static $TIERS = array(
		'1' => 'Tier 1',
		'2' => 'Tier 2',
		'3' => 'Tier 3',
		'4' => 'Tier 4'
	);

	public static $PROMOTION_TYPES = array(
		'entertainment' => 'Entertainment / Events',
		'movies' => 'Movies',
		'food' => 'Food',
		'electronics' => 'Electronics / Technology',
		'animals' => 'Pets / Animals',
		'clothing' => 'Clothing / Fashon',
		'sports' => 'Sports / Fitness',
		'apps' => 'Games / Apps',
		'beauty' => 'Beauty Products',
		'travel' => 'Travel'
	);

	public static $CONTENT_RATINGS = array(
		'G' => 'G',
		'PG' => 'PG',
		'PG-13' => 'PG-13',
		'R' => 'R'
	);

	public static $CONTENT_CATEGORIES = array(
		'comedy' => 'Comedy',
		'sports' => 'Sports',
		'art' => 'Art',
		'music' => 'Music',
		'dance' => 'Dance',
		'travel' => 'Travel',
		'beauty' => 'Beauty',
		'clothing' => 'Clothing',
		'food' => 'Food',
		'apps' => 'Apps',
		'animals' => 'Animals',
		'lifestyle' => 'Lifestyle'
	);

	public static $HEIGHT_OPTIONS = array(
		'57' => "4'9\"",
		'58' => "4'10\"",
		'59' => "4'11\"",
		'60' => "5'0\"",
		'61' => "5'1\"",
		'62' => "5'2\"",
		'63' => "5'3\"",
		'64' => "5'4\"",
		'65' => "5'5\"",
		'66' => "5'6\"",
		'67' => "5'7\"",
		'68' => "5'8\"",
		'69' => "5'9\"",
		'70' => "5'10\"",
		'71' => "5'11\"",
		'72' => "6'0\"",
		'73' => "6'1\"",
		'74' => "6'2\"",
		'75' => "6'3\"",
		'76' => "6'4\"",
		'77' => "6'5\"",
		'78' => "6'6\"",
		'79' => "6'7\"",
		'80' => "6'8\"",
		'81' => "6'9\"",
		'82' => "6'10\"",
		'83' => "6'11\""
	);

	public static $SHIRT_SIZES = array(
		'XXS' => 'XXS',
		'XS' => 'XS',
		'S' => 'S',
		'M' => 'M',
		'L' => 'L',
		'XL' => 'XL',
		'2XL' => '2XL',
		'3XL' => '3XL',
	);

	public static $ETHNICITY_TYPES = array(
		'Caucasian' => 'Caucasian',
		'African American/Black' => 'African American/Black',
		'Hispanic/Latino' => 'Hispanic/Latino',
		'Asian' => 'Asian',
		'Middle Eastern' => 'Middle Eastern',
		'Pacific Islander' => 'Pacific Islander',
		'Native American/Alaskan' => 'Native American/Alaskan',
		'Other' => 'Other'
	);

	public static $EYE_COLORS = array(
		'Amber' => 'Amber',
		'Blue' => 'Blue',
		'Brown' => 'Brown',
		'Green' => 'Green',
		'Gray' => 'Gray',
		'Hazel' => 'Hazel',
		'Red and Violet' => 'Red and Violet'
	);

	public static $HAIR_COLORS = array(
		'Black' => 'Black',
		'Brown' => 'Brown',
		'Blond' => 'Blond',
		'Auburn' => 'Auburn',
		'Chestnet' => 'Chestnet',
		'Red' => 'Red',
		'Gray/White' => 'Gray/White',
	);

	public static $COUNTRIES = array(
		'United States' => 'United States',
		'United Kingdom' => 'United Kingdom',
		'Australia' => 'Australia',
		'Canada' => 'Canada',
		'France' => 'France',
		'New Zealand' => 'New Zealand',
		'India' => 'India',
		'Brazil' => 'Brazil',
		'-' => '----',
		'Afghanistan' => 'Afghanistan',
		'Åland Islands' => 'Åland Islands',
		'Albania' => 'Albania',
		'Algeria' => 'Algeria',
		'American Samoa' => 'American Samoa',
		'Andorra' => 'Andorra',
		'Angola' => 'Angola',
		'Anguilla' => 'Anguilla',
		'Antarctica' => 'Antarctica',
		'Antigua and Barbuda' => 'Antigua and Barbuda',
		'Argentina' => 'Argentina',
		'Armenia' => 'Armenia',
		'Aruba' => 'Aruba',
		'Austria' => 'Austria',
		'Azerbaijan' => 'Azerbaijan',
		'Bahamas' => 'Bahamas',
		'Bahrain' => 'Bahrain',
		'Bangladesh' => 'Bangladesh',
		'Barbados' => 'Barbados',
		'Belarus' => 'Belarus',
		'Belgium' => 'Belgium',
		'Belize' => 'Belize',
		'Benin' => 'Benin',
		'Bermuda' => 'Bermuda',
		'Bhutan' => 'Bhutan',
		'Bolivia' => 'Bolivia',
		'Bosnia and Herzegovina' => 'Bosnia and Herzegovina',
		'Botswana' => 'Botswana',
		'British Indian Ocean Territory' => 'British Indian Ocean Territory',
		'Brunei Darussalam' => 'Brunei Darussalam',
		'Bulgaria' => 'Bulgaria',
		'Burkina Faso' => 'Burkina Faso',
		'Burundi' => 'Burundi',
		'Cambodia' => 'Cambodia',
		'Cameroon' => 'Cameroon',
		'Cape Verde' => 'Cape Verde',
		'Cayman Islands' => 'Cayman Islands',
		'Central African Republic' => 'Central African Republic',
		'Chad' => 'Chad',
		'Chile' => 'Chile',
		'China' => 'China',
		'Colombia' => 'Colombia',
		'Comoros' => 'Comoros',
		'Democratic Republic of the Congo' => 'Democratic Republic of the Congo',
		'Republic of the Congo' => 'Republic of the Congo',
		'Cook Islands' => 'Cook Islands',
		'Costa Rica' => 'Costa Rica',
		"C&ocirc;te d'Ivoire" => "C&ocirc;te d'Ivoire",
		'Croatia' => 'Croatia',
		'Cuba' => 'Cuba',
		'Cyprus' => 'Cyprus',
		'Czech Republic' => 'Czech Republic',
		'Denmark' => 'Denmark',
		'Djibouti' => 'Djibouti',
		'Dominica' => 'Dominica',
		'Dominican Republic' => 'Dominican Republic',
		'East Timor' => 'East Timor',
		'Ecuador' => 'Ecuador',
		'Egypt' => 'Egypt',
		'El Salvador' => 'El Salvador',
		'Equatorial Guinea' => 'Equatorial Guinea',
		'Eritrea' => 'Eritrea',
		'Estonia' => 'Estonia',
		'Ethiopia' => 'Ethiopia',
		'Faroe Islands' => 'Faroe Islands',
		'Fiji' => 'Fiji',
		'Finland' => 'Finland',
		'Gabon' => 'Gabon',
		'Gambia' => 'Gambia',
		'Georgia' => 'Georgia',
		'Germany' => 'Germany',
		'Ghana' => 'Ghana',
		'Gibraltar' => 'Gibraltar',
		'Greece' => 'Greece',
		'Grenada' => 'Grenada',
		'Guatemala' => 'Guatemala',
		'Guinea' => 'Guinea',
		'Guinea-Bissau' => 'Guinea-Bissau',
		'Guyana' => 'Guyana',
		'Haiti' => 'Haiti',
		'Honduras' => 'Honduras',
		'Hong Kong' => 'Hong Kong',
		'Hungary' => 'Hungary',
		'Iceland' => 'Iceland',
		'Indonesia' => 'Indonesia',
		'Iran' => 'Iran',
		'Iraq' => 'Iraq',
		'Ireland' => 'Ireland',
		'Israel' => 'Israel',
		'Italy' => 'Italy',
		'Jamaica' => 'Jamaica',
		'Japan' => 'Japan',
		'Jordan' => 'Jordan',
		'Kazakhstan' => 'Kazakhstan',
		'Kenya' => 'Kenya',
		'Kiribati' => 'Kiribati',
		'North Korea' => 'North Korea',
		'South Korea' => 'South Korea',
		'Kuwait' => 'Kuwait',
		'Kyrgyzstan' => 'Kyrgyzstan',
		'Laos' => 'Laos',
		'Latvia' => 'Latvia',
		'Lebanon' => 'Lebanon',
		'Lesotho' => 'Lesotho',
		'Liberia' => 'Liberia',
		'Libya' => 'Libya',
		'Liechtenstein' => 'Liechtenstein',
		'Lithuania' => 'Lithuania',
		'Luxembourg' => 'Luxembourg',
		'Macedonia' => 'Macedonia',
		'Madagascar' => 'Madagascar',
		'Malawi' => 'Malawi',
		'Malaysia' => 'Malaysia',
		'Maldives' => 'Maldives',
		'Mali' => 'Mali',
		'Malta' => 'Malta',
		'Marshall Islands' => 'Marshall Islands',
		'Mauritania' => 'Mauritania',
		'Mauritius' => 'Mauritius',
		'Mexico' => 'Mexico',
		'Micronesia' => 'Micronesia',
		'Moldova' => 'Moldova',
		'Monaco' => 'Monaco',
		'Mongolia' => 'Mongolia',
		'Montenegro' => 'Montenegro',
		'Morocco' => 'Morocco',
		'Mozambique' => 'Mozambique',
		'Myanmar' => 'Myanmar',
		'Namibia' => 'Namibia',
		'Nauru' => 'Nauru',
		'Nepal' => 'Nepal',
		'Netherlands' => 'Netherlands',
		'Netherlands Antilles' => 'Netherlands Antilles',
		'Nicaragua' => 'Nicaragua',
		'Niger' => 'Niger',
		'Nigeria' => 'Nigeria',
		'Norway' => 'Norway',
		'Oman' => 'Oman',
		'Pakistan' => 'Pakistan',
		'Palau' => 'Palau',
		'Palestine' => 'Palestine',
		'Panama' => 'Panama',
		'Papua New Guinea' => 'Papua New Guinea',
		'Paraguay' => 'Paraguay',
		'Peru' => 'Peru',
		'Philippines' => 'Philippines',
		'Poland' => 'Poland',
		'Portugal' => 'Portugal',
		'Puerto Rico' => 'Puerto Rico',
		'Qatar' => 'Qatar',
		'Romania' => 'Romania',
		'Russia' => 'Russia',
		'Rwanda' => 'Rwanda',
		'Saint Kitts and Nevis' => 'Saint Kitts and Nevis',
		'Saint Lucia' => 'Saint Lucia',
		'Saint Vincent and the Grenadines' => 'Saint Vincent and the Grenadines',
		'Samoa' => 'Samoa',
		'San Marino' => 'San Marino',
		'Sao Tome and Principe' => 'Sao Tome and Principe',
		'Saudi Arabia' => 'Saudi Arabia',
		'Senegal' => 'Senegal',
		'Serbia' => 'Serbia',
		'Seychelles' => 'Seychelles',
		'Sierra Leone' => 'Sierra Leone',
		'Singapore' => 'Singapore',
		'Slovakia' => 'Slovakia',
		'Slovenia' => 'Slovenia',
		'Solomon Islands' => 'Solomon Islands',
		'Somalia' => 'Somalia',
		'South Africa' => 'South Africa',
		'Spain' => 'Spain',
		'Sri Lanka' => 'Sri Lanka',
		'Sudan' => 'Sudan',
		'Suriname' => 'Suriname',
		'Swaziland' => 'Swaziland',
		'Sweden' => 'Sweden',
		'Switzerland' => 'Switzerland',
		'Syria' => 'Syria',
		'Taiwan' => 'Taiwan',
		'Tajikistan' => 'Tajikistan',
		'Tanzania' => 'Tanzania',
		'Thailand' => 'Thailand',
		'Togo' => 'Togo',
		'Tonga' => 'Tonga',
		'Trinidad and Tobago' => 'Trinidad and Tobago',
		'Tunisia' => 'Tunisia',
		'Turkey' => 'Turkey',
		'Turkmenistan' => 'Turkmenistan',
		'Tuvalu' => 'Tuvalu',
		'Uganda' => 'Uganda',
		'Ukraine' => 'Ukraine',
		'United Arab Emirates' => 'United Arab Emirates',
		'United States Minor Outlying Islands' => 'United States Minor Outlying Islands',
		'Uruguay' => 'Uruguay',
		'Uzbekistan' => 'Uzbekistan',
		'Vanuatu' => 'Vanuatu',
		'Vatican City' => 'Vatican City',
		'Venezuela' => 'Venezuela',
		'Vietnam' => 'Vietnam',
		'Virgin Islands, British' => 'Virgin Islands, British',
		'Virgin Islands, U.S.' => 'Virgin Islands, U.S.',
		'Yemen' => 'Yemen',
		'Zambia' => 'Zambia',
		'Zimbabwe' => 'Zimbabwe'
	);

	public static $STATES = array(
		'AL'=>'Alabama',
		'AK'=>'Alaska',
		'AZ'=>'Arizona',
		'AR'=>'Arkansas',
		'CA'=>'California',
		'CO'=>'Colorado',
		'CT'=>'Connecticut',
		'DE'=>'Delaware',
		'DC'=>'District of Columbia',
		'FL'=>'Florida',
		'GA'=>'Georgia',
		'HI'=>'Hawaii',
		'ID'=>'Idaho',
		'IL'=>'Illinois',
		'IN'=>'Indiana',
		'IA'=>'Iowa',
		'KS'=>'Kansas',
		'KY'=>'Kentucky',
		'LA'=>'Louisiana',
		'ME'=>'Maine',
		'MD'=>'Maryland',
		'MA'=>'Massachusetts',
		'MI'=>'Michigan',
		'MN'=>'Minnesota',
		'MS'=>'Mississippi',
		'MO'=>'Missouri',
		'MT'=>'Montana',
		'NE'=>'Nebraska',
		'NV'=>'Nevada',
		'NH'=>'New Hampshire',
		'NJ'=>'New Jersey',
		'NM'=>'New Mexico',
		'NY'=>'New York',
		'NC'=>'North Carolina',
		'ND'=>'North Dakota',
		'OH'=>'Ohio',
		'OK'=>'Oklahoma',
		'OR'=>'Oregon',
		'PA'=>'Pennsylvania',
		'RI'=>'Rhode Island',
		'SC'=>'South Carolina',
		'SD'=>'South Dakota',
		'TN'=>'Tennessee',
		'TX'=>'Texas',
		'UT'=>'Utah',
		'VT'=>'Vermont',
		'VA'=>'Virginia',
		'WA'=>'Washington',
		'WV'=>'West Virginia',
		'WI'=>'Wisconsin',
		'WY'=>'Wyoming'
	);

	protected $email = null;
	protected $doc = null;

	public function init()
	{
		$this->setCollection('profile');
	}

	public function setUser($email) {
		$this->email = $email;
		$this->doc = $this->collection->findOne(array('email' => $email));
	}

	public function profileCompleted() {
		if (!isset($this->doc)) {
			throw new Exception('User not set');
		}

		return $this->doc['completion'];
	}

	/********************************
	USED FOR DATA IMPORT (TEMPORARY)
	*********************************/
	/*
	public function insert($user)
	{
		$test = $this->collection->findOne(array('email' => $user['email']));
		if ($test === null) {
			$this->collection->insert($user);
		}
		else {
			$this->collection->update(array('email' => $user['email']), $user);
		}
		//$this->collection->save($user);
	}
	*/

	/**
	 * @param $email
	 */
	public function create($email)
	{
		if (empty($email)) {
			return;
		}
		// A profile must have an email
		$email = trim($email);
		if($email == '') {
			return;
		}

		// TODO: This should use the arrays above for categories etc.
		$data = array(
			'email' => $email,
			'dob' => null,
			'gender' => null,
			'address' => null,
			'address2' => null,
			'city' => null,
			'state' => null,
			'zip' => null,
			'country' => null,
			'paypal' => null,
			'phone' => null,
			'avatar' => null,
			'exclusive' => null,
			'history' => null,
			'initial_total_followers' => 0,
			'categories' => array(
				'comedy' => false,
				'sports' => false,
				'art' => false,
				'music' => false,
				'dance' => false,
				'travel' => false,
				'beauty' => false,
				'clothing' => false,
				'food' => false,
				'apps' => false,
				'animals' => false,
				'lifestyle' => false
			),
			'height' => null,
			'height_unit' => 'in',
			'shoe_size' => null,
			'dress_size' => null,
			'pant_size' => null,
			'shirt_size' => null,
			'pant_waist' => null,
			'pant_length' => null,
			'ethnicity' => null,
			'glasses' => null,
			'eye_color' => null,
			'hair_color' => null,
			'travel' => null,
			'passport' => null,
			'drivers_license' => null,
			'married' => null,
			'children' => null,
			'phone_type' => null,
			'music_genre' => array(
				'pop' => false,
				'rap' => false,
				'rock' => false,
				'country' => false,
				'dance' => false,
				'indie' => false,
				'alternative' => false,
				'classical' => false,
				'electronic' => false,
				'greatest_hits' => false,
				'metal' => false,
				'jazz' => false,
				'rb' => false,
				'reggae' => false
			),
			'pet' => null,
			'pet_type' => array(
				'dog' => false,
				'cat' => false,
				'other' => false
			),
			'education' => null,
			'drink' => null,
			'promoting' => array(
				'entertainment' => false,
				'clothing' => false,
				'movies' => false,
				'sports' => false,
				'food' => false,
				'apps' => false,
				'electronics' => false,
				'beauty' => false,
				'animals' => false,
				'travel' => false
			),
			'hobbies' => array(
				'art' => false,
				'clothing' => false,
				'movies' => false,
				'team_sports' => false,
				'individual_sports' => false,
				'extreme_sports' => false,
				'fitness' => false,
				'travel' => false,
				'cooking' => false,
				'reading' => false,
				'photography' => false,
				'dancing' => false,
				'tv' => false,
				'crafts' => false,
				'writing' => false,
				'outdoor_recreation' => false,
				'hunting' => false
			),
			'content_rating' => null,
			'brands' => null,
			'created' => new MongoDate(),
			'updated' => new MongoDate(),
			'completion' => false,
			'ip_address' => '',
			'tier' => 3,
			'cpp' => null,
			'cpe' => null,
			'cpm' => null
		);

		if (isset($_SERVER['REMOTE_ADDR'])) {
			$data['ip_address'] = $_SERVER['REMOTE_ADDR'];
		}

		$this->collection->save($data);
	}

	/**
	 * Returns an array of profile records that match the fields in $filters.
	 *
	 * @param $filters
	 */
	public function findProfiles($filters) {
		$profiles = $this->collection->find($filters);
		return $profiles;
	}

	public function getProfile($email) {
		$profile = $this->collection->findOne(
			array('email' => $email)
		);

		return $profile;
	}

	// TODO: change these to use the internal representation on the document, force use of setUser().

	public function addOrUpdateProfile($email, $data) {
		// Check to see if the profile already exists first
		$profile = $this->getProfile($email);
		if ($profile == null) {
			// Create it
			$profile = $this->create($email);
		}

		// Make sure we keep the same case for the email
		if (isset($data['email'])) {
			$profile['email'] = $data['email'];
		}

		// Have to use dot notation for nested arrays since the
		// mongo php driver does not support updating nested arrays
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				//unset($profile[$key]);

				foreach ($value as $key2 => $value2) {
					$profile[$key][$key2] = $value2;
				}
			}
			else {
				$profile[$key] = $value;
			}
		}

		$profile['updated'] = new MongoDate();

		// Update it
		//$newData = array('$set' => $data);
		$this->collection->update(array("email" => $email), $profile);
	}

	public function updateInitalTotalFollowers($email, $num) {
		$this->collection->update(array('email' => $email), array('$set' => array('initial_total_followers' => $num)));
	}

	public function setInfluencerRating($email, $rating) {
		$this->collection->update(array('email' => $email), array('$set' => array('rating' => $rating)));
	}

	public function setTags($email, $tags) {
		$this->collection->update(array('email' => $email), array('$set' => array('tags' => $tags)));
	}

	public function getTags($email) {
		$doc = $this->collection->findOne(array('email' => $email));
		if (isset($doc['tags'])) {
			return $doc['tags'];
		}

		return array();
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
}
