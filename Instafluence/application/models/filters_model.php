<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author Jim McGowen <jim@builtbyhq.com>
 */

require_once('BaseModel.php');

class Filters_model extends BaseModel
{
	public function init()
	{
		$this->setCollection('filters');
	}

	/**
	 * @param $name
	 * @param $filters
	 * @return ID of the new document
	 */
	public function createOrUpdate($name, $filters)
	{
		$data = array('name' => $name, 'filters' => $filters);
		$result = $this->collection->update(
			array('name' => $name), 
			$data, 
			array('upsert' => true));
		
		return $result;
	}

	/**
	 * @param string
	 *
	 * @return Array, the user.
	 */
	public function getSavedFilters($name)
	{
		$filter = $this->collection->findOne(
			array('name' => $name)
		);
		
		if($filter != null) {
			return $filter['filters'];
		}
		
		return null;
	}
	
	public function getSavedFilterNames() {
		$names = $this->collection->find(array(), array('name'));
		$namesArr = array();
		foreach($names as $name) {
			$namesArr[] = $name;
		}
		return $namesArr;
	}
}