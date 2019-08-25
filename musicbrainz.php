<?php
error_reporting(E_STRICT);
ini_set('display_errors', 1);
$time_start = microtime(true); 

$a = "ZZ Top"; // artist to search
// $a = $_POST['artist'];
$t = "Beer Drinkers & Hell Raisers"; // title to search
// $t = $_POST['title'];


use Guzzle\Http\Client;
use MusicBrainz\Filters\ArtistFilter;
use MusicBrainz\Filters\LabelFilter;
use MusicBrainz\Filters\RecordingFilter;
use MusicBrainz\Filters\ReleaseGroupFilter;
use MusicBrainz\HttpAdapters\GuzzleHttpAdapter;
use MusicBrainz\MusicBrainz;

require dirname(__DIR__) . '/stream/musicbrainz/vendor/autoload.php';
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';

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
    'release'     => null,
    'releaseDate' => new DateTime(),
    'releaseCount' => null,    
    'recording'   => null,
    'artistId'    => null,
    'recordingId' => null,
    'trackLength' => null,
    'execution' => null
);

$args = array(
    "recording"     => $t,
    "artist"        => $a,
    "country"       => 'us',
    "status"        => 'official',
    "primarytype"   => 'album',
    "score"         => 100,
    "type"          => 'album'
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
    
            if (strtoupper($recording->getArtist()->getName()) == strtoupper($args['artist'])
                && $releaseDates[$oldestReleaseKey] < $firstRecording['releaseDate']
            ) {
    
                

                $firstRecording = array(
                    'release'     => $recording->releases[$oldestReleaseKey],
                    'releaseDate' => $recording->releases[$oldestReleaseKey]->getReleaseDate(),
                    'recording'   => $recording,                   
                    'recordingId' => $recording->getId(),
                    'trackLength' => $recording->getLength(),

                );

              
            }
        
    } //foreach

  
    // get release-group for this release, and we'll use it to be sure we get a coverart, I hope
    $release = $brainz->lookup('release', $firstRecording['release']->id, array('release-groups','annotation'));    
    $firstRecording['release']->annotation = $release['annotation'];
    $firstRecording['release-group-id'] = $release['release-group']['id'];
    

    // MusicBrainz.org url for review
    $firstRecording['musicbrainz']->releaseGroupUrl = "https://musicbrainz.org/release-group/".$release['release-group']['id'];
    $firstRecording['musicbrainz']->releaseUrl = "https://musicbrainz.org/release/" . $firstRecording['release']->id;
    
    // grab recording annotation and artist-rels
    // $recordingGroup = $brainz->lookup('recording', $firstRecording['recordingId'], array('annotation', 'artist-rels'));
    $recordingGroup = $brainz->lookup('recording', $firstRecording['recordingId'], array('annotation'));
    $firstRecording['recording']->annotation = $recordingGroup['annotation'];
   
    // -------------- initialize the object for our output ------------------/
    $out->firstRecording = $firstRecording; 


    echo "<pre>";
    print_r($out);
    exit;

    // Gather players on the recording, and instrument/vocal
    // $playerArray = array();

    // foreach($recordingGroup['relations'] as $row){
    //     $player = new stdClass(); // init players object
    //     $player->instrument = $row['type'] == 'instrument' ? key($row['attribute-ids']) : $row['type'];
    //     $player->name = $row['artist']['name'];
    //     array_push($playerArray, $player);
    // }
   
    // $firstRecording['recording']->players = $playerArray;

    // get the label for our release
    $includes = array('aliases');
    $labels = $brainz->browseLabel('release', $firstRecording['release']->id, $includes);  
    $out->firstRecording['release']->label = $labels['label-count'] > 0 ? $labels['labels'][0]['name']: null;
        
       
    // Pick up the artist relative to the recording id
    $artistDetails = $brainz->browseArtist('recording', $firstRecording['recordingId']);
    // $artistIncludes      = array('annotation','artist-rels','aliases','tags','url-rels');
    // $artistIncludes      = array('aliases','tags');
    $artistIncludes      = array();
    $artist = $brainz->lookup('artist', $artistDetails['artists'][0]['id'], $artistIncludes);
    // Obtain Aritst Extract From Wikipedia
    // foreach($artist['relations'] as $row){
    //     if($row['type']=='wikidata'){
    //         $url = $row['url']['resource'];
    //         $path = parse_url($url, PHP_URL_PATH);
    //         $pathFragments = explode('/', $path);
    //         $end = end($pathFragments);
    //         $response = wikiArtist($end);
    //         $Artistextract = $response['extract'];
            
    //     }
    // }

    // Build the artist object
    $out->firstRecording['artist'] = new stdClass(); // initialize artist obj
    $out->firstRecording['artist']->name                     = $artist['name'];
    // if there's no Wikitext try the annotation for the artist
    // $out->firstRecording['artist']->annotation               = $Artistextract == null ? $artist['annotation'] : $Artistextract;
    $out->firstRecording['artist']->id                       = $artist['id'];
    $out->firstRecording['musicbrainz']->artistUrl           = "https://musicbrainz.org/artist/".$artist['id'];
    
    $out->firstRecording['artist']->disambiguation           = $artist['disambiguation'];
    $out->firstRecording['artist']->country                  = $artist['country'];
    $out->firstRecording['artist']->area                     = isset($artist['area']['name']) ? $artist['area']['name']: null;
    $out->firstRecording['artist']->begin_area               = isset($artist['begin_area']['name']) ? $artist['begin_area']['name'] : null;
    $out->firstRecording['artist']->{"life-span"}            = new stdClass();
    $out->firstRecording['artist']->{"life-span"}->begin     = isset($artist['life-span']['begin']) ? date('Y', strtotime($artist['life-span']['begin'])) : null;
    $out->firstRecording['artist']->{"life-span"}->end       = isset($artist['life-span']['end']) ? date('Y', strtotime($artist['life-span']['end'])) : null;
    
    // 4-digit Year for life-span dates or null
    $out->firstRecording['artist']->{'life-span'}             = new stdClass(); // initalize life-span object
    $out->firstRecording['artist']->{'life-span'}->begin     = $artist['life-span']['begin'] ? date('Y', strtotime( $artist['life-span']['begin'])) : null;
    $out->firstRecording['artist']->{'life-span'}->end       = $artist['life-span']['end'] ? date('Y', strtotime( $artist['life-span']['end'])) : null;



    // relations object of artist members
    // $relations = array();
        
    // foreach($artist['relations'] as $relation){
    //     $members   = new stdClass(); // init members obj

    //     if($relation['type'] == 'member of band'): // just band members pls.. 

    //         // lookup this members artist info
    //         $artistIncludes      = array('artist-rels');
    //         $memberArtist       = $brainz->lookup('artist', $relation['artist']['id'], $artistIncludes);
         
    //         // load artist object in members array   
    //         $members->artist = new stdClass();
    //         $members->artist->name              = $memberArtist['name'];
    //         $members->artist->id                = $memberArtist['id'];
    //         $members->artist->disambiguation    = $memberArtist['disambiguation'];            
    //         $members->artist->area              = $memberArtist['area']['name'];
    //         // $members->artist->annotation        = $memberArtist['annotation'];

    //         // get instrument, and date range with the band
    //         $members->artist->instrument        = key($memberArtist['relations'][0]['attribute-ids']);       
    //         $members->artist->begin_area        = $memberArtist['begin_area']['name'] ? $memberArtist['begin_area']['name'] : null;
            
            
    //         array_push($relations, $members );

    //     endif;
    // }
    
    // // artist members object
    // $out->firstRecording['artist']->members       = $relations;

    // drop recording object to lighten the load.
     unset($out->firstRecording['recording']->releases);

        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        $formatted_time = number_format((float) $execution_time, 1);
        
        $out->firstRecording['execution-time'] = $formatted_time;

        

        $res = do_dbUpdate($out->firstRecording);
     
     echo "<pre>";
    //  echo "Exec time: " . $formatted_time;
    //   echo json_encode($res);
    echo json_encode($out->firstRecording);
     exit;
        

} catch (Exception $e) {
    print $e->getMessage();
}

// FUNCTION FUNCTION WHATS YOUR FUNCTION?


// To get the title by Qid, we need to ask wikidata for it
// https://www.wikidata.org/w/api.php?action=wbgetentities&format=xml&props=sitelinks&ids=Q203871&sitefilter=enwiki&format=json

// https://en.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exlimit=max&explaintext&exintro&titles=Rush%20(band)

                    
function wikiArtist($qid){
    $url = "https://www.wikidata.org/w/api.php?action=wbgetentities&format=xml&props=sitelinks&ids=$qid&sitefilter=enwiki&format=json";

    $curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER  => 1,
        CURLOPT_URL             => $url,
        CURLOPT_USERAGENT       => 'Hawkwynd Radio 1.0'
    ]);
    // Send the request & save response to $resp
    $resp = curl_exec($curl);
    // Close request to clear up some resources
    curl_close($curl);

    $data = json_decode($resp);
    $title = rawurlencode($data->entities->$qid->sitelinks->enwiki->title); // Rush (band)
    $wikiUrl = "https://en.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exlimit=1&explaintext&exintro&titles=$title";

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER  => 1,
        CURLOPT_URL             => $wikiUrl,
        CURLOPT_USERAGENT       => 'Hawkwynd Radio 1.0'
    ]);

    $wikiResponse = json_decode(curl_exec($curl));
    $output = array('title'=> '', 'extract'=>'');
    foreach($wikiResponse->query->pages as $page){
        $output['title'] = $page->title;
        $output['extract'] = $page->extract;
    }

    // Close request to clear up some resources
    curl_close($curl);

    return $output;

}


function do_dbUpdate($out) {
    
    $collection         = (new MongoDB\Client)->stream->musicbrainz;
    $updateResult = $collection->findOneAndUpdate(
            [
                'recording-id' => $out['recording']->id
            ],
            ['$set'  => [
                'release-id'            => $out['release']->id,
                'release-title'         => $out['release']->title,
                'release-date'          => $out['release']->date,
                'release-label'         => $out['release']->label,
                'release-annotation'    => $out['release']->annotation,
                'release-country'       => $out['release']->country,
                'release-coverart'      => $out['release']->coverart,
                'release-fulldate'      => $out['releaseDate']->date,
                'recording-id'          => $out['recording']->id,
                'recording-title'       => $out['recording']->title,
                'recording-length'      => $out['recording']->length,
                'recording-annotation'  => $out['recording']->annotation,
                // 'recording-players'     => $out['recording']->players,
                'recording-artistID'    => $out['recording']->artistID,
                'release-group-id'      => $out['release-group-id'],
                'release-group-annotation' => $out['release-group']->annotation,
                'artist-name'           => $out['artist']->name,
                'artist-id'             => $out['artist']->id,
                'artist-annotation'     => $out['artist']->annotation,
                'artist-disambiguation' => $out['artist']->disambiguation,
                // 'artist-wikidata'       => $out['artist']->wikidata,
                'artist-country'        => $out['artist']->country,
                'artist-area'           => $out['artist']->area,
                'artist-begin_area'     => $out['artist']->begin_area,
                'aritst-life-span'      => $out['artist']->{'life-span'},
                // 'artist-members'        => $out['artist']->members,
                'musicbrainz'           => $out['musicbrainz'],
                'execution-time'        => $out['execution-time']
            ]
        ],
        ['upsert'           => true,
        'projection'        => 
        [ 
            '_id' => 0,
            'artist-name'   => 1, 
            'release-id'    => 1, 
            'release-title' => 1,
            'release-date'  => 1,
            'release-label' => 1,
            'release-annotation' => 1,
            'release-coverart' => 1,
            'recording-id'  => 1,
            'recording-title' => 1,
            'recording-length' => 1,
            'recording-annotation' => 1,
            // 'recording-players' => 1,
            'release-group-id' => 1,
            'release-group-annotation' =>1,
            'artist-name' => 1,
            'artist-id' => 1,
            'artist-annotation' => 1,
            // 'artist-wikidata'   => 1,
            'artist-disambiguation' => 1,
            'artist-country' => 1,
            'artist-area'    => 1,
            'artist-begin_area' => 1,
            'aritst-life-span' => 1,
            // 'artist-members' => 1,
            'musicbrainz' =>1,
            'execution-time'

        ],
        'returnDocument'    => MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER,
        ]
    );

    return $updateResult;
}