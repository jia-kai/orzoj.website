#!/bin/bash -e
# $File: mkphp.sh
# $Date: Fri Jan 06 14:44:32 2012 +0800

if [ -z "$1" ]
then
	echo "usage: $0 <po file>"
fi

msgfmt -cvf $1 -o output.mo
php ../mo2php.php output.mo > system.php
rm -f output.mo

