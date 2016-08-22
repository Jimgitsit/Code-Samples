#!/bin/sh
now=$(date +'%Y-%m-%d_%H-%M-%S')
backupfolder="data/local-$now"
echo "*** Backing up local.instafluence-dev to $backupfolder..."
mongodump -d instafluence-dev -o $backupfolder/ > /dev/null
devfolder="data/dev-$now"
echo "*** Dumping dev.instafluence-dev to $devfolder..."
mongodump -h dev.instafluence.com -d instafluence-dev -o $devfolder/ > /dev/null
echo "*** Migrating data from dev to local..."
mongo instafluence-dev --eval "db.dropDatabase()" > /dev/null
mongorestore --drop -d instafluence-dev $devfolder/instafluence-dev/ > /dev/null
echo "*** Finished!"
