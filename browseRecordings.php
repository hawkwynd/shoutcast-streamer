<?php
error_reporting(E_STRICT);
ini_set('display_errors', 1);

use Guzzle\Http\Client;
use MusicBrainz\HttpAdapters\GuzzleHttpAdapter;
use MusicBrainz\MusicBrainz;
require dirname(__DIR__) . '/stream/musicbrainz/vendor/autoload.php';

$releaseId = $_GET['releaseId'];

// Create new MusicBrainz object
$brainz = new MusicBrainz(new GuzzleHttpAdapter(new Client()));
$brainz->setUserAgent('hawkwyndRadio', '1.0', 'http://stream.hawkwynd.com');
$pkg = new stdClass();
$out =[];

try{
    $recordings = (object) $brainz->browseRecording('release', $releaseId);
   
    foreach($recordings->recordings as $track){
            array_push($out, $track);
    }

    echo json_encode($out);

} catch(Exception $e) {
    print $e->getMessage();
}
exit;