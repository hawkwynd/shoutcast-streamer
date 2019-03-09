<pre><?php
/**
 * Date: 3/5/19
 * Time: 4:35 PM
 * hawkwynd.com - scottfleming
 * http://musicbrainz.org/ws/2/artist/53fa91ca-a2b9-463d-b78e-daca9894082a?inc=artist-rels&fmt=json
 *
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('include/config.inc.php');
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';
require '/var/www/hawkwynd.com/guzzle/vendor/autoload.php';

$payload = $members = [];
$arid = "66c662b6-6e2f-4930-8610-912e24c63ed1"; // The Beatles
$arid = "8741f7c9-2329-48cb-a9ed-122f0336d2bd"; // Jambros
$arid = "3f5be744-e867-42fb-8913-5fd69e4099b5"; // Chicago

$data = json_decode(  do_lookup($arid) );

//print_r($data);

$payload['group_name']  = $data->name;
$begin                  = (!empty($data->{"life-span"}->begin)) ? date('Y', strtotime($data->{"life-span"}->begin)) : '';
$payload['group_begin'] = $begin;
$end                    = (!empty($data->{"life-span"}->end)) ? date('Y', strtotime($data->{"life-span"}->end)) : '';
$payload['group_end']   = $end;

foreach( $data->relations as $relation )
{
    if($relation->type == 'member of band') {

        $begin = (!empty($relation->begin)) ? date('Y', strtotime($relation->begin)) : '';
        $end   = (!empty($relation->end)) ? date('Y', strtotime($relation->end)) : '';

       // echo $relation->artist->name . " " . $begin  . "-" . $end . PHP_EOL;
        $instruments = implode(', ', $relation->attributes);
            //echo "Instruments: " . $instruments . PHP_EOL;
        array_push($members, array('member_name' => $relation->artist->name, 'begin' => $begin, 'end' => $end, 'instruments' => $instruments ) );

    }
}

$payload['members'] = $members;

echo json_encode( $payload, true );


function do_lookup($arid)
{
    $client = new \GuzzleHttp\Client();

    $url = "http://musicbrainz.org/ws/2/artist/".$arid."?inc=artist-rels&fmt=json";
    $response = $client->request('GET', $url);
    return $response->getBody() ;
}