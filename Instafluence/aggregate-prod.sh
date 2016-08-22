#!/bin/bash
network=$1
type=$2
echo "Network :" $network
echo "Type :" $type

now=$(date +'%Y-%m-%d_%H-%M-%S')
backupfolder="data/prod-$now"

echo "Backing up prod.instafluence to $backupfolder..."
mongodump -d instafluence -o $backupfolder/ > /dev/null

echo "Starting aggregation. Log is application/logs/aggregatioin_log.txt"
if [ "$type" != "" ]; then
        url="public/index.php aggregate/$network/$type"
else
        url="public/index.php aggregate/$network"
fi
echo "Calling: $url"
php $url >> application/logs/aggregation_log.txt

echo "Finished."

