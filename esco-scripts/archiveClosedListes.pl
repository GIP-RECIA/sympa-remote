#!/usr/bin/perl

# le requette pour avoir les listes closes : select name_list, robot_list  from list_table where status_list ='closed'
#	eleves.2020.03.06.17.18.19      0450822x.list.netocentre.fr

# mysql -h condor -u sympa_c_g -p -D sympa_coucou_grue < closeList.sql > closeList.list
# ou pour un etablissement:
# mysql -h condor -u sympa_c_g -p -D sympa_coucou_grue < closeListEtab.sql | archiveClosedListes.pl


@date = localtime(time); 

$a= $date[5]+1900 ;
$M = $date[4]+1;
$j = $date[3];
$h = $date[2];
$m = $date[1];
$s = $date[0];


$date = sprintf(".%.4i.%.2i.%.2i.%.2i.%.2i.%.2i" , $a, $M, $j, $h, $m, $s);
$dateCourte = sprintf(".%.4i.%.2i.%.2i", $a, $M, $j);
while (<>) {
	if (/^((\w|[-_])+)\s+(.+\.fr)$/) {
		$nom=$1;
		$robot=$3;
		if ($nom =~ /(\.\d{2}){2}$/) {
			print STDERR "$nom@$robot déjà archivée \n";
		} else {
			if (length($nom) > 30) {
				$newName= substr($nom, 0, 39) . $dateCourte;
			} else {
				$newName = $nom . $date;
			}
			$arg =  "/usr/local/sbin/sympa.pl --rename_list=$nom\@$robot --new_listname=$newName --new_listrobot=$robot";
			system $arg;
		}
	} else {
		print STDERR "Liste non reconnue: $_";
	}
}
