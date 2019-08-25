<?php
/**
 * displays history from shoutcast admin.cgi
 */
date_default_timezone_set("America/Chicago");
require_once('include/config.inc.php');

$json   = file_get_contents(SHOUTCAST_HOST."/played?sid=1&pass=".SHOUTCAST_ADMIN_PASS."&type=json");
$obj    = json_decode($json);
unset($obj[0]);

$out    = [];
foreach($obj as $row){
    array_push($out,  date('h:i', $row->playedat ) . " " . $row->title);
}

echo json_encode($out);
exit;