<?php

/**
 * Custom Mongo library. Handles loading the database. Configuration is 
 * in /application/config/mongo.php.
 * 
 * Loaded automatically in autoload.php.
 * 
 * @author jim
 *
 */
class CI_Mongo extends MongoClient
{
	
	public $db = null;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Fetch CodeIgniter instance
		$ci =& get_instance();
		
		// Load Mongo configuration file
		$ci->load->config('mongo');
		
		// Fetch Mongo server and database configuration
		$server = $ci->config->item('mongo_server');
		$dbname = $ci->config->item('mongo_dbname');
		
		// Initialise Mongo
		if ($server)
			parent::__construct($server);
		else
			parent::__construct();
		
		$this->db = $this->$dbname;
	}

}