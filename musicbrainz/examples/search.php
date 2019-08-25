<pre>
<?php
error_reporting(E_STRICT);
ini_set('display_errors', 1);

$a = "The Beatles"; // artist to search
$t = "Drive My Car"; // title to search

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
    "status"        => 'official',
    "primarytype"   => 'album',
    "score"         => 100
);

try {
    $recordings = $brainz->search(new RecordingFilter($args));
    $releases = [];
  
    foreach($recordings as $recording){
        // if (null != $lastScore && $recording->getScore() < $lastScore) {
        //     break;
        // }    
            // get recording annotation            
            $lastScore        = $recording->getScore();
            
            array_push(
                $releases, $recording
               
            );
          
        
    } //foreach

    print_r($recordings);

//   echo json_encode($releases );
   exit;

    //   get the label for our release
    $includes = array('aliases');
    $details = $brainz->browseLabel('release', $latestRelease['release_id'], $includes);
      
    // release object   
    $out->release->id           = $latestRelease['release_id'];
    $out->release->title        = $latestRelease['release_title'];
    $out->release->date         = $latestRelease['release_date'];
    $out->release->label        = $details['labels'][0]['name'];
    $out->release->annotation   = $relann['annotation'];

    // coverartarchive.org for image
    $url    = "https://coverartarchive.org/release/".$latestRelease['release_id'];
    $image  = json_decode( file_get_contents( $url ));    
    $out->release->coverart = $image->images[0]->thumbnails->large;


    // recording object (track)
    $out->recording->id     = $latestRelease['recording_id'];
    $out->recording->title  = $latestRelease['recording_title'];
    $out->recording->length = $latestRelease['recording_length'];
    $out->recording->annotation = $latestRelease['recording_annotation'];

    // artist relative to the recording id
    $artistDetails = $brainz->browseArtist('recording', $out->recording->id);
    $artist = $brainz->lookup('artist', $artistDetails['artists'][0]['id'], ['annotation','artist-rels']);
   
       
    // relations object of artist
    $relations = [];
    foreach($artist['relations'] as $relation){
        if($relation['type'] == 'member of band'): // just band members pls.. 
            // get instrument, and date range with the band
            $relation['artist']['instrument'] = key($relation['attribute-ids']);
            $relation['artist']['begin']      = $relation['begin'] ? date('Y', strtotime( $relation['begin'])) : null;
            $relation['artist']['end']        = $relation['end'] ? date('Y', strtotime( $relation['end'])) : null;    
            array_push($relations, $relation['artist'] );
        endif;
    }

    // load up and spew.. 
    $out->artist->name          = $artist['name'];
    $out->artist->id            = $artist['id'];
    $out->artist->annotation    = $artist['annotation'];
    $out->artist->country       = $artist['area']['name'];
    $out->artist->begin         = $artist['life-span']['begin'] ? date('Y', strtotime( $artist['life-span']['begin'])) : null;
    $out->artist->end           = $artist['life-span']['end'] ? date('Y', strtotime( $artist['life-span']['end'])) : null;
    $out->artist->members       = $relations;

echo json_encode($out);
   

} catch (Exception $e) {
    print $e->getMessage();
}


/**
 * Do a search for a label
 * @see http://musicbrainz.org/doc/Label
 */

// $args = array(
//     "label" => "Bob & Toby"
// );
// try {
//     $labels = $brainz->search(new LabelFilter($args));
//     print_r($labels);
// } catch (Exception $e) {
//     print $e->getMessage();
// }
