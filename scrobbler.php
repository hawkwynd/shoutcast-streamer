<?php
/**
 * Date: 2/25/19
 * Time: 2:48 PM
 * hawkwynd.com - scottfleming
 */




error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Date: 2/25/19
 * Time: 2:48 PM
 * hawkwynd.com - scottfleming
 *
 * Scrobble an artist and title and return a shit-ton of data about the artist, album, and tracks on the album
 *
 */

require_once('include/config.inc.php');
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';
require '/var/www/hawkwynd.com/guzzle/vendor/autoload.php';

$track  = rawurlencode($_GET['track']);
$artist = rawurlencode($_GET['artist']);

// $json   = file_get_contents('http://ws.audioscrobbler.com/2.0/?method=track.getInfo&api_key='.SCROBBLER_API.'&track='.$track.'&artist='.$artist.'&format=json');

// echo $json;

$mb_json = do_mb_search($artist, $track);

echo "<pre>";
echo $mb_json;

exit;

function do_mb_search($a, $t){
    
    $client = new \GuzzleHttp\Client();
    
    $url = "https://musicbrainz.org/ws/2/recording/?query=$t%20AND%20artist:$a%20AND%20primarytype:album%20AND%20country:US%20AND%20status:Official&fmt=json";
    
    $response = $client->request('GET', $url);
    
    return $response->getBody() ;
}
