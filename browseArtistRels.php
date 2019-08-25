<pre><?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use Guzzle\Http\Client;
use MusicBrainz\HttpAdapters\GuzzleHttpAdapter;
use MusicBrainz\MusicBrainz;
require dirname(__DIR__) . '/stream/musicbrainz/vendor/autoload.php';

$artistId = "092b603f-eb4c-4958-b10e-02420de5885b";
$recordingId = "de1182b0-36cb-47ae-864c-0ba9ad30f6f7"; // Let's Go
// Create new MusicBrainz object
$brainz = new MusicBrainz(new GuzzleHttpAdapter(new Client()));
$brainz->setUserAgent('hawkwyndRadio', '1.0', 'http://stream.hawkwynd.com');
$out =new stdClass(); 
$rec_array=[];


try{
    // $includes = array('labels', 'recordings','artist-rels');
    $includes = array('recordings', 'labels');

    $releases = $brainz->browseRelease('artist', $artistId, $includes, 25);
     foreach($releases['releases'] as $release){
         echo $release['date'] . " " . $release['title']. " " . $release['country']  . PHP_EOL;
    }
    
    print_r($releases);
    


} catch(Exception $e) {
    print $e->getMessage();
}
exit;