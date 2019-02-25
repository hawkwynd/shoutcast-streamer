<?php
/**
 * Date: 2/4/19
 * Time: 3:12 PM
 * hawkwynd.com - sfleming
 * http://hawkwynd.com:8000/admin.cgi?sid=1&mode=history
 */

$json   = file_get_contents('http://hawkwynd.com:8000/played?sid=1&pass=scootre&type=json');
$obj    = json_decode($json);
echo json_encode($obj[1]->title);
exit;