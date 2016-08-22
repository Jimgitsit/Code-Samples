<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$influencer_url = '/account/';
$influencer_url2 = '/profile/';
$client_url = '/';
$admin_url = '/influencers/';
$sadmin_url = '/';


$config['accounts'][1] = array(
	'name' => 'influencer',
	'url' => $influencer_url,
	'url2' => $influencer_url2
);
$config['accounts']['influencer'] = array(
	'code' => 1,
	'url' => $influencer_url,
	'url2' => $influencer_url2
);

$config['accounts'][2] = array(
	'name' => 'client',
	'url' => $client_url
);
$config['accounts']['client'] = array(
	'code' => 2,
	'url' => $client_url
);

$config['accounts'][3] = array(
	'name' => 'admin',
	'url' => $admin_url
);
$config['accounts']['admin'] = array(
	'code' => 3,
	'url' => $admin_url
);

$config['accounts'][4] = array(
	'name' => 'super_admin',
	'url' => $sadmin_url
);
$config['accounts']['super_admin'] = array(
	'code' => 4,
	'url' => $sadmin_url
);