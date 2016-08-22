<?php

use \Gozer\Core\Core;
use \PHPMailer\PHPMailer\PHPMailer;
use phpseclib\Net\SFTP;
use NAHDS\NAHLDAP;

/**
 * Class Import
 * 
 * This class handles automatic imports.
 * 
 * A backup of the database is made before any new data is imported. 
 * DB transactions are used throughout the import process. If any exception occurs the db will be unaffected.
 * Logs are located in application/logs/.
 * 
 * Note: The file names and their structure are hard coded here so if anything changes
 * with them the code here will also have to be updated.
 */
class Import extends Core 
{
	/** @var array Helper to define properties of the data files. */
	public static $imports = array(
		'providers' => array(
			'files' => array(
				'providers'         => 'Providers_Export.txt',
				'offices'           => 'Offices_Export.txt',
				'provider_offices'  => 'Provider_Offices_Export.txt',
				'education'         => 'Education_Export.txt',
				'licenses'          => 'Licenses_Export.txt',
				'appointments'      => 'Appointment_Export.txt'
			)
		),
		'employees' => array(
			'files' => array(
				'employees' => array(
					'required' => true,
					'filename' => 'NAHActive.csv'
				),
				'term_list' => array(
					'required' => false,
					'filename' => 'NAHTerms.csv'
				)
			)
		),
		'businesses' => array(
			'files' => array(
				'departments' => 'NAHDepts.csv'
			)
		)
	);
	
	/** @var null Holder for the current backup file name. */
	private $backupFileName = null;
	
	/** @var string The local directory to look for data files. Must be read/write. */
	private $dataDir = IMPORT_DATA_DIR;
	
	/** @var bool Whether or not to send the log in an email. Gets set to true on exceptions and certain warnings. */
	private $sendLog = false;
	
	/** @var bool Only used for Offices and Providers */
	private $truncateData = true;
	
	/**
	 * This is the entry point for both automatic imports.
	 * 
	 * @var $getRemoteFiles bool Whether or not to get the data files from the sftp first.
	 * @throws Exception
	 */
	public function autoImport() {
		try {
			Log::getLogger()->info("*** Import Started ***");
			
			// TODO: If dataDir doesn't exist maybe just create it?
			if (!file_exists($this->dataDir)) {
				throw new Exception("Data directory '{$this->dataDir}' could not be found.");
			}
			
			// Needed for csv files created on a mac
			ini_set("auto_detect_line_endings", true);
			
			// Begin DB transaction
			$em = $this->getEntityManager();
			$conn = $em->getConnection();
			
			if (IMPORT_GET_FILES_FROM_SFTP) {
				//$this->getFilesSFTP();
			}
			$this->makeBackup();
			
			// Import departments
			Log::getLogger()->info("-= Importing Departments =-");
			$conn->beginTransaction();
			$this->importDepartments();
			$conn->commit();
			
			// Import employees
			Log::getLogger()->info("-= Importing Employees =-");
			$conn->beginTransaction();
			$this->importEmployees();
			$conn->commit();
			
			// Import badge photos
			Log::getLogger()->info("-= Importing Badge Photos =-");
			$conn->beginTransaction();
			$this->importBadgePhotos();
			$conn->commit();
			
			// Import providers
			/*
			Log::getLogger()->info("-= Importing Providers =-");
			$conn->beginTransaction();
			$this->importProviders();
			$conn->commit();
			*/
			
			$this->cleanUpMetaData();
			
			$this->saveImport();
			
			Log::getLogger()->info("*** Finished Import ***");
			
			if ($this->sendLog) {
				$this->sendLog();
			}
		}
		catch (Exception $e) {
			Log::getLogger()->info("Exception occurred during import. Import halted.");
			Log::getLogger()->critical($e->getMessage());
			
			$this->resetEntityManager();
			$em = $this->getEntityManager();
			
			// Roll back the transaction
			$em->rollback();
			
			$this->saveImport(true);
			
			$this->sendLog = true;
			$this->sendLog();
		}
	}
	
	/**
	 * Retrieves the data files via SFTP and downloads them to the local data dir ($this->dataDir).
	 * Requires IMPORT_SFTP_* defines in site config.
	 * 
	 * @throws Exception
	 */
	private function getFilesSFTP() {
		// Connect
		Log::getLogger()->info("Connecting to SFTP server...");
		$sftp = new SFTP(IMPORT_SFTP_HOST, IMPORT_SFTP_PORT);
		if (!$sftp->login(IMPORT_SFTP_USER, IMPORT_SFTP_PASS)) {
			$this->sendLog = true;
			throw new Exception('Could not connect to sftp server ' . SMTP_HOST);
		}
		Log::getLogger()->info("Connected.");
		
		$sftpFiles = $sftp->nlist();
		
		// Get providers files
		foreach (self::$imports['providers']['files'] as $fileName) {
			if (in_array($fileName, $sftpFiles)) {
				Log::getLogger()->info("Downloading file: $fileName");
				
				// Download the file to the local data directory
				if ($sftp->get($fileName, $this->dataDir . '/' . $fileName) === false) {
					throw new Exception("Failed to retrieve remote file: $fileName");
				}
			}
			else {
				throw new Exception("File not found: $fileName");
			}
		}
		
		// Get employee files
		foreach (self::$imports['employees']['files'] as $file) {
			$fileName = $file['filename'];
			if (in_array($fileName, $sftpFiles)) {
				Log::getLogger()->info("Downloading file: $fileName");
				
				// Download the file to the local data directory
				if ($sftp->get($fileName, $this->dataDir . '/' . $fileName) === false) {
					throw new Exception("Failed to retrieve remote file: $fileName");
				}
			}
			else {
				throw new Exception("File not found: $fileName");
			}
		}
		
		// Get businesses files
		foreach (self::$imports['businesses']['files'] as $fileName) {
			if (in_array($fileName, $sftpFiles)) {
				Log::getLogger()->info("Downloading file: $fileName");
				
				// Download the file to the local data directory
				if ($sftp->get($fileName, $this->dataDir . '/' . $fileName) === false) {
					throw new Exception("Failed to retrieve remote file: $fileName");
				}
			}
			else {
				throw new Exception("File not found: $fileName");
			}
		}
	}
	
	/**
	 * Imports employees data. The employee table is not truncated to allow terminated employees to 
	 * still have a record since terminated employees no longer show up in the data file.
	 * 
	 * @throws Exception
	 */
	private function importEmployees() 
	{
		// Double check for all required files
		foreach (self::$imports['employees']['files'] as $file) {
			if ($file['required'] == true && !file_exists(IMPORT_DATA_DIR . '/' . $file['filename'])) {
				$this->sendLog = true;
				Log::getLogger()->error("Missing required file {$file['filename']}. Aborting import.");
				return;
			}
		}
		
		$em = $this->getEntityManager();
		
		$fqFile = IMPORT_DATA_DIR . "/" . self::$imports['employees']['files']['employees']['filename'];
		Log::getLogger()->info("Importing employees from file: $fqFile...");
		
		// Get the file handle
		$fp = fopen($fqFile, "r");
		
		// Get an array of existing employee IDs
		$query = $em->getConnection()->prepare("SELECT employeeID FROM employees");
		$query->execute();
		$existing = $query->fetchAll(PDO::FETCH_COLUMN);
		
		// Get the headers
		$headers = fgetcsv($fp, 0, ',');
		$idIndex = array_search('EMPLOYEE', $headers);
		if ($idIndex === false) {
			throw new Exception("Missing EMPLOYEE column in provider data!");
		}
		
		$phoneNumberCount = 0;
		
		$batchSize = 200;
		$count = 0;
		$dataRow = 1;
		$ids = array();
		while ($data = fgetcsv($fp, 0, ',')) {
			
			$dataRow++;
			
			if (count($data) != count($headers)) {
				$this->sendLog = true;
				Log::getLogger()->warning("Wrong number of columns for row $dataRow. Ignoring row.");
				continue;
			}
			
			// Check for duplicate employeeID
			if (in_array($data[$idIndex], $ids)) {
				$this->sendLog = true;
				Log::getLogger()->warning("Found duplicate id " . $data[$idIndex] . " in row $dataRow. Ignoring row.");
				continue;
			}
			$ids[] = $data[$idIndex];
			
			if (in_array($data[$idIndex], $existing)) {
				// Update
				$entity = $em->getRepository('Employee')->findOneByEmployeeID($data[$idIndex]);
				$entity->initProps();
			}
			else {
				// New
				$entity = new Employee();
				$entity->setCreated(new DateTime());
			}
			
			$initStatus = $entity->initWithImportData($em, $data, $headers, $dataRow);
			if ($initStatus !== true) {
				if ($initStatus === false) {
					// Caused a warning
					$this->sendLog = true;
				}
				// Ignore this row
				continue;
			}
			
			// Check if the costCenter is valid (import the employee anyway)
			if (!$entity->entityExists($em, 'Business', 'costCenter', $entity->getCostCenter())) {
				Log::getLogger()->warning("Invalid cost center " . $entity->getCostCenter() . " for row $dataRow.");
			}
			
			// Generate a username if missing
			if (empty($entity->getUserName())) {
				$userName = strtolower(substr($entity->getFirstName(), 0, 1)) . strtolower(substr($entity->getLastName(), 0, 1)) . $entity->getEmployeeID();
				$entity->setUserName($userName);
			}
			
			// If hire date is in the future don't check for data from AD
			$now = new DateTime();
			if ($entity->getHireDate() <= $now) {
				// Get info from AD
				$nahldap = new NAHLDAP();
				$adUser = $nahldap->findByUserName($entity->getUserName());
				if ($adUser === null) {
					Log::getLogger()
						->warning('Username ' . $entity->getUserName() . ' (' . $entity->getFirstName() . ' ' . $entity->getLastName() . ':' . $entity->getEmployeeID() . ') not found in Active Directory.');
					$this->sendLog = true;
					//Log::getLogger()->warning($entity->getEmployeeID() . ',' . $entity->getUserName());
				}
				else {
					Util::addMetaPhone($em, $entity->getEmployeeID(), $adUser['phonenumbers']);
					$phoneNumberCount += count($adUser['phonenumbers']);
					
					if (!empty($adUser['givenname'][0])) {
						$entity->setFirstName(trim($adUser['givenname'][0]));
					}
					
					if (!empty($adUser['sn'][0])) {
						$entity->setLastName(trim($adUser['sn'][0]));
					}
					
					if (!empty($adUser['title'][0])) {
						$entity->setTitle(trim($adUser['title'][0]));
					}
					
					// TODO: This should be in meta data
					if (!empty($adUser['mail'][0])) {
						$entity->setEmail(trim($adUser['mail'][0]));
					}
				}
			}
			
			$entity->setLastUpdated(new DateTime());
			
			$em->persist($entity);
			$count++;
			if ($count == $batchSize) {
				$em->flush();
				$count = 0;
			}
		}
		$em->flush();
		$em->clear();
		
		fclose($fp);
		
		Log::getLogger()->info("Total phone numbers from AD: $phoneNumberCount");
		Log::getLogger()->info("Done.");
		
		// Import the Termination list
		$fqFile = IMPORT_DATA_DIR . "/" . self::$imports['employees']['files']['term_list']['filename'];
		Log::getLogger()->info("Importing termination list from file: $fqFile...");
		
		// Get the file handle
		$fp = fopen($fqFile, "r");
		
		// Get the headers
		$headers = fgetcsv($fp, 0, ',');
		$idIndex = array_search('EMPLOYEE', $headers);
		$termDateIndex = array_search('TERM_DATE', $headers);
		if ($idIndex === false || $termDateIndex === false) {
			throw new Exception("Missing EMPLOYEE and/or TERM_DATE column in provider data!");
		}
		
		$dataRow = 1;
		while ($data = fgetcsv($fp, 0, ',')) {
			
			$dataRow++;
			
			if (count($data) != count($headers)) {
				$this->sendLog = true;
				Log::getLogger()->warning("Wrong number of columns for row $dataRow. Ignoring row.");
				continue;
			}
			
			// Find the employee record
			$employee = $em->getRepository('Employee')->findOneByEmployeeID($data[$idIndex]);
			if (empty($employee)) {
				Log::getLogger()->warning("Invalid EMPLOYEE value on row $dataRow. Ignoring row.");
				continue;
			}
			
			// Update the termination date for the employee
			try {
				$employee->setTerminationDate(new DateTime($data[$termDateIndex]));
			}
			catch (Exception $e) {
				Log::getLogger()->warning("Bad date format for TERM_DATE {$data[$termDateIndex]} in row $dataRow. Ignoring row.");
				continue;
			}
			
			$em->persist($employee);
			$em->flush();
		}
		$em->clear();
		
		fclose($fp);
		
		// Cleanup, deletes the data file
		//unlink(IMPORT_DATA_DIR . '/' . self::$imports['employees']['files']['employees']['filename']);
		
		Log::getLogger()->info("Done.");
		Log::getLogger()->info("Updating User defined employees...");
		
		// Update "User" sourced employees
		$employees = $em->getRepository('Employee')->findBy(array('source' => 'User'));
		foreach ($employees as $entity) {
			// Get info from AD
			$nahldap = new NAHLDAP();
			$adUser = $nahldap->findByUserName($entity->getUserName());
			if ($adUser === null) {
				Log::getLogger()->warning('Username ' . $entity->getUserName() . ' (' . $entity->getFirstName() . ' ' . $entity->getLastName() . ':' . $entity->getEmployeeID() . ') not found in Active Directory.');
				$this->sendLog = true;
				//Log::getLogger()->warning($entity->getEmployeeID() . ',' . $entity->getUserName());
			}
			else {
				Util::addMetaPhone($em, $entity->getEmployeeID(), $adUser['phonenumbers']);
				//$phoneNumberCount += count($adUser['phonenumbers']);
				
				if (!empty($adUser['givenname'][0])) {
					$entity->setFirstName(trim($adUser['givenname'][0]));
				}
				
				if (!empty($adUser['sn'][0])) {
					$entity->setLastName(trim($adUser['sn'][0]));
				}
				
				if (!empty($adUser['title'][0])) {
					$entity->setTitle(trim($adUser['title'][0]));
				}
				
				// TODO: This should be in meta data
				if (!empty($adUser['mail'][0])) {
					$entity->setEmail(trim($adUser['mail'][0]));
				}
				
				$em->persist($entity);
				$em->flush();
			}
		}
		
		Log::getLogger()->info("Done.");
	}
	
	public function importBadgePhotos() {
		
		Log::getLogger()->info("Importing badge photos from sftp...");
		
		// Connect to SFTP
		Log::getLogger()->info("Connecting to SFTP server...");
		$sftp = new SFTP(IMPORT_SFTP_HOST, IMPORT_SFTP_PORT);
		if (!$sftp->login(IMPORT_SFTP_USER, IMPORT_SFTP_PASS)) {
			$this->sendLog = true;
			throw new Exception('Could not connect to sftp server ' . SMTP_HOST);
		}
		Log::getLogger()->info("Connected.");
		
		// List all .jpg files
		Log::getLogger()->info("Getting file list...");
		$sftpFiles = $sftp->nlist('photos/badge');
		Log::getLogger()->info("Done.");
		
		// Look for existing Badge photo in metadata
		$em = $this->getEntityManager();
		$batchSize = 200;
		$count = 0;
		$updatedCount = 0;
		$addedCount = 0;
		foreach ($sftpFiles as $file) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			$fileInfo = pathinfo($file);
			$id = $fileInfo['filename'];
			$emp = $em->getRepository('Employee')->findOneByEmployeeID($id);
			if (empty($emp)) {
				//Log::getLogger()->warning("Employee with id $id not found. Ignoring file $file.");
				//$this->sendLog = true;
				continue;
			}
			
			// Add or update(if file name is different) badge photo for user
			$md = $em->getRepository('MetaData')->findOneBy(array(
				'type' => 'employee',
				'typeID' => $id,
				'valueType' => 'Photo',
				'valueSubType' => 'Badge'
			));
			
			if (empty($md)) {
				// New meta data
				$md = new MetaData();
				$md->setType('employee')
					->setTypeID($id)
					->setValueType('Photo')
					->setValueSubType('Badge')
					->setValueOrder(10)
					->setAudience('Private')
					->setCreated(new DateTime);
				$addedCount++;
			}
			else {
				$updatedCount++;
			}
			
			$md->setSource('Lawson')
				->setValue($file)
				->setIsActive(true)
				->setLastUpdated(new DateTime);
			
			$em->persist($md);
			$count++;
			if ($count == $batchSize) {
				$em->flush();
				$count = 0;
			}
		}
		$em->flush();
		$em->clear();
		
		Log::getLogger()->info("Updated $updatedCount, Added $addedCount");
		Log::getLogger()->info("Done.");
	}
	
	/**
	 * One time import.
	 * 
	 * @throws Exception
	 */
	public function importMetaData() {
		$fqFile = IMPORT_DATA_DIR . "/communicate_export.csv";
		Log::getLogger()->info("Importing meta data from file: $fqFile...");
		
		if (!file_exists($fqFile)) {
			$this->sendLog = true;
			Log::getLogger()->error("Missing required file $fqFile. Aborting import.");
			return;
		}
		
		// Get the file handle
		ini_set("auto_detect_line_endings", true);
		$fp = fopen($fqFile, "r");
		
		$em = $this->getEntityManager();
		$conn = $em->getConnection();
		$conn->beginTransaction();
		
		$this->transactionSafeTruncate('MetaData');
		
		try {
			
			// Get the headers
			$headers = fgetcsv($fp, 0, ',');
			
			$batchSize = 200;
			$count = 0;
			$dataRow = 1;
			while ($data = fgetcsv($fp, 0, ',')) {
				$dataRow++;
				
				if (count($data) != count($headers)) {
					Log::getLogger()->warning("Wrong number of columns for row $dataRow. Ignoring row.");
					continue;
				}
				
				$entity = new MetaData();
				
				$initStatus = $entity->initWithImportData($em, $data, $headers, $dataRow);
				if ($initStatus !== true) {
					if ($initStatus === false) {
						// Caused a warning
						$this->sendLog = true;
					}
					// Ignore this row
					continue;
				}
				
				// Validate and normalize phone numbers
				if ($entity->getValueType() == 'Phone') {
					$number = $entity->getValue();
					// Handle extensions in the phone number
					// TODO: No longer separating extensions
					if (strstr($number, '|')) {
						$numbers = explode('|', $number);
						
						$number = trim($numbers[0]);
						
						$ext = '';
						if (count($numbers) > 2) {
							// Multiple extensions found
							for ($i = 1; $i < count($numbers); $i++) {
								$ext .= trim($numbers[$i]) . ',';
							}
							$ext = substr($ext, 0, strlen($ext) - 1);
						}
						else {
							$ext = trim($numbers[1]);
						}
						
						$entity->setValue($number);
						$entity->setExtension($ext);
					}
					
					$normPhone = $this->normalizePhoneNumber($number, $dataRow);
					$entity->setValue($normPhone);
				}
				
				$em->persist($entity);
				$count++;
				if ($count == $batchSize) {
					$em->flush();
					$count = 0;
				}
			}
			
			$em->flush();
			$em->clear();
			
			fclose($fp);
			
			Log::getLogger()->info("Done.");
			
			$conn->commit();
		}
		catch (Exception $e) {
			Log::getLogger()->error($e->getMessage());
			
			$conn->rollBack();
		}
	}
	
	/**
	 * One time!
	 * 
	 * @throws \Doctrine\DBAL\ConnectionException
	 */
	public function importEmployeeUsernames() {
		$fqFile = IMPORT_DATA_DIR . "/employee-id_username2.csv";
		Log::getLogger()->info("Importing employee usernames from file: $fqFile...");
		
		if (!file_exists($fqFile)) {
			$this->sendLog = true;
			Log::getLogger()->error("Missing required file $fqFile. Aborting import.");
			return;
		}
		
		// Get the file handle
		ini_set("auto_detect_line_endings", true);
		$fp = fopen($fqFile, "r");
		
		$em = $this->getEntityManager();
		$conn = $em->getConnection();
		$conn->beginTransaction();
		
		try {
			
			// Get the headers
			$headers = fgetcsv($fp, 0, ',');
			
			$batchSize = 200;
			$count = 0;
			$dataRow = 1;
			$ids = array();
			while ($data = fgetcsv($fp, 0, ',')) {
				$dataRow++;
				
				if (count($data) != count($headers)) {
					Log::getLogger()->warning("Wrong number of columns for row $dataRow. Ignoring row.");
					continue;
				}
				
				$employeeID = trim($data[0]);
				$userName = trim($data[1]);
				
				if (empty($employeeID)) {
					Log::getLogger()->warning("Missing employeeID for row $dataRow. Ignoring row.");
					continue;
				}
				
				if (empty($userName)) {
					Log::getLogger()->warning("Missing username for row $dataRow. Ignoring row.");
					continue;
				}
				
				// Check for duplicate employeeID
				if (in_array($employeeID, $ids)) {
					$this->sendLog = true;
					Log::getLogger()->warning("Found duplicate id $employeeID in row $dataRow. Ignoring row.");
					continue;
				}
				$ids[] = $employeeID;
				
				$entity = $em->getRepository('Employee')->findOneByEmployeeID($employeeID);
				if (empty($entity)) {
					Log::getLogger()->warning("Invalid employeeID $employeeID for row $dataRow. Ignoring row.");
					continue;
				}
				
				$entity->setUserName($userName);
				
				$em->persist($entity);
				$count++;
				if ($count == $batchSize) {
					$em->flush();
					$count = 0;
				}
			}
			
			$em->flush();
			
			fclose($fp);
			
			Log::getLogger()->info("Done.");
			
			$conn->commit();
		}
		catch (Exception $e) {
			Log::getLogger()->error($e->getMessage());
			
			$conn->rollBack();
		}
	}
	
	/**
	 * One time!
	 *
	 * @throws \Doctrine\DBAL\ConnectionException
	 */
	public function importNonEmployees() {
		$fqFile = IMPORT_DATA_DIR . "/non-employee-user_export_20160623.csv";
		Log::getLogger()->info("Importing non employees from file: $fqFile...");
		
		if (!file_exists($fqFile)) {
			$this->sendLog = true;
			Log::getLogger()->error("Missing required file $fqFile. Aborting import.");
			return;
		}
		
		// Get the file handle
		ini_set("auto_detect_line_endings", true);
		$fp = fopen($fqFile, "r");
		
		$em = $this->getEntityManager();
		$conn = $em->getConnection();
		$conn->beginTransaction();
		
		try {
			
			// Get the headers
			$headers = fgetcsv($fp, 0, ',');
			
			$batchSize = 200;
			$count = 0;
			$dataRow = 1;
			$ids = array();
			while ($data = fgetcsv($fp, 0, ',')) {
				$dataRow++;
				
				if (count($data) != count($headers)) {
					Log::getLogger()->warning("Wrong number of columns for row $dataRow. Ignoring row.");
					continue;
				}
				
				$employeeID = trim($data[0]);
				$userName = trim($data[1]);
				$busId = trim($data[2]);
				$npiTemp = trim($data[10]);
				
				if (empty($employeeID)) {
					Log::getLogger()->warning("Missing employeeID for row $dataRow. Ignoring row.");
					continue;
				}
				
				if (empty($userName)) {
					Log::getLogger()->warning("Missing username for row $dataRow. Ignoring row.");
					continue;
				}
				
				// Check for duplicate employeeID
				if (in_array($employeeID, $ids)) {
					$this->sendLog = true;
					Log::getLogger()->warning("Found duplicate id $employeeID in row $dataRow. Ignoring row.");
					continue;
				}
				$ids[] = $employeeID;
				
				$entity = $em->getRepository('Employee')->findOneByEmployeeID($employeeID);
				if (!empty($entity)) {
					Log::getLogger()->warning("EmployeeID $employeeID for row $dataRow already exists. Ignoring row.");
					continue;
				}
				
				$entity = new Employee();
				$entity->setEmployeeID($employeeID);
				$entity->setUserName($userName);
				$entity->setNpiTemp($npiTemp);
				$entity->setSource('User');
				
				/*
				// Get the costCenter
				$bus = $em->getRepository('Business')->findOneByBusinessID($busId);
				if (empty($bus)) {
					Log::getLogger()->warning("Invalid deptID $busId for row $dataRow. Ignoring row.");
					//continue;
				}
				else {
					$entity->setCostCenter($bus->getCostCenter());
				}
				*/
				
				// Get info from AD
				$nahldap = new NAHLDAP();
				$adUser = $nahldap->findByUserName($entity->getUserName());
				if ($adUser === null) {
					Log::getLogger()->warning('Username ' . $entity->getUserName() . ' (' . $entity->getFirstName() . ' ' . $entity->getLastName() . ':' . $entity->getEmployeeID() . ') not found in Active Directory.');
					$this->sendLog = true;
					//Log::getLogger()->warning($entity->getEmployeeID() . ',' . $entity->getUserName());
				}
				else {
					Util::addMetaPhone($em, $entity->getEmployeeID(), $adUser['phonenumbers']);
					
					if (!empty($adUser['givenname'][0])) {
						$entity->setFirstName(trim($adUser['givenname'][0]));
					}
					
					if (!empty($adUser['sn'][0])) {
						$entity->setLastName(trim($adUser['sn'][0]));
					}
					
					if (!empty($adUser['title'][0])) {
						$entity->setTitle(trim($adUser['title'][0]));
					}
					
					// TODO: This should be in meta data
					if (!empty($adUser['mail'][0])) {
						$entity->setEmail(trim($adUser['mail'][0]));
					}
				}
				
				$em->persist($entity);
				$count++;
				if ($count == $batchSize) {
					$em->flush();
					$count = 0;
				}
			}
			
			$em->flush();
			$em->clear();
			
			fclose($fp);
			
			Log::getLogger()->info("Done.");
			
			$conn->commit();
		}
		catch (Exception $e) {
			Log::getLogger()->error($e->getMessage());
			
			$conn->rollBack();
		}
	}
	
	/**
	 * Cleans up a row of data (removes double pipes, line endings etc) and 
	 * returns an array of the data.
	 * 
	 * Note: This function isn't used anymore but keeping it just in case.
	 * 
	 * @param $data
	 * @param $headers
	 *
	 * @return array|mixed|string
	 */
	private function cleanDataRow($data, $headers) {
		$data = str_replace(array('||', "\n", "\r"), array('|', '', ''), $data);
		if (substr($data, 0, 1) == '|') {
			$data = substr($data, 1);
		}
		if (substr($data, strlen($data) - 1) == ',') {
			$data = substr($data, 0, strlen($data) - 1);
		}
		if (substr($data, strlen($data) - 1) == '|') {
			$data = substr($data, 0, strlen($data) - 1);
		}
		$data = explode('|', $data, count($headers));
		
		return $data;
	}
	
	/**
	 * Imports providers data.
	 * 
	 * @throws Exception
	 */
	private function importProviders() 
	{
		Log::getLogger()->info("Importing providers...");
		
		$providersDataDir = IMPORT_DATA_DIR;
		$zipFile = null;
		$extractDir = null;
		
		// Note: We are not currently using zip files but I'm leaving this here 
		//       in case we someday do.
		if (isset(self::$imports['providers']['container'])) {
			// Only for zip files
			$zipFile = $this->dataDir . '/' . self::$imports['providers']['container'];
			if (!file_exists($zipFile)) {
				// At this point the container file should exist
				$this->sendLog = true;
				Log::getLogger()->error("Zip file not found: '$zipFile'.");
				
				return;
			}
			
			Log::getLogger()->info("Extracting zip file '$zipFile'");
			
			$zip = new ZipArchive();
			$zip->open($zipFile);
			
			$providersDataDir = IMPORT_DATA_DIR . "/providers/";
			
			// Check the contents of the zip
			$zipContents = array();
			$extractDir = $providersDataDir;
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$zipContents[$i] = $zip->getNameIndex($i);
			}
			foreach (self::$imports['providers']['files'] as $reqFile) {
				$i = array_search($reqFile, $zipContents);
				if ($i === false) {
					// We need all files to continue
					$this->sendLog = true;
					Log::getLogger()->error("Missing file $reqFile in zip $zipFile.");
					
					return;
				}
				
				// Extract (always extract to IMPORT_DATA_DIR despite what $this->dataDir is)
				$zip->extractTo($extractDir, $zipContents[$i]);
			}
			
			Log::getLogger()->info("Done.");
		}
		
		// Double check for all required files
		foreach (self::$imports['providers']['files'] as $fileName) {
			if (!file_exists("$providersDataDir/$fileName")) {
				$this->sendLog = true;
				Log::getLogger()->error("Missing required file $fileName. Aborting import.");
				return;
			}
		}
		
		$em = $this->getEntityManager();
		
		$this->importProvidersFile($em, 'Provider', 'providers', $providersDataDir . '/' . self::$imports['providers']['files']['providers'], true);
		$this->importOfficesFile($em, 'Office', 'offices', $providersDataDir . '/' . self::$imports['providers']['files']['offices']);
		$this->importProvidersFile($em, 'ProviderOffices', 'provider_offices', $providersDataDir . '/' . self::$imports['providers']['files']['provider_offices']);
		$this->importProvidersFile($em, 'ProviderLicenses', 'provider_licenses', $providersDataDir . '/' . self::$imports['providers']['files']['licenses']);
		$this->importProvidersFile($em, 'ProviderEducation', 'provider_education', $providersDataDir . '/' . self::$imports['providers']['files']['education']);
		$this->importProvidersFile($em, 'ProviderAppointment', 'provider_appointment', $providersDataDir . '/' . self::$imports['providers']['files']['appointments']);
		
		// Cleanup
		if (isset(self::$imports['providers']['container'])) {
			// Deletes the temp directory AND the zip file
			Util::delTree($extractDir);
			unlink($zipFile);
		}
		else {
			foreach (self::$imports['providers']['files'] as $fileName) {
				//unlink("$providersDataDir/$fileName");
			}
		}
		
		Log::getLogger()->info("Done.");
	}
	
	/**
	 * Helper function for importing provider offices.
	 * 
	 * @param $em
	 * @param $entityName
	 * @param $dbTable
	 * @param $fqFile
	 *
	 * @throws Exception
	 */
	private function importOfficesFile($em, $entityName, $dbTable, $fqFile) {
		Log::getLogger()->info("Importing file $fqFile");
		
		$this->transactionSafeTruncate($entityName);
		
		// Get the file handle
		$fp = fopen($fqFile, "r");
		
		$existing = array();
		if (!$this->truncateData) {
			$query = $em->getConnection()->prepare("SELECT officeID FROM $dbTable");
			$query->execute();
			$existing = $query->fetchAll(PDO::FETCH_COLUMN);
		}
		
		// Get the headers
		$headers = fgetcsv($fp, 0, '|');
		$officeIdIndex = array_search('Office_OfficeID', $headers);
		if ($officeIdIndex === false) {
			throw new Exception("Missing Office_OfficeID column in provider data!");
		}
		
		$batchSize = 200;
		$count = 0;
		$dataRow = 1;
		$officeIds = array();
		while ($data = fgetcsv($fp, 0, '|')) {
			
			$dataRow++;
			
			if (count($data) != count($headers)) {
				$this->sendLog = true;
				Log::getLogger()->warning("Wrong number of columns for row $dataRow.");
				continue;
			}
			
			if (empty($data[$officeIdIndex])) {
				$this->sendLog = true;
				Log::getLogger()->warning("Missing Office_OfficeID for row $dataRow.");
				continue;
			}
			
			if (in_array($data[$officeIdIndex], $officeIds)) {
				$this->sendLog = true;
				Log::getLogger()->warning("Found duplicate Office_OfficeID " . $data[$officeIdIndex] . " in row $dataRow. Ignoring.");
				continue;
			}
			$officeIds[] = $data[$officeIdIndex];
			
			if (in_array($data[$officeIdIndex], $existing)) {
				$entity = $em->getRepository($entityName)->findOneByOfficeID($data[$officeIdIndex]);
			}
			else {
				$entity = new $entityName();
				$entity->setCreated(new DateTime());
			}
			
			$initStatus = $entity->initWithImportData($em, $data, $headers, $dataRow);
			if ($initStatus !== true) {
				if ($initStatus === false) {
					// Caused a warning
					$this->sendLog = true;
				}
				// Ignore this row
				continue;
			}
			
			$entity->setLastUpdated(new DateTime());
			
			$em->persist($entity);
			$count++;
			if ($count == $batchSize) {
				$em->flush();
				$count = 0;
			}
		}
		$em->flush();
		$em->clear();
	}
	
	/**
	 * Helper function for importing the different providers files.
	 * 
	 * @param $em \Doctrine\ORM\EntityManager
	 * @param $entityName
	 * @param $dbTable
	 * @param $fqFile
	 * @param bool $ignoreDupNPIs
	 *
	 * @throws Exception
	 */
	private function importProvidersFile($em, $entityName, $dbTable, $fqFile, $ignoreDupNPIs = false) {
		
		Log::getLogger()->info("Importing file $fqFile");
		
		$this->transactionSafeTruncate($entityName);
		
		// Get the file handle
		$fp = fopen($fqFile, "r");
		
		$existing = array();
		if (!$this->truncateData) {
			$query = $em->getConnection()->prepare("SELECT providerNPI FROM $dbTable");
			$query->execute();
			$existing = $query->fetchAll(PDO::FETCH_COLUMN);
		}
		
		// Get the headers
		$headers = fgetcsv($fp, 0, '|');
		$npiIndex = array_search('Provider_NPI', $headers);
		if ($npiIndex === false) {
			throw new Exception("Missing Provider_NPI column in provider data!");
		}
		
		$batchSize = 200;
		$count = 0;
		$dataRow = 1;
		$npis = array();
		while ($data = fgetcsv($fp, 0, '|')) {
			
			$dataRow++;
			
			if (count($data) != count($headers)) {
				$this->sendLog = true;
				Log::getLogger()->warning("Wrong number of columns for row $dataRow.");
				continue;
			}
			
			if (empty($data[$npiIndex])) {
				$this->sendLog = true;
				Log::getLogger()->warning("Missing Provider_NPI for row $dataRow.");
				continue;
			}
			
			if ($ignoreDupNPIs && in_array($data[$npiIndex], $npis)) {
				$this->sendLog = true;
				Log::getLogger()->warning("Found duplicate Provider_NPI " . $data[$npiIndex] . " in row $dataRow. Ignoring row.");
				continue;
			}
			$npis[] = $data[$npiIndex];
			
			if (in_array($data[$npiIndex], $existing)) {
				$entity = $em->getRepository($entityName)->findOneByProviderNPI($data[$npiIndex]);
			}
			else {
				$entity = new $entityName();
				$entity->setCreated(new DateTime());
			}
			
			$initStatus = $entity->initWithImportData($em, $data, $headers, $dataRow);
			if ($initStatus !== true) {
				if ($initStatus === false) {
					// Caused a warning
					$this->sendLog = true;
				}
				// Ignore this row
				continue;
			}
			
			// TODO: The lawsonId field in this file is not reliable
			if ($entityName == 'ProviderAppointment') {
				// Look for an employee for the lawson id
				$employee = $em->getRepository('Employee')->findOneByEmployeeID($entity->getLawsonID());
				if (!empty($employee)) {
					// Add the employeeID to the provider record
					$provider = $em->getRepository('Provider')->findOneByProviderNPI($entity->getProviderNPI());
					$provider->setEmployeeID($entity->getLawsonID());
					$em->persist($provider);
				}
			}
			
			$entity->setLastupdated(new DateTime());
			
			$em->persist($entity);
			$count++;
			if ($count == $batchSize) {
				$em->flush();
				$count = 0;
			}
		}
		$em->flush();
		$em->clear();
	}
	
	/**
	 * Imports from the bizlist_export.csv file.
	 * 
	 * TODO: This is written for a one time import but eventually needs to be modified for auto imports
	 * 
	 * @throws Exception
	 */
	public function importBusinesses() {
		
		$fqFile = IMPORT_DATA_DIR . "/bizlist_export.csv";
		
		Log::getLogger()->info("Importing file $fqFile...");
		
		$em = $this->getEntityManager();
		$conn = $em->getConnection();
		$conn->beginTransaction();
		
		try {
			$this->transactionSafeTruncate('Business');
			
			// Get the file handle
			ini_set("auto_detect_line_endings", true);
			$fp = fopen($fqFile, "r");
			
			// Get the headers
			$headers = fgetcsv($fp, 0, '|');
			$indexCol = array_search('biz_ID', $headers);
			if ($indexCol === false) {
				throw new Exception("Missing biz_ID column in $fqFile header!");
			}
			
			$batchSize = 200;
			$count = 0;
			$dataRow = 1;
			$bizIds = array();
			while ($data = fgetcsv($fp, 0, '|')) {
				
				$dataRow++;
				
				if (count($data) != count($headers)) {
					$this->sendLog = true;
					Log::getLogger()->warning("Wrong number of columns for row $dataRow.");
					continue;
				}
				
				if (empty($data[$indexCol])) {
					$this->sendLog = true;
					Log::getLogger()->warning("Missing biz_ID for row $dataRow.");
					continue;
				}
				
				if (in_array($data[$indexCol], $bizIds)) {
					$this->sendLog = true;
					Log::getLogger()->warning("Found duplicate biz_ID " . $data[$indexCol] . " in row $dataRow. Ignoring row.");
					continue;
				}
				$bizIds[] = $data[$indexCol];
				
				$entity = new Business();
				
				$initStatus = $entity->initWithImportData($em, $data, $headers, $dataRow);
				if ($initStatus !== true) {
					// Ignore this row
					continue;
				}
				
				if ($entity->getParentBusinessID() == '') {
					$entity->setParentBusinessID(0);
				}
				
				if ($entity->getParentBusinessID() != 0 && !in_array($entity->getParentBusinessID(), $bizIds)) {
					Log::getLogger()->warning("Invalid Biz_ID_parent " . $entity->getParentBusinessID() . " in row $dataRow.");
				}
				
				$em->persist($entity);
				$count++;
				if ($count == $batchSize) {
					$em->flush();
					$count = 0;
				}
			}
			$em->flush();
			$em->clear();
			
			Log::getLogger()->info("Done.");
			
			$conn->commit();
		}
		catch (Exception $e) {
			Log::getLogger()->error($e->getMessage());
			
			$conn->rollBack();
		}
	}
	
	/**
	 * Auto import for departments
	 * 
	 * @throws Exception
	 * @throws \Doctrine\DBAL\DBALException
	 */
	public function importDepartments() {
		$fqFile = IMPORT_DATA_DIR . '/' . self::$imports['businesses']['files']['departments'];
		Log::getLogger()->info("Importing file $fqFile");
		
		if (!file_exists($fqFile)) {
			Log::getLogger()->warning("Missing file " . self::$imports['businesses']['files']['departments']);
			return;
		}
		
		// Get the file handle
		ini_set("auto_detect_line_endings", true);
		$fp = fopen($fqFile, "r");
		
		// Get the headers
		$headers = fgetcsv($fp, 0, ',');
		
		$costCenterCol = array_keys($headers, 'Dept #')[0];
		$deptNameCol = array_keys($headers, 'Department Name')[0];
		$plCol = array_keys($headers, 'PL')[0];
		$dirIdCol = array_keys($headers, 'Dir EE')[0];
		$vpIDCol = array_keys($headers, 'VP EE')[0];
		
		$em = $this->getEntityManager();
		
		$missingInDb = array();
		$costCentersInFile = array();
		
		$batchSize = 200;
		$count = 0;
		$dataRow = 1;
		while ($data = fgetcsv($fp, 0, ',')) {
			$dataRow++;
			
			if (count($data) != count($headers)) {
				Log::getLogger()->warning("Wrong number of columns for row $dataRow. Ignoring row.");
				continue;
			}
			
			if (empty($data[$costCenterCol])) {
				Log::getLogger()->warning("Invalid cost center (Dept #) in row $dataRow. Ignoring row.");
				continue;
			}
			
			$costCentersInFile[] = $data[$costCenterCol];
			
			$bus = $em->getRepository('Business')->findOneByCostCenter($data[$costCenterCol]);
			if (empty($bus)) {
				// Create a new record (note: this does not use @ImportMap
				$missingInDb[] = $data[$costCenterCol];
				Log::getLogger()->info("Creating new department with costCenter: {$data[$costCenterCol]}, name: {$data[$deptNameCol]}");
				$this->sendLog = true;
				$bus = new Business();
				$bus->setCostCenter($data[$costCenterCol]);
				$bus->setType('Department');
				$bus->setName($data[$deptNameCol]);
				$bus->setDisplayName($data[$deptNameCol]);
				$bus->setIsBlind(true);
				$bus->setIsActive(true);
				$bus->setIsNew(true);
				$bus->setCreated(new DateTime());
				$bus->setBusinessID($bus->getNewBusinessID($em));
			}
			
			$bus->setProcessLevel($data[$plCol]);
			$bus->setDirectorID($data[$dirIdCol]);
			$bus->setVpID($data[$vpIDCol]);
			$bus->setLastUpdated(new DateTime());
			$bus->setSource('HR');
			
			$em->persist($bus);
			
			$count++;
			if ($count == $batchSize) {
				$em->flush();
				$count = 0;
			}
		}
		
		$em->flush();
		$em->clear();
		
		// Find cost centers in the db that don't exist in the file
		$query = $em->getConnection()->prepare("SELECT costCenter FROM businesses WHERE costCenter != 0 AND isActive = 1");
		$query->execute();
		$costCenters = $query->fetchAll(PDO::FETCH_COLUMN);
		
		$missingInFile = array();
		foreach ($costCenters as $costCenter) {
			if (!in_array($costCenter, $costCentersInFile)) {
				$missingInFile[] = $costCenter;
				
				$business = $em->getRepository('Business')->findOneByCostCenter($costCenter);
				$business->setIsActive(false);
				$business->setLastUpdated(new DateTime());
				$em->persist($business);
				$em->flush();
				
				Log::getLogger()->warning("Cost center $costCenter does not appear in the file. Inactivated it.");
			}
		}
		
		Log::getLogger()->info("Done.");
	}
	
	/**
	 * One time import of the Keyword_export.csv file
	 */
	public function importKeywords() {
		$fqFile = IMPORT_DATA_DIR . "/keyword_export.csv";
		Log::getLogger()->info("Importing search phrases from file: $fqFile...");
		
		if (!file_exists($fqFile)) {
			$this->sendLog = true;
			Log::getLogger()->error("Missing required file $fqFile. Aborting import.");
			return;
		}
		
		// Get the file handle
		ini_set("auto_detect_line_endings", true);
		$fp = fopen($fqFile, "r");
		
		$em = $this->getEntityManager();
		$conn = $em->getConnection();
		$conn->beginTransaction();
		
		$this->transactionSafeTruncate('SearchPhrase');
		
		try {
			
			// Get the headers
			$headers = fgetcsv($fp, 0, '|');
			
			$batchSize = 200;
			$count = 0;
			$dataRow = 1;
			while ($data = fgetcsv($fp, 0, '|')) {
				$dataRow++;
				
				if (count($data) != count($headers)) {
					Log::getLogger()->warning("Wrong number of columns for row $dataRow. Ignoring row.");
					continue;
				}
				
				$entity = new SearchPhrase();
				
				$initStatus = $entity->initWithImportData($em, $data, $headers, $dataRow);
				if ($initStatus !== true) {
					if ($initStatus === false) {
						// Caused a warning
						$this->sendLog = true;
					}
					// Ignore this row
					continue;
				}
				
				// Normalize the type
				if ($entity->getType() == 'Person') {
					if (strlen($entity->getTypeId()) == 10) {
						$entity->setType('provider');
						
						// Exists
						$provider = $em->getRepository('Provider')->findOneByProviderNPI($entity->getTypeId());
						if ($provider == null) {
							Log::getLogger()->warning("Invalid provider NPI on row $dataRow. Ignoring row.");
							continue;
						}
						
						// Commas
						$commaPos = stripos($entity->getPhrase(), ',');
						if ($commaPos !== false) {
							$entity->setPhrase(substr($entity->getPhrase(), 0, $commaPos));
						}
						
						// Exact matches
						$parts = explode(' ', $entity->getPhrase());
						if (count($parts) == 2) {
							if (strtolower($provider->getFirstName()) == strtolower($parts[0]) && strtolower($provider->getLastName()) == strtolower($parts[1])) {
								Log::getLogger()->warning("Fond provider exact match (first name, last name) on row $dataRow. Ignoring row.");
								continue;
							}
							else if (stripos($provider->getFirstName(), $parts[0]) === 0 && stripos($provider->getLastName(), $parts[1]) === 0) {
								Log::getLogger()->warning("Fond provider starts with (first name or last name) on row $dataRow. Ignoring row.");
								continue;
							}
						}
					}
					else {
						$entity->setType('employee');
						
						// Exists
						$employee = $em->getRepository('Employee')->findOneByEmployeeID($entity->getTypeId());
						if ($employee == null) {
							Log::getLogger()->warning("Invalid employee ID on row $dataRow. Ignoring row.");
							continue;
						}
						
						// Commas
						$commaPos = stripos($entity->getPhrase(), ',');
						if ($commaPos !== false) {
							$entity->setPhrase(substr($entity->getPhrase(), 0, $commaPos));
						}
						
						// Exact matches
						$parts = explode(' ', $entity->getPhrase(), 2);
						if (count($parts) == 2) {
							if (strtolower($employee->getFirstName()) == strtolower($parts[0]) && strtolower($employee->getLastName()) == strtolower($parts[1])) {
								Log::getLogger()->warning("Fond employee exact match (first name, last name) on row $dataRow. Ignoring row.");
								continue;
							}
							else if (stripos($employee->getFirstName(), $parts[0]) === 0 && stripos($employee->getLastName(), $parts[1]) === 0) {
								Log::getLogger()->warning("Fond employee starts with (first name or last name) on row $dataRow. Ignoring row.");
								continue;
							}
						}
					}
				}
				else if ($entity->getType() == 'Department' || $entity->getType() == 'Business') {
					$entity->setType('business');
					
					$business = $em->getRepository('Business')->findOneByBusinessID($entity->getTypeId());
					if ($business == null) {
						Log::getLogger()->warning("Invalid business ID on row $dataRow. Ignoring row.");
						continue;
					}
					
					if (strtolower($entity->getPhrase()) == strtolower($business->getName())) {
						Log::getLogger()->warning("Fond business exact match (name) on row $dataRow. Ignoring row.");
						continue;
					}
				}
				else {
					Log::getLogger()->warning("Unknown type " . $entity->getType());
					continue;
				}
				
				$em->persist($entity);
				$count++;
				if ($count == $batchSize) {
					$em->flush();
					$count = 0;
				}
			}
			
			$em->flush();
			$em->clear();
			
			fclose($fp);
			
			Log::getLogger()->info("Done.");
			
			$conn->commit();
		}
		catch (Exception $e) {
			Log::getLogger()->error($e->getMessage());
			
			$conn->rollBack();
		}
	}
	
	public function importAddresses() {
		$fqFile = IMPORT_DATA_DIR . "/address_export.csv";
		Log::getLogger()->info("Importing addresses from file: $fqFile...");
		
		if (!file_exists($fqFile)) {
			$this->sendLog = true;
			Log::getLogger()->error("Missing required file $fqFile. Aborting import.");
			return;
		}
		
		// Get the file handle
		ini_set("auto_detect_line_endings", true);
		$fp = fopen($fqFile, "r");
		
		$em = $this->getEntityManager();
		$conn = $em->getConnection();
		$conn->beginTransaction();
		
		try {
			// Get the headers
			$headers = fgetcsv($fp, 0, ',', '"');
			
			$dataRow = 1;
			while ($data = fgetcsv($fp, 0, ',', '"')) {
				$dataRow++;
				
				if (count($data) != count($headers)) {
					Log::getLogger()->warning("Wrong number of columns for row $dataRow. Ignoring row.");
					continue;
				}
				
				$md = new MetaData();
				
				$type = $data[array_keys($headers, 'profile_type')[0]];
				$typeId = $data[array_keys($headers, 'profile_type_id')[0]];
				if ($type == 'Business') {
					$type = 'business';
					if(!$md->entityExists($em, 'Business', 'businessID', $typeId)) {
						Log::getLogger()->warning("Unknown businessID $typeId on row $dataRow. Ignoring row.");
						continue;
					}
				}
				else if ($type == 'Person') {
					$type = 'employee';
					if(!$md->entityExists($em, 'Employee', 'employeeID', $typeId)) {
						Log::getLogger()->warning("Unknown employeeID $typeId on row $dataRow. Ignoring row.");
						continue;
					}
				}
				
				$md->setType($type);
				$md->setTypeID($typeId);
				$md->setSource($data[array_keys($headers, 'source')[0]]);
				$md->setLabel($data[array_keys($headers, 'label')[0]]);
				$md->setAudience($data[array_keys($headers, 'audience')[0]]);
				$md->setValueType($data[array_keys($headers, 'communication_type')[0]]);
				
				$value = array(
					"address1" => $data[array_keys($headers, 'address1')[0]],
					"address2" => $data[array_keys($headers, 'address2')[0]],
					"city" => $data[array_keys($headers, 'city')[0]],
					"state" => $data[array_keys($headers, 'state')[0]],
					"zip" => $data[array_keys($headers, 'zip')[0]]
				);
				$md->setValue($value);
				
				$dt = new DateTime();
				$md->setCreated($dt);
				$md->setLastUpdated($dt);
				
				$em->persist($md);
				$em->flush();
			}
			
			Log::getLogger()->info("Done.");
			
			$conn->commit();
		}
		catch (Exception $e) {
			Log::getLogger()->error($e->getMessage());
			
			$conn->rollBack();
		}
	}
	
	public function fixPhonesInMetaData() {
		$em = $this->getEntityManager();
		$em->getConnection()->beginTransaction();
		
		$mdPhones = $em->getRepository('MetaData')->findByValueType('Phone');
		
		$batchSize = 200;
		$count = 0;
		$total = 0;
		foreach ($mdPhones as $mdPhone) {
			$number = $mdPhone->getValue();
			$ext = $mdPhone->getExtension();
			$hours = $mdPhone->getHours();
			
			$newValue = array(
				"number" => $number,
				"ext" => $ext,
				"hours" => $hours
			);
			
			$mdPhone->setValue($newValue);
			$mdPhone->setExtension('');
			$mdPhone->setHours('');
			
			$em->persist($mdPhone);
			$total++;
			
			$count++;
			if ($count == $batchSize) {
				$em->flush();
				$count = 0;
			}
		}
		
		$em->flush();
		
		$em->getConnection()->commit();
	}
	
	/**
	 * Makes a backup of the relevant tables in the database. The back is saved to a .sql file 
	 * in IMPORT_BACKUP_DIR.
	 * 
	 * @throws Exception
	 */
	private function makeBackup() 
	{
		// TODO: Clean up old backups. Keep 5 days worth.
		
		Log::getLogger()->info("Backing up database...");
		$this->backupFileName = '/import_backup_' . date("Y-m-d_H-i-s") . '.sql';
		$backupFile = IMPORT_BACKUP_DIR . $this->backupFileName;
		$command = "mysqldump --opt -h localhost -u " . DOCTRINE_DB_USER . " -p" . DOCTRINE_DB_PASSWORD . " " . DOCTRINE_DB_NAME . " employees providers provider_appointment provider_education provider_licenses provider_offices offices > $backupFile";
		$result = system($command);
		if ($result === false) {
			throw new Exception("Could not make backup in " . IMPORT_BACKUP_DIR . ", canceling import!");
		}
		Log::getLogger()->info("Done.");
	}
	
	/**
	 * Saves a record of the import in the database table 'imports'.
	 * 
	 * @var $error
	 */
	private function saveImport($error = false) {
		$em = $this->getEntityManager();
		$import = new ImportEntity();
		$import->setDateRun(new DateTime());
		if (!empty($this->backupFileName)) {
			$import->setBackupFile($this->backupFileName);
		}
		if ($this->sendLog) {
			$import->setWarningsOccurred(true);
		}
		if ($error) {
			$import->setErrorsOccured(true);
		}
		$em->persist($import);
		$em->flush();
	}
	
	/**
	 * Returns an array of all previous imports from the 'imports' table in the database.
	 * 
	 * @return array
	 */
	public function getPreviousImports() {
		$em = $this->getEntityManager();
		$imports = $em->getRepository('Imports')->findAll();
		return $imports;
	}
	
	/**
	 * Emails the current log file.
	 *
	 * @throws \PHPMailer\PHPMailer\Exception
	 */
	private function sendLog() {
		if (EMAIL_LOG === false) {
			return;
		}
		
		Log::getLogger()->info("Sending log...");
		$logFilePath = Log::getLogger()->getLogFilePath();
		
		$mail = new PHPMailer;
		$mail->isSMTP();
		//$mail->SMTPDebug = 2;
		//$mail->Debugoutput = 'html';
		$mail->Host = SMTP_HOST;
		$mail->Port = SMTP_PORT;
		$mail->SMTPAutoTLS = false;
		
		$mail->setFrom(SMTP_LOG_FROM_ADDRESS, SMTP_LOG_FROM_NAME);
		
		$toAddys = explode(',', SMTP_LOG_TO_ADDRESS);
		$toNames = explode(',', SMTP_LOG_TO_NAME);
		
		if (count($toAddys) != count($toNames)) {
			Log::getLogger()->error("Different number of values for SMTP_LOG_TO_ADDRESS and SMTP_LOG_TO_NAMES");
			return;
		}
		
		for ($i = 0; $i < count($toAddys); $i++) {
			$mail->addAddress($toAddys[$i], $toNames[$i]);
		}
		
		$mail->Subject = LOG_EMAIL_SUBJECT;
		$mail->Body = file_get_contents($logFilePath);
		
		//send the message, check for errors
		if (!$mail->send()) {
			echo "Mailer Error: " . $mail->ErrorInfo;
			Log::getLogger()->info("Error sending email: " . $mail->ErrorInfo);
		}
		
		Log::getLogger()->info("Done.");
	}
	
	protected function normalizePhoneNumber($number, $dataRow) {
		$number = preg_replace("/[^0-9,.]/", "", $number);
		
		if (empty($number)) {
			Log::getLogger()->warning("Missing phone number for row $dataRow.");
		}
		else if (strlen($number) != 10) {
			Log::getLogger()->warning("Possible invalid phone number {$number} for row $dataRow.");
			$this->sendLog = true;
		}
		
		return $number;
	}
	
	/**
	 * Looks for any entities that don't exist and deletes the meta data associated with them.
	 */
	public function cleanUpMetaData() {
		Log::getLogger()->info("Cleaning up meta data...");
		
		$em = $this->getEntityManager();
		
		$sql = "DELETE md FROM meta_data md LEFT JOIN employees e ON md.typeID = e.employeeID LEFT JOIN businesses b ON md.typeID = b.businessID WHERE (md.type='employee' AND e.employeeID IS NULL) OR (md.type='business' AND b.businessID IS NULL);";
		$stmt = $em->getConnection()->prepare($sql);
		$success = $stmt->execute();
		if ($success !== true) {
			Log::getLogger()->error("Error cleaning up meta data.");
			$this->sendLog = true;
		}
		
		Log::getLogger()->info($stmt->rowCount() . ' rows deleted.');
		Log::getLogger()->info("Done");
	}
	
	public function diffEmployeesFromFile() {
		// We want a report of employees that are in the database but not in the current NAHAcrtive.csv file.
		// Not counting 'User' sourced or terminated employees
		Log::getLogger()->info("Starting diff...");
		
		$em = $this->getEntityManager();
		
		$fqFile = IMPORT_DATA_DIR . "/" . self::$imports['employees']['files']['employees']['filename'];
		
		// Get the file handle
		$fp = fopen($fqFile, "r");
		
		// Get an array of existing employee IDs
		$query = $em->getConnection()->prepare("SELECT employeeID FROM employees WHERE source='HR' AND (terminationDate IS NULL OR terminationDate > NOW())");
		$query->execute();
		$dbIds = $query->fetchAll(PDO::FETCH_COLUMN);
		
		// Get the headers
		$headers = fgetcsv($fp, 0, ',');
		$idIndex = array_search('EMPLOYEE', $headers);
		if ($idIndex === false) {
			throw new Exception("Missing EMPLOYEE column in provider data!");
		}
		
		$fileIds = array();
		while ($data = fgetcsv($fp, 0, ',')) {
			
			if (count($data) != count($headers)) {
				$this->sendLog = true;
				Log::getLogger()->warning("Wrong number of columns for row $dataRow. Ignoring row.");
				continue;
			}
			
			// Check for duplicate employeeID
			if (in_array($data[$idIndex], $fileIds)) {
				$this->sendLog = true;
				Log::getLogger()->warning("Found duplicate id " . $data[$idIndex] . " in row $dataRow. Ignoring row.");
				continue;
			}
			$fileIds[] = $data[$idIndex];
		}
		
		$diffIds = array();
		foreach ($dbIds as $dbId) {
			if (!in_array($dbId, $fileIds)) {
				$diffIds[] = $dbId;
			}
		}
		
		$in = implode(',', $diffIds);
		$employees = $em->createQueryBuilder()
			->select('e')
			->from('Employee', 'e')
			->where('e.employeeID IN (:ids)')
			->setParameter('ids', $diffIds)
			->getQuery()
			->getResult();
		
		$file = fopen(LOG_DIR . '/employee-diff.csv', 'w');
		
		$header = array(
			'employeeID',
			'firstName',
			'lastName',
			'title',
			'costCenter',
			'userName');
		fputcsv($file, $header);
		foreach ($employees as $employee) {
			$fields = array(
				$employee->getEmployeeID(),
				$employee->getFirstName(),
				$employee->getLastName(),
				$employee->getTitle(),
				$employee->getCostCenter(),
				$employee->getUserName()
			);
			fputcsv($file, $fields);
		}
		
		fclose($file);
	}
		
	// TODO: Move the following methods to Gozer Core
	
	/**
	 * Performs a transaction safe truncate on the table for the given Doctrine Entity. 
	 * Note that this does NOT reset an auto incremented id field.
	 * 
	 * @param $entityName
	 *
	 * @throws \Doctrine\DBAL\DBALException
	 */
	protected function transactionSafeTruncate($entityName) {
		$em = $this->getEntityManager();
		$cmd = $em->getClassMetadata($entityName);
		$conn = $em->getConnection();
		
		$conn->query('SET FOREIGN_KEY_CHECKS=0');
		$conn->query('DELETE FROM ' . $cmd->getTableName());
		// Beware of ALTER TABLE here--it's another DDL statement and will cause
		// an implicit commit.
		$conn->query('SET FOREIGN_KEY_CHECKS=1');
	}
}