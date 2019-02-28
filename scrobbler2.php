<pre>
<?php
error_reporting(E_STRICT);
ini_set('display_errors', 1);

/**
 * Date: 2/25/19
 * Time: 2:48 PM
 * hawkwynd.com - scottfleming
 *
 * Scrobble an artist and title and return a shit-ton of data about the artist, album, and tracks on the album
 *
 */

require_once('include/config.inc.php');
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';

$track  = rawurlencode($_GET['track']);
$artist = rawurlencode($_GET['artist']);

$trackSearch =  json_decode( file_get_contents('http://ws.audioscrobbler.com/2.0/?method=track.search&api_key='.SCROBBLER_API.'&track='.$track.'&artist='.$artist. '&format=json') );

//foreach( $trackSearch->results->trackmatches->track as $result ) // just the first result... {
$result = $trackSearch->results->trackmatches->track[0];
$out = new stdClass();

unset($out->url, $out->streamable, $out->listeners);

    if($result->mbid){
        $trackId     = $result->mbid;
        $trackFind   = json_decode(file_get_contents('http://ws.audioscrobbler.com/2.0/?method=track.getInfo&api_key='.SCROBBLER_API.'&mbid='. $trackId.'&format=json'));

        $artistInfo  = ArtistInfoById($trackFind->track->artist->mbid);

        if($artistInfo) {
            $out->artist->name = $artistInfo[0]['name'];
            $out->artist->mbid =  $artistInfo[0]['mbid'];
            $out->artist->summary = str_replace('Read more on Last.fm','', strip_tags( $artistInfo[0]['summary'] ) );
            $out->track->name = $trackFind->track->name;
            $out->track->mbid = $trackFind->track->mbid;
            $out->track->duration = $trackFind->track->duration;
            $out->album->title = $trackFind->track->album->title;
            $out->album->mbid = $trackFind->track->album->mbid;
            $out->album->image = $trackFind->track->album->image[2]->{"#text"}; // large
            $releaseDate =  getLPRelease( $trackFind->track->album->mbid);
            $out->album->releaseDate = $releaseDate['first_release_date'];
    }

print_r($out);

    }

exit;

/**
 * @param $mbid
 * @return array
 * @desc since last.fm doesn't supply the release date, we'll get it from
 * musicbrainz api and update our content with it.
 */
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


function AlbumTracksList($alid, $tracklist=array())
{
        $albumInfo = json_decode( file_get_contents('http://ws.audioscrobbler.com/2.0/?method=album.getInfo&api_key='.SCROBBLER_API.'&mbid='. $alid .'&format=json') );

        foreach($albumInfo->album->tracks->track as $track)
        {
            array_push($tracklist, array('track_no' => $track->{"@attr"}->rank, 'title' => $track->name));

        }
        return $tracklist;
}



function ArtistInfoById($arid, $artistResult=array())
{
    $artistInfo   = json_decode( file_get_contents('http://ws.audioscrobbler.com/2.0/?method=artist.getInfo&api_key='.SCROBBLER_API.'&mbid='.$arid.'&format=json') );
    $tagArr=[];

    foreach($artistInfo->artist->tags->tag as $tag)
    {
        array_push($tagArr, $tag->name);
    }

    if($artistInfo->artist->mbid)
    {
        array_push(
            $artistResult, array(
            'name'      => $artistInfo->artist->name,
            'mbid'      => $artistInfo->artist->mbid,
            'tags'      => json_encode( $tagArr ),
            'summary'   => $artistInfo->artist->bio->summary,
            'content'   => $artistInfo->artist->bio->content
            )
        );
        return $artistResult;
    }


}

/*
// Get the artists top albumbs
$topAlbums = json_decode( file_get_contents('http://ws.audioscrobbler.com/2.0/?method=artist.gettopalbums&api_key='.SCROBBLER_API.'&artist='.$artist.'&format=json') );

foreach($topAlbums->topalbums->album as $album)
{
    if($album->name && $album->mbid)
    {
        echo $album->name . PHP_EOL;
        echo $album->mbid . PHP_EOL;
        $albumInfo = json_decode( file_get_contents('http://ws.audioscrobbler.com/2.0/?method=album.getInfo&api_key='.SCROBBLER_API.'&mbid='. $album->mbid .'&format=json') );
        print_r($albumInfo->album->tracks->track);
    }

}
*/

// try musicbrainz.org
$url    = 'https://musicbrainz.org/ws/2/artist/?query='.$artist.'%20AND%20type:group&fmt=json';

$arr = json_decode(fetch($url), true);
$artist_mbid = $arr['artists'][0]['id'];

//echo "Artist: ". $artist . " mbid: ". $artist_mbid . " track: " . $track;

//$r_url = "http://musicbrainz.org/ws/2/release/?artist=$artist_mbid&inc=release-groups&country=US&status=official&type=album&fmt=json";

$r_url = "https://musicbrainz.org/ws/2/recording/?query=$track%20AND%20arid:$artist_mbid%20AND%20country:US&fmt=json";

$out = fetch($r_url);
$obj =  json_decode( $out );

echo '<pre>';

print_r($out);

exit;
foreach($obj->releases as $release){
    echo $release->id .PHP_EOL;
    echo $release->title .PHP_EOL;
    echo $release->date . PHP_EOL;
    echo "---------------------" . PHP_EOL;
}






function fetch($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);    // get the url contents
    curl_setopt($ch, CURLOPT_USERAGENT,  "hawkwyndRadio/1.1");
    $data = curl_exec($ch); // execute curl request
    curl_close($ch);
    return $data;
}


function tags($t , $tags=array()){
    function cmp($a, $b)
    {
        return strcmp($a->name, $b->name);
    }
    foreach($t as $tag)
    {
        unset($tag->url);
    }
    usort($t, "cmp");

    return $t;
}



exit;