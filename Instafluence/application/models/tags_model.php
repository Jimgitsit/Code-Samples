<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author Jim McGowen
 */

require_once('BaseModel.php');

class Tags_model extends BaseModel
{
	private $doc = null;
	private $allTags = null;
	
	public function init()
	{
		$this->setCollection('tags');
		$this->doc = $this->collection->findOne();
		if ($this->doc == null) {
			$this->create();
		}
		$this->allTags = &$this->doc['all_tags'];
	}
	
	private function create() {
		$this->doc = array('all_tags' => array());
		$this->collection->save($this->doc);
	}
	
	public function getAllTags() {
		return $this->allTags;
	}
	
	public function save($tags)
	{
		$save = false;
		foreach ($tags as $tag) {
			if (!in_array($tag, $this->allTags)) {
				$this->allTags[] = $tag;
				$save = true;
			}
		}
		
		if ($save) {
			$this->collection->save($this->doc);
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