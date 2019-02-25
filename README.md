# Shoutcast Stream Project

## Introduction
I wanted to be able to deliver audio from my home computer running a playlist of tracks and stream it to my website so I could listen to it
from anywhere as long as I had an internet connection from my device (phone/tablet/pc). Furthermore, I wanted the ability to provide live audio
from my mixer source where I have a microphone, and broadcast the stream as a live broadcast.

The plan was to configure my software [Mixxx](https://www.mixxx.org/) so I could cue up tracks, hit play and stream it so I could leave, and listen to the tracks from my phone.

I expanded this plan to allow me to provide a live stream through the use of [Darkice](http://manpages.ubuntu.com/manpages/trusty/man1/darkice.1.html) on a second computer which simply could be as small as a Raspberry Pi machine.



## sc_serv configuration


Login to your server and install sc_serv on your machine:

```
wget http://download.nullsoft.com/shoutcast/tools/sc_serv2_linux_x64-latest.tar.gz

mkdir shoutcast
cd shoutcast/
tar -xvzf ~/sc_serv2_linux_x64-latest.tar.gz
```

Edit your config file
`nano sc_serv.conf`

Basic settings for sc_serv.conf

```
adminpassword=whateverYourPasswordYouWant
password=whateverYourPasswordYouWant
requirestreamconfigs=0
streamadminpassword_1=whateverYourPasswordYouWant
streamid_1=1
streampassword_1=whateverYourPasswordYouWant
streampath_1=stream
shoutcastsourcedebug=1
logfile=logs/sc_serv_2.log
w3clog=logs/sc_w3c.log
banfile=control/sc_serv.ban
ripfile=control/sc_serv.rip
streamauthhash_1=whateverYourHashisForShoutCastDirectory

```
Save your settings, and start the sc_serv:
```
./sc_serv &
```
Or use screen and run it as well.

To verify your server is running, browse to the host with port 8000
```
http://example.com:8000/admin.cgi
```
Login with admin/password you created in sc_serv.conf and you'll be able to see your stream server admin management page.

# Broadcasting to your shoutcast server

Here's going to be the setup info for configuring stuff on a seperate host, website.

