<pre>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Date: 2/25/19
 * Time: 2:48 PM
 * hawkwynd.com - scottfleming
 *
 * Find an artist and title and return a shit-ton of data about the artist, album, and tracks on the album
 *
 */

require_once('include/config.inc.php');
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';
require '/var/www/hawkwynd.com/guzzle/vendor/autoload.php';


$title  = $_GET['title'];
$artist = $_GET['artist'];
$payload = [];
$out = new stdClass();

echo "<pre>";
echo "Artist: " . $artist . PHP_EOL;
echo "Title: " . $title . PHP_EOL;

print_r(  mongoBrainz( $artist, $title ) );

exit;

// lastfm collection find
function do_find($t, $a) {
    $out                = new stdClass();
    $collection         = (new MongoDB\Client)->stream->lastfm;
    $cursor = $collection->find(
        ['$and'  => [

            [ 'track-name'  => new MongoDB\BSON\Regex($t, 'i')  ],
            [ 'artist-name' => new MongoDB\BSON\Regex($a, 'i')  ]
        ]
        ]
    );

    foreach($cursor as $row){
        $out->artist->name      = $row->{"artist-name"};
        $out->artist->mbid      = $row->{"artist-mbid"};
        $out->artist->summary   = $row->{"artist-summary"};
        $out->track->name       = $row->{"track-name"};
        $out->track->mbid       = $row->{"track-mbid"};
        $out->track->duration   = $row->{"track-duration"};
        $out->album->title      = $row->{"album-name"};
        $out->album->image      = $row->{"album-image"};
        $out->album->mbid       = $row{"album-mbid"};
        $out->album->releaseDate= $row{"album-released"};
        $out->album->label      = $row{"album-label"};
        $out->artist->members   = $row{"artist-members"};
        $out->status            = "MongoDB";
    }

    return $out;

}



/**
 * New And Improved mongoBrainz
 *  ------------ query params ---------------------------------
 *  [ 'recording.title'  =>  new MongoDB\BSON\Regex($t, 'ig') ],
 *  [ 'artist.name'       => new MongoDB\BSON\Regex($a, 'ig')  ]
 */

function mongoBrainz($a, $t){
    
    $out                = new stdClass();
    $collection         = (new MongoDB\Client)->stream->musicbrainz;
    $cursor = $collection->find(
        ['$and'         => [
            // [ 'recording.title'  =>  new MongoDB\BSON\Regex($t, 'ig') ],
            // [ 'recording.title'  =>  $t ],
            [ 'artist.name'      => new MongoDB\BSON\Regex($a, 'ig')  ]
                   ]
        ],
        ['projection'  => [
                            'recording'     => 1,
                            'release'       => 1,
                            'artist'        => 1,
                            'release-group' => 1
                         ],
        ['limit'      => 100 ]]
    );

    foreach($cursor as $row){
        
        // track (recording)
        $out->track->name        = $row->recording->title;
        $out->track->mbid        = $row->recording->id;
        $out->track->duration    = $row->recording->length;
        
        // Artist
        $out->artist->name       = $row->artist->name;
        $out->artist->mbid       = $row->artist->id;
        $out->artist->summary    = $row->artist->wiki->extract;
        $out->artist->wikiurl    = $row->artist->wiki->pageUrl;
        
        // Album (release)
        // $out->release   = $row->release;
        $out->album->title       = $row->release->title;
        $out->album->mbid        = $row->release->id;
        $out->album->image       = $row->{"release-group"}->coverart;
        $out->album->releaseDate = $row->release->date;
        $out->album->label       = $row->release->label;
        $out->album->wikiUrl     = $row->{"release-group"}->wiki->pageUrl;
        $out->album->wikiExtract = $row->{"release-group"}->wiki->extract;

        // $out->{"release-group"} = $row->{"release-group"};
    }

    return $out;
 }

