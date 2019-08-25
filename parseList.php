<?php
/**
 * Date: 2/9/19
 * Time: 1:22 AM
 * hawkwynd.com - scottfleming
 */


$playlist_arr = getDirContents('playlists');

foreach($playlist_arr as $file):
    if (file_exists($file)) {
        $c   = 0;
        $xml = simplexml_load_file($file);
        echo "<h2>". $xml->title . "</h2>";
        echo "<pre>";
    } else {
        exit('Failed to open test.xml.');
    }

    foreach($xml->trackList->track as $track){
        if($track->title && $track->creator):
            $c++;
            $dur = $track->duration;
            echo $c . ": " .$track->title . " - " . $track->creator . " - " .date("i:s", $dur /1000) . PHP_EOL;
        endif;
    }
endforeach;



function getDirContents($dir, &$results = array()){
    $files = scandir($dir);

    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if(!is_dir($path)) {
            $results[] = $path;
        } else if($value != "." && $value != "..") {
            getDirContents($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}