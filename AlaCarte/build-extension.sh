#!/bin/bash
cd /Users/jim/Documents/Local\ Projects/Alacarte/ff-ext-sdk
#zip -r ../alacart-allied.xpi *
#wget --post-file=../alacart-allied.xpi http://localhost:8888/
#while true; do 
	cfx xpi
	wget --post-file=alacarte.xpi http://127.0.0.1:8871/
#	sleep 5
#done