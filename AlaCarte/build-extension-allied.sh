#!/bin/bash
cd /Users/jim/Documents/Local\ Projects/Alacarte/ff-ext-sdk
#zip -r ../alacart.xpi *
#wget --post-file=../alacart.xpi http://localhost:8888/
#while true; do 
	cfx xpi
	wget --post-file=alacarte-allied.xpi http://127.0.0.1:8881/
#	sleep 5
#done