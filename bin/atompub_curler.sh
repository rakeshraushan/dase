#!/bin/sh

coll=archivision

cd $PWD

for file in `ls atoms/`
do
echo 
echo "posting $file to collection $coll"
echo

PASSWD=$1

curl -d @atoms/$file -X post -H 'Content-type: application/atom+xml;type=entry' -u pkeane:$PASSWD http://quickdraw.laits.utexas.edu/dase1/collection/$coll
done
