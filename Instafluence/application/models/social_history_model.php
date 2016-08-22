<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author Jason Maurer <jason@builtbyhq.com>
 */

require_once('BaseModel.php');

class Social_history_model extends BaseModel
{
	// The current in user's email
	protected $email;

	// The entire object from the social collection for the current user
	protected $doc;
	
	public function init()
	{
		$this->setCollection('social_history');
	}

	/**
	 * Fetch user's social info from the database,
	 * and store in object for use by other methods.
	 * Also stores the user's email.
	 *
	 * @param $email
	 * 
	 * @throws Exception if setUser is not called first.
	 */
	public function setUser($email)
	{
		$this->email = $email;
		$this->doc = $this->collection->findOne(array('email' => $email));
		
		if ($this->doc == null) {
			//throw new Exception('Social_history_model->setUser: User ' . $email . ' not found.');
			$this->create($email);
		}
	}

	/**
	 * Returns the social document for the user set with setUser.
	 *
	 * @return social doc or null.
	 */
	public function getDoc()
	{
		return $this->doc;
	}

	/**
	 * Creates a new document with empty social history data.
	 *
	 * @param string
	 */
	public function create($email)
	{
		$data = array('email' => $email, 'networks' =>  array());
		
		/*
		foreach (Social_model::$networks as $network) {
			$data[$network] = array(
				'followers' => array(
					'daily' => array(),
					'monthly' => array(),
					'yearly' => array()
				),
				'following' => array(
					'daily' => array(),
					'monthly' => array(),
					'yearly' => array()
				),
				'posts' => array(
					'daily' => array(),
					'monthly' => array(),
					'yearly' => array()
				)
			);
		}
		*/

		$this->collection->save($data);
		
		// Set the current user
		$this->setUser($email);
	}

	/**
	 * Updates the social history for the current user and given network.
	 * setUser() must be called first.
	 * 
	 * @param $data
	 * @param $network
	 * @param $accountIndex
	 *
	 * @throws Exception if user is not set
	 */
	public function update($data, $network, $accountIndex)
	{
		// Ignore blogs
		if ($network == 'blog') {
			return;
		}
		
		if (!isset($this->email) || !isset($this->doc)) {
			throw new Exception('Social_history_model->update: User not set');
		}
		
		// Create the network if it doesn't exist
		if (!isset($this->doc['networks'][$network])) {
			$this->doc['networks'][$network] = array();
			$this->doc['networks'][$network][$accountIndex] = array();
		}
		
		$tz = new DateTimeZone("UTC");
		
		$dateObj = new DateTime("now", $tz);
		$dateObj->setTime(0,0,0);
		$dateMongo = new MongoDate($dateObj->getTimestamp());
		
		foreach ($data as $name => $count) {
			// Create the $name if it doesn't exist
			if (!isset($this->doc['networks'][$network][$accountIndex][$name])) {
				$this->doc['networks'][$network][$accountIndex][$name] = array(
					'daily' => array(), 
					'monthly' => array(), 
					'yearly' => array()
				);
			}
			
			// For convenience.
			$account = &$this->doc['networks'][$network][$accountIndex];
			
			// See if we already have an entry for this day
			$replace = false;
			foreach ($account[$name]['daily'] as $index => $day) {
				if (!is_array($day) || !isset($day['date']) || !isset($day['count'])) {
					// We found a bad entry, lets get rid of it
					unset($account[$name]['daily'][$index]);
					continue;
				}
				$thisDate = new DateTime("now", $tz);
				$thisDate->setTimestamp($day['date']->sec);
				$thisDate->setTime(0,0,0);
				if ($thisDate == $dateObj) {
					// Dates are the same so drop the last entry and replace it
					$replace = true;
				}
			} 
			
			// Add to daily
			if ($replace) {
				array_pop($account[$name]['daily']);
			}
			$account[$name]['daily'][] = array('date' => $dateMongo, 'count' => $count);

			// Is this the first day of the month?
			if (date('j', $dateMongo->sec) == 1) {
				// Add to monthly
				if ($replace) {
					array_pop($account[$name]['monthly']);
				}
				$account[$name]['monthly'][] = array('date' => $dateMongo, 'count' => $count);
			}

			// Is this the first month of the year?
			if (date('n', $dateMongo->sec) == 1) {
				// Add to yearly
				if ($replace) {
					array_pop($account[$name]['yearly']);
				}
				$account[$name]['yearly'][] = array('date' => $dateMongo, 'count' => $count);
			}

			// Only keep the last 31 days in 'daily'
			if (count($account[$name]['daily']) > 31) {
				array_shift($account[$name]['daily']);
			}

			// Only keep the last 12 months in monthly
			if (!empty($account[$name]['monthly'])
				&& count($account[$name]['monthly']) > 12)
			{
				array_shift($account[$name]['monthly']);
			}
		}
		
		$this->calcGrowthRate($network, $accountIndex);
		$this->clacAvgGrowthRate();

		$this->collection->update(array('email' => $this->email), $this->doc);
	}

	/**
	 * Returns all networks for the current user.
	 * Must call setUser first.
	 *
	 * @return array|null
	 */
	public function getNetworks() {
		if (isset($this->doc['networks'])) {
			return $this->doc['networks'];
		}

		return null;
	}
	
	
	private function calcGrowthRate($network, $accountIndex) {
		if (isset($this->doc['networks'][$network][$accountIndex]['followers']['daily'])) {
			// Weekly
			$dailyHistory = $this->doc['networks'][$network][$accountIndex]['followers']['daily'];
			if (count($dailyHistory) >= 7) {
				// The indexes might not start at zero for older records
				$keys = array_keys($dailyHistory);
				$lastIndex = $keys[count($keys) - 1];
				$firstIndex = $keys[count($keys) - 7];

				// Calc difference
				$second = $dailyHistory[$lastIndex]['count'];
				$first = $dailyHistory[$firstIndex]['count'];
				$diff =  $second - $first;
				if ($first == 0) {
					$percent = 100;
				}
				else {
					$percent = (($second - $first) / abs($first)) * 100;
				}
				
				$this->doc['networks'][$network][$accountIndex]['followers']['growth_rate_week'] = $diff;
				$this->doc['networks'][$network][$accountIndex]['followers']['growth_rate_week_percent'] = $percent;
			}
		}
	}
	
	private function clacAvgGrowthRate() {
		$count = 0;
		$totalNum = 0;
		$totalPer = 0;
		
		$networks = $this->getNetworks();
		
		foreach ($networks as $network) {
			if (is_array($network)) {
				foreach ($network as $account) {
					if (isset($account['followers']['growth_rate_week']) && isset($account['followers']['growth_rate_week_percent'])) {
						$totalNum += $account['followers']['growth_rate_week'];
						$totalPer += $account['followers']['growth_rate_week_percent'];
						$count++;
					}
				}
			}
		}
		
		if ($count > 0) {
			$avg = $totalNum / $count;
			$per = $totalPer / $count;

			$this->doc['networks']['growth_rate_week_avg'] = $avg;
			$this->doc['networks']['growth_rate_week_avg_percent'] = $per;
		}
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