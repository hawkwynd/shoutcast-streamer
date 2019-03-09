<?php
/**
 * Use musicbrainz and coverartarchive for all lookups
 *
 * Find recordings of title and artist released in US, official status and primary type of album.
 * https://musicbrainz.org/ws/2/recording/?query=Roadhouse+Blues%20AND%20artist:Jambros%20AND%20primarytype:album&inc=release-groups&fmt=json
 *
 * Get a recordings' mbid artists-relationships
 * https://musicbrainz.org/ws/2/recording/77f99f81-c15f-429c-a2d0-70ad4fa26ca3?fmt=json&inc=artist-rels
 *
 * Get release cover art
 * http://coverartarchive.org/release/e2d1f705-adc0-41f9-ad89-5b3d15c81ea0
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../include/config.inc.php');
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';
require '/var/www/hawkwynd.com/guzzle/vendor/autoload.php';

$artist = "Aerosmith";
$title  = "Dream On"
;
$payload = new stdClass();

$data =  json_decode(  do_recording_lookup($artist, $title) );
$recordings = $data->recordings;

//print_r($recordings);
echo "<pre>";

$args = array('status','country','date');

foreach($recordings as $recording)
{
    foreach($recording->releases as $release)
    {
        $test = true;
        // only want the artist's releases
        foreach($release->{"artist-credit"} as $artist_credit){
            if( property_exists($artist_credit, 'artist') && $artist_credit->artist->name != $artist ):
                //echo "Not $artist :" . $artist_credit->artist->name . PHP_EOL;
                $test = false;
                 break;
            endif;

        }

        foreach($release->media as $media ){
            foreach($media->track as $track){
                if( property_exists($track, 'title') && $track->title != $title):
                    //echo "Not $title :" . $track->title . PHP_EOL;
                    $test = false;
                    break;
                endif;
            }
        }


        if( $test && property_exists($release,'status') && $release->status === 'Official' &&
            property_exists($release, 'country') && $release->country === 'US' &&
            property_exists($release->{"release-group"}, 'primary-type') && $release->{"release-group"}->{"primary-type"} === "Album" &&
            property_exists($release, 'date')
        ):
                    echo "artist id: ". $release->{"artist-credit"}[0]->artist->id . PHP_EOL;

                    echo "recording id: ". $recording->id . PHP_EOL;
                    echo "recording title: " . $recording->title . PHP_EOL;
                    echo "recording length: " . formatMilliseconds( $recording->length ) .PHP_EOL;
                    echo "\n\trelease id: ". $release->id . PHP_EOL;
                    echo "\trelease status: " . $release->status . PHP_EOL;
                    echo "\trelease title: ". $release->title . PHP_EOL;
                    echo "\trelease country: " . $release->country . PHP_EOL;
                    echo "\trelease date: ". $release->date . PHP_EOL;
                    echo "--------------------------------\n\n";
        //print_r($release);

        endif;

   }
}


function formatMilliseconds($milliseconds) {
    $seconds = floor($milliseconds / 1000);
    $minutes = floor($seconds / 60);
    //$hours = floor($minutes / 60);
    $milliseconds = $milliseconds % 1000;
    $seconds = $seconds % 60;
    $minutes = $minutes % 60;

    $format = '%02u:%02u';
    $time = sprintf($format,  $minutes, $seconds);
    return rtrim($time, '0');
}


function get_release_details($rid)
{
    $client = new \GuzzleHttp\Client();
    $url = "http://musicbrainz.org/ws/2/release/$rid?inc=labels+discids+recordings&fmt=json";

        $response = $client->request('GET', $url);
    return json_decode( $response->getBody() );

}


function get_recording_detail($mbid)
{
    $url = "https://musicbrainz.org/ws/2/recording/";
    $url .= $mbid . "?fmt=json&inc=artist-rels";

    return do_curl($url);

}

function do_recording_lookup($artist,$title){

    $artist = rawurlencode($artist);
    $title  = rawurlencode($title);

    $client = new \GuzzleHttp\Client();

    $url = "https://musicbrainz.org/ws/2/recording/?query=$title%20AND%20artist:$artist%20AND%20primarytype:album%20AND%20status:official%20AND%20country:US&inc=labels+artist-credits+recording&fmt=json";

    $response = $client->request('GET', $url);

    return $response->getBody() ;

}




function getLPRelease($mbid)
{
    $url    = 'http://musicbrainz.org/ws/2/release/'.$mbid.'?inc=release-groups&fmt=xml';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);    // get the url contents
    curl_setopt($ch, CURLOPT_USERAGENT,  "hawkwyndRadio/1.1");
    $data = curl_exec($ch); // execute curl request
    curl_close($ch);

    $xml                = simplexml_load_string($data);
    $first_release_date = $xml->release->{"release-group"}->{"first-release-date"};
    $title              = (string) $xml->release->{"release-group"}->title;
    $formatted_release_year = date('Y', strtotime($first_release_date));

    $out = array('title' => $title, 'first_release_date' =>  $formatted_release_year, 'mbid' => $mbid);

    return $out;
}
