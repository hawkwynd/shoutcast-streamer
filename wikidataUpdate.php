<?php
// update an existing mongo record 
// add wikidata results for artist, release-group
// fields required:
// recording.id, release-group.id, artist.id

// ================= sample in data =======================
// artist.id: "668fd73c-bf54-4310-a139-305517f05311"
// recording.id: "68de8206-6ce2-41f8-9c70-a1debe60e320"
// release.id: "d9edc269-d416-4a4b-8e76-b86ed4cd2e96"
// release-group.id: "4182146e-3f7a-38e0-96d2-8ec32077f224"
// =======================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

// test variables
$aID         = "9d97b077-b28d-4ba8-a3d9-c71926e3b2b6"; 
$recID       = "5fce2044-1f2f-4064-81f6-b749ea10c0ef"; 
$rgID        = "8746cc0f-cf83-3878-b7c0-89913edc5210"; 

$totaltime_start = microtime(true);  // being entire script timer

use Guzzle\Http\Client;
use MusicBrainz\Filters\ArtistFilter;
use MusicBrainz\Filters\LabelFilter;
use MusicBrainz\Filters\RecordingFilter;
use MusicBrainz\Filters\ReleaseGroupFilter;
use MusicBrainz\HttpAdapters\GuzzleHttpAdapter;
use MusicBrainz\MusicBrainz;

require dirname(__DIR__) . '/stream/musicbrainz/vendor/autoload.php';
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';
include_once dirname(__DIR__) . '/stream/include/functions.php';

$out = [
    'recording'     => ['id' => $recID],
    'release-group' => [],
    'artist'        => [],
    'total-etime'   => null,
];

// Create new MusicBrainz object
$brainz = new MusicBrainz(new GuzzleHttpAdapter(new Client()),'hawkwynd', 'scootre');
$brainz->setUserAgent('Hawkwynd Radio', '1.0', 'http://stream.hawkwynd.com');

// Gather release-group wikidata extraction by release-group.id
$time_start         = microtime(true); // start group timer
$releaseGroup       = $brainz->lookup('release-group', $rgID ,array('url-rels'));
$wikidataKey        = recursive_array_search('wikidata', $releaseGroup['relations']);
$releaseGroupQid    = end( explode('/',  parse_url($releaseGroup['relations'][$wikidataKey]['url']['resource'], PHP_URL_PATH) ) );

// add release-group content to array
if($releaseGroupQid) {
   $releaseGroupData                = wikiExtract($releaseGroupQid);
   $releaseGroupData['target']      = 'release-group';
   $out['release-group']            = $releaseGroupData;
   $out['release-group']['etime']   = etime($time_start);
}


// Gather artist wikidata extraction by recording.id
$time_start         = microtime(true);  // start group timer
$artist             = $brainz->lookup('artist' , $aID, ['url-rels']);
$wikidataKey        = recursive_array_search('wikidata', $artist['relations']);
$artistQID          = end( explode('/',  parse_url($artist['relations'][$wikidataKey]['url']['resource'], PHP_URL_PATH) ) );

// add artist content to array
if($artistQID) {
  
    $artistData                = wikiExtract($artistQID);
    $artistData['target']      = 'artist';
    $out['artist']            = $artistData;
    $out['artist']['etime']   = etime($time_start);
}

$out['total-etime'] = etime($totaltime_start );

print_r($out);

$res = db_wikidataUpdate($out);

echo "<hr>";

