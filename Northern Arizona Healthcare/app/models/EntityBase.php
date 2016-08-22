<?php

use \phpDocumentor\Reflection\DocBlock;
use \ForceUTF8\Encoding;

class EntityBase {
	
	/**
	 * A mapping of import data column names to property names in this class.
	 * This is pre-populated the fist time getImportPropertyName is called.
	 */
	protected $props = null;
	
	public function __construct() {
		$this->initProps();
	}
	
	/**
	 * Implemented for JsonSerializable interface.
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		$vars = get_object_vars($this);
		
		// Remove the $props property
		if (key_exists('props', $vars)) {
			unset($vars['props']);
		}
		
		// Format DateTime objects
		foreach ($vars as &$var) {
			if (is_a($var, 'DateTime')) {
				$var = $var->format('m/d/Y');
			}
		}
		
		return $vars;
	}
	
	/**
	 * Helper function for importing from csv.
	 *
	 * @param $em
	 * @param $data
	 * @param $map
	 * @param $dataRow
	 * 
	 * @return true if successful, false otherwise, 1 to ignore
	 */
	public function initWithImportData($em, $data, $map, $dataRow)
	{
		$colNum = 0;
		foreach ($map as $key => $col) {
			$colNum++;
			
			if (!empty($col)) {
				$propName = $this->getImportPropertyName($col);
				if ($propName !== null) {
					
					// Trim the value
					$data[$key] = trim($data[$key]);
					
					// Validate required fields
					if (isset($this->props[$propName]['ImportRequired'])) {
						if (empty($data[$key])) {
							Log::getLogger()->warning("Missing $col for row $dataRow. Ignoring row.");
							return false;
						}
					}
					
					// Validate relationships
					if (isset($this->props[$propName]['ImportExists'])) {
						$entityName = $this->props[$propName]['ImportExists'];
						if (!$this->entityExists($em, $entityName, $propName, trim($data[$key]))) {
							Log::getLogger()->warning("Invalid $col " . $data[$key] . " for row $dataRow. Ignoring row.");
							return false;
						}
					}
					
					// Check ignore values
					if (isset($this->props[$propName]['ImportIgnore'])) {
						$ignoreValues = explode(',', $this->props[$propName]['ImportIgnore']);
						foreach ($ignoreValues as $ignoreValue) {
							if (trim($data[$key]) == trim($ignoreValue)) {
								return 1;
							}
						}
					}
					
					// Normalize phone numbers
					if (isset($this->props[$propName]['ImportPhone'])) {
						$data[$key] = preg_replace("/[^0-9,.]/", "", $data[$key]);
						if (strlen($data[$key]) > 0 && strlen($data[$key]) < 10) {
							Log::getLogger()->warning("Possible invalid phone number {$data[$key]} for row $dataRow.");
						}
					}
					
					$type = $this->getPropertyType($propName);
					if ($type == 'date') {
						try {
							$this->$propName = new \DateTime($data[$key]);
						}
						catch (Exception $e) {
							Log::getLogger()->warning("Bad date format {$data[$key]}");
							return false;
						}
					}
					else {
						if ($data[$key] == 'NONE') {
							$this->$propName = '';
						}
						else {
							$this->$propName = trim($data[$key]);
							
							// Encode non-utf8 chars
							if (mb_detect_encoding($this->$propName, 'UTF-8, ISO-8859-1, GBK, ASCII', true) !== 'UTF-8') {
								$this->$propName = Encoding::toUTF8($this->$propName);
							}
							// Make sure we fix any invalid utf8 chars
							$this->$propName = Encoding::fixUTF8($this->$propName);
						}
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Returns the property name given the value of @ImportName in the property doc block.
	 *
	 * @param $importName
	 *
	 * @return mixed|null
	 */
	protected function getImportPropertyName($importName) {
		foreach ($this->props as $propName => $tags) {
			if ($tags['ImportMap'] == $importName) {
				return $propName;
			}
		}
		
		return null;
	}
	
	protected function getPropertyType($propName) {
		$col = $this->props[$propName]['Column'];
		$start = strpos($col, 'type="') + 6;
		$end = strpos($col, '"', $start);
		$type = substr($col, $start, $end - $start);
		return $type;
	}
	
	public function setEntityProperty($name, $value) {
		$funcName = 'set' . ucfirst($name);
		
		if (method_exists($this, $funcName)) {
			if (is_string($value)) {
				$value = trim($value);
			}
			$this->{$funcName}($value);
		}
	}
	
	public function initProps() {
		if ($this->props == null) {
			// Get import mapping and cache the result in $this->props
			$rc = new \ReflectionClass(get_class($this));
			$props = $rc->getProperties();
			foreach ($props as $prop) {
				$docBlock = new DocBlock($prop);
				$tags = $docBlock->getTags();
				foreach ($tags as $tag) {
					$this->props[$prop->getName()][$tag->getName()] = $tag->getContent();
				}
				
				// If @ImportMap is not defined then set it to the same name as the property itself
				if (!$docBlock->hasTag('ImportMap')) {
					$this->props[$prop->getName()]['ImportMap'] = $prop->getName();
				}
			}
		}
	}
	
	/**
	 * Helper function to determine quickly if a record exists.
	 *
	 * @param $em
	 * @param $entityName
	 * @param $field
	 * @param $value
	 *
	 * @return bool
	 */
	public function entityExists($em, $entityName, $field, $value) {
		$dql = "SELECT 1 FROM $entityName t WHERE t.$field = :value";
		$query = $em->createQuery($dql);
		$query->setParameter('value', $value);
		
		$res = $query->getResult();
		return !empty($res);
	}
	
	/**
	 * @param $em \Doctrine\ORM\EntityManager
	 * @param $entityName string
	 * @param $field string
	 * @param $filters array Simple 'and equals' filtering
	 *
	 * @return array
	 */
	public static function selectDistinct($em, $entityName, $field, $filters = array()) {
		$qb = $em->createQueryBuilder();
		$qb->select("e.$field")->distinct()
			->from($entityName, 'e')
			->where("e.$field != ''");
		
		foreach ($filters as $filterBy => $values) {
			if (is_array($values)) {
				$sql = '';
				foreach ($values as $value) {
					$sql .= "e.$filterBy = '$value' OR ";
				}
				$sql = rtrim($sql, ' OR ');
				$qb->andWhere($sql);
			}
			else {
				$qb->andWhere("e.$filterBy = '$values'");
			}
		}
		
		$types = $qb->getQuery()->getArrayResult();
		return $types;
	}
}