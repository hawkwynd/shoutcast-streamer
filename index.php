<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Hawkwynd Radio</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <link href="jPlayer/dist/skin/blue.monday/css/jplayer.blue.monday.css" rel="stylesheet" type="text/css" />
    <link href="css/style.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="jPlayer/lib/jquery.min.js"></script>
    <!--
    <script type="text/javascript" src="jPlayer/dist/jplayer/jquery.jplayer.min.js"></script>
    <script type="text/javascript" src="js/lastfm/lastfm.api.md5.js"></script>
    <script type="text/javascript" src="js/lastfm/lastfm.api.js"></script>
     -->
    <script type="text/javascript" src="js/stream.js"></script>
    <script type="text/javascript">
        //<![CDATA[
        $(document).ready(function(){

            statistics(); // query server for stream data

            setInterval(function(){
               statistics(); // query for data from server
            }, 10000); // 10 seconds calls to refresh
        });
    </script>
</head>
<body>


<div id="jquery_jplayer_1" class="jp-jplayer"></div>
<div id="jp_container_1" class="jp-audio-stream" role="application" aria-label="media player">
        <div class="jp-details">
            <div class="jp-title" aria-label="title">Hawkwynd Radio</div>
            <div class="jp-motd" aria-label="motd"></div>
            <div class="nowplaying-title">Now Playing</div>
            <div class="nowplaying">
                <div class="thumb-container"></div>
                <div class="artist-name"></div>
                <div class="song-title"></div>
                <div class="song-album-yr"></div>
                <div class="song-duration"></div>
                <div class="genre"></div>
            </div>
        </div>

    <div class="jp-type-single">
        <div id="wb_MediaPlayer1">
            <audio src="http://54.158.47.252:8000/;" id="MediaPlayer1" controls="controls"></audio>
        </div>
    </div>
    <div class="extract"></div>
    <div class="statistics">
        <div class="nerdystats"></div>
        <div class="uptime"></div>
        <div class="listeners"></div>
    </div>


<div id="socialLinks" style="text-align: center;padding: 6px;">
        <div class="fb-share-button" data-href="http://stream.hawkwynd.com" data-layout="button" data-size="large" data-mobile-iframe="true"><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fstream.hawkwynd.com%2F&amp;src=sdkpreparse" class="fb-xfbml-parse-ignore">Share</a></div>

    <div class="chatLink"><a href="chat/" target="_blank">Got a request?</a></div>

</div>
        <div class="jp-no-solution">
            <span>Update Required</span>
            To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
        </div>
    </div>
</div>

<div id="history"></div>
<div id="fb-root"></div>
<script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2&appId=161144281083138&autoLogAppEvents=1';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>


</body>

</html>
