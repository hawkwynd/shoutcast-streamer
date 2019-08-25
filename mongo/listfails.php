<pre><?php
/**
 * Date: 3/1/19
 * Time: 2:35 PM
 * hawkwynd.com - scottfleming
 */

//require_once('../include/config.inc.php');
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';


$collection         = (new MongoDB\Client)->stream->musicbrainz;
$cursor = $collection->find(
    [
        'release-group.coverart' => null
    ]
);

foreach($cursor as $row){
    
    echo $row->artist->name . PHP_EOL;
    echo $row->recording->title . PHP_EOL;
    echo $row->release->title . PHP_EOL;
    echo 'https://musicbrainz.org/release/' . $row->release->id . PHP_EOL;

    print_r($row->{"release-group"});
    
}