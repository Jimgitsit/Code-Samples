#!/bin/sh
now=$(date +'%Y-%m-%d_%H-%M-%S')
backupfolder="data/dev-$now"
echo "*** Dumping dev.instafluence-dev to $backupfolder..."
mongodump -h dev.instafluence.com -d instafluence-dev -o $backupfolder/ > /dev/null
localfolder="data/local-$now"
echo "*** Dumping local.instafluence-dev to $localfolder..."
mongodump -d instafluence-dev -o $localfolder/ > /dev/null
echo "*** Migrating data from local to dev..."
mongo --host dev.instafluence.com instafluence-dev --eval "db.dropDatabase()" > /dev/null
mongorestore --drop -h dev.instafluence.com -d instafluence-dev $localfolder/instafluence-dev/ > /dev/null
echo "*** Finished!"
