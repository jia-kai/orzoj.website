#!/bin/bash
# $File: search-src.sh
# $Date: Fri Jan 06 21:37:09 2012 +0800
# $Author: jiakai <jia.kai66@gmail.com>

grep . -R --exclude-dir=contents/lang --exclude-dir=contents/editors \
	--exclude-dir=contents/highlighters --exclude-dir=contents/themes/default/scripts \
	--exclude-dir=.git -I "$@"


