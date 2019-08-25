<pre><?php
error_reporting(E_STRICT);
ini_set('display_errors', 1);

/**
 * Date: 2/25/19
 * Time: 2:48 PM
 * hawkwynd.com - scottfleming
 *
 * search an artist and title in local mongoDB musicbrainz collection
 *
 */

require_once('include/config.inc.php');
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';
require '/var/www/hawkwynd.com/guzzle/vendor/autoload.php';



// keep real spacing for search of mongo
$t        =  preg_quote("It’s All I Can Do");
$a         = "The Cars";

// echo $t .PHP_EOL;
// $t = str_replace("'","‘",$t);

$output = mongoBrainz($a, $t);

echo $output->artist->name . PHP_EOL;
echo $output->artist->id . PHP_EOL;



print_r( iterator_to_array( $output ));



function mongoBrainz($a, $t){
    
    $out                = new stdClass();
    $collection         = (new MongoDB\Client)->stream->musicbrainz;
    $cursor = $collection->find(
      ['$and' => [
                     
                    ['recording.title'  => new MongoDB\BSON\Regex($t, "img")],
                    ['artist.name'      => new MongoDB\BSON\Regex($a, "img")]
                    
                ]
    ],                       
        ['projection'  => [
                            'recording'     => 1,
                            'release'       => 1,
                            'artist'        => 1,
                            'release-group' => 1
                         ],
        ['limit'      => 1 ]]
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

        $out->{"release-group"} = $row->{"release-group"};

        $payout = $row;
    }

    return $payout;
 }