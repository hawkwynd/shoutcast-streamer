<pre><?php
/**
 * stream.hawkwynd.com - scottfleming - get results from musicbrainz based on mbid
 */

 echo "Hi there.";

 $relid = 'e51afa51-9f7d-4adc-aece-fae3a5be3c43'; // road apples

$url    = "http://musicbrainz.org/ws/2/release/$relid?inc=recordings&fmt=json";

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);    // get the url contents
curl_setopt($ch, CURLOPT_USERAGENT,  "hawkwyndRadio/1.1");
$data = curl_exec($ch); // execute curl request
curl_close($ch);

$xml                = simplexml_load_string($data);

print_r($xml);
