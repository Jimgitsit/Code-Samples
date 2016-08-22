<?php

use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\ODM\MongoDB\DocumentManager;

class AlaCarteDB {
	public static function connect() {
		// Initialize db connection and document manager
		$connection = new Connection(MONGO_DB_HOST);
		$config = new Configuration();
		$config->setProxyDir('../cache/mongo/proxies');
		$config->setProxyNamespace('MongoProxies');
		$config->setHydratorDir('../cache/mongo/hydrators');
		$config->setHydratorNamespace('MongoHydrators');
		$config->setDefaultDB(MONGO_DB_NAME);
		$config->setMetadataDriverImpl(AnnotationDriver::create('../app/models/MongoDocs'));
		AnnotationDriver::registerAnnotationClasses();
		return DocumentManager::create($connection, $config);
	}
}