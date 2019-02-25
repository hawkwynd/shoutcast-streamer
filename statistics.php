<?php
/**
 * Date: 2/4/19
 * Time: 2:18 PM
 * hawkwynd.com - sfleming
    http://hawkwynd.com:8000/statistics?json=1
 */

// call stats
$json   = file_get_contents('http://54.158.47.252:8000/statistics?json=1');
echo $json;
exit;