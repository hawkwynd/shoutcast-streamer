<pre><?php
/**
 * Date: 3/1/19
 * Time: 2:35 PM
 * hawkwynd.com - scottfleming
 */

//require_once('../include/config.inc.php');
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';


$collection         = (new MongoDB\Client)->stream->lastfm_fail;
$cursor = $collection->find();

foreach($cursor as $row){
    $out    = new stdClass();
    $artist = rawurlencode($row->{"artist"});
    $track  = rawurlencode( $row->{"title"} );

    echo  $row->{"title"} . " by " . $row->{"artist"} . " : " . $row->{"arid"} . PHP_EOL;;
}