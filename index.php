<?php
require_once('include/config.inc.php');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title><?php echo APPLICATION_NAME; ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <link href="css/style.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/stream.js"></script>
    <script type="text/javascript">
        //<![CDATA[
        $(document).ready(function(){
            statistics(); // query server for stream data on page load.
            // refresh the screen every 10 seconds to update the track/artist info
            setInterval(function(){
               statistics();
            }, 10000); // 10 seconds calls to refresh
        });
    </script>
</head>
<body>

<div id="jp_container_1" class="jp-audio-stream" role="application" aria-label="media player">
        <div class="jp-details">
            <div class="jp-title" aria-label="title"><?php echo APPLICATION_NAME; ?></div>
            <div class="jp-motd" aria-label="motd"></div>
            <div class="nowplaying-title"><?php echo NOW_PLAYING_TXT; ?></div>
            <div class="nowplaying">
                <div class="thumb-container"></div>
                  <div class="current-song-container">
                    <div class="artist-name"></div>
                    <div class="song-title"></div>
                    <div class="song-album-yr"></div>
                    <div class="song-duration"></div>
                  </div>
            </div>
        </div>

    <div class="jp-type-single">
        <div id="wb_MediaPlayer1">
            <audio src="<?php echo SHOUTCAST_HOST.'/;';?>" id="MediaPlayer1" controls="controls"></audio>
        </div>
    </div>

    <div class="summary"></div>
    <div class="members"></div>

    <div class="statistics">
        <div class="nerdystats"></div>
        <div class="uptime"></div>
        <div class="listeners"></div>
    </div>


<div id="socialLinks" style="text-align: center;padding: 6px;">
        <div class="fb-share-button" data-href="<?php echo SITE_URL;?>" data-layout="button" data-size="large" data-mobile-iframe="true"><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fstream.hawkwynd.com%2F&amp;src=sdkpreparse" class="fb-xfbml-parse-ignore">Share</a></div>

    <div class="chatLink"><a href="chat/" target="_blank">Got a request?</a></div>

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
