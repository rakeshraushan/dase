#!/bin/sh

cd $PWD


if [ "$1" ]
then
	APACHE_GRP=$1
else
	echo "usage: tools/setup.sh <apache_group>"
	exit
fi

chgrp $APACHE_GRP files/cache
chmod g+w files/cache
chgrp $APACHE_GRP files/log
chmod g+w files/log
chgrp $APACHE_GRP files/media
chmod g+w files/media


