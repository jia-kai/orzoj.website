#!/bin/bash -e
list=`find . -name 'make*'`;
for file in $list 
do
	echo Excuting $file ...
	php $file
done
