<pre><?php

// https://musicbrainz.org/ws/2/artist/?query=Jambros&fmt=json
/**
 * SimpleXMLElement Object
    (
        [@attributes] => Array
        (
            [id] => 8741f7c9-2329-48cb-a9ed-122f0336d2bd
            [type] => Group
            [type-id] => e431f5f6-b5d2-343d-8b36-72607fffb74b
        )
    )
 * to call the @attributes use $obj->attributes()->id;
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('include/config.inc.php');
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';
require '/var/www/hawkwynd.com/guzzle/vendor/autoload.php';

$output       = $payload = array();
$artist       = $_GET['artist'];
$title        = $_GET['title'];

$response     = fetchMB($artist, $title);

foreach($response->{"recording-list"}->recording as $rec)

{
    //print "\n" . $rec->{"artist-credit"}->{"name-credit"}->artist->name . " : " . $rec->{"artist-credit"}->{"name-credit"}->artist->attributes()->id."\n";

    $payload['artist_name'] = (string)$rec->{"artist-credit"}->{"name-credit"}->artist->name;
    $payload['artist_id']   = (string) $rec->{"artist-credit"}->{"name-credit"}->artist->attributes()->id;

    $release = $rec->{"release-list"}->release;
   // print $release->title . " : " . $release->attributes()->id . " : " . $release->date ."\n";

    $payload['release_title'] = (string)$release->title;
    $payload['release_date'] =  date('Y' , strtotime( (string)$release->date ) );
    $payload['release_id'] = (string)$release->attributes()->id;

    $payload['release_coverart'] = coverArt($payload['release_id']);

    $payload['release_country'] = (string) $release->country;
    $medium = $release->{"medium-list"}->medium;
    $track  = $medium->{"track-list"}->track;

    $payload['track_title']  = (string) $track->title;
    $payload['track_id']     = (string) $track->attributes()->id;
    $payload['track_number'] = (string) $track->number;
    $payload['track_length'] = (string) $track->length;

   // print "\t" . $track->title . " : " . $track->attributes()->id . " : " . $track->number . " : " . $track->length. "\n";

    //if($payload['release_date'] && $payload['release_country'] === 'US' && $payload['artist_name'] == $artist) array_push ($output, $payload);

    if($payload['release_coverart']) array_push ($output, $payload);

}

usort($output, function($a, $b) {
    return $a['release_date'] <=> $b['release_date'];
});

print_r($output);

exit;



/**
 * @param $a
 * @return object
 * @desc query an artist string
 * artist id
 */
function coverArt($releaseID){

//use GuzzleHttp\Client;
//use GuzzleHttp\Exception\RequestException;

    $client     = new \GuzzleHttp\Client();

    try {
        $response = $client->get("http://coverartarchive.org/release/$releaseID", [
            'connect_timeout' => 3
        ]);

        // Here the code for successful request
        return json_decode( $response->getBody() );

    } catch (RequestException $e) {

        // Catch all 4XX errors
        // To catch exactly error 400 use
        if ($e->getResponse()->getStatusCode() == '400') {
           // echo "Got response 400";
        }

        // You can check for whatever error status code you need

    } catch (\Exception $e) {
        // There was another exception.
    }
}



function fetchMB($a, $t){
    $client     = new \GuzzleHttp\Client();

    $params     = "$t AND artist:$a AND primarytype:Album AND status:Official AND country:US";
    $url        = "https://musicbrainz.org/ws/2/recording/?query=" . urlencode($params) . "&inc=release-groups+artist-rels+artist-credits" ;

    $response = $client->request('GET', $url);
    $xml        = simplexml_load_string( $response->getBody() );
    return $xml;
}


function coverartarchive($rid){

}