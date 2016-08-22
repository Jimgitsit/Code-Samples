<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

/** CLI CONTROLLERS **/
//$route['data_import'] = 'default/DataImport';
$route['aggregate/(:any)/(:any)'] = 'default/Aggregate/index/$1/$2';
$route['aggregate/(:any)'] = 'default/Aggregate/index/$1';
$route['aggregate'] = 'default/Aggregate/index';
$route['emailer'] = 'default/Emailer/index';
// $route['data_import_google_doc/(:any)'] = 'default/DataImportGoogleDoc/index/$1';

/** ADMIN CONTROLLERS **/
$route['influencers'] = 'admin/Influencers';
$route['admin/logout'] = 'admin/Influencers/logout';
$route['admin/downloadExport/(:any)'] = 'admin/Influencers/downloadExport/$1';
$route['admin/exportData'] = 'admin/Influencers/exportData';
$route['admin/getData'] = 'admin/Influencers/getData';
$route['admin/getDataNew'] = 'admin/Influencers/getDataNew';
// $route['admin/getDataNew/(:any)/(:any)'] = 'admin/Influencers/getDataNew/$1/$2';
$route['admin/saveFilterSet'] = 'admin/Influencers/saveFilterSet';
$route['admin/saveRating'] = 'admin/Influencers/saveRating';
$route['admin/emailInUse'] = 'admin/Influencers/emailInUse';
$route['admin/addInfluencer'] = 'admin/Influencers/addInfluencer';
$route['influenceredit'] = 'admin/InfluencerEdit';
$route['influenceredit/(:any)'] = 'admin/InfluencerEdit/$1';

/** INFLUENCER CONTROLLERS **/
$route['profile'] = 'influencer/Profile';
$route['profile/(:any)'] = 'influencer/Profile/$1';
$route['profile/finished'] = 'influencer/Finish';
$route['auth/(:any)'] = 'influencer/Auth/$1';
$route['deauth/(:any)'] = 'influencer/Deauth/platform/$1/$2';
$route['account'] = 'influencer/Account';
$route['influencer/logout'] = 'influencer/Profile/logout';
$route['home'] = 'influencer/Dashboard';

/** DEFAULT CONTROLLERS **/
$route['contactformsubmit'] = 'default/Home/contactformsubmit';
$route['confirmation'] = 'default/Home/confirmation';
$route['press'] = 'default/Home/press';
$route['portfolio'] = 'default/Home/portfolio';
$route['about-instafluence'] = 'default/Home/about';
$route['app'] = 'default/Home/app';
$route['brand'] = 'default/Home/brand';
$route['influencer'] = 'default/Home/influencer';
$route['login'] = 'default/Login';
$route['signup'] = 'default/Signup';
$route['setpassword'] = 'default/SetPassword';
$route['privacy'] = 'default/Privacy';

$route['default_controller'] = 'default/Home/index';
$route['404_override'] = '';


/* End of file routes.php */
/* Location: ./application/config/routes.php */
