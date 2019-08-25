
var flag = true;
var loop = 1;
var songId = null;

// seconds converter 
function secondsTimeSpanToHMS(s) {
    var h = Math.floor(s/3600); //Get whole hours
    s -= h*3600;
    var m = Math.floor(s/60); //Get remaining minutes
    s -= m*60;
    return h+":"+(m < 10 ? '0'+m : m)+":"+(s < 10 ? '0'+s : s); //zero padding on minutes and seconds
}

function statistics(songId){
       
        // Get data from our shoutcast server 
        
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

        // still alive?
        if(streamstatus > 0 ){

            // Check if no artist/title data came, but we have a streamstatus
            var listeners = data.currentlisteners;

            lastfm(artist, title, songId); // query lastFM for correct artist/title and metadata
            
            if(!songId){
                console.log('Render artist name');
                $('.artist-name').html(artist.trim());
                console.log('Render song title');
                $('.song-title').html(title.trim());
                $('.nowplaying-title').html('Now Playing');
                $('.jp-title').html( servertitle );
                $('.recording-list-container').html('');
            }

            
            // console.log('Render current listeners: ' + listeners);
            $('.listeners').html(listeners + ' listener'+ (listeners === 1 ? '':'s') );            
            $('.nerdystats').html(samplerate + ' kHz @ ' + bitrate + ' kbps');
            $('.uptime').html('Uptime: '+ secondsTimeSpanToHMS(streamuptime));

        // no stream, just throw the maintenance item

        }else{

            $('.nowplaying-title').html('Offline for Maintenance.');
            $('.artist-name').html('');
            $('.song-title').html('');
            $('.song-album').html('');
            $('.year-label').html('');
            $('.thumb-container').html('<img src="img/no_image.png">');
            $('.summary-container').html('').css("padding", 0);
            $('.members-container').html('');
            $('.recording-list-container').html('');

        }
    }); // $.getJSON
}

/**
 * Functions for workload begin here
 */

function history(){
    $.getJSON('history.php' , function(list){
        var output = '';
        var listing = '';

        $.each(list, function(idx, val){
            listing += '<div class="listing">' + val + '</div>';
        });

        $('.wrap-collapsible-history').html(
            '<input id="collapsible" class="toggle" type="checkbox">' +
            '<label for="collapsible" class="lbl-toggle">Play History</label>'+
            '<div class="collapsible-content">'+
              '<div class="content-inner">' +
            listing +
            '</div>' +
            '</div>'
        );
    });
}

// no longer used in rendering for now.
function millisToMinutesAndSeconds(millis) {
    var minutes = Math.floor(millis / 60000);
    var seconds = ((millis % 60000) / 1000).toFixed(0);
    return minutes + ":" + (seconds < 10 ? '0' : '') + seconds;
}

// callback function renders page with results
function callback(results, songId){
    
    

    if(results){
        
        // load vars from results
        var arid        = results.artist.mbid;
        var album       = results.album.title;        
        var image       = results.album.image == null ? 'img/no_image.png' : results.album.image;      
        var tid         = results.track.mbid;
        var relid       = results.album.mbid;
        var d           = new Date(results.album.releaseDate);
        var release     = d.getFullYear();
        var AExtract    = '';
        var label       = results.album.label == null ? '' : results.album.label;
        var totalRecs   = results.totalRecs;

        // Clear artist and release summary containers
        $('#release-wiki').html(''); // release wiki
        $('#artist-wiki').html(''); // artist extract   

        // render release summary
        if(results.album.hasOwnProperty('wikiExtract') && results.album.wikiExtract !=null ){       
            
            var AExtract = '<div class="wrap-collapsible members-container">' +
            '<input type="checkbox" class="toggle" id="members">' +
            '<label id="lbl-members" for="members" class="lbl-toggle">About '+results.album.title + '</label>' +
            '<div class="collapsible-content">' +
            '<div class="content-inner">';                
            var AExtractCloser = '</div></div></div>';
            var extract_array = results.album.wikiExtract.split('. ');
            var extract_trunc = extract_array.slice(0, 3).join('. ');
            AExtract +=  extract_trunc + AExtractCloser;
            
            console.log('Render release-wiki');
            $('#release-wiki').html(AExtract); // add release container info
            
        }
        // Render artist summary
        if(results.artist.hasOwnProperty('summary') && results.artist.summary !=null ){

            var summaryContent = '<div class="wrap-collapsible summary-container">' +
            '<input type="checkbox" class="toggle" id="summary">' +
            '<label id="lbl-summary" for="summary" class="lbl-toggle">About ' + results.artist.name +'</label>' +
            '<div class="collapsible-content"><div class="content-inner">';                
            var summaryContentCloser = '</div></div></div>';
            var summary_array = results.artist.summary.split('. ');
            var summary_trunc = summary_array.slice(0, 3).join('. ') + '.';           
            var summary = results.artist.summary.length > 2 ? summaryContent + '<div class="summary">'+ summary_trunc + '</div>' + summaryContentCloser :'';       
            
            console.log('Render artist-wiki');
            $('#artist-wiki').html( summary ); // artist extract   
    
        }

        // set id to containers and render content
         console.log('Render artistId:' + arid);
         $('.artist-name').prop('id', arid);
         console.log('Render songId: ' + tid);
         $('.song-title').prop('id', tid);
         console.log('Render releaseId ' + relid);
         $('.song-album').prop('id', relid);

        //  do recording-list
        $('.recording-list-container').html('');

         $.getJSON('browseRecordings.php', { // call internal search for artist/track
            releaseId: relid
        }).done(function(results){ 
            
            console.log('Render track listing');

            $('.recording-list-container').html('<div class="header">Track List</div><ol></ol>');
            $.each(results, function(idx, track){
             $('.recording-list-container ol').prepend('<li>' + track.title + ' ' + millisToMinutesAndSeconds(track.length) + '</li>');
           });
           
        });

         console.log('Render release title: ' + album);
         $('.song-album').html(album);
         console.log('Render label: ' + release + ' ' + label);
         $('.year-label').html('(' + release + ') ' + label );              
         console.log('Render coverImage') ;
         $('.thumb-container').html('<img src="'+ image + '">'); // update the image with the image we got
        
         //  update history listing
         console.log('Render History');     
         history();  

         console.log('Render count');
         $('.totalRecs').html('Titles: ' +totalRecs);

   }else{

        console.log('No results from callback.');

        // no data from lastfm, wipe the dataset
        $('.song-duration').html('');
        $('.song-album').html('');
        $('.year-label').html('');
        $('.thumb-container').html('<img src="img/no_image.png">'); // thumbnail of LP cover
        $('.content-container').html('').css("padding", 0);
        $('.article').html('');
        $('.recording-list-container').html('');

        console.log('No internal match found.');
        
        
   }
}

function singleArrayRemove(array, value){
    var index = array.indexOf(value);
    if (index > -1) array.splice(index, 1);
    return array;
  }

// search mongoDB for match on artist and title.
// return json data if a match is found
function lastfm(a, t, songId){

    $.getJSON('lookup.php', { // call internal search for artist/track
        track: t,
        artist: a,
        test: true

    }).done(function(results){ // mongo successful find in lastfm collection

        if (results.hasOwnProperty('artist') ){
        
            // if our songId doesn't match the returned recording.id
            // then we know it's a new song, and do a render to update
            // the page with the new information, otherwise move on.

            if(results.track.mbid !== songId){
                
                console.clear(); // clear console so we dont get a long train of data

                $('.song-title').html(t);
                $('.artist-name').html(a);
                console.log('Rendering page results.track.mbid:' + results.track.mbid);
                callback(results, songId); // process success results  
            }
            

        }else{
                   

            // Wipe the page renders
            // console.clear();
            $('.song-title').html(t);
            $('.artist-name').html(a);
            
            $('.song-duration').html('');
            $('.song-album').html('');
            $('.year-label').html('');
            $('.thumb-container').html('<img src="img/no_image.png">'); // thumbnail of LP cover
            $('.summary-container').html('').css("padding", 0);
            $('.members-container').html('');
            $('.article').html('');
            
            // do a first search
            console.log('MusicbrainzSearchFirst :' + a + ', ' + t);
            $('.recording-list-container').html('');
            musicbrainzSearchFirst(a,t);


        }

        }).fail(function() { // trigger musicbrainzSearchFirst

                callback(null);
                failed(a ,t );           

    });

}

function failed(a, t){
    console.log( 'Triggering musicbrainzSearchFirst for ' + a + ' : ' + t);  
    musicbrainzSearchFirst(a, t);  

}

// search musicbrainz api for song
function musicbrainzSearchFirst(a , t, flag){
     $.post( "firstrecording.php", { artist: a, title: t})      
      .done(function( data ) {            

           console.log('Got results from musicbrainz.org.');
           console.log('I searched artist:' + a + ' title:' + t);
           console.log( $.parseJSON(data) );
             

      });
       
}

//]]>