<?php
// This can only be run on dev
if ($_SERVER['SERVER_NAME'] == "dev.vrmobileapp.com") {
	$out = `git pull origin dev`;
	echo("<pre>$out</pre>");
}
else {
	echo ("No way Jos.");
}
?>