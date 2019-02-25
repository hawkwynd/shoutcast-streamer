<?php
/**
 * Date: 2/4/19
 * Time: 3:12 PM
 * hawkwynd.com - sfleming
 * http://54.158.47.252:8000/admin.cgi?sid=1&mode=history
 */
date_default_timezone_set("America/Chicago");
$json   = file_get_contents('http://54.158.47.252:8000/played?sid=1&pass=scootre&type=json');
$obj    = json_decode($json);
unset($obj[0]);

$out    = [];
foreach($obj as $row){
    array_push($out,  date('h:i', $row->playedat ) . " " . $row->title);
    //print_r($row);
}

echo json_encode($out);
exit;