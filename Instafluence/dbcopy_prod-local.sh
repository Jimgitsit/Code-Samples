#!/bin/sh
now=$(date +'%Y-%m-%d_%H-%M-%S')
backupfolder="data/local-$now"
echo "*** Backing up local.instafluence-dev to $backupfolder..."
mongodump -d instafluence-dev -o $backupfolder/ > /dev/null
prodfolder="data/prod-$now"
echo "*** Dumping prod.instafluence to $prodfolder..."
mongodump -h dev.instafluence.com -d instafluence -o $prodfolder/ > /dev/null
echo "*** Migrating data from prod.instafluence to local.instafluence-dev..."
mongo instafluence-dev --eval "db.dropDatabase()" > /dev/null
mongorestore --drop -d instafluence-dev $prodfolder/instafluence/ > /dev/null
echo "*** Finished!"
