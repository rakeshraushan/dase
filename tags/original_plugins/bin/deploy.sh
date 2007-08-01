#!/bin/sh

WORKING=$PWD
PUBLIC_BETA=/var/www/html/dase

echo "copying $WORKING/* to $PUBLIC_BETA"
rsync -ar --delete --exclude='.svn' -e /bin/ssh $WORKING/* $PUBLIC_BETA
rsync -ar  -e /bin/ssh $WORKING/prod_htaccess $PUBLIC_BETA/.htaccess

echo "dase hase been updated!"

