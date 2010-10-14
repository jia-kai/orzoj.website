#!/bin/sh
mv messages.pot ../../old.pot -f
cd ../../
xgettext `find . -iname '*.php'` --from-code=UTF-8 --language=PHP --keyword=__ -o messages.pot
msgmerge old.pot messages.pot -o messages.pot
rm old.pot
mv messages.pot contents/lang/
