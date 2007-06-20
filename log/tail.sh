#!/bin/bash

for file in `ls *.log`
do	echo 
	echo "truncating $file"
	tail $file > $file.new
	mv $file.new $file
	chmod g+w $file
done

