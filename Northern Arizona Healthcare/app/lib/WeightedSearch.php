<?php

namespace NAHDS;

use PHPMailer\PHPMailer\Exception;

class WeightedSearch {
	
	private $limit = 20;
	private $andWhere = '';
	private $addSelect = '';
	
	/**
	 * Performs a weighted search on a MySQL database using Doctrine where the results are ordered by best match.
	 * An 'exact' match will have the most weight, a 'begins with' match will have a little less and a 'contains' match 
	 * will have the least. 
	 * 
	 * TODO: Not any more -> In addition, the search fields are weighted in order where an exact match on the first field
	 * will have a higher weight than an exact match on the last field.
	 * 
	 * Example: 
	 * ```
	 * $terms = array('matt', 'smith');
	 * $em = $this->getEntityManager();
	 * $ws = new WeightedSearch();
	 * $results = $ws->setLimit(50)->search($em, $terms, 'providers', array('lastName', 'firstName'), 'lastName ASC', array('id', 'firstName', 'lastName');
	 * ```
	 * 
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param array $terms An array of terms to search for. The first term should always be the entire phrase.
	 * @param string $table The DB table to search.
	 * @param array $searchFields An array of fields to search against.
	 * @param string $orderBy string Optional. The results will be sorted by weight then by this. Ex: "lastName ASC, firstName DESC"
	 * @param array $responseFields array Optional. The fields to return in the results. The default is to return all fields (including `weight`).
	 * @param string $phraseType The search phrase type from the search_phrases db table.
	 * @param string $phraseTypeField The key field from $table to match with typeId in the search_phrases db table.
	 *
	 * @return mixed
	 */
	public function search($em, $terms, $table, $searchFields, $orderBy = '', $responseFields = array('*'), $phraseType, $phraseTypeField) {
		$conn = $em->getConnection();
		
		$sql = 'SELECT ';
		foreach ($responseFields as $field) {
			if ($field != '*') {
				$sql .= "t.`$field`, ";
			}
			else {
				$sql .= "t.$field, ";
			}
		}
		$sql .= "\n";
		
		if ($this->addSelect != '') {
			$sql .= $this->addSelect . ",\n";
		}
		
		$sql .= "IF(sp.typeId = $phraseTypeField, 10000, 0) + \n";
		
		//$fieldWeight = (count($searchFields) * 3) * count($terms) + 1;
		$multiplier = count($searchFields);
		foreach ($searchFields as $field) {
			foreach ($terms as $term) {
				$termQuot = $conn->quote($term);
				$term = trim($termQuot, "'");
				
				// Weighted by fields and terms
				//$sql .= "IF(`$field` = $termQuot, " . (--$fieldWeight) . ", IF(`$field` LIKE '$term%', " . (--$fieldWeight) . ", IF(`$field` LIKE '%$term%', " . (--$fieldWeight) . ", 0))) * POW($multiplier,3) + \n";
				
				// Weighted by fields and terms all have the same weight
				//$sql .= "IF(`$field` = $termQuot, 8, IF(`$field` LIKE '$term%', 4, IF(`$field` LIKE '%$term%', 2, 0))) * POW($multiplier,3) + \n";
				
				// Equal weight for fields and terms
				$sql .= "IF(`$field` = $termQuot, 8, IF(`$field` LIKE '$term%', 4, IF(`$field` LIKE '%$term%', 2, 0))) + \n";
			}
			$multiplier -= 1;
		}
		$sql = substr($sql, 0, strlen($sql) - 3);
		
		$sql .= "AS weight \nFROM `$table` AS t \n";
		
		// The last term should always be the entire phrase
		$phraseTypeQuot = $conn->quote($phraseType);
		$phraseQuot = $conn->quote($terms[0]);
		$sql .= "LEFT JOIN search_phrases AS sp ON sp.type=$phraseTypeQuot AND sp.phrase=$phraseQuot AND sp.typeId=$phraseTypeField";
		
		$sql .= "\nWHERE \n(";
		foreach ($searchFields as $field) {
			foreach ($terms as $term) {
				$termQuot = $conn->quote($term);
				$term = trim($termQuot, "'");
				$sql .= "`$field` LIKE '%$term%' OR \n";
			}
		}
		$sql .= " sp.typeId=$phraseTypeField)";
		
		if (strlen($this->andWhere) > 0) {
			$sql .= "\nAND ($this->andWhere)";
		}
		
		$sql .= "\nORDER BY `weight` DESC";
		if (!empty($orderBy)) {
			$sql .= ", $orderBy ";
		}
		$sql .= "\nLIMIT " . $this->limit;
		
		$stmt = $conn->prepare($sql);
		$stmt->execute();
		$results = $stmt->fetchAll();
		
		return $results;
	}
	
	public function setLimit($limit) {
		if (!is_numeric($limit)) {
			throw new Exception('limit must be numeric');
		}
		$this->limit = $limit;
		return $this;
	}
	
	public function getLimit() {
		return $this->limit;
	}
	
	/**
	 * Set $fieldValues like array("isActive=false","userName!=''",etc...)
	 * 
	 * Caller is responsible for quoting string (ie. $em->getConnection()->quote('blah');
	 * 
	 * @param string $whereSql
	 * @return self
	 */
	public function setAndWhere($whereSql) {
		$this->andWhere = $whereSql;
		return $this;
	}
	
	public function addSelect($stmt) {
		$this->addSelect = $stmt;
		return $this;
	}
}