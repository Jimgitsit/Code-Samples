#!/usr/bin/env bash

ssh -L 27018:localhost:27017 jim@dev.alacarte.bluetree.ws '
    echo "Connected on Remote End, sleeping for 10"; 
    sleep 10; 
    exit' &
echo "Waiting 10 sec on local";
sleep 10;
echo "Connecting to Mongo and piping in script";
cat dbcopy-dev-local.js | mongo