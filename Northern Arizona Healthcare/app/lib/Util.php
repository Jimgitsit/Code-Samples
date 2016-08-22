<?php

/**
 * Class Util
 * 
 * Sample class that can be used anywhere in the application.
 */
class Util {
	/**
	 * Convert strings with underscores into CamelCase
	 *
	 * @param    string    $string    The string to convert
	 * @param    bool    $first_char_caps    camelCase or CamelCase
	 * @return    string    The converted string
	 *
	 */
	public static function underscoreToCamelCase( $string, $first_char_caps = false)
	{
		if( $first_char_caps == true )
		{
			$string[0] = strtoupper($string[0]);
		}
		$func = create_function('$c', 'return strtoupper($c[1]);');
		return preg_replace_callback('/_([a-z])/', $func, $string);
	}
	
	public static function delTree($dir) {
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file) {
			(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}
	
	public static function fileExistsCi($fileName, $caseSensitive = true) {
		
		if(file_exists($fileName)) {
			return $fileName;
		}
		if($caseSensitive) return false;
		
		// Handle case insensitive requests            
		$directoryName = dirname($fileName);
		$fileArray = glob($directoryName . '/*', GLOB_NOSORT);
		$fileNameLowerCase = strtolower($fileName);
		foreach($fileArray as $file) {
			if(strtolower($file) == $fileNameLowerCase) {
				return $file;
			}
		}
		return false;
	}
	
	/**
	 * Updates meta data phone numbers for the employee.
	 * 
	 * Important: You must call $em->flush() after calling this function.
	 * 
	 * @param $em
	 * @param $empId
	 * @param $phoneNumbers
	 */
	public static function addMetaPhone($em, $empId, $phoneNumbers) {
		
		// Remove all phone numbers for this user where source='AD' and re-add them.
		// That way our meta data will reflect exactly what's in AD.
		$adNumbers = $em->getRepository('MetaData')->findBy(array('type' => 'employee', 'typeID' => $empId, 'valueType' => 'Phone', 'source' => 'AD'));
		foreach ($adNumbers as $adNumber) {
			$em->remove($adNumber);
		}
		
		foreach ($phoneNumbers as $i => $phoneNumber) {
			
			// Sanity check
			if (empty($phoneNumber['number']) || !isset($phoneNumber['ext'])) {
				continue;
			}
			
			// Phone numbers should be exactly 10 digits
			if (strlen($phoneNumber['number']) != 10) {
				Log::getLogger()->warning("Possible invalid phone number {$phoneNumber['number']} for employeeID $empId");
			}
			
			$value = array(
				"number" => $phoneNumber['number'],
				"ext" => $phoneNumber['ext'],
				"hours" => ''
			);
			
			$md = new MetaData();
			$md->setType('employee')
				->setTypeID($empId)
				->setValueType('Phone')
				->setValueSubType('Work')
				->setSource('AD')
				->setLabel('')
				->setValue($value)
				->setAudience('Internal')
				->setValueOrder($i)
				->setIsActive(true)
				->setCreated(new DateTime())
				->setLastUpdated(new DateTime());
			$em->persist($md);
		}
	}
}