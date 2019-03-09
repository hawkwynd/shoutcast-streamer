<pre><?php
error_reporting(E_ALL);
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
require '/var/www/hawkwynd.com/guzzle/vendor/autoload.php';

// keep real spacing for search of mongo
$tt         = $_GET['track'];
$ar         = $_GET['artist'];


$track  = rawurlencode($_GET['track']);
$artist = rawurlencode($_GET['artist']);

$payload = $final = array();
$response = json_decode( do_mb_search($ar, $tt) );

foreach($response->recordings as $recording){
    foreach ($recording->releases as $release){
        if( (property_exists($release,'country') && $release->country === "US") &&
            $release->status === "Official" &&
            $release->{"release-group"}->{"primary-type"} === "Album" &&
            $release->date !=='' && !property_exists($release->{"release-group"}, 'secondary-types' )
        ){
            array_push($payload, $release);
        }

    }
}

echo "RELEASE COUNT: " . count($payload) . " \n\n";

foreach($payload as $release){
$release = $payload[0];
    $out    = new stdClass();

    foreach($release->media as $media){
     foreach($media->track as $track){

            if(property_exists($track, 'length')):
                    $out->{"album-mbid"} = $release->id;
                    $out->{"album-title"} = $release->title;
                    $out->{"album-released"} = date('Y' , strtotime($release->date));

                   // echo $track->title . "\n";
                    $out->{"track-name"} = $track->title;
                   // echo $track->id . "\n";
                    $out->{"track-mbid"} = $track->id;
                   // echo $track->length . "\n";
                    $out->{"track-duration"} = $track->length;

                //echo "//--------- End of Release " . $release->id . " -----\n\n";
                array_push($final, $out);
            endif;

               // print_r($media);
            //}

            $caResult = json_decode( do_coverart($release->id) );

            if( property_exists($caResult, 'images') ):
                foreach ($caResult->images as $image) { // only get the Front image, no Booklet or additionals
                    if( in_array('Front', $image->types) && count($image->types) === 1 ){
                        foreach($image->types as $type){
                           // echo $type . ":" . $image->thumbnails->large . "\n";
                            $out->{"album-image"} =  $image->thumbnails->large; // assign the album-image
                        }
                    }
                }


           endif;
        } // media->track as track
    } // release->media as media
} // payload->release

print_r($final);


exit;

/**
 * @param $out object
 *
 */

function do_trunc($file, $maxlen)
{

    if ( strlen($file) > $maxlen ){
        return  substr($file,0,strrpos($file,". ",$maxlen-strlen($file)) + 1);
    }else{
        return($file);
    }

}

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
            'album-image'    => $out->album->image,
            'album-label'    => $out->album->label,
            'album-lid'      => $out->album->lid,
            'artist-members' => $out->artist->members
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
        $out->artist->name      = $row->{"artist-name"};
        $out->artist->mbid      = $row->{"artist-mbid"};
        $out->artist->summary   = $row->{"artist-summary"};
        $out->track->name       = $row->{"track-name"};
        $out->track->mbid       = $row->{"track-mbid"};
        $out->track->duration   = $row->{"track-duration"};
        $out->album->title      = $row->{"album-name"};
        $out->album->image      = $row->{"album-image"};
        $out->album->mbid       = $row{"album-mbid"};
        $out->album->releaseDate= $row{"album-released"};
        $out->album->label      = $row{"album-label"};
        $out->artist->members   = $row{"artist-members"};
        $out->status            = "MongoDB";
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

function get_release_details($rid)
{
    $client = new \GuzzleHttp\Client();
    $url = "http://musicbrainz.org/ws/2/release/$rid?inc=labels&fmt=json";

    $response = $client->request('GET', $url);
    return json_decode( $response->getBody() );

}


function get_members($arid)
{
    $payload = $members = [];
    $data = json_decode(  do_member_lookup($arid) );
    $payload['group_name']   = $data->name;
    $begin = (!empty($data->{"life-span"}->begin)) ? date('Y', strtotime($data->{"life-span"}->begin)) : '';
    $payload['group_begin']  = $begin;
    $end = (!empty($data->{"life-span"}->end)) ? date('Y', strtotime($data->{"life-span"}->end)) : '';
    $payload['group_end']   = $end;

    foreach( $data->relations as $relation )
    {
        if($relation->type == 'member of band') {
            $begin = (!empty($relation->begin)) ? date('Y', strtotime($relation->begin)) : '';
            $end   = (!empty($relation->end)) ? date('Y', strtotime($relation->end)) : '';

            $instruments    = implode(', ', $relation->attributes);

            array_push($members, array('member_name' => $relation->artist->name, 'begin' => $begin, 'end' => $end, 'instruments' => $instruments ) );

        }
    }
    $payload['members'] = $members;
    return $payload;
}


function do_member_lookup($arid)
{
    $client = new \GuzzleHttp\Client();
    $url = "http://musicbrainz.org/ws/2/artist/".$arid."?inc=artist-rels&fmt=json";
    $response = $client->request('GET', $url);
    return $response->getBody() ;
}

function do_mb_search($a, $t){
    $client = new \GuzzleHttp\Client();
    $url = "https://musicbrainz.org/ws/2/recording/?query=$t%20AND%20artist:$a%20AND%20primarytype:Album%20AND%20country:US%20AND%20status:Official&inc=release-groups&fmt=json";
    $response = $client->request('GET', $url);
    return $response->getBody() ;
}

function do_coverart($rid){
    $client = new \GuzzleHttp\Client();
    $url = "https://coverartarchive.org/release/" . $rid;
    $res = $client->request('GET', $url);

  return  $res->getBody();

}