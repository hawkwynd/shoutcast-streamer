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

// keep real spacing for search of mongo
$tt         = $_GET['track'];
$ar         = $_GET['artist'];


// check the mongodb if we have it
$internalFind = do_find($tt,$ar);
if($internalFind->album->mbid){ // we have this result.
    echo json_encode($internalFind);
    exit;
}

// check the failed db if it's a fail.
$fail = do_findfail($tt, $ar);
if($fail->artist){
    # fail found, just exit.
    //echo json_encode(array("status" => "fail"));
    exit;
}

// Nothing returned on Mongo, and its not in the failed table, so let's call lastFM for it.

$track  = rawurlencode($_GET['track']);
$artist = rawurlencode($_GET['artist']);
$out    = new stdClass();

$trackSearch =  json_decode( file_get_contents('http://ws.audioscrobbler.com/2.0/?method=track.search&api_key='.SCROBBLER_API.'&track='.$track.'&artist='.$artist. '&format=json') );


//foreach( $trackSearch->results->trackmatches->track as $result ) // just the first result... {
$result = $trackSearch->results->trackmatches->track[0];

unset($out->url, $out->streamable, $out->listeners);

if($result->mbid){
    $trackId     = $result->mbid;
    $trackFind   = json_decode(file_get_contents('http://ws.audioscrobbler.com/2.0/?method=track.getInfo&api_key='.SCROBBLER_API.'&mbid='. $trackId.'&format=json'));
    $artistInfo  = ArtistInfoById($trackFind->track->artist->mbid);

    if($artistInfo) {
        $out->artist->name       = $artistInfo[0]['name'];
        $out->artist->mbid       = $artistInfo[0]['mbid'];
        $out->artist->summary    = str_replace('Read more on Last.fm','', strip_tags( $artistInfo[0]['summary'] ) );
        $out->track->name        = $trackFind->track->name;
        $out->track->mbid        = $trackFind->track->mbid;
        $out->track->duration    = $trackFind->track->duration;
        $out->album->title       = $trackFind->track->album->title;
        $out->album->mbid        = $trackFind->track->album->mbid;
        $out->album->image       = $trackFind->track->album->image[2]->{"#text"}; // large
        $releaseDate             = getLPRelease( $trackFind->track->album->mbid);
        $out->album->releaseDate = $releaseDate['first_release_date'];
        $out->status             = "lastFM";
    }
    echo json_encode($out, true); // return result
    do_dbUpdate($out); // update mongo db to add new record found.
}
exit;

/**
 * @param $out object
 *
 */

function do_dbUpdate($out)
{
    $collection         = (new MongoDB\Client)->stream->lastfm;
    $updateResult = $collection->findOneAndUpdate(
        ['$and'    => [
            [ 'track-mbid' => $out->track->mbid ]
        ]
        ],
        ['$set'  => [
            'artist-name'    => $out->artist->name,
            'artist-mbid'    => $out->artist->mbid,
            'artist-summary' => $out->artist->summary,
            'track-name'     => $out->track->name,
            'track-mbid'     => $out->track->mbid,
            'track-duration' => $out->track->duration,
            'album-name'     => $out->album->title,
            'album-mbid'     => $out->album->mbid,
            'album-released' => $out->album->releaseDate,
            'album-image'    => $out->album->image
        ]
        ],
        ['upsert'   => true]
    );

}

function do_find($t, $a)
{
    $out                = new stdClass();
    $collection         = (new MongoDB\Client)->stream->lastfm;
    $cursor = $collection->find(
        ['$and'  => [

            [ 'track-name'  => new MongoDB\BSON\Regex($t, 'i')  ],
            [ 'artist-name' => new MongoDB\BSON\Regex($a, 'i')  ]
        ]
        ]
    );

    foreach($cursor as $row){
        $out->artist->name = $row->{"artist-name"};
        $out->artist->mbid = $row->{"artist-mbid"};
        $out->artist->summary = $row->{"artist-summary"};
        $out->track->name  = $row->{"track-name"};
        $out->track->mbid  = $row->{"track-mbid"};
        $out->track->duration = $row->{"track-duration"};
        $out->album->title = $row->{"album-name"};
        $out->album->image = $row->{"album-image"};
        $out->album->mbid = $row{"album-mbid"};
        $out->album->releaseDate = $row{"album-released"};
        $out->status = "MongoDB";
    }

    return $out;

}

function do_findfail($t, $a)
{
    $out                = new stdClass();
    $collection         = (new MongoDB\Client)->stream->lastfm_fail;
    $cursor = $collection->find(
            [ 'title'  => new MongoDB\BSON\Regex($t, 'i')  ]
    );

    foreach($cursor as $row){
        $out->artist->name = $row->{"artist"};
        $out->track->name  = $row->{"title"};
        $out->status = "MongoDB";
    }

    return $out;

}




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

