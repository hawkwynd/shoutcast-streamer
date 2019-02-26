<?php
/**
 * Date: 10/22/18
 * Time: 4:07 PM
 * scottybox - sfleming
 * This updates the mongo db lovedSongs table on Scottybox.
 * Handles duplicate loves as well, by updating the datetime stamp
 * and it's records.
 * https://www.discogs.com/developers/#page:home
 * Api information
 */
      error_reporting(E_ALL);
      ini_set('display_errors', 1);

// TODO: Build and admin panel to manage the records.
// TODO: Edit/Update/Delete records functions on the back end
// TODO: Require authentication to do this.

// mongodb connection
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';
$collection         = (new MongoDB\Client)->stream->fails;


$updateResult = $collection->findOneAndUpdate(
    ['$and'    =>   [
                        ['artist'    => $_POST['artist']],
                        ['title'     => $_POST['title'] ]
                    ]
    ],
        ['$set'  => [
                    'title'         => $_POST['title'],
                    'artist'        => $_POST['artist'],
                 ]
    ],
    ['upsert'   => true]
);

echo (string) $updateResult->_id;

exit; // shut the door on your way out..

/**
 * @param $s
 * @return mixed
 * wikidefinition -- returns json format of results from the query searching for
 */

function wikidefinition($s) {
    $url = "https://en.wikipedia.org/w/api.php?action=query&prop=extracts&exintro=&format=json&titles=".urlencode($s);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
    curl_setopt($ch, CURLOPT_POST, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_NOBODY, FALSE);
    curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
    curl_setopt($ch, CURLOPT_REFERER, "");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; he; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8");
    $page = curl_exec($ch);

    return($page);
}