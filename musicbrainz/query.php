<pre><?php
require_once('../include/config.inc.php');

/**
 * stream.hawkwynd.com - scottfleming - get results from musicbrainz based on artist mbid and track
 * https://musicbrainz.org/ws/2/recording/?query=Headknocker%20AND%20artist:foreigner%20AND%20primarytype:album%20AND%20country:US&inc=release-groups&fmt=json
 */

$out        = new stdClass();
$a          = rawurlencode( $_GET['artist'] );
$t          = rawurlencode( $_GET['title'] );
$url        = "https://musicbrainz.org/ws/2/recording/?query=$t%20AND%20artist:$a%20AND%20primarytype:album%20AND%20country:US%20AND%20status:official&fmt=xml";
$xml        = getXML($url);

$release    = $xml->{"recording-list"}->{"recording"}->{"release-list"}->{"release"};
$array      = (array) $release;

$releaseGroup              = (array) $array['release-group'];


$out->ambid           = $releaseGroup['@attributes']['id'];
$aid = $out->ambid;
$out->atitle          = $array['title'];
$out->released        = $array['date'];
$out->country        = $array['country'];
$trackList           = (array) $array['medium-list']->{'medium'}->{"track-list"};
$out->trackcount      = $trackList['@attributes']['count'];

$track                = (array) $trackList['track'];

$out->trackno         = $track['number'];
$out->duraiton        = $track['length'];
$out->ttitle          = $track['title'];
$out->tid             = $track['@attributes']['id'];

$tid=$out->tid;

print_r($out);

$trackSearch =  getXML('http://ws.audioscrobbler.com/2.0/?method=track.getInfo&api_key='.SCROBBLER_API.'&mbid='.$out->tid);

print_r($trackSearch);

//echo $title . PHP_EOL;
//echo $first_release_date. PHP_EOL;

function getXML($url)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);    // get the url contents
    curl_setopt($ch, CURLOPT_USERAGENT,  "hawkwyndRadio/1.1");
    $data = curl_exec($ch); // execute curl request
    curl_close($ch);

    $xml                = simplexml_load_string($data);
    return $xml;
}