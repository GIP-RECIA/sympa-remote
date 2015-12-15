#!/usr/bin/perl

# pseudo sympa.pl qui ne fait rien; utilisé pour les tests sans craindre la generation de liste:
# permet de visualiser les arguments dans /tmp/sympa.out, ainsi que le contenue du fichier tmp passer en paramatre.
# le fichier tmp est supprimer 

print "\nlist has been modified.\n";

print "\nerr admin::install_aliases() admin::install_aliases : Aliases installed successfully\n";
 @date = localtime time;

open LOG ,  ">>/tmp/sympa.out";
print LOG   sprintf "\n%.2d/%.2d/%.4d %.2dh%.2d:%.2d", $date[3], $date[4]+1 ,$date[5]+1900, $date[2], $date[1], $date[0];

print LOG "\nARGV=@ARGV\n" or print "$! \n";


$nb = 0;
foreach $arg (@ARGV) {
	$nb++;
	if ($arg =~ /input_file/) {
			$filename = $ARGV[$nb];
			last;
	}
}
if (-f $filename) {
		open IN, "$filename";
		while (<IN>) {
				print LOG;
		}
		unlink $filename; 
}


