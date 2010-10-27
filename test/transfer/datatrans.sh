#!/bin/bash -e
cd /home/jiakai/programming/orzoj/server-judge/test-server/data
while read file t
do
	echo "$t..."
	[ -e $t ] && rm -rf $t

	7z x $file > /dev/null
	mv data $t
	cd $t

	echo '<orzoj-prob-conf version="1.0">
	<verifier standard="1"></verifier> ' > probconf.xml

	for cfg in `ls *.cfg | sort -n`
	do
		id=`echo $cfg | sed -e 's/\.cfg//g'`

		echo `cat $cfg` > tmp
		while [ 1 ]
		do
			read cfg_time cfg_mem cfg_score
			break
		done < tmp
		rm tmp

		cfg_time=$(( $cfg_time * 1000 ))
		cfg_mem=$(( $cfg_mem * 1024 ))

		echo "
		<case>
			<input>$t-$id.in</input>
			<output>$t-$id.out</output>
			<time>$cfg_time</time>
			<mem>$cfg_mem</mem>
			<score>$cfg_score</score>
		</case>
		" >> probconf.xml

		rm $cfg
		mv $id.in $t-$id.in
		mv $id.out $t-$id.out
	done

	echo '</orzoj-prob-conf>' >> probconf.xml

	cd ..

	echo "done"
done 
