#!/bin/sh
now=$(date +'%Y-%m-%d_%H-%M-%S')
backupfolder="data/dev-$now"
echo "*** Dumping local.instafluence-dev to $backupfolder..."
mongodump -d instafluence-dev -o $backupfolder/ > /dev/null
prodfolder="data/prod-$now"
echo "*** Dumping local.instafluence to $prodfolder..."
mongodump -d instafluence -o $prodfolder/ > /dev/null
echo "*** Migrating data from dev to production..."
mongo instafluence --eval "db.dropDatabase()" > /dev/null
mongorestore --drop -d instafluence $backupfolder/instafluence-dev/ > /dev/null
echo "*** Finished!"
