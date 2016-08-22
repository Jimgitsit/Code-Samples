<?php

/**
 * Bootstrap file for running the automated import service.
 */

chdir(dirname($_SERVER['SCRIPT_FILENAME']));
require_once('../../vendor/autoload.php');
$import = new Import();
$import->autoImport();