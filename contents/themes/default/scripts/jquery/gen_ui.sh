#!/bin/bash -e
list_ui='core widget mouse button draggable position resizable dialog tabs'
list_effects='core fade scale'

rm ui.js

for i in $list_ui
do
	cat "orig/ui/development-bundle/ui/minified/jquery.ui.$i.min.js" >> ui.js
done

for i in $list_effects
do
	cat "orig/ui/development-bundle/ui/minified/jquery.effects.$i.min.js" >> ui.js
done

ls -alh ui.js
