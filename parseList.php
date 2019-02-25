<?php
/**
 * Date: 2/9/19
 * Time: 1:22 AM
 * hawkwynd.com - scottfleming
 */

$file = "feb-8-2019-hawkwynd-radio.xspf";
$c=0;

if (file_exists($file)) {
    $xml = simplexml_load_file($file);
    echo "<pre>";
   //print_r($xml);
} else {
    exit('Failed to open test.xml.');
}

foreach($xml->trackList->track as $track){
    if($track->title && $track->creator):
        $c++;
        $dur = $track->duration;
        echo $c . ": " .$track->title . " - " . $track->creator . " - " .date("i:s", $dur /1000) . PHP_EOL;
    endif;
}