<pre><?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Date: 07/05/19
 * stream.hawkwynd.com - scottfleming
 *
 * Query the mongo musicbrainz db and get 1 record that matches
 * the artist and title of song. Return data for Artist, recording, release and release-group.
 *
 */

require_once('include/config.inc.php');
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';
require '/var/www/hawkwynd.com/guzzle/vendor/autoload.php';

// keep real spacing for search of mongo
$tt         = $_GET['track'];
$ar         = $_GET['artist'];

echo "Searching $tt" . PHP_EOL;
echo "Artist $ar" . PHP_EOL;

$collection         = (new MongoDB\Client)->stream->musicbrainz;
$cursor = $collection->find(
    ['$and'    => [
        [ 'recording.title'     =>  $tt ],
        [ 'artist.name'         =>  $ar ]
    ]
]
);

foreach($cursor as $row){
    print_r( json_encode($row));
}
