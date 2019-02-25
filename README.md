# Shoutcast Stream Project

## Introduction
I wanted to be able to deliver audio from my home computer running a playlist of tracks and stream it to my website so I could listen to it
from anywhere as long as I had an internet connection from my device (phone/tablet/pc). Furthermore, I wanted the ability to provide live audio
from my mixer source where I have a microphone, and broadcast the stream as a live broadcast.

The plan was to configure my software [Mixxx](https://www.mixxx.org/) so I could cue up tracks, hit play and stream it so I could leave, and listen to the tracks from my phone.

I expanded this plan to allow me to provide a live stream through the use of [Darkice](http://manpages.ubuntu.com/manpages/trusty/man1/darkice.1.html) on a second computer which simply could be as small as a Raspberry Pi machine.

You can see this application at [http://stream.hawkwynd.com](http://stream.hawkwynd.com)

## Addition information

`index.php` -- contains a section in the `<audio>` tag, which you will need to point to your shoutcast server
```
 <div id="wb_MediaPlayer1">
            <audio src="http://54.158.47.252:8000/;" id="MediaPlayer1" controls="controls"></audio>
        </div>
```
You will need to set the `src` to the domain or ip address of your shoutcast server.

