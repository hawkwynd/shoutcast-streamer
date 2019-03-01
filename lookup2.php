<?php
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

//$track  = $_GET['track'];
//$artist = $_GET['artist'];

//echo $artist . " - " . $track . "<br/>";

do_find();


exit;


/*
 *  ['$and'  => [

            [ 'track-name' => $_GET['track']],
            [ 'artist-name' => $_GET['artist'] ]

        ]
        ]
 */

function do_find(){
    $collection         = (new MongoDB\Client)->stream->lastfm;
    $cursor             = $collection->find();

    foreach($cursor as $row){
        echo "Artist :" . $row->{"artist-name"}. " <br/> ";
        echo "Artist-mbid: ". $row->{"artist-mbid"} . "<br/>";
        echo "Track Name: ". $row->{"track-name"} . "<br/>";
        echo "track-mbid: " . $row->{"track-mbid"} . "<br/>";
        echo "Album Name: " . $row->{"album-name"} . " (" . $row->{'album-released'} . ")<br/>";
        echo "album-mbid: ". $row->{"album-mbid"} . "<br/>";
        echo "album-image:" . $row->{"album-image"} . "<br/>";
        echo "<hr>";
    }
}







