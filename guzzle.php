<?php
/**
 * Date: 3/5/19
 * Time: 12:10 PM
 * hawkwynd.com - scottfleming
 * Example guzzle call for obtaining json
 */

require '/var/www/hawkwynd.com/guzzle/vendor/autoload.php';

$client = new \GuzzleHttp\Client();
$obj    = new stdClass();


$response = $client->request('GET', 'https://musicbrainz.org/ws/2/recording/77f99f81-c15f-429c-a2d0-70ad4fa26ca3?fmt=json&inc=artist-rels');

//echo $response->getStatusCode(); # 200
//echo $response->getHeaderLine('content-type'); # 'application/json; charset=utf8'
header("Content-type: application/json; charset=utf-8");

$obj->response->status = $response->getStatusCode();
$obj->response->contentType = $response->getHeaderLine('content-type');
$obj->response->body   = json_decode( $response->getBody() );

echo json_encode($obj, true);

exit;

# Send an asynchronous request.
$request = new \GuzzleHttp\Psr7\Request('GET', 'http://httpbin.org');
$promise = $client->sendAsync($request)->then(function ($response) {
    echo 'I completed! ' . $response->getBody();
});

$promise->wait();