#!/bin/sh

ASSET_DIR=/home/pkeane/samples
DASE=http://littlehat.com/dase
COLL=test
USER=pkeane
PASS=xxx

for file in `find $ASSET_DIR -name '*'`
do
	echo 
	echo "posting $file to $COLL"
	mime=`file -bi $file`
	curl --data-binary @$file -X post -H "Content-type: $mime" -u $USER:$PASS $DASE/media/$COLL
done
