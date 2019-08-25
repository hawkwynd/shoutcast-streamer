<pre><?php

use Guzzle\Http\Client;
use MusicBrainz\Filters\RecordingFilter;
use MusicBrainz\HttpAdapters\GuzzleHttpAdapter;
use MusicBrainz\MusicBrainz;

require dirname(__DIR__) . '/vendor/autoload.php';  

//Create new MusicBrainz object
$brainz = new MusicBrainz(new GuzzleHttpAdapter(new Client()), 'hawkwynd', 'scootre');
$brainz->setUserAgent('ApplicationName', '0.2', 'http://example.com');
// $brainz->auth(['user' => 'hawkwynd', 'password' => 'scootre']);

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

// Set the search arguments to pass into the RecordingFilter
$a = "Jonny Lang"; // artist to search
$t = "Lie to Me"; // title to search


$args = array(
    "recording" => $t,
    "artist"    => $a,
    'status'    => 'official',
    'country'   => 'US'
);

try {
    // Find all the recordings that match the search and loop through them
    $recordings = $brainz->search(new RecordingFilter($args));

    /** @var $recording \MusicBrainz\Recording */
    foreach ($recordings as $recording) {

        // if the recording has a lower score than the previous recording, stop the loop.
        // This is because scores less than 100 usually don't match the search well
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
                'artistId'    => $recording->getArtist()->getId(),
                'recordingId' => $recording->getId(),
                'trackLength' => $recording->getLength('long')
            );
        }
    }

    // print_r(array($firstRecording));

    echo json_encode($firstRecording);

} catch (Exception $e) {
    print ($e->getMessage());
}
