# Shoutcast Stream Front-End

# sc_serv configuration

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
