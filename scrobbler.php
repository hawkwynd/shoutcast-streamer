<?php
/**
 * Date: 2/25/19
 * Time: 2:48 PM
 * hawkwynd.com - scottfleming
 */

require_once('include/config.inc.php');

$track  = $_GET['track'];
$artist = $_GET['artist'];

// call stats from shoutcast server
$json   = file_get_contents('http://ws.audioscrobbler.com/2.0/?method=track.getInfo&api_key='.SCROBBLER_API.'&track='.$track.'&artist='.$artist.'&format=json');


echo $json;
exit;