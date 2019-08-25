<?php
require_once('include/config.inc.php');
require '/var/www/hawkwynd.com/mongodb/vendor/autoload.php';

$out                = new stdClass();
$collection         = (new MongoDB\Client)->stream->musicbrainz;
$cursor = $collection->find();
$count = count(iterator_to_array($cursor));

echo $count;

?>