<?php
/**
 * Get statistic data as well as current playing info
 * to be displayed
 */
require_once('include/config.inc.php');

// call stats from shoutcast server
$json   = file_get_contents(SHOUTCAST_HOST .'/statistics?json=1');
echo $json;
exit;