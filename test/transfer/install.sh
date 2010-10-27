#!/bin/sh -e
pushd .
cd ..
files='make_option.php make_avatar.php make_plang_wlang.php make_team.php make_user_grp.php'

for i in $files
do
	echo $i
	php $i
done

popd

files=`ls *.php | sort`

for i in $files
do
	if [[ `echo $i | grep '^[0-9][0-9]_.*\.php'` == $i ]]
	then
		echo $i
		php $i
	fi
done

