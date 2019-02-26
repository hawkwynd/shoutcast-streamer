<?php
/**
 * Date: 2/25/19
 * Time: 2:48 PM
 * hawkwynd.com - scottfleming
 */

require_once('include/config.inc.php');

$track  = rawurlencode($_GET['track']);
$artist = rawurlencode($_GET['artist']);

// call stats from shoutcast server
//$json   = file_get_contents('http://ws.audioscrobbler.com/2.0/?method=track.getInfo&api_key='.SCROBBLER_API.'&track='.$track.'&artist='.$artist.'&format=json');

//echo 'http://ws.audioscrobbler.com/2.0/?method=track.getInfo&api_key='.SCROBBLER_API.'&track='.$track.'&artist='.$artist.'&format=json';
//echo $json;

// try musicbrainz.org
$url = 'https://musicbrainz.org/ws/2/artist/?query='.$artist.'%20AND%20type:group&fmt=json';

//echo $url;

echo "<pre>";

$arr = json_decode(fetch($url), true);

$artist_mbid = $arr['artists'][0]['id'];

$release_url = 'https://musicbrainz.org/ws/2/recording?query="'.$track.
                    '"%20AND%20arid:'.$artist_mbid.
                    '%20AND%20primarytype:single%20AND%20country:US&fmt=json';
echo $release_url;

$arr = fetch($release_url);

print_r($arr);

function fetch($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);    // get the url contents
    curl_setopt($ch, CURLOPT_USERAGENT,  "hawkwyndRadio/1.1");
    $data = curl_exec($ch); // execute curl request
    curl_close($ch);
    return $data;
}




exit;