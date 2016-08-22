<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['mailtype'] = 'html';
$config['wordwrap'] = false;

$host = $_SERVER['HTTP_HOST'];
$split = explode('.', $host);

if ($split[1] === 'local') {
	$config['protocol'] = 'smtp';
	$config['smtp_host'] = '';
	$config['smtp_user'] = '';
	$config['smtp_pass'] = '';
}