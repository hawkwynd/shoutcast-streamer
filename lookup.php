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
require '/var/www/hawkwynd.com/guzzle/vendor/autoload.php';
include_once dirname(__DIR__) . '/stream/include/functions.php';

// keep real spacing for search of mongo
$test       = $_GET['test'];
$tt         = preg_quote($_GET['track']); // pattern match on special characters
$ar         = preg_quote($_GET['artist']);

if(isset($_GET['test'])){
    $dump = mongoBrainz($ar,$tt);
    echo json_encode($dump);
    exit;
}


/**
 * @param $out object
 *
 */

function do_trunc($file, $maxlen) {
    if ( strlen($file) > $maxlen ){
        return  substr($file,0,strrpos($file,". ",$maxlen-strlen($file)) + 1);
    }else{
        return($file);
    }
}


/**
 * New And Improved mongoBrainz
 *  ------------ query params ---------------------------------
 *  [ 'recording.title'  =>  new MongoDB\BSON\Regex($t, 'ig') ],
 *  [ 'artist.name'       => new MongoDB\BSON\Regex($a, 'ig')  ]
 */

 function mongoBrainz($a, $t){
    
    $out                = new stdClass();
    $collection         = (new MongoDB\Client)->stream->musicbrainz;
    $cursor = $collection->find(
        ['$and' => [               
                    [ 'recording.title'     =>  new MongoDB\BSON\Regex($t, 'i') ],                 
                    [ 'artist.name'         => new MongoDB\BSON\Regex($a, 'i') ]
                  ]
        ],
        ['projection'  => [
                            'recording'     => 1,
                            'release'       => 1,
                            'artist'        => 1,
                            'release-group' => 1
                         ],
        ['limit'      => 1 ]]
        
    );


    foreach($cursor as $row){
        
        // track (recording)
        $out->track->name        = $row->recording->title;
        $out->track->mbid        = $row->recording->id;
        $out->track->duration    = $row->recording->length;
        
        // Artist
        $out->artist->name       = $row->artist->name;
        $out->artist->mbid       = $row->artist->id;
        $out->artist->summary    = $row->artist->wiki->extract;
        $out->artist->wikiurl    = $row->artist->wiki->pageUrl;
        
        // Album (release)
        $out->album->title       = $row->release->title;
        $out->album->mbid        = $row->release->id;
        $out->album->image       = $row->{"release-group"}->coverart;
        $out->album->releaseDate = $row->release->date;
        $out->album->label       = $row->release->label;
        $out->album->wikiUrl     = $row->{"release-group"}->wiki->pageUrl;
        $out->album->wikiExtract = $row->{"release-group"}->wiki->extract;

        
    }

    
    $cursor             = $collection->find();
    $out->totalRecs     = count(iterator_to_array($cursor));
 
    return $out;
 }



function do_find($t, $a) {

    $out                = new stdClass();
    $collection         = (new MongoDB\Client)->stream->lastfm;
    $cursor = $collection->find(
        ['$and'  => [           
             [ 'track-name'  =>  array('$regex' => '$t')  ],
            // ['track-name'   => new MongoDB\BSON\Regex($t, 'i') ],
            [ 'artist-name' => $a  ]
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


function fetch($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);    // get the url contents
    curl_setopt($ch, CURLOPT_USERAGENT,  "hawkwyndRadio/1.1");
    $data = curl_exec($ch); // execute curl request
    curl_close($ch);
    return $data;
}


function tags($t , $tags=array())
{
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

function annotation($id)
{
    $url = "https://musicbrainz.org/ws/2/annotation/?query=entity:$id&fmt=json&limit=1&offset=1";
    $url = "http://musicbrainz.org/ws/2/artist/".$arid."?inc=artist-rels&fmt=json";
    $response = $client->request('GET', $url);
    return $response->getBody() ;
}