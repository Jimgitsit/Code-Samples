<?php

chdir(dirname($_SERVER['SCRIPT_FILENAME']));
require_once('../../vendor/autoload.php');
$import = new Import();
$import->importEmployeeUsernames();