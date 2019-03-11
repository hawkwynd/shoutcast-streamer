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
               // $('.nowplaying-title').html(motd);
                $('.thumb-container').html('<img src="img/no_image.png">');
                $('.listeners').html(listeners + ' current listener'+ (listeners === 1 ? '':'s') );
                $('.nerdystats').html('Nerd stats:' + samplerate + ' kHz @ ' + bitrate + ' kbps');
                $('.uptime').html('Stream uptime: '+ secondsTimeSpanToHMS(streamuptime));

            }else{

               console.log( 'Asking for: ' + artist + ' : ' + title );

               lastfm(artist,title); // query lastFM for correct artist/title and metadata

                $('.artist-name').html(artist.trim());
                $('.song-title').html(title.trim());
                $('.nowplaying-title').html('Now Playing');
                $('.listeners').html(listeners + ' current listener'+ (listeners === 1 ? '':'s') );
                $('.jp-title').html( servertitle );
            //    $('.jp-motd').html(motd);
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
            $('.summary').html().css("padding", 0);
            $('.members').html('');

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

      var currImage = $('.thumb-container img').attr('src'); // get the current image, if exists

    if(results){

        var album       = results.album.title;
        var image       = results.album.image == '' ? 'img/no_image.png' : results.album.image;
        var track       = results.track.name;
        var mbid        = results.album.mbid;
        var duration    = results.track.duration > 0 ? millisToMinutesAndSeconds(results.track.duration): null;
        var release     = results.album.releaseDate;
        var label       = results.album.label == null ? '' : results.album.label;
        var members     = results.artist.members;
        var summary     = results.artist.summary.length > 2 ? results.artist.summary:'';


        if(duration) $('.song-duration').html('Duration: ' + duration); // duration of track XX:XX

        // if we have an image, and the current image is undefined, or different than the image
        // update with the image.
        if(currImage != image) {
            $('.thumb-container').html('<img src="'+ image + '">'); // update the image with the image we got
        }

        // Greatest Hits (1995) A&M Records
        $('.song-album-yr').html(album +' (' + release + ') ' + label );
        $('.summary').html(summary);

        // here we will kick out the members of the band.
        if(members.members.length > 0){
            var mdata       = '';
            var gbegin =    members.group_begin;
            var gend   =    members.group_end == '' ? '' : ' - ' + members.group_end;

            mdata = "<div class='memberHeader'>" + members.group_name + " (" + gbegin + gend + "): </div>";

            $.each(members.members, function(idx, obj){
                var begin = obj.begin;                            // 1955
                var end   = obj.end == '' ? '' : ' - ' + obj.end; // 1955-1999 or 1955
                mdata += "<div class='memberline'>" + obj.member_name + ": " + begin + end + " " + obj.instruments + "</div>";
                return idx < 8; // first 8 only of the array
            });
            $('.members').html(mdata);

        }else{
            $('.members').html('');
        }


   }else{
        // no data from lastfm, wipe the dataset
        $('.song-duration').html('');
        $('.song-album-yr').html('');
        $('.thumb-container').html('<img src="img/no_image.png">'); // thumbnail of LP cover
        $('.summary').html('').css("padding", 0);
        $('.members').html('');
   }
}

function lastfm(a,t){

    $.getJSON('lookup.php', {
        track: t,
        artist: a
    }).done(function(results){

        //   console.log('artistid: ' + results.artist.mbid);
        //   console.log('releaseid: '+ results.album.mbid);
        //   console.log('trackid: ' + results.track.mbid);

            callback(results);

        }).fail(function() {
                callback(null);
                failed(a,t);
    });
}

function failed(a,t){
    $.post( "mongo/update.php", { artist: a, title: t})
        .done(function( data ) {
           // console.log('lastfm_fail updated: ' + a + ' : ' + t);
            //console.log($.parseJSON( data ));
        });
}

//]]>