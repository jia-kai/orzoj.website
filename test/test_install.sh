#!/bin/bash -e
for file in make*.php
do
	echo php $file ...
	php $file
done
