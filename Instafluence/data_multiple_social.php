<?php

$m = new MongoClient();
$db = $m->selectDB('instafluence-dev');
$c = $db->selectCollection('social');

$cur = $c->find();
//$cur = $c->find(array('email' => 'b@b.com'));

foreach ($cur as $index => $doc) {
	foreach ($doc as $name => &$network) {
		if (is_array($network)) {
			if (isset($network['connected'])) {
				$fail = false;
				$newNetwork = array();
				
				if ($name == 'tumblr' || $name == 'wordpress' || $name == 'linkedin') {
					echo('removing ' . $name . "\n");
					$fail = true;
				}
				elseif ($network['connected'] == true) {
					if (!isset($network['username'])) {
						switch ($name) {
							case 'facebook': {
								echo('setting username for ' . $name . ' for user ' . $doc['email'] . "\n");
								if (!isset($network['profile']['email'])) {
									echo ('ERROR: email not set for ' . $name . ', account ' . $doc['email'] . "\n");
									$fail = true;
									break;
								}
								$network['username'] = $network['profile']['email'];
								break;
							}
							case 'youtube':
							case 'googleplus':
							case 'foursquare': {
								echo('setting username for ' . $name . ' for user ' . $doc['email'] . "\n");
								if (!isset($network['id'])) {
									echo ('ERROR: id not set for ' . $name . ', account ' . $doc['email'] . "\n");
									$fail = true;
									break;
								}
								$network['username'] = $network['id'];
								break;
							}
						}
					}
					
					if (!$fail) {
						$newNetwork[] = $network;
					}
				}
				
				$network = $newNetwork;
				//var_dump($network);
			}
		}
	}
	
	$c->update(array('email' => $doc['email']), $doc);
}
