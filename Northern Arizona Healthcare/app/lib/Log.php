<?php

if (!defined('LOG_DIR')) {
	define('LOG_DIR', dirname($_SERVER['SCRIPT_FILENAME']) . '/logs');
}

if (!defined('LOG_LEVEL')) {
	define('LOG_LEVEL', \Psr\Log\LogLevel::DEBUG);
}

/**
 * Class Log
 *
 * Singleton wrapper for KLogger (https://github.com/katzgrau/KLogger).
 *
 * Example usage: Log::getLogger()->info("message");
 *
 * You can override defines LOG_DIR and LOG_LEVEL. Defaults are
 * dirname($_SERVER['SCRIPT_FILENAME']) . '/logs' and \Psr\Log\LogLevel::DEBUG respectively.
 */
class Log {
	private static $logger = null;

	private function __construct() {}

	public static function getLogger() {
		if (self::$logger === null) {
			self::$logger = new \Katzgrau\KLogger\Logger(LOG_DIR, LOG_LEVEL);
		}

		return self::$logger;
	}
}