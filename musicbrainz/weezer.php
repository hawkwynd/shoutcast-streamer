<pre>
<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    $time_start = microtime(true); 

    use Guzzle\Http\Client;
    use MusicBrainz\Filters\ArtistFilter;
    use MusicBrainz\Filters\RecordingFilter;
    use MusicBrainz\HttpAdapters\GuzzleHttpAdapter;
    use MusicBrainz\MusicBrainz;

    require __DIR__ . '/vendor/autoload.php';

    $brainz = new MusicBrainz(new GuzzleHttpAdapter(new Client()), 'hawkwynd', 'scootre');
    $brainz->setUserAgent('ApplicationName', '0.2', 'http://example.com');


    $args = array(
        "recording"  => 'Roadhouse Blues',
        "artist"     => 'Rod Piazza',
        "status"     => 'Official',
        'country'    => 'US'
    );

    $firstRecording = array(
        'release'     => null,
        'releaseDate' => new DateTime(),
        'releaseCount' => null,    
        'recording'   => null,
        'artistId'    => null,
        'recordingId' => null,
        'trackLength' => null,
        );
$lastScore = null;

    try {
        $recordings = $brainz->search(new RecordingFilter($args));
        $arr = array();
        // echo json_encode($recordings);
        foreach($recordings as $recording){
            // dont keep going if the recording score is lower than what we have now, we want 100.
            if (null != $lastScore && $recording->getScore() < $lastScore) {
                break; // skip this recording
            }      
            $lastScore        = $recording->getScore();
            $releaseDates     = $recording->getReleaseDates();
            $oldestReleaseKey = key($releaseDates);
                      
            if($releaseDates[$oldestReleaseKey]->format('Y') <= $firstRecording['releaseDate']->format('Y')){
                    
                $firstRecording = array(
                    'release'     => $recording->releases[$oldestReleaseKey],
                    'releaseDate' => $recording->releases[$oldestReleaseKey]->getReleaseDate(),
                    'release-count' => count($recording->releases),
                    'recording'   => $recording,            
                    // 'artist'      => new stdClass(),
                    'recordingId' => $recording->getId(),
                    'trackLength' => $recording->getLength(),
                    // 'execution'   => new stdClass(),
                    );
                    
            }
   
            
        }
        print "Recording Title: " . $firstRecording['recording']->title ."\n";
        print "Recording Length: " . $firstRecording['recording']->getLength()."\n";
        print "Release Title: " . $firstRecording['release']->title . "\n";
        print "Reelase ID: " . $firstRecording['release']->id . "\n";
        print "Release Date: " . $firstRecording['releaseDate']->format('Y')."\n";
        print "recordingId: " . $firstRecording['recordingId']."\n";
        print "releases: " .$firstRecording['release-count'] . "\n";
        print "Artist: " . $firstRecording['recording']->getArtist()->getName()."\n";
        print "Artist ID: " . $firstRecording['recording']->getArtist()->getId()."\n";

        // print "Release ID: " . $firstRecording['recording']->getRelease()->getId();
        // print_r($firstRecording['release']);
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);

        print "Execution time: " . number_format((float) $execution_time, 1);

        // print_r($firstRecording);

    } catch (Exception $e) {
        print $e->getMessage();
    }
?>