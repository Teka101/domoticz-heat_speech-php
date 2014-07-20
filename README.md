domoticz-heat_speech-php
========================

Add functionnality to Domoticz : control heating system and speech recognition for control

CONFIGURATION
-------------
Edit file 'config.php'

INSTALLATION
------------
Add line to crontab:
	0 * * * * /usr/bin/wget -O /dev/null 'http://localhost/domoticz-heat_speech-php/update.php' >/dev/null 2>/dev/null
