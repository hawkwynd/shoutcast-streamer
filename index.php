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
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link href="css/sassy.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/stream.js"></script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-141850006-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'UA-141850006-1');
    </script>

    <script type="text/javascript">
        //<![CDATA[
        $(document).ready(function(){

              statistics(); // query server for stream data on page load.
                            
              
            // refresh the screen every 12 seconds to update the track/artist info
           

            setInterval(function(){
                var songId = $('.song-title').attr('id');
                statistics(songId); // send the current songId for comparison


            }, 12000); // 12 seconds calls to refresh
        });
    </script>
</head>
<body>

<div class="main-container">

    <div id="wb_MediaPlayer1">
            <div class="stream-details">
                <div class="app-title" aria-label="title">
                    <?php echo APPLICATION_NAME; ?>
                </div>
                <div class="app-motd" aria-label="motd"></div>
                <div class="nowplaying-title"><?php echo NOW_PLAYING_TXT; ?></div>
                <div class="nowplaying">

                    <div class="thumb-container"></div>
                    <div class="current-song-container">
                        <div class="artist-name"></div>
                        <div class="song-title"></div>
                        <div class="song-album"></div>
                        <div class="year-label"></div>
                    </div>
                    <div class="recording-list-container"></div>
                </div>
            </div>

                <audio src="<?php echo SHOUTCAST_HOST.'/;';?>" id="MediaPlayer1" controls="controls"></audio>

    </div>
    <div class="statistics">
            <div class="totalRecs"></div>
            <div class="nerdystats"></div>
            <div class="uptime"></div>
            <div class="listeners"></div>
    </div>

    <div class="content-container">
        <div id="artist-wiki"></div>
        <div id="release-wiki"></div>
               
    </div>


 
     <div class="wrap-collapsible-history"></div><!-- wrap-collapsible //-->
 


</div><!-- main-container -->



</body>
</html>
