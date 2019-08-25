<?php
// ===========Breadcrumb Notes:  Recording disambiguation field added to recording for displaying 'pretty' titles. 
// "recording": {
//     "id": "97409e42-ffb3-4aeb-b2af-235fa4dcf9d9",
//     "title": "Further On Up - 06-01-19",
//     "score": 100,
//     "releases": [],
//     "brainz": {},
//     "length": 399000,
//     "artistID": "8741f7c9-2329-48cb-a9ed-122f0336d2bd",
//     "disambiguation": "Further On Up"
//     }
// ===========================================================================================


error_reporting(E_STRICT);

ini_set('display_errors', 1);
$time_start = microtime(true); 

// $a = "Jambros"; // artist to search
$a = $_POST['artist'];
// $t = "Further On Up - 06-01-19"; // title to search
$t = $_POST['title'];


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

// Create new MusicBrainz object
$brainz = new MusicBrainz(new GuzzleHttpAdapter(new Client()),'hawkwynd', 'scootre');
$brainz->setUserAgent('Hawkwynd Radio', '1.0', 'http://stream.hawkwynd.com');

/**
 * Do a recording (song) search
 * @see http://musicbrainz.org/doc/Recording
 */

// set defaults

$releaseDate    = new DateTime();
$artistId       = null;
$songId         = null;
$trackLen       = -1;
$albumName      = '';
$lastScore      = null;
$firstRecording = array(
    'query'       => array(),
    'release'     => null,
    'releaseDate' => new DateTime(),
    'releaseCount' => null,    
    'recording'   => null,
    'artistId'    => null,
    'recordingId' => null,
    'trackLength' => null,
    'execution' => new stdClass(),
    'dump'      => new stdClass()
);

$args = array(
    "recording"     => $t,
    "artist"        => $a,
    // "country"       => "US",
    "status"        => "Official",
    "primarytype"   => "Album",
    // "score"         => 100
);

try {

    $recordings = $brainz->search(new RecordingFilter($args));
    $releases   = []; 
    $out        = new stdClass(); // our output obj

    foreach($recordings as $recording){
        // dont keep going if the recording score is lower than what we have now, we want 100.
         if (null != $lastScore && $recording->getScore() < $lastScore) {
            break;
         }      
         
        $lastScore        = $recording->getScore();
        $releaseDates     = $recording->getReleaseDates();
        $oldestReleaseKey = key($releaseDates);
        
        if($releaseDates[$oldestReleaseKey] < $firstRecording['releaseDate']){
                    
            $firstRecording = array(
                'query'       => $args,
                'release'     => $recording->releases[$oldestReleaseKey],
                'releaseDate' => $recording->releases[$oldestReleaseKey]->getReleaseDate(),
                'release-count' => count($recording->releases),
                'recording'   => $recording,            
                'artist'      => $recording->getArtist(),
                'recordingId' => $recording->getId(),
                'trackLength' => $recording->getLength(),
                'execution'   => new stdClass(),
                );
                
        }
        
    }
   
    // Build release-group object
    $releaseGroup = $brainz->lookup('release', $firstRecording['release']->id, array('release-groups','labels')); 
    $firstRecording['release-group'] = $releaseGroup['release-group'];
    $firstRecording['release-group']['musicbrainz'] = "https://musicbrainz.org/release-group/".$releaseGroup['release-group']['id'];
    $firstRecording['release-group']['url-rels'] = "https://musicbrainz.org/ws/2/release-group/".$releaseGroup['release-group']['id']."?inc=url-rels&fmt=json";
   
    //  wikidata for release-group
    $rgUrlRels = $brainz->lookup('release-group', $releaseGroup['release-group']['id'], array('url-rels'));
    $wikidataKey         = recursive_array_search('wikidata', $rgUrlRels['relations']);
    $releaseGroupQID     = $wikidataKey ? end( explode('/',  parse_url($rgUrlRels['relations'][$wikidataKey]['url']['resource'], PHP_URL_PATH) ) ): null;    
    $firstRecording['release-group']['wiki'] =  $releaseGroupQID ? wikiExtract($releaseGroupQID) : null;


    // coverart for release-group
    $ReleaseGroupImage  = json_decode( @file_get_contents( "https://coverartarchive.org/release-group/".$releaseGroup['release-group']['id'] )); 
    $firstRecording['release-group']['coverart']= $ReleaseGroupImage != false ? $ReleaseGroupImage->images[0]->thumbnails->large : null;

    $releaseLabel = $brainz->lookup('release', $firstRecording['release']->id, array('labels'));
    foreach($releaseLabel['label-info'] as $label){
        $firstRecording['release']->label = $label['label']['name'] ? $label['label']['name'] : 'none';
    }
     // wikidata for artist
     $artistUrlRels                     = $brainz->lookup('artist', $firstRecording['artist']->id, array('url-rels','annotation'));
     $wikidataKey                       = recursive_array_search('wikidata', $artistUrlRels['relations']);
     $artistQID                         = $wikidataKey ? end( explode('/',  parse_url($artistUrlRels['relations'][$wikidataKey]['url']['resource'], PHP_URL_PATH) ) ): null;
     $firstRecording['artist']->wiki    = $artistQID ? wikiExtract($artistQID) : null;

    //  Null wiki data? Try annotation from artist query
    // for wikiUrl use musibrainz.org/artist/id

    if(!$firstRecording['artist']->wiki){
        $firstRecording['artist']->wiki->extract = nl2br( $artistUrlRels['annotation'] );
        $firstRecording['artist']->wiki->pageUrl = 'https://musicbrainz.org/artist/' . $artistUrlRels['id'];
    }
    
          
    // Log execution object    
    $time_end = microtime(true);
    $execution_time = ($time_end - $time_start);
    $firstRecording['execution']->time = number_format((float) $execution_time, 1);
    $firstRecording['execution']->artistQuery = $a;
    $firstRecording['execution']->recordingQuery = $t;

    // perform mongo update/insert and return results
    if($firstRecording['artist']->name == $a){
        $firstUpdate = firstFindMongoUpdate( $firstRecording );
        // throw results back 
        echo json_encode( 
            array(
                'Status' => 'OK',
                'Score'  => $lastScore,
                'Message' => $firstUpdate 
            )
        );
    
    }else{
        // Show mismatch of artist and score
        echo json_encode(
            array(
                'Status'   => 'Mismatch Error?',
                'Queried'   => $a,
                'MB Result'   =>  $firstRecording['artist']->name,
                'Score'     => $lastScore
            ));
    } 
    

    

} catch (Exception $e) {
    // return bad error message data, or no results found
    echo json_encode(  $e->getMessage() );

}

exit;