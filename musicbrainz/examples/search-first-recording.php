<pre>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$a = "ZZ Top"; // artist to search
$t = "A Fool For Your Stockings"; // title to search

use Guzzle\Http\Client;
use MusicBrainz\Filters\ArtistFilter;
use MusicBrainz\Filters\LabelFilter;
use MusicBrainz\Filters\RecordingFilter;
use MusicBrainz\Filters\ReleaseGroupFilter;
use MusicBrainz\HttpAdapters\GuzzleHttpAdapter;
use MusicBrainz\MusicBrainz;

require dirname(__DIR__) . '/vendor/autoload.php';

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
    'recording'   => null,
    'artistId'    => null,
    'recordingId' => null,
    'trackLength' => null
);

$args = array(
    "recording"     => $t,
    "artist"        => $a,
    "country"       => 'US',
    "status"        => 'Official',
    "primarytype"   => 'Album',
    "score"         => 100
);

try {
    $recordings = $brainz->search(new RecordingFilter($args));
    $releases   = []; 
    $out        = new stdClass(); // our output obj
  
    echo "There are " . count($recordings) . " recordings \n\n";

    foreach($recordings as $recording){
        // dont keep going if the recording score is lower than what we have now, we want 100.
        if (null != $lastScore && $recording->getScore() < $lastScore) {
            break;
        }    
            // get recording annotation            
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
                    'trackLength' => $recording->getLength()
                );
            }
        
    } //foreach
    
    // get release annotation
    $relann = $brainz->lookup('release', $firstRecording['release']->id, array('annotation'));    
    
    $out->firstRecording = $firstRecording;


    //   get the label for our release
    $includes = array('aliases');
   
    $labels = $brainz->browseLabel('release', $firstRecording['release']->id, $includes);
    $out->firstRecording['release']->label        = $labels['label-count'] > 0 ? $labels[0]['name']: null;
    $out->firstRecording['release']->annotation   = $relann['annotation'];

    // coverartarchive.org for release coverart
    $url    = "https://coverartarchive.org/release/".$firstRecording['release']->id;
    $image  = json_decode( @file_get_contents( $url ));    
    $out->firstRecording['release']->coverart = $image != false ? $image->images[0]->thumbnails->large : null;


    // Pick up the artist relative to the recording id
    $artistDetails = $brainz->browseArtist('recording', $firstRecording['recordingId']);

    $artistIncludes      = array('annotation','artist-rels','aliases','tags');
    $artist = $brainz->lookup('artist', $artistDetails['artists'][0]['id'], $artistIncludes);
   
    // Build the artist object
    $out->firstRecording['artist'] = new stdClass(); // initialize artist obj
    $out->firstRecording['artist']->name                     = $artist['name'];
    $out->firstRecording['artist']->id                       = $artist['id'];
    $out->firstRecording['artist']->annotation               = $artist['annotation'];
    $out->firstRecording['artist']->disambiguation           = $artist['disambiguation'];
    $out->firstRecording['artist']->country                  = $artist['country'];
    $out->firstRecording['artist']->area                     = isset($artist['area']['name']) ? $artist['area']['name']: null;
    $out->firstRecording['artist']->begin_area               = isset($artist['begin_area']['name']) ? $artist['begin_area']['name'] : null;
    $out->firstRecording['artist']->{"life-span"}            = new stdClass();
    $out->firstRecording['artist']->{"life-span"}->begin     = isset($artist['life-span']['begin']) ? date('Y', strtotime($artist['life-span']['begin'])) : null;
    $out->firstRecording['artist']->{"life-span"}->end     = isset($artist['life-span']['end']) ? date('Y', strtotime($artist['life-span']['end'])) : null;
    
    // 4-digit Year for life-span dates or null
    $out->firstRecording['artist']->{'life-span'}             = new stdClass(); // initalize life-span object
    $out->firstRecording['artist']->{'life-span'}->begin     = $artist['life-span']['begin'] ? date('Y', strtotime( $artist['life-span']['begin'])) : null;
    $out->firstRecording['artist']->{'life-span'}->end       = $artist['life-span']['end'] ? date('Y', strtotime( $artist['life-span']['end'])) : null;

    // relations object of artist members
    $relations = array();
        
    foreach($artist['relations'] as $relation){
        $members   = new stdClass(); // init members obj

        if($relation['type'] == 'member of band'): // just band members pls.. 

             print "\n ArtisID Lookup = ". $relation['artist']['id'] . "\n";

            // lookup this members artist info
             $artistIncludes      = array('annotation','artist-rels','aliases','tags');
             $memberArtist       = $brainz->lookup('artist', $relation['artist']['id'], $artistIncludes);
         
            // load artist object in members array
            
            $members->artist->name              = $memberArtist['name'];
            $members->artist->id                = $memberArtist['id'];
            $members->artist->disambiguation    = $memberArtist['disambiguation'];            
            $members->artist->area              = $memberArtist['area']['name'];
            $members->artist->annotation        = $memberArtist['annotation'];

            // get instrument, and date range with the band
            $members->artist->instrument        = key($memberArtist['relations'][0]['attribute-ids']);       
            $members->artist->begin_area        = $memberArtist['begin_area']['name'] ? $memberArtist['begin_area']['name'] : null;
            
            
            array_push($relations, $members );

        endif;
    }
    
    // echo "\n --------- realations array ---- \n";
    // print_r($relations);

    // artist members object
    $out->firstRecording['artist']->members       = $relations;

    // drop recording object to lighten the load.
    unset($out->firstRecording['recording']->releases);


     echo json_encode($out);
   

} catch (Exception $e) {
    print $e->getMessage();
}
