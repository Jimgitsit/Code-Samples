<?php

/**
 * Bootstrap for AltoRouter and Symphony YAML parser.
 */

define('ROUTES_FILE', '../config/routes.yaml');
define('COMPOSER_AUTOLOAD_FILE', '../../vendor/autoload.php');

use Symfony\Component\Yaml\Parser;

try {
	/**
	 * Composer's autoloader
	 */
	$loader = require_once(COMPOSER_AUTOLOAD_FILE);
	
	/**
	 * Routing
	 */
	// Init router
	$router = new AltoRouter();
	//$routes = json_decode(file_get_contents(ROUTES_FILE));
	$yaml = new Parser();
	$routes = $yaml->parse(file_get_contents(ROUTES_FILE));
	foreach ($routes as $name => $route) {
		$router->map($route['method'], $route['route'], array('c' => $route['controller'], 'a' => $route['action']), $name);
	}
	
	// Match current request
	$match = $router->match();
	$test = class_exists($match['target']['c']);
	if ($match && method_exists($match['target']['c'], $match['target']['a'])) {
		$controller = new $match['target']['c'];
		call_user_func_array(array($controller, $match['target']['a']), $match['params']);
	}
	else {
		// No route was matched
		header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
	}
}
catch(Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"] . ' 500 Internal Server Error');
	echo($e->getMessage());
}