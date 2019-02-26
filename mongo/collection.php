<?php
/**
 * Date: 11/26/18
 * Time: 3:19 PM
 * hawkwynd.com - scottfleming
 * Adds the selected YouTube video to the Mongodb youtube collection db
 *
 * Array (
        [videoId] => auLBLk4ibAk
        [title] => Rush - Tom Sawyer
        [postDate] => 2012-12-21T08:00:50.000Z
        [thumb] => https://i.ytimg.com/vi/auLBLk4ibAk/mqdefault.jpg
        [duration] => 00:04:35
    )
*
*/
require 'mongodb/vendor/autoload.php';

$collection         = (new MongoDB\Client)->scottybox->youtube;
$tz                 = 'America/Chicago';
$timestamp          = time();
$dt                 = new DateTime("now", new DateTimeZone($tz));  //first argument "must" be a string
$dt->setTimestamp($timestamp); //adjust the object to correct timestamp

$updateResult = $collection->findOneAndUpdate(
    ['$and'    =>   [
        ['videoId'     => $_POST['videoId'] ]
    ]
    ],
    ['$set'  => [
        'videoId'        => $_POST['videoId'],
        'title'         => $_POST['title'],
        'postDate'      => $_POST['postDate'],
        'thumb'         => $_POST['thumb'],
        'duration'      => $_POST['duration'],
        'add_date'      => $dt->format('m-d-y g:i:s a')
        ]
    ],
    ['upsert'   => true]
);

echo (string) $updateResult->_id;


