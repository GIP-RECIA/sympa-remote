#!/usr/bin/perl -i.bak

while (<>) {
	if (m/ENTAuxEnsGroupes=ENTStructureSIREN=[^,]+,ou=structures,dc=esco-centre,dc=fr\$([^)]+)/) {
		$grp = $1;
			#print $_;
			s/ENTAuxEnsGroupes/ENTAuxEnsGroupesMatieres/;
			s/\$$grp/\$$grp\$*/;
		#print $_;
	}
	print $_;
}

__END__
exemple d'utilisation 

find list_data/ -name config -exec grep -q ENTAuxEnsGroupes \{\} \; -print | xargs ./updateListConf.pl 

et de resultat:
diff list_data/0450822x.list.netocentre.fr/groupe-profs503_gr_a/config*
60c60
< filter (ENTAuxEnsGroupesMatieres=ENTStructureSIREN=00000000000001,ou=structures,dc=esco-centre,dc=fr$503_GR_A$*)
---
> filter (ENTAuxEnsGroupes=ENTStructureSIREN=00000000000001,ou=structures,dc=esco-centre,dc=fr$503_GR_A)

