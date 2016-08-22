<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('BaseDefaultController.php');

/**
 * Class Aggregate
 * 
 * Controller for aggregation from cron job.
 */
class Aggregate extends BaseDefaultController
{

	public function index($network = null, $type = null)
	{
		// Do not allow running this from a browser
		if (isset($_SERVER['REMOTE_ADDR'])) {
			die('This service cannot be run from a web browser!');
		}
		
		// We can also get $network from the query string after ?
		if ($network !== null && $this->input->get('network') && trim($this->input->get('network')) != '') {
			$network = trim($this->input->get('network'));
		}
		
		$this->load->library('Aggregation');
		$this->aggregation->setAllUsers();
		$this->aggregation->aggregate(true, false, $network, $type);
	}

}