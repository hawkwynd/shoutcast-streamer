<?php
/**
 * Date: 10/22/18
 * Time: 4:07 PM
 * scottybox - sfleming
 * This updates the mongo db lastfm_fail table.
 * Handles duplicate loves as well, by updating the datetime stamp
 * and it's records.
 * https://www.discogs.com/developers/#page:home
 * Api information
 */
      error_reporting(E_ALL);
      ini_set('display_errors', 1);

// mongodb connection
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';

$collection         = (new MongoDB\Client)->stream->lastfm_fail;

$track  = rawurlencode($_POST['title']);
$artist = rawurlencode($_POST['artist']);

$url    = 'https://musicbrainz.org/ws/2/artist/?query='.$artist.'%20AND%20type:group&fmt=json';

$arr        = json_decode(fetch($url), true);

$artist_mbid = $arr['artists'][0]['id'];

$updateResult = $collection->findOneAndUpdate(
    ['$and'    =>   [
                        ['artist'    => $_POST['artist']],
                        ['title'     => $_POST['title'] ]
                    ]
    ],
        ['$set'  => [
                    'arid'          => $artist_mbid,
                    'title'         => $_POST['title'],
                    'artist'        => $_POST['artist'],
                 ]
    ],
    [
        'upsert' => true,
        'projection' => [ 'arid' => 1 ],
    ]
    
);

echo json_encode($updateResult['arid']);
exit; // shut the door on your way out..



/**
 * @param $s
 * @return mixed
 * wikidefinition -- returns json format of results from the query searching for
 */

function fetch($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);    // get the url contents
    curl_setopt($ch, CURLOPT_USERAGENT,  "hawkwyndRadio/1.1");
    $data = curl_exec($ch); // execute curl request
    curl_close($ch);
    return $data;
}
