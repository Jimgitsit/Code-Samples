<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Base class for all models.
 *
 * @author jim, Jason Maurer
 *
 */
abstract class BaseModel extends CI_Model
{
	public static $USER_NOT_FOUND = 100;

	protected $collection = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		if (method_exists($this, 'init'))
			$this->init();
	}

	protected function setCollection($collectionName)
	{
		$this->collection = $this->mongo->db->$collectionName;
	}

	/**
	 * Returns the MongoCollection object for the
	 * collection given in the constructor.
	 */
	public function getCollection()
	{
		return $this->collection;
	}

	public function getAll()
	{
		return $this->collection->find();
	}

	/**
	 * Dumps all data in the table for debugging.
	 */
	public function dumpAll()
	{
		$all = $this->collection->find();

		foreach($all as $row)
			var_dump($row);
	}

	public function isUnique($column, $value)
	{
		$row = $this->collection->findOne(array($column => $value));

		if ($row == null)
			return true;
		else
			return false;
	}

}
