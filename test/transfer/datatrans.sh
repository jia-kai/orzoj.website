#!/bin/bash -e
cd /srv/orzoj-new-server-judge/test-server/data
while read file t
do
	echo "$t..."

	[ -e $t ] && rm -rf $t

	7z x $file > /dev/null
	mv data $t
	cd $t

	echo '<orzoj-prob-conf version="1.0">
	<verifier standard="1" /> ' > probconf.xml

	for cfg in `ls *.cfg | sort -n`
	do
		id=`echo $cfg | sed -e 's/\.cfg//g'`

		if [ ! -e $id.in ]
		then
			continue
		fi

		echo `cat $cfg` > tmp
		while [ 1 ]
		do
			read cfg_time cfg_mem cfg_score
			break
		done < tmp
		rm tmp

		cfg_time=`echo $cfg_time | sed -e 's/\\r//g'`
		cfg_mem=`echo $cfg_mem | sed -e 's/\\r//g'`
		cfg_score=`echo $cfg_score | sed -e 's/\\r//g'`

		cfg_time=$(( $cfg_time * 1000 ))
		cfg_mem=$(( $cfg_mem * 1024 ))

		echo "
		<case input=\"$t-$id.in\" output=\"$t-$id.out\" time=\"$cfg_time\" mem=\"$cfg_mem\" score=\"$cfg_score\" />
		" >> probconf.xml

		rm $cfg
		mv $id.in $t-$id.in
		mv $id.out $t-$id.out
	done

	echo '</orzoj-prob-conf>' >> probconf.xml

	cd ..

	echo "done"
done 
