#!/bin/sh

mysqldump --compact --compatible=ansi --default-character-set=binary -u pkeane -p dase |
grep -v '  KEY "' |
grep -v '  UNIQUE KEY "' |
perl -e 'local $/;$_=<>;s/,\n\)/\n\)/gs;print "begin;\n";print;print "commit;\n"' |
perl -pe '
if (/^(INSERT.+?)\(/) {
	$a=$1;
	s/\\'\''/'\'\''/g;
	s/\\n/\n/g;
	s/\),\(/\);\n$a\(/g;
}
' | sed 's/auto_increment//g' | sqlite3 output.db



