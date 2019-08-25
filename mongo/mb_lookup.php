<?php
/**
 * Use musicbrainz and coverartarchive for all lookups
 *
 * Find recordings of title and artist released in US, official status and primary type of album.
 * https://musicbrainz.org/ws/2/recording/?query=Roadhouse+Blues%20AND%20artist:Jambros%20AND%20primarytype:album&inc=release-groups&fmt=json
 *
 * Get a recordings' mbid artists-relationships
 * https://musicbrainz.org/ws/2/recording/77f99f81-c15f-429c-a2d0-70ad4fa26ca3?fmt=json&inc=artist-rels
 *
 * Get release cover art
 * http://coverartarchive.org/release/c0e5b91a-d2f5-4066-b00b-90f62b67894b
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../include/config.inc.php');
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';
require '/var/www/hawkwynd.com/guzzle/vendor/autoload.php';

require('./functions/functions.php');

// Vars to be replaced by $_POST
$artist     = $_POST['artist-name'];
$title      = $_POST['track-name'];
$payload    = [];

$ar_data = json_decode( do_artist_lookup($artist) );


if( !$ar_data){
    die('not found');
}

echo "<pre>";

$artist = current( $ar_data->artists ); // get the first artist element
$artist->annotation = get_annotation( $artist->id );


$data       =  json_decode(  do_recording_lookup($artist->id, $title) );
$recordings = $data->recordings;
$args       = array('status','country','date');

print_r($recordings);
exit;


foreach($recordings as $recording) {
    if( property_exists($recording, 'artist-credit')) {
        $artist = $recording->{"artist-credit"}[0]->artist;
    
        //   Releases iteration
        foreach($recording->releases as $release) {
                $obj = new stdClass();
                
                // Media iteration
                foreach($release->media as $media ){
                    foreach($media->track as $track){
                        if( property_exists($track, 'title') && $track->title != $title):
                           // echo "Not $title :" . $track->title . PHP_EOL;
                            $test = false;
                            break;
                        endif;
                    }
                }

            if( property_exists($release,'status') && $release->status === 'Official' &&
                    property_exists($release, 'country') && $release->country === 'US' &&
                    property_exists($release->{"release-group"}, 'primary-type') && $release->{"release-group"}->{"primary-type"} === "Album" &&
                    property_exists($release, 'date')
                ):
            
                // ARTIST OBJECT
                $obj->artist         = $artist;                            
                // $obj->artist->name      = $artist->name;
                
                // GET ARTIST ANNOTATION if one exists
                $annotation = get_annotation($artist->id);
                if(property_exists($annotation, 'text')):
                    $obj->artist->annotation = $annotation->text;
                endif;
                

                // RELEASE OBJECT
                $obj->release->id   = $release->id;
                $obj->release->title    = $release->title;
                $obj->release->country  = $release->country;                            
                $obj->release->date      = date('Y', strtotime($release->date));

                // RELEASE ANNOTATION if one exists
                $annotation = get_annotation($release->id);
                if( property_exists($annotation, 'text') ):
                    $obj->release->annotation = $annotation->text;
                endif;

                // RELEASE COVERART
                $coverArt = json_decode( coverArt($release->id) );    
                
                if($coverArt):
                    foreach($coverArt->images as $imagegrp){
                    // only grab the front image, no others.
                        if($imagegrp->front):
                            $obj->release->coverArt = $imagegrp->thumbnails->{500};
                        endif;
                    }
                endif;
                
                // RECORDING OBJECT
                $obj->recording->id = $recording->id;
                $obj->recording->title = $recording->title;
                // Get the players of the recording, if any
                property_exists($recording, 'length') ? $obj->recording->length = formatMilliseconds( $recording->length ) : $obj->length = 0;
                
                // RECORDING ANNOTATION if exists
                $annotation = get_annotation($recording->id);
                if( property_exists($annotation, 'text') ):
                    $obj->recording->annotation = $annotation->text;
                endif;

                
                // RELATIONS OBJECT
                $obj->recording->relations = get_relations($recording->id);
                                            
                
                // stuff all our shit in a payload 
                array_push($payload, $obj);
                    
                        
            endif;


        } // foreach releases
} // if property_exists('artist-credit')

} //foreach recordings

usort($payload, "cmp");

// print_r($payload);
echo json_encode( $payload, true );

function cmp($a, $b) {
    return strcmp($a->date, $b->date);
}


