<?php
/**
 * stream.hawkwynd.com - scottfleming - get results from musicbrainz based on mbid
 */

$mbid   = $_GET['mbid'];
$mbid = "c0e5b91a-d2f5-4066-b00b-90f62b67894b";

$url    = 'http://musicbrainz.org/ws/2/release/'.$mbid.'?inc=release-groups&fmt=xml';

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);    // get the url contents
curl_setopt($ch, CURLOPT_USERAGENT,  "hawkwyndRadio/1.1");
$data = curl_exec($ch); // execute curl request
curl_close($ch);

$xml                = simplexml_load_string($data);

print_r($xml);

$first_release_date = $xml->release->{"release-group"}->{"first-release-date"};
$title              = $xml->release->{"release-group"}->title;

if($first_release_date){
    $formatted_release_year = date('Y', strtotime($first_release_date));
}

$out = array('title' => $title, 'first_release_date' =>  $formatted_release_year, 'mbid' => $mbid);

//echo json_encode($out, true);

//echo $title . PHP_EOL;
//echo $first_release_date. PHP_EOL;
