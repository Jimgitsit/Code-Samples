<?php

use PHPUnit\Framework\TestCase;

/**
 * Created by PhpStorm.
 * User: jim
 * Date: 7/11/16
 * Time: 10:14 AM
 */
class APITest extends TestCase {
	// TODO: Finish this
	public function testAPI() {
		$ds = new NAHDirectoryService(true);
		
		return;
		
		$ds->defaultAction();
		
		$ds->getAllBusinesses();
		
		$ds->getAllEmployees();
		
		$ds->getAllProviders();
		
		$ds->getBusinessByCostCenter(14788);
		
		$business = $ds->getBusinessById(198);
		$this->assertNotNull($business);
		
		$ds->getEmployeeById();
		
		$ds->getEmployeeByUserName();
		
		$ds->getEmployeesByCostCenter();
		
		$ds->getMetaDataById();
		
		$ds->getProviderByNPI();
		
		$ds->removeEmployee();
		
		$ds->removeMetaData();
		
		$ds->removeSearchPhrase();
		
		// Save existing business
		$ds->saveBusiness();
		
		// Save a new business
		$ds->saveBusiness();
		
		$ds->removeBusiness();
		
		$ds->saveEmployee();
		
		$ds->saveMetaData();
		
		$ds->saveSearchPhrase();
		
		$ds->search();
		
		$ds->searchBusinesses();
		
		$ds->searchEmployees();
		
		$ds->searchProviders();
	}
	
	public function testPIOauth() {
		
	}
}