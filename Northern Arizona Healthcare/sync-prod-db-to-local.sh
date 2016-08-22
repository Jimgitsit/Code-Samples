#!/bin/sh -e

echo Syncing production db to local...

# Remove the previous local file
rm -f qpp/backups/prod-sync.sql

# Establish a temporary tunnel to the prod server
ssh -4 -f -o ExitOnForwardFailure=yes -L 3310:localhost:3306 jim@10.219.0.131 sleep 10

# Dump the prod database to a local file
mysqldump -P 3310 -h 127.0.0.1 -u root -pmysqlr00t --add-drop-database --databases nahds > app/backups/prod-sync.sql

# Restore the local file to the local database
mysql -u root -pmysqlr00t nahds < app/backups/prod-sync.sql

# Update any schema changes with doctrine
php vendor/bin/doctrine orm:schema-tool:update --force

echo Done.
