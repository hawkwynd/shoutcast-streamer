# Shoutcast Stream Project

## Introduction
I wanted to be able to deliver audio from my home computer running a playlist of tracks and stream it to my website so I could listen to it
from anywhere as long as I had an internet connection from my device (phone/tablet/pc). Furthermore, I wanted the ability to provide live audio
from my mixer source where I have a microphone, and broadcast the stream as a live broadcast.

The plan was to configure my software [Mixxx](https://www.mixxx.org/) so I could cue up tracks, hit play and stream it so I could leave, and listen to the tracks from my phone.

I expanded this plan to allow me to provide a live stream through the use of [Darkice](http://manpages.ubuntu.com/manpages/trusty/man1/darkice.1.html) on a second computer which simply could be as small as a Raspberry Pi machine.

You can see this application at [http://stream.hawkwynd.com](http://stream.hawkwynd.com)

## Additional requirements

### LastFM Api Key
You must register for an api key from lastFM for this application to work.

[Create API Account with lastFM](https://www.last.fm/api/account/create)

### Update `include/config.inc.php` with your settings

You will need to edit `include/config.inc.php` file to configure your settings:

```define('SHOUTCAST_HOST', 'http://##.###.###.####:8000');             // url:port to your shoutcast server
   define('SHOUTCAST_ADMIN_PASS', 'password_here');                     // admin password for accessing admin.cgi
   define('SCROBBLER_API', 'lastFMApIKeyHere');                         // API key from lastfm to query data
   define('APPLICATION_NAME', 'My Streaming Radio Station Name');       // Name of your website's application
   define('NOW_PLAYING_TXT', 'Now Playing');                            // Content to display as Now Playing
   define('SITE_URL', 'http://example.com');                            // used in FB share link

```

