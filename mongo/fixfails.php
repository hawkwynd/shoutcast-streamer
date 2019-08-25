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

require_once('../include/config.inc.php');
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';
require '/var/www/hawkwynd.com/guzzle/vendor/autoload.php'; // guzzle function
require './functions/functions.php';

$trunc              = 300; // limit for summary
$collection         = (new MongoDB\Client)->stream->lastfm_fail;
$cursor             = $collection->find();

foreach($cursor as $row){
    
    $found = false;
    $out    = new stdClass();
    $artist = $row->{"artist"};
    $title  = $row->{"title"};

echo "Searching: " . $row->{"title"} . " by " . $row->{"artist"} . PHP_EOL;

// $artist= "Jimmy Page and Robert Plant";
$payload = (object) json_decode( do_artist_lookup($artist) );

// Skip this one if we dont have a matching artist.
if ( !property_exists($payload, 'artists') ):
    print_r($payload);
    echo "Nothing found for " . $row->{"title"} . " by " . $row->{"artist"} . PHP_EOL;
    continue;
endif;

// exit;

$artist = current($payload->artists);

// print_r($artist);


$out->{"artist-mbid"} = $artist->id;
$out->{"artist-name"} = $artist->name;

// ARTIST ANNOTATION
$a_annotation =  json_decode( get_annotation( $artist->id ) );

print_r( $a_annotation );

// $out->{"artist-summary"}    = $a_annotation->text;
    
$result = json_decode( do_recording_lookup($artist->id, $title) );

$recordings = $result->recordings;

// Get first recording group
$recording = current($recordings);

// RECORDING OBJECT
$out->recording->id = $recording->id;
$out->recording->title = $recording->title;
$out->recording->length = $recording->length;

// $ann = get_annotation( $recording->id );
// $out->recording->annotation = $ann->text;

// RECORDING RELATIONS OBJECT
$relations = get_relations($recording->id);
$out->recording->relations->instruments = $relations->instruments;
$out->recording->relations->vocals = $relations->vocals;

// get the releases in the group
$releases = $recording->releases;

// get the first release
$release = current($releases);
$out->release->id = $release->id;
$out->release->title = $release->title;
$out->release->date = $release->date;
$out->release->country = $release->country;
$out->release->primarytype = $release->{"release-group"}->{"primary-type"};
$out->release->media = $release->media[0]->format;

// RELEASE ANNOTATION OBJECT
$ann = get_annotation($release->id);
$out->release->annotation = $ann->text;

// RELEASE COVER ART
$cover = json_decode( coverArt($release->id) );
$images = current($cover->images);
$out->release->coverArt = $images->thumbnails->large;



// $update = insertUpdate( $out );

print_r($out);
echo "<hr>";

}


/**
 * functions begin here 
 */

function insertUpdate($out)
{
    $collection         = (new MongoDB\Client)->stream->musicbrainz;
    $updateResult       = $collection->findOneAndUpdate(
            [ 'album-mbid' => $out->recording->id ],
            ['$set'  => [
                'artist-name'    => $out->artist->name,
                'artist-mbid'    => $out->artist->id,
                'artist-summary' => $out->artist->annotation,
                'track-name'     => $out->recording->title,
                'track-mbid'     => $out->recording->id,
                'track-duration' => $out->recording->length,
                'track-summary'  => $out->recording->annotation,
                'album-name'     => $out->release->title,
                'album-mbid'     => $out->release->id,
                'album-released' => $out->release->date,
                'album-image'    => $out->release->coverArt,
                'album-summary' => $out->release->annotation
            ]
],
[
    'projection' => [
                        'track-mbid'    => 1,
                        'album-image'   => 1,
                        'album-label'   => 1,
                        'album-mbid'    => 1,
                        'album-name'    => 1,
                        'album-released'=> 1,
                        'artist-name'   => 1,
                        'artist-mbid'   => 1,
                        'artist-summary'=> 1,
                        'track-name'    => 1,
                    ],

    'upsert'    => true,
    'returnDocument' => MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER,

            ]);

    
    return json_encode( $updateResult );

//    deleteLastFmFail($updateResult);

}


function do_trunc($file, $maxlen) {
    if ( strlen($file) > $maxlen ){
        return  substr($file,0,strrpos($file,". ",$maxlen-strlen($file)) + 1);
    }else{
        return($file);
    }
}

// Update lastfm_fail with correct arid for artists
function updateFail($out){

    $collection         = (new MongoDB\Client)->stream->lastfm_fail;
    $updateResult       = $collection->updateMany(
        ['artist'   => $out->artist->name],
        ['$set'     => [
            'artist'    => $out->artist->name,
            'arid'      => $out->artist->id  
        ]]
        );

        $match = $updateResult->getMatchedCount();
        $res = $updateResult->getModifiedCount();
        return $match . " objects matched. $res objects updated.";
}

/**
 * @param $out object
 *
 */

function do_dbUpdate($out)
{
    $collection         = (new MongoDB\Client)->stream->lastfm2;
    $updateResult       = $collection->findOneAndUpdate(
            [ 'track-mbid' => $out->track->mbid ],
            ['$set'  => [
                            'artist-name'    => $out->{"artist-name"},
                            'artist-mbid'    => $out->{"artist-mbid"},
                            'artist-summary' => $out->{"artist-summary"},
                            'track-name'     => $out->track->name,
                            'track-mbid'     => $out->track->mbid,
                            'track-duration' => $out->track->duration,
                            'album-name'     => $out->album->title,
                            'album-mbid'     => $out->album->mbid,
                            'album-released' => $out->album->releaseDate,
                            'album-image'    => $out->album->image
                        ]
            ],
            [
                'projection' => [
                                    'track-mbid'    => 1,
                                    'album-image'   => 1,
                                    'album-label'   => 1,
                                    'album-mbid'    => 1,
                                    'album-name'    => 1,
                                    'album-released'=> 1,
                                    'artist-name'   => 1,
                                    'artist-mbid'   => 1,
                                    'artist-summary'=> 1,
                                    'track-name'    => 1,
                                ],

                'upsert'    => true

            ]);

    print "UPDATE: " . PHP_EOL;
    print_r($updateResult);

//    deleteLastFmFail($updateResult);

}

function deleteLastFmFail($track){

    // remove the lastfm_fail record.
    $rcollection = (new MongoDB\Client)->stream->lastfm_fail;
    $deleteResult = $rcollection->deleteOne( ['title' =>  $track ] );

    printf("\nDeleteing %s\n", $track);
    printf("\nDeleted %d document(s)\n", $deleteResult->getDeletedCount());

}

function do_find($t, $a)
{
    $out                = new stdClass();
    $collection         = (new MongoDB\Client)->stream->lastfm;
    $cursor = $collection->find(
        ['$and'  => [

            [ 'track-name'  => $t ] ,
            [ 'artist-name' => $a ]
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

// this is a comment line.

