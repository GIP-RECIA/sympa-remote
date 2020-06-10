#!/usr/bin/perl

# script de correction de sources parameters contenant des ,
# il faut lui passer un fichier contenant sur la fin des  lignes le path des fichier config a modifier
# fichier issue de find  par exemple:
# find . -name config -exec egrep -e 'source_parameters.+ou=people,dc=esco-centre,dc=fr' \{\} \; -ls > /home/sympa/sourceParameter.error
# pour tester commenter le rename de la fin de la boucle 

while (<>) {
	if (/\s\.(\/\S+\/config)$/) {
		$fichier="list_data$1";
		print $fichier,  "\n";
		$new = "${fichier}.new";
		$old = "${fichier}.old";
		open  INPUT , $fichier || die $!;
		open NEW , ">$new" || die $!;
		open OLD , ">$old" || die $!;
		while (<INPUT>) {
			print OLD;
			s/(^source_parameters.*)\ ?ou=people,dc=esco-centre,dc=fr/\1/;
			print NEW;
		}
		close INPUT;
		close NEW;
		close OLD;
		rename $new, $fichier;
	}
}

