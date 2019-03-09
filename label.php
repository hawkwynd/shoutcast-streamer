<pre><?php
/**
 * Date: 3/5/19
 * Time: 2:54 PM
 * hawkwynd.com - scottfleming
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '/var/www/hawkwynd.com/guzzle/vendor/autoload.php';

$id      = "4946f82c-2cc1-3bbd-8ae3-5b89a79d7c39"; // Abbey Road
$details =  get_release_details($id);

$label = $details->{"label-info"}[0]; // first element of array, fuck the others.

    echo "name: ". $label->label->name . PHP_EOL;
    echo "label-id: " . $label->label->id . PHP_EOL;

print_r($label);

function get_release_details($rid)
{
    $client = new \GuzzleHttp\Client();
    $url = "http://musicbrainz.org/ws/2/release/$rid?inc=labels&fmt=json";

    $response = $client->request('GET', $url);
    return json_decode( $response->getBody() );

}

