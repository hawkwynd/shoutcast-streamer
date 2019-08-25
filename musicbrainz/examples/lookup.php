<pre><?php

use Guzzle\Http\Client;
use MusicBrainz\HttpAdapters\GuzzleHttpAdapter;
use MusicBrainz\MusicBrainz;

require dirname(__DIR__) . '/vendor/autoload.php';

// Create new MusicBrainz object
$brainz = new MusicBrainz(new GuzzleHttpAdapter(new Client()),'hawkwynd', 'scootre');
$brainz->setUserAgent('ApplicationName', '0.2', 'http://example.com');

/**
 * Lookup an Artist and include a list of Releases, Recordings, Release Groups and User Ratings
 * Note: You must authenticate to retrieve user-ratings.
 * @see http://musicbrainz.org/doc/Artist
 */
// $includes = array(
//      'aliases',
//      'tags',
//      'annotation',
//      'ratings'
// );
// try {
//     $artist = $brainz->lookup('artist', '3c0560e6-d31b-4ac4-9bda-7621e83f7432', $includes);

//     echo 'Lookup an Artist and include a list of Releases, Recordings, Release Groups' . PHP_EOL;

//     print_r($artist);

// } catch (Exception $e) {
//     print $e->getMessage();
// }
// print "\n\n";
// exit;

/**
 * Lookup a Release Group based on an MBID
 * @see http://musicbrainz.org/doc/Release_Group
 */

 try {
    // Summer Side Of Life - Gordon Lightfoot
    $releaseGroup = $brainz->lookup('release', 'e51afa51-9f7d-4adc-aece-fae3a5be3c43', array('recordings'));

    // echo "Summer Side Of Life - Gordon Lightfoot" . PHP_EOL;
    print_r($releaseGroup['media']);



} catch (Exception $e) {
    echo $e->getMessage();
}

exit;

//  Lookup a Label for a release MBID
try{
    $releaseLabels = $brainz->lookup('recording','8debe8d9-a657-4a93-b37f-0f5363c71958', array('artist-rels'));
    
    print_r($releaseLabels);
    
} catch (Exception $e) {
    echo $e->getMessage();
}