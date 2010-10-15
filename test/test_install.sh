#!/bin/bash -e
list=`find . -name 'make*'`;
for file in $list 
do
	echo php $file ...
	php $file
done
