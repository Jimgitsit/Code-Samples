/*
 This script (Javascript Timezone Detection) detects Olson timezone  
 ID of user's system's timezone using getTimezoneOffset(). Detection
 is tested and passed 100% in the following conditions:

 * Windows 7,Opera 11.00
 * Windows 7,Firefox 3.6.13
 * Windows 7,Safari 5.0.3
 * Windows 7,Google Chrome 8.0.552.237
 * Windows 7,Internet Explorer 8.0.7600.16385

 * Mac OS X,Google Chrome 8.0.552.237
 * Mac OS X,Firefox 3.6.13
 * Mac OS X,Opera 11.01
 * Mac OS X,Safari Version 5.0.3

 * Centos 5,Firefox 3.6.13
 * Centos 5,Safari
 * Centos 5,Opera 11.00

 The script has ability to detect 90 timezones.

 Every row in zones-array consist of
 0: Olson timezone ID,
 1-6: Transition start time in UTC (usually summer time start and sometimes winter time start) 
 7: Utc offset in minutes
 8: 1 = has transition,0 = has not transition


 Timezones are listed in zones-array this way:  
 - first comes section of timezones, that have transitions and then section of timezones that have no transitions  
 - Both sections are then ordered by offset  
 - Finally the has transitions section is ordered by the transition start time.    
 If you add more timezones or replaces some, please be sure to maintain this order, otherwise the detection fails in some cases.

 Minified size: about 4.46KB  
 */
var zones = new Array(
	// FIRST DST TIMEZONES
	[ 'Pacific/Apia',                    2011,3,3,11,0,0,      -660, 1 ]/* For Opera Linux. Unexpectedly winter-offset. */,

	[ 'Pacific/Apia',                    2010,8,26,11,0,0,     -600, 1 ]/* STD: -660 */,

	[ 'America/Adak',                    2011,2,13,12,0,0,     -540, 1 ]/* STD: -600 */,

	[ 'America/Anchorage',               2011,2,13,11,0,0,     -480, 1 ]/* STD: -540 */,

	[ 'America/Los_Angeles',             2011,2,13,10,0,0,     -420, 1 ]/* STD: -480 */,
	[ 'America/Santa_Isabel',            2011,3,3,10,0,0,      -420, 1 ]/* STD: -480 */,

	[ 'America/Denver',                  2011,2,13,9,0,0,      -360, 1 ]/* STD: -420 */,
	[ 'America/Mazatlan',                2011,3,3,9,0,0,       -360, 1 ]/* STD: -420 */,

	[ 'America/Chicago',                 2011,2,13,8,0,0,      -300, 1 ]/* STD: -360 */,
	[ 'America/Mexico_City',             2011,3,3,8,0,0,       -300, 1 ]/* STD: -360 */,
	[ 'Pacific/Easter',                  2011,9,9,4,0,0,       -300, 1 ]/* STD: -360 */,

	[ 'America/Havana',                  2011,2,13,5,0,0,      -240, 1 ]/* STD: -300 */,
	[ 'America/New_York',                2011,2,13,7,0,0,      -240, 1 ]/* STD: -300 */,

	[ 'America/Goose_Bay',               2011,2,13,4,1,0,      -180, 1 ]/* STD: -240 */,
	[ 'America/Halifax',                 2011,2,13,6,0,0,      -180, 1 ]/* STD: -240 */,
	[ 'Atlantic/Stanley',                2011,8,4,6,0,0,       -180, 1 ]/* STD: -240 */,
	[ 'America/Asuncion',                2011,9,2,4,0,0,       -180, 1 ]/* STD: -240 */,
	[ 'America/Santiago',                2011,9,9,4,0,0,       -180, 1 ]/* STD: -240 */,
	[ 'America/Campo_Grande',            2011,9,16,4,0,0,      -180, 1 ]/* STD: -240 */,

	[ 'America/St_Johns',                2011,2,13,3,31,0,     -150, 1 ]/* STD: -210 */,

	[ 'America/Miquelon',                2011,2,13,5,0,0,      -120, 1 ]/* STD: -180 */,
	[ 'America/Godthab',                 2011,2,27,1,0,0,      -120, 1 ]/* STD: -180 */,
	[ 'America/Montevideo',              2011,9,2,5,0,0,       -120, 1 ]/* STD: -180 */,
	[ 'America/Sao_Paulo',               2011,9,16,3,0,0,      -120, 1 ]/* STD: -180 */,

	[ 'Atlantic/Azores',                 2011,2,27,1,0,0,      0, 1 ]/* STD: -60 */,
	[ 'Atlantic/Azores',                 2010,2,28,3,0,0,      0, 1 ], /* Windows fix */

	[ 'Europe/London',                   2011,2,27,1,0,0,      60, 1 ]/* STD: 0 */,

	[ 'Europe/Berlin',                   2011,2,27,1,0,0,      120, 1 ]/* STD: 60 */,
	[ 'Africa/Windhoek',                 2011,8,4,1,0,0,       120, 1 ]/* STD: 60 */,

	[ 'Asia/Gaza',                       2011,2,25,22,1,0,     180, 1 ]/* STD: 120 */,
	[ 'Asia/Beirut',                     2011,2,26,22,0,0,     180, 1 ]/* STD: 120 */,
	[ 'Europe/Minsk',                    2011,2,27,0,0,0,      180, 1 ]/* STD: 120 */,
	// Istanbul and Helsinki are the same, please select which one to use
	//  [ 'Europe/Istanbul',                 2011,2,27,1,0,0,      180, 1 ]/* STD: 120 */,
	[ 'Europe/Helsinki',                 2011,2,27,1,0,0,      180, 1 ]/* STD: 120 */,
	[ 'Asia/Jerusalem',                  2011,3,1,0,0,0,       180, 1 ]/* STD: 120 */,
	[ 'Africa/Cairo',                    2011,3,28,22,0,0,     180, 1 ]/* STD: 120 */,
	[ 'Asia/Damascus',                   2011,9,27,21,0,0,     120, 1 ]/* Unexpectedly here winter-offset */,
	[ 'Asia/Amman',                      2011,9,27,22,0,0,     120, 1 ]/* Unexpectedly here winter-offset */,

	[ 'Europe/Moscow',                   2011,2,26,23,0,0,     240, 1 ]/* STD: 180 */,

	[ 'Asia/Tehran',                     2009,2,21,20,30,0,    270, 1 ]/* STD: 210 */,

	[ 'Asia/Yerevan',                    2011,2,26,22,0,0,     300, 1 ]/* STD: 240 */,
	[ 'Asia/Baku',                       2011,2,27,0,0,0,      300, 1 ]/* STD: 240 */,

	[ 'Asia/Yekaterinburg',              2011,2,26,21,0,0,     360, 1 ]/* STD: 300 */,

	[ 'Asia/Omsk',                       2011,2,26,20,0,0,     420, 1 ]/* STD: 360 */,

	[ 'Asia/Krasnoyarsk',                2011,2,26,19,0,0,     480, 1 ]/* STD: 420 */,

	[ 'Asia/Irkutsk',                    2011,2,26,18,0,0,     540, 1 ]/* STD: 480 */,

	[ 'Asia/Yakutsk',                    2011,2,26,17,0,0,     600, 1 ]/* STD: 540 */,

	[ 'Australia/Adelaide',              2011,9,1,16,30,0,     630, 1 ]/* STD: 570 */,

	[ 'Asia/Vladivostok',                2011,2,26,16,0,0,     660, 1 ]/* STD: 600 */,
	[ 'Australia/Lord_Howe',             2011,9,1,15,30,0,     660, 1 ]/* STD: 630 */,
	[ 'Australia/Sydney',                2011,9,1,16,0,0,      660, 1 ]/* STD: 600 */,

	[ 'Pacific/Fiji',                    2011,2,5,14,0,0,      720, 1 ]/* STD: 660 */,
	[ 'Pacific/Fiji',                    2011,2,26,14,0,0,     720, 1 ]/* STD: 660 */,
	[ 'Asia/Kamchatka',                  2011,2,26,15,0,0,     720, 1 ]/* STD: 660 */,

	[ 'Pacific/Auckland',                2011,8,24,14,0,0,     780, 1 ]/* STD: 720 */,

	[ 'Pacific/Chatham',                 2011,8,24,14,0,0,     825, 1 ]/* STD: 765 */,

	// AND THEN NON-DST TIMEZONES: 

	[ 'Etc/GMT+12',                      2011,0,1,0,0,0,       -720, 0 ]/* STD: -720 */,

	[ 'Pacific/Pago_Pago',               2011,0,1,0,0,0,       -660, 0 ]/* STD: -660 */,

	[ 'Pacific/Kiritimati',              2010,0,1,0,0,0,        840, 0, '(LINT)' ], /* To prevent Firefox detecting Pacific/Kiritimati as Pacific Honolulu */
	[ 'Pacific/Honolulu',                2011,0,1,0,0,0,       -600, 0 ]/* STD: -600 */,

	[ 'Pacific/Marquesas',               2011,0,1,0,0,0,       -570, 0 ]/* STD: -570 */,

	[ 'Pacific/Gambier',                 2011,0,1,0,0,0,       -540, 0 ]/* STD: -540 */,

	[ 'Pacific/Pitcairn',                2011,0,1,0,0,0,       -480, 0 ]/* STD: -480 */,

	[ 'America/Phoenix',                 2011,0,1,0,0,0,       -420, 0 ]/* STD: -420 */,

	[ 'America/Guatemala',               2011,0,1,0,0,0,       -360, 0 ]/* STD: -360 */,

	[ 'America/Bogota',                  2011,0,1,0,0,0,       -300, 0 ]/* STD: -300 */,

	[ 'America/Caracas',                 2011,0,1,0,0,0,       -270, 0 ]/* STD: -270 */,

	[ 'America/Santo_Domingo',           2011,0,1,0,0,0,       -240, 0 ]/* STD: -240 */,

	[ 'America/Argentina/Buenos_Aires',  2011,0,1,0,0,0,       -180, 0 ]/* STD: -180 */,

	[ 'America/Noronha',                 2011,0,1,0,0,0,       -120, 0 ]/* STD: -120 */,

	[ 'Atlantic/Cape_Verde',             2011,0,1,0,0,0,       -60, 0 ]/* STD: -60 */,

	[ 'Africa/Casablanca',               2011,0,1,0,0,0,       0, 0 ]/* STD: 0 */,

	[ 'Africa/Lagos',                    2011,0,1,0,0,0,       60, 0 ]/* STD: 60 */,

	[ 'Africa/Johannesburg',             2011,0,1,0,0,0,       120, 0 ]/* STD: 120 */,

	[ 'Asia/Baghdad',                    2011,0,1,0,0,0,       180, 0 ]/* STD: 180 */,

	[ 'Asia/Dubai',                      2011,0,1,0,0,0,       240, 0 ]/* STD: 240 */,

	[ 'Asia/Kabul',                      2011,0,1,0,0,0,       270, 0 ]/* STD: 270 */,

	[ 'Asia/Karachi',                    2011,0,1,0,0,0,       300, 0 ]/* STD: 300 */,

	[ 'Asia/Kolkata',                    2011,0,1,0,0,0,       330, 0 ]/* STD: 330 */,

	[ 'Asia/Kathmandu',                  2011,0,1,0,0,0,       345, 0 ]/* STD: 345 */,

	[ 'Asia/Dhaka',                      2011,0,1,0,0,0,       360, 0 ]/* STD: 360 */,

	[ 'Asia/Rangoon',                    2011,0,1,0,0,0,       390, 0 ]/* STD: 390 */,

	[ 'Asia/Jakarta',                    2011,0,1,0,0,0,       420, 0 ]/* STD: 420 */,

	[ 'Asia/Shanghai',                   2011,0,1,0,0,0,       480, 0 ]/* STD: 480 */,

	[ 'Australia/Eucla',                 2011,0,1,0,0,0,       525, 0 ]/* STD: 525 */,

	[ 'Asia/Tokyo',                      2011,0,1,0,0,0,       540, 0 ]/* STD: 540 */,

	[ 'Australia/Darwin',                2011,0,1,0,0,0,       570, 0 ]/* STD: 570 */,

	[ 'Australia/Brisbane',              2011,0,1,0,0,0,       600, 0 ]/* STD: 600 */,

	[ 'Pacific/Noumea',                  2011,0,1,0,0,0,       660, 0 ]/* STD: 660 */,

	[ 'Pacific/Norfolk',                 2011,0,1,0,0,0,       690, 0 ]/* STD: 690 */,

	[ 'Pacific/Tarawa',                  2011,0,1,0,0,0,       720, 0 ]/* STD: 720 */,

	[ 'Pacific/Tongatapu',               2011,0,1,0,0,0,       780, 0 ]/* STD: 780 */,

	[ 'Pacific/Kiritimati',              2011,0,1,0,0,0,       840, 0 ]/* STD: 840 */
)

function get_timezone_id()
{
	var tz="";
	for (var i in zones)
	{
		olson_name = zones[i][0];
		has_transitions = zones[i][8];

		date_1 = new Date(Date.UTC(zones[i][1],zones[i][2],zones[i][3],zones[i][4],zones[i][5],zones[i][6]));
		date_1_offset_data = zones[i][7];
		date_1_offset_user = -date_1.getTimezoneOffset();

		date_2 = new Date(Date.UTC(zones[i][1],zones[i][2],zones[i][3],zones[i][4],zones[i][5],zones[i][6]) - 1000);
		date_2_offset_user = -date_2.getTimezoneOffset();

		var patt1=/\([^)]+\)/g;
		if (zones[i][9] != undefined) var abbr = zones[i][9]; // To determine Pacific/Kiritimati in Firefox
		else var abbr = "";

		if
		(
			abbr == date_1.toString().match(patt1)
				// Detect timezones that has transitions
			|| (has_transitions &&  date_1_offset_user == date_1_offset_data && date_2_offset_user != date_1_offset_data)
				// Detect timezones that has not transitions
			|| (!has_transitions && date_1_offset_data == date_1_offset_user)
		)
		{
			tz = olson_name;
			return tz;
		}
	}
}