<?php


// First Find Recording add/update mongo musicbrainz collection
// ============================================================

function firstFindMongoUpdate($out) {
    $collection         = (new MongoDB\Client)->stream->musicbrainz;
    $updateResult = $collection->findOneAndUpdate(
            [
                'recording-id'          => $out['recording']->id
            ],
            ['$set'  => [
                    
                    'query'             => $out['query'],

                    'release'           =>    [
                        'id'            => $out['release']->id, 
                        'title'         => $out['release']->title,
                        'date'          => $out['release']->date,
                        'country'       => $out['release']->country,
                        'label'         => $out['release']->label,
                    ],
                    'artist'            => [
                        'id'            => $out['artist']->id,
                        'name'          => $out['artist']->name, 
                        'wiki'          => $out['artist']->wiki,
                    ],
                    'recording'         => [
                        'id'            => $out['recording']->id,
                        'title'         => $out['recording']->title, // sometimes contains apostophe ie; Bill‘s Love (option ] key)
                        'score'         => $out['recording']->score,
                        'length'        => $out['recording']->length,
                        'release-count' => count($out['recording']->releases)
                    ],
                    'release-group'     => [
                        'id'            => $out['release-group']['id'],
                        'title'         => $out['release-group']['title'],
                        'first-release-date'=>$out['release-group']['first-release-date'],
                        'primary-type'  => $out['release-group']['primary-type'],
                        'musicbrainz'   => $out['release-group']['musicbrainz'],
                        'url-rels'      => $out['release-group']['url-rels'],
                        'coverart'      => $out['release-group']['coverart'],
                        'wiki'          => $out['release-group']['wiki']
                    ],
                    'execution'         => [ 
                        'artistQuery'   => $out['execution']->artistQuery,
                        'recordingQuery'=> $out['execution']->recordingQuery,
                        'time'          => $out['execution']->time
                    ]
                   
            ]
        ],
        ['upsert'           => true,
        'projection'        => 
            [ 
            '_id'             => 1,
             'query'          => 1,
             'release'        => 1, 
             'artist'         => 1,
             'recording'      => 1,
             'release-group'  => 1,
             'execution'      => 1
        
            ],
            'returnDocument'    => MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER,
        ]
    );

    return $updateResult;
}


/**
 * @qid of wikidata path
 * @return extract content from wikipedia of qid
 */
function wikiExtract($qid){
    $url = "https://www.wikidata.org/w/api.php?action=wbgetentities&format=xml&props=sitelinks&ids=$qid&sitefilter=enwiki&format=json";

    $curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER  => 1,
        CURLOPT_URL             => $url,
        CURLOPT_USERAGENT       => 'Hawkwynd Radio 1.0'
    ]);
    // Send the request & save response to $resp
    $resp = curl_exec($curl);
    // Close request to clear up some resources
    curl_close($curl);

    $data = json_decode($resp);
    $title = rawurlencode($data->entities->$qid->sitelinks->enwiki->title); // Rush (band)
    $wikiUrl = "https://en.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exlimit=1&explaintext&exintro&titles=$title";

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER  => 1,
        CURLOPT_URL             => $wikiUrl,
        CURLOPT_USERAGENT       => 'Hawkwynd Radio 1.0'
    ]);

    $wikiResponse = json_decode(curl_exec($curl));

    if(!property_exists($wikiResponse,'query')) return false; // no data found, get the fuck out.

    // init output array
    $output = array(
        'qid'           => null,
        'title'         => null, 
        'extract'       => null,
        'pageid'        => null,
        'pageUrl'       => null
    );
    // iterate response build output
    foreach($wikiResponse->query->pages as $page){
        $output['qid']              = $qid;
        $output['title']            = $page->title;
        $output['extract']          = $page->extract;
        $output['pageid']           = $page->pageid; 
        $output['pageUrl']          = "https://en.wikipedia.org/wiki/".$page->title;
         
    }

    // Close request to clear up some resources
    curl_close($curl);

    return $output; // array data out
}

// Recursive array search function
// searches an associative array for a key, and returns the value of found key.
function recursive_array_search($needle,$haystack) {
    foreach($haystack as $key=>$value) {
        $current_key=$key;
        if($needle===$value OR (is_array($value) && recursive_array_search($needle,$value) !== false)) {
            return $current_key;
        }
    }
    return false;
}

/*
*  execution time calcuate start - end time of process
*/
function etime($time_start){
    $time_end = microtime(true);
    $execution_time = ($time_end - $time_start);

    return number_format((float) $execution_time, 1);
}

