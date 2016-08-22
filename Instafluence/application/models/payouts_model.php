<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author Jim M
 */

require_once('BaseModel.php');

class Payouts_model extends BaseModel
{
	public function init()
	{
		$this->setCollection('payouts');
	}
	
	public function getDoc() {
		return $this->collection->findOne();
	}
}