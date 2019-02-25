<?php
/**
 * Date: 2/21/19
 * Time: 1:50 PM
 * hawkwynd.com - scottfleming
 */

$mbid = $_GET['mbid'];

$url = 'http://musicbrainz.org/ws/2/release/'.$mbid.'?inc=release-groups&fmt=xml';

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);    // get the url contents
curl_setopt($ch, CURLOPT_USERAGENT,  "hawkwyndRadio/1.1");

$data = curl_exec($ch); // execute curl request
curl_close($ch);

$xml                = simplexml_load_string($data);
$first_release_date = $xml->release->{"release-group"}->{"first-release-date"};
$title              = $xml->release->{"release-group"}->title;

if($first_release_date){
    $formatted_release_year = date('Y', strtotime($first_release_date));
}

$out = array('title' => $title, 'first_release_date' =>  $formatted_release_year, 'mbid' => $mbid);

echo json_encode($out, true);

//echo $title . PHP_EOL;
//echo $first_release_date. PHP_EOL;
