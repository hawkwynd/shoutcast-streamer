<?php
/**
 * Date: 11/2/18
 * Time: 6:12 PM
 * scottybox - scottfleming
 *
 * Display total plays for an artist queried overall.
 */
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

require 'mongodb/vendor/autoload.php';

date_default_timezone_set('America/Chicago');


if (isset($_GET['artist'])) {
    $artist             = $_GET['artist'];
}
// Declare vars
$collection         = (new MongoDB\Client)->scottybox->pianobar;
$collection_count   = $collection->count();
$allSongsCount      = 0;
$station_names      = $genres = $titles = $albums = $years = $albumdata = [];

if (isset($artist)):

        $results    = $collection->find( ['artist'          => $artist],
                                         ['projection'      =>
                                         [  'artist'        => 1,
                                            'num_plays'     => 1,
                                            'title'         => 1,
                                            'last_played'   => 1,
                                            'stationName'   => 1,
                                            'genre'         => 1,
                                            'album'         => 1,
                                        ] ]);

        // how many times does 'artist' appear in collection?
        $artistCount = $collection->count(['artist' => $artist]);

        foreach( $results as $row ){
            $allSongsCount += $row->num_plays;
            array_push($station_names,$row->stationName);
            array_push($genres, $row->genre);
            array_push($titles, $row->title);
            array_push( $albums, $row->album);
        }

        // tally num_plays per title
        $tCount = []; $i=0;
        foreach($titles as $t){
            $tcounts = $collection->find(['title'           => $t],
                                         ['projection'      =>
                                         [  'title'         => 1,
                                            'stationName'   => 1,
                                            'num_plays'     => 1
                                        ],
                                    ]);

        foreach($tcounts as $tt){
            $i++;
            $tCount[$i]['title']        = $tt->title;
            $tCount[$i]['stationName']  = $tt->stationName;
            $tCount[$i]['count']        = $tt->num_plays;
        } // foreach tcounts
        } // foreac titles

        // sort array by count descending
        array_multisort(array_column($tCount, 'count'), SORT_DESC, $tCount);

        echo json_encode(array(
            'collection_count'          => $collection_count,
            'artist_name'               => $artist,
            'artist_hit_count'          => $artistCount,
            'artist_percentile'         => round($artistCount / ($collection_count / 100),2)."%",
            'artist_stations'           => array_values(array_unique($station_names)),
            'artist_genres'             => array_values(array_unique($genres)),
            'artist_titles'             => $titles,
            'artist_albums'             => array_values(array_unique($albums)),
            'allsongs_played_count'     => $allSongsCount,
            'count_per_title'           => $tCount

        ));

else:

    /**
     * Global statistical data for footer
     */

    // count today's total song plays.

    $today              = date('m').'-'.date('d').'-'.date('y'); // 12-16-16
    $regex              = new MongoDB\BSON\Regex ($today, 'ig');

    $todaysPlays        = $collection->count(['last_played' => $regex ] );
    $glbl               = $collection->find();
    $artists            = $channels = $genres = $titles = $albums = $labels =[];

    // find the top played artist/title and num_plays
    $filter     = [];
    $options    = ['sort' => ['num_plays' => -1]]; // -1 is for DESC
    $result     = $collection->findOne($filter, $options);

    foreach($glbl as $row){
        array_push($artists, $row->artist);
        array_push($channels, $row->stationName);
        array_push($genres, $row->genre);
        array_push($titles, $row->title);
        array_push($albums, $row->album);
        array_push($labels, $row->label);
    }

    $out = array(
        'channelcount'          => count(array_unique($channels)),
        'artistcount'           => count(array_unique($artists)),
        'genrecount'            => count(array_unique($genres)),
        'titlecount'            => count(array_unique($titles)),
        'albumcount'            => count(array_unique($albums)),
        'labelcount'            => count(array_unique($labels)),
        'total_songs_today'     => $todaysPlays,
        'top_artist'            => $result->artist,
        'top_title'             => $result->title,
        'top_plays'             => $result->num_plays,
        'last_played'           => $result->last_played

    );

    echo json_encode($out);

endif;


function wikidefinition($s) {
    $url = "https://en.wikipedia.org/w/api.php?action=query&prop=extracts&exintro=&format=json&titles=" . urlencode($s);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
    curl_setopt($ch, CURLOPT_POST, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_NOBODY, FALSE);
    curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
    curl_setopt($ch, CURLOPT_REFERER, "");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; he; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8");
    $page = curl_exec($ch);
    return ($page);
}
