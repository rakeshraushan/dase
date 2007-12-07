#!/bin/sh

for file in `cat routes.xml | grep action | sed 's/.*action="\([^"]*\)".*/\1/g'` 
do
	ls -al ../actions/$file.php | grep 'no such'
done
