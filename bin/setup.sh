#!/bin/sh

cd $PWD


if [ "$1" ]
then
	APACHE_GRP=$1
else
	echo "usage: tools/setup.sh <apache_group>"
fi

chgrp $APACHE_GRP cache
chmod g+w cache
touch log/dase.log
chgrp $APACHE_GRP log/dase.log 
chmod g+w log/dase.log 


