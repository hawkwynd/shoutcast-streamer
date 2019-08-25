<?php

/**
 * Find artist and track and return 1st result
 */
function do_recording_lookup($arid, $title){
    $arid = rawurlencode($arid);
    $title  = rawurlencode($title);
    $client = new \GuzzleHttp\Client(["http_errors" => false]);
    $fileExists = false;

    // Query title and arid to get recordings
    $url = "https://musicbrainz.org/ws/2/recording/?query=$title%20AND%20arid:$arid%20AND%type=recording&fmt=json";
       
    echo "\n" . $url .PHP_EOL;

    try {
    $response = $client->request('GET', $url); 
    
    } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
        
        // If a 404 was returned, then the file doesn't exist
        $fileExists = ( $e->getResponse()->getStatusCode() != 404 );
    }

    if ($fileExists) {
        return false;                                                                         
    } else {
        return $response->getBody() ;                                                                  
    }
}

function fetch_artist($id){

    if(!$id) return false;

    $client = new \GuzzleHttp\Client(["http_errors" => false]);
    
    $url = "https://musicbrainz.org/ws/2/artist/?query=arid:$id&fmt=json";

    try {
        $response = $client->request('GET', $url); 
        
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
            
            // If a 404 was returned, then the file doesn't exist
            $fileExists = ( $e->getResponse()->getStatusCode() != 404 );
        }
    
        if ($fileExists) {
            return false;                                                                         
        } else {
            return $response->getBody() ;                                                                  
        }
}


function do_artist_lookup($artist){
    $artist = rawurlencode($artist);
    $client = new \GuzzleHttp\Client(["http_errors" => false]);
    $fileExists = false;
    $url = "https://beta.musicbrainz.org/ws/2/artist/?query=$artist&fmt=json&limit=1";

    try {
    $response = $client->request('GET', $url); 
    
    } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {        
        // If a 404 was returned, then the file doesn't exist
        $fileExists = ( $e->getResponse()->getStatusCode() != 404 );
    }

    if ($fileExists) {
        return false;                                                                         
    } else {

        $data = $response->getBody();

        return $response->getBody() ;                                                                  
    }
}


/**
 * Get relations for recording mbid, if available
 */
function get_relations($rid){
    $url = "http://musicbrainz.org/ws/2/recording/$rid?inc=artist-rels+artist-credits+isrcs+releases+annotation&fmt=json&limit=1";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);    // get the url contents
    curl_setopt($ch, CURLOPT_USERAGENT,  "hawkwyndRadio/1.1");
    $data = curl_exec($ch); // execute curl request
    curl_close($ch);

    $payload = json_decode($data);

    //print_r($payload);
    $vocals = $instruments = [];
    $out = new stdClass();

    if ( property_exists($payload, 'relations') ):

        foreach ($payload->relations as $relation){
            if($relation->type === 'instrument'){
                array_push($instruments , (object) array('name' => $relation->artist->name, 'instrument' => $relation->attributes[0]));
            }
            if($relation->type === 'vocal'){
                array_push($vocals , (object) array('name' => $relation->artist->name, 'vocal' => $relation->type));
        }

            $out->instruments = $instruments;
            $out->vocals = $vocals;
            
        }
    endif;
    //print_r($out);

    return $out;
    
    ;
}

function coverArt($id){
    $url        = "http://coverartarchive.org/release/$id";    
    $client     = new \GuzzleHttp\Client(["http_errors" => false]);  
    $fileExists = false;
    
    try {
        $response = $client->request('GET', $url);
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
            
            // If a 404 was returned, then the file doesn't exist
            $fileExists = ( $e->getResponse()->getStatusCode() != 404 );
        }
    
        if ($fileExists) {
            return false;                                                                         
        } else {            
            return $response->getBody() ;                                                                  
        }
}

/**
 * get_annotation 
 * @desc Retrieive the last group of annotation history for and artist.
 */
function get_annotation($id){
    $url = "http://musicbrainz.org/ws/2/annotation/?query=entity:$id&fmt=json";
    // echo $url . PHP_EOL;
    $client     = new \GuzzleHttp\Client();  
    $fileExists = false;
    
    try {
        $response = $client->request('GET', $url);
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {            
            // If a 404 was returned, then the file doesn't exist
            $fileExists = ( $e->getResponse()->getStatusCode() != 404 );
        }
    
        if ($fileExists) {
            return false;;                                                                         
        } else {            
            $annotation = json_decode( $response->getBody() );
            $annotation = end($annotation->annotations);
            return $annotation->text;
                                                                
        }  

}


function formatMilliseconds($milliseconds) {
    $seconds = floor($milliseconds / 1000);
    $minutes = floor($seconds / 60);
    $milliseconds = $milliseconds % 1000;
    $seconds = $seconds % 60;
    $minutes = $minutes % 60;
    $format = '%02u:%02u';
    $time = sprintf($format,  $minutes, $seconds);
    return rtrim($time, '0');
}
