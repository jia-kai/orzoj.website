#!/bin/bash
# $File: update.sh
# $Date: Sat Jan 07 11:36:20 2012 +0800
# $Author: jiakai <jia.kai66@gmail.com>

cp js-dir/jquery.js .
for i in js-dir/*
do
	[ "$i" == "js-dir/jquery.js" ] && continue
	cat $i >> jquery.js
done

cat css-dir/* > styles.css

