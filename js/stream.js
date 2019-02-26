/**
 * FUNCTIONS
 * @param s
 * @returns {string}
 */

function secondsTimeSpanToHMS(s) {
    var h = Math.floor(s/3600); //Get whole hours
    s -= h*3600;
    var m = Math.floor(s/60); //Get remaining minutes
    s -= m*60;
    return h+":"+(m < 10 ? '0'+m : m)+":"+(s < 10 ? '0'+s : s); //zero padding on minutes and seconds
}

function statistics(){

    $.getJSON('statistics.php', function(data){

     //  console.log(data);

        var meta            = data.streams[0].songtitle;
        var artist          = meta.substr(0, meta.indexOf(' - '));
        var title           = meta.substr(meta.indexOf(' - ') + 3);
        var servercontent   = data.streams[0].servertitle.split('-'); // configured in software - some text
        var servertitle     = servercontent.shift();
        var motd            = servercontent;                         // one-line motd on server
        var samplerate      = data.streams[0].samplerate;           // samplerate 44100
        var bitrate         = data.streams[0].bitrate;              // bitrate  128
        var genre           = data.streams[0].servergenre;          // not used
        var streamstatus    = data.streams[0].streamstatus;         // status of the stream
        var streamuptime    = data.streams[0].streamuptime;         // how long stream is playing

        if(streamstatus > 0 ){

            // Check if no artist/title data came, but we have a streamstatus
            // must only mean we're doing a live broadcast.

            var listeners = data.currentlisteners;

            if(!artist || !title){

                console.log('Live broadcast detected : ' + motd[0] );
                $('.nowplaying').css('width','30%').css('margin','auto');
                $('.nowplaying-title').html(motd);
                $('.thumb-container').html('<img src="img/no_image.png">');
                $('.listeners').html(listeners + ' current listener'+ (listeners === 1 ? '':'s') );
                $('.nerdystats').html('Nerd stats:' + samplerate + ' kHz @ ' + bitrate + ' kbps');
                $('.uptime').html('Stream uptime: '+ secondsTimeSpanToHMS(streamuptime));

            }else{

               lastfm(artist,title); // query lastFM for correct artist/title and metadata

                $('.artist-name').html(artist.trim());
                $('.song-title').html(title.trim());
                $('.nowplaying-title').html('Now Playing');
                $('.listeners').html(listeners + ' current listener'+ (listeners === 1 ? '':'s') );
                $('.jp-title').html( servertitle );
                $('.jp-motd').html(motd);
                $('.nerdystats').html('Nerd stats:' + samplerate + ' kHz @ ' + bitrate + ' kbps');
                $('.uptime').html('Stream uptime: '+ secondsTimeSpanToHMS(streamuptime));

                history();

            }

            // no stream, just throw the maintenance item
        }else{

            $('.nowplaying-title').html('Please check back later. Maintenance time!');
            $('.artist-name').html('');
            $('.song-title').html('');
            $('.song-duration').html('');
            $('.song-album-yr').html('');
            $('.thumb-container').html('<img src="img/no_image.png">');

        }
    }); // $.getJSON



}

/**
 * Functions for workload begin here
 */

function history(){
    $.getJSON('history.php' , function(list){
        var output = '<h3>Whats Been Played</h3>';
        $.each(list, function(idx, val){
            output += '<div class="listing">'+ val + '</div>';
        });
        $('#history').html(output);
    });
}


function millisToMinutesAndSeconds(millis) {
    var minutes = Math.floor(millis / 60000);
    var seconds = ((millis % 60000) / 1000).toFixed(0);
    return minutes + ":" + (seconds < 10 ? '0' : '') + seconds;
}


function callback(results){

    if(results){
        var album       = results.track.album;
        var track       = results.track.name;
        var mbid        = results.track.album.mbid;
        var duration    = results.track.duration > 0 ? millisToMinutesAndSeconds(results.track.duration): null;

        if(duration) $('.song-duration').html('duration: ' + duration); // duration of track XX:XX

        if(album.image[2]['#text']){
            $('.thumb-container').html('<img src="'+ album.image[2]['#text'] + '" id="'+ album.image[2].size +'">'); // thumbnail of LP cover
        }else{
            //console.log('No image data from LastFM');
            $('.thumb-container').html('<img src="img/no_image.png">'); // no_image
        }

        // be sure we have mbid before calling musicbrainz for data
       if(results.track.album.hasOwnProperty('mbid')){
           $.getJSON('musicbrainz.php',{mbid:mbid},function(release){
               if(release.first_release_date) $('.song-album-yr').html(album.title +' (' + release.first_release_date + ')');
           });
       }
   }else{
       $('.song-duration').html('');
       $('.song-album-yr').html('');
       $('.thumb-container').html('<img src="img/no_image.png">');
   }
}


function lastfm(a,t){

    $.getJSON('scrobbler.php', {
        track: t,
        artist: a
    }).done(function(results){

            console.log(results);

            callback(results);

        }).fail(function( ) {

           failed(a,t);

            callback(null);

    });

}

function failed(a,t){
    $.post( "mongo/update.php", { artist: a, title: t})
        .done(function( data ) {
            console.log( "stored as fail : " + data );
        });
}

//]]>