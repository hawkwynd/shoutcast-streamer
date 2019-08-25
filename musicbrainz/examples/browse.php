<pre><?php

use Guzzle\Http\Client;
use MusicBrainz\HttpAdapters\GuzzleHttpAdapter;
use MusicBrainz\MusicBrainz;

require dirname(__DIR__) . '/vendor/autoload.php';

// Create new MusicBrainz object
$brainz = new MusicBrainz(new GuzzleHttpAdapter(new Client()));
$brainz->setUserAgent('hawkwyndRadio', '1.0', 'http://stream.hawkwynd.com');
$out =[];

try{
    $recordings = (object) $brainz->browseRecording('release', 'e51afa51-9f7d-4adc-aece-fae3a5be3c43');
   
    foreach($recordings->recordings as $track){
        array_push($out, $track['title']);
    }

    echo json_encode($out);

} catch(Exception $e) {
    print $e->getMessage();
}
exit;



/**
 * Browse Releases based on an Artist MBID (Jambros in this case)
 * Include the Labels for the Release and the Recordings in it
 */

//  $includes = array('labels', 'recordings','artist-rels');
// try {
//     $details = $brainz->browseRelease('artist', '8741f7c9-2329-48cb-a9ed-122f0336d2bd', $includes, 2);

//     echo "Browse Releases By Artist " . PHP_EOL;
//     print_r($details);

// } catch (Exception $e) {
//     print $e->getMessage();
// }
// print "\n\n";


/**
 * Browse an artist based on a Recording MBID and include their aliases and ratings
 */
// $includes = array('aliases', 'ratings');
// try {
//     $details = $brainz->browseArtist('recording', 'cd5eb8da-5a53-4750-8d87-e87a03e6ee2b', $includes);

//     echo "Browse Artist on Recording MBID: " . PHP_EOL;

//     print_r($details);
// } catch (Exception $e) {
//     print $e->getMessage();
// }
// print "\n\n";


/**
 * Browse information for a Label based on an Artist's MBID
 */
// $includes = array('aliases');
// try {
//     $details = $brainz->browseLabel('artist', '6fe07aa5-fec0-4eca-a456-f29bff451b04', $includes);
//     print_r($details);
// } catch (Exception $e) {
//     print $e->getMessage();
// }
// print "\n\n";


/**
 * Browse information for a Label based on a Release's MBID
 */

$includes = array('aliases');
try {
    $details = $brainz->browseLabel('release', '1f95a960-25d2-4f53-90d0-76474910e79a', $includes);
    print_r($details);
} catch (Exception $e) {
    print $e->getMessage();
}
