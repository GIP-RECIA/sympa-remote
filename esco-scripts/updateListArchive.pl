#!/usr/bin/perl -i.bak

#script pour changer la valeur de archive.max_month dans le fichier de conf des listes.
# le 1er argument doit etre la valeur de max_month en mois
# le deuxieme le fichier config de la liste  avec le path complet (comprenant le nom du robot
# genre recia.list.netocentre.fr/itsm_len/config

#exemple d'utilisation:
# cd list_data; find *.fr -name config -exec ~/esco-scripts/updateListArchive.pl 48 \{\} \;

$val = shift;
$ok = 0; # si on a modifié le fichier 
$tt2 = 0 ; # si true on n'a pas un fichier de conf de liste mais an template (de fammille par exemple)
$file = $ARGV[0];
print $file, ":\n";
if ($file =~ m/([^\/]+.fr)\/([^\/]+)\/config$/) {
	$robot = $1;
	$list = $2;
} elsif ($file =~ m/(^|\/)config.tt2$/) {
	$tt2 = 1;
} else {
	if ($file) {
		die "erreur de fichier , doit être du type 'robot/list/config' ou '.../config.tt2'\n";
	}
	die "il doit manquer un argument: $0 nbMois files\n";
}
while (<>) {
	print ;
	if (m/^archive$/) {
		while (<>) {
			if (/max_month/){
				print "max_month $val\n";
				$ok = 1;
				last;
			}; 
			if (/^\s*$/) {
				print "max_month $val\n\n";
				$ok = 1;
				last;
			}
			print;
		}
		last;
	} 
}
while (<>) {
	print;
}
if ($ok && !$tt2) {
	$commande = "sympa.pl --reload_list_config --list=$list@$robot";
	print $commande , "\n";
	system $commande && die $!;
}
