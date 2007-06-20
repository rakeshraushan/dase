#!/bin/bash

for file in `ls *.log`
do	echo 
	echo "$file:"
	tail $file
done

