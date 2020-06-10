#!/usr/bin/perl

#script de mise a jour  des listes d'un ou plusieurs établisements
$listData = "list_data";


opendir LISTDATA, $listData or die $!;

$firstArg=$ARGV[0];

unless (@ARGV % 2) {
	die "nombre d'argument incorecte \n doit être de la forme : \n\tuai (oldval newval)+\n ou\tfilename\n"
}



if ($firstArg =~ /^(\d{7}\w)/) {
	&unEtab(@ARGV);
} else {
	# on a un fichier de donnée au format de ligne : uai; (oldval; newval)+
	unless (-s $firstArg) {
		die "fichier introuvable ou vide : $firstArg";
	}
	open INPUT, "$firstArg" or die "$!\n";
	
	while (<INPUT>) {
		chop;
		$_ =~ s/^\s+//;
		if ($_) {
			@LINE = split ('\s*;\s*', $_);
			if (@LINE > 1) {
				&unEtab(@LINE);
			} 
		}
	}
}

closedir LISTDATA;

sub unEtab () {
	$uai = lc(shift);
	
	if (@_ < 2 || @_ % 2) {
		print STDERR "$uai error nb argument incorrecte \n";
		return 0;
	}
	print "uai = $uai : ";
	rewinddir(LISTDATA);
	@ROBOT =  grep (/^$uai/, readdir(LISTDATA));

	if (@ROBOT != 1) {
		foreach $rep (@ROBOT) {
			print "\n$rep\n";
		}
		die "\nPlusieurs ou aucun robot trouvé \n";
	} 

	$robot = $ROBOT[0];

	opendir DIR, "$listData/$robot" or die "$!";

	$nbList = 0;
	while ($rep = readdir DIR) {
		$conf = "$listData/$robot/$rep/config";
		if (-s $conf) {
			$nbList++;
#			print "$conf : \n";
			open NEW, "> ${conf}.new" or next;
			open OLD , "$conf" or next;
			while (<OLD>) {
				for ($i = 0; $i < @_;) {
					$old = $_[$i++];
					$new = $_[$i++];
					$_ =~ s/$old/$new/g;
				}
				print NEW $_;
			}
			close OLD;
			close NEW;
			rename $conf, "${conf}.bak" or next;
			rename "${conf}.new", $conf;
			#print ("sympa.pl --reload_list_config --list=${rep}\@${robot}\n");
			system "sympa.pl --reload_list_config --list=${rep}\@${robot}";
		}
	}
	closedir DIR;
	print " #list = $nbList\n";
}



