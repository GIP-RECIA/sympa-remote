#!/usr/bin/perl -w

# This script build a new sympa robot.
# It make etc and list_data folders.
# Add the alias in the postfix aliases file.
# Write de robot.conf file.

#example
#./createRobot.pl 0180592w.list.chercan.fr


# Configuration
$sympa_home = "/home/sympa";

$aliases_file = "$sympa_home/etc/sympa_aliases";

$sympa_user = "sympa";
#$listmaster = "pierre.legay\@recia.fr, maxime.bossard\@recia.fr";
$newAliases = "/usr/sbin/postalias $aliases_file";

# Direcotry creation
sub createDir {
	$dir = $_[0];

	if (-d $dir) { 
		print "$dir already exists !\n";
		return 0;
	} else { 
		$creation =  system("mkdir $dir") == 0;
		if (!$creation) {
			print "mkdir $dir failed !\n";
			return 0;
		}
		system("chown $sympa_user:$sympa_user $dir");
	}
	
	return 1;
}

# Add a unique line in a file (test unicity with regexp)
sub addUniqueLineInFile {
	$line = $_[0];
	$file = $_[1];
	$regexp = $_[2];

	if (-f $file) {
		# Verify if line not already there
		open (FILE, "+< $file") || die("Could not open $file !");
		while ($l = <FILE>) {
			chomp($l);
			if ($l =~ m/$regexp/i) {
				close (FILE);
				return 0;
			}
		}
		
		# Write line
		print FILE "$line\n";
		close(FILE); 
	} else {
		print "$file doesn't exists !\n";
	}
	
	return 1;
}

#$user = $ENV{USER};
#$isRoot = $> == 0;
#if ($isRoot || $user ne $sympa_user) {
#	print "\nUsage: you have to run this script with the user $sympa_user !\n";
#	exit;
#}

# (1) quit unless we have the correct number of command-line args
$num_args = $#ARGV + 1;
if ($num_args != 1) {
  	print "\nUsage: createRobot.pl robotName\n";
    	exit;
}

$argument = lc($ARGV[0]);
$robotName = "$argument";
print "\nCreating sympa robot [$robotName] ...\n";

$listDataDir = "$sympa_home/list_data/$robotName";
$robotConfDir = "$sympa_home/etc/$robotName";

# (2) creating list data directory
$listDataDirCreation = createDir($listDataDir);
if ($listDataDirCreation) {
	print "- list_data directory created.\n";	
}


# (3) creating robot conf directory
$robotConfDirCreation = createDir($robotConfDir);
if ($robotConfDirCreation) {
	print "- config directory created.\n";
}

# (4) adding robot domain in aliases file
$alias = "$robotName-sympa:\t\t\"| $sympa_home/bin/queue sympa\@$robotName\"";
$regexp = "^$robotName.*";
$aliasAdded = addUniqueLineInFile($alias, $aliases_file, $regexp);
if ($aliasAdded) {
	print "- alias added in $aliases_file.\n";
} else {
	print "* alias already in $aliases_file.\n";
}

system($newAliases);


# (5) creating robot conf file
$robotConfFile = "$robotConfDir/robot.conf";

if (! -f $robotConfFile) {
	open (ROBOT_CONF, "> $robotConfFile") || die("Could not open $robotConfFile !");
	#$robotConfFileCreation = print ROBOT_CONF "listmaster \t$listmaster\n";
	$robotConfFileCreation = print ROBOT_CONF "host    \t$robotName\n";
	$robotConfFileCreation &&= print ROBOT_CONF "http_host \t$robotName\n";
	$robotConfFileCreation &&= print ROBOT_CONF "wwsympa_url \thttps://$robotName/sympa\n";
	$robotConfFileCreation &&= print ROBOT_CONF "soap_url \thttps://$robotName/sympasoap\n";
	$robotConfFileCreation &&= print ROBOT_CONF "email   \t$sympa_user\n";
	$robotConfFileCreation &&= close (ROBOT_CONF);

	if ($robotConfFileCreation) {
		system("chown $sympa_user:$sympa_user $robotConfFile");
		print "- robot config file created.\n";
	} else {
		print "* robot config file not complete !\n";
	}	
} else {
	die("* robot config file already exists.\n");
}

print "\nSympa robot [$robotName] created.\n";
