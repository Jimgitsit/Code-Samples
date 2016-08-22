#!/bin/bash
network=$1
type=$2
echo "Network :" $network
echo "Type :" $type

now=$(date +'%Y-%m-%d_%H-%M-%S')
backupfolder="data/dev-$now"

echo "Backing up dev.instafluence-dev to $backupfolder..."
mongodump -d instafluence-dev -o $backupfolder/ > /dev/null

echo "Starting aggregation. Log is application/logs/aggregatioin_log.txt"
if [ "$type" != "" ]; then
	url="public/index.php aggregate/$network/$type"
else
	url="public/index.php aggregate/$network"
fi
echo "Calling: $url"
php $url >> application/logs/aggregation_log.txt

echo "Finished."

