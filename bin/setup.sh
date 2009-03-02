#!/bin/sh

cd $PWD


if [ "$1" ]
then
	APACHE_GRP=$1
else
	echo "usage: tools/setup.sh <apache_group>"
fi

chgrp $APACHE_GRP files/cache
chmod g+w cache
chgrp $APACHE_GRP files/log
chmod g+w log
chgrp $APACHE_GRP files/media
chmod g+w media


