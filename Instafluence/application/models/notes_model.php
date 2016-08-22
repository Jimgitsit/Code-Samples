<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author Jim M
 */

require_once('BaseModel.php');

class Notes_model extends BaseModel
{
	public function init()
	{
		$this->setCollection('notes');
	}
	
	public function getUserNotes($email) {
		$docs = $this->collection->find(array('email' => $email));
		return $docs;
	}
	
	public function addOrUpdateNote($id, $email, $markup, $name = 'Unknown') {
		if ($id == '' || $id == null) {
			// Add
			$doc = array(
				'email' => $email,
				'markup' => $markup,
				'created_date' => new MongoDate(),
				'created_by' => $name,
				'modified_date' => new MongoDate(),
				'modified_by' => $name
			);
			$this->collection->insert($doc);
			return $doc['_id'];
		}
		else {
			// Update
			$this->collection->update(
				array(
					'_id' => new MongoId($id)
				),
				array(
					'$set' => array(
						'email' => $email,
						'markup' => $markup,
						'modified_date' => new MongoDate(),
						'modified_by' => $name
					)
				)
			);
			return $id;
		}
	}
	
	public function deleteNote($id) {
		// Delete
		return $this->collection->remove(array(
			'_id' => new MongoId($id)
		));
	}
	
	public function changeEmail($oldEmail, $newEmail) {
		$this->collection->update(
			array(
				'email' => $oldEmail
			),
			array(
				'$set' => array(
					'email' => $newEmail,
				)
			),
			array(
				'multiple' => true
			)
		);
	}
}