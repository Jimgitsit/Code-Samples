<?php

namespace MongoDocs;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\MappedSuperclass
 */
abstract class BaseDocument implements \JsonSerializable 
{
	public function jsonSerialize() {
		return $this->toArray();
	}
	
	public function toArray() {
		$getter_names = get_class_methods(get_class($this));
		$gettable_attributes = array();
		foreach ($getter_names as $key => $funcName) {
			if (substr($funcName, 0, 3) === 'get') {
				$propName = strtolower(substr($funcName, 3, 1));
				$propName .= substr($funcName, 4);
				$value = $this->$funcName();
				if (is_object($value) && get_class($value) == 'Doctrine\ODM\MongoDB\PersistentCollection') {
					$values = array();
					$collection = $value;
					foreach ($collection as $obj) {
						$values[] = $obj->toArray();
					}
					$gettable_attributes[$propName] = $values;
				}
				else if (is_object($value) && get_class($value) == 'DateTime') {
					$gettable_attributes[$propName] = $value->getTimestamp();
				}
				else if (is_object($value)) {
					// Ignore plain objects
					//$gettable_attributes[$propName] = $value->toArray();
				}
				else {
					$gettable_attributes[$propName] = $value;
				}
			}
		}
		return $gettable_attributes;
	}
}
