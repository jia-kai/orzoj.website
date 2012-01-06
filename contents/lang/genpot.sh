#!/bin/bash -e
# $File: genpot.sh
# $Date: Fri Jan 06 14:26:19 2012 +0800

(cd ../.. ; find . -name "*.php" | xgettext -f - --from-code=UTF-8 --language=PHP --keyword=__ -o -) | \
	msgmerge messages.pot - -o messages.pot
