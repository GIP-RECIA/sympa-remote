#!/usr/bin/perl -w

# This script delete an existing sympa robot.
# It remove etc and list_data folders.
# Remove the alias in the postfix aliases file.


# Configuration
$sympa_home = "/home/sympa";
#$sympa_home = "/home/max/sources/sympaRobots";
$aliases_file = "$sympa_home/etc/sympa_aliases";
#$aliases_file = "/home/max/sources/sympaRobots/aliases";

$newAliases = "/usr/sbin/postalias $aliases_file";

# Direcotry deletion
sub deleteDir {
	$dir = $_[0];

	if (! -d $dir) { 
		print "$dir doesn't exist !\n";
		return 0;
	} else { 
		$deletion =  system("rm -rf $dir") == 0;
		if (!$deletion) {
			print "rm -rf $dir failed !\n";
			return 0;
		}
	}
	
	return 1;
}

# Del a unique line in a file (test with regexp)
sub delUniqueLineInFile {
	$file = $_[0];
	$regexp = $_[1];

	if (-f $file) {
		# Verify if line not already there
		open(FILE, "< $file") || die("Could not open $file !");
		my @contents = <FILE>;
		close(FILE);

		@newContents = grep !/$regexp/, @contents;

		return 0 if ($#newContents == $#contents);

		open(FILE, "> $file") || die("Could not open $file !");
                print FILE @newContents;
                close(FILE);
	} else {
		print "$file doesn't exists !\n";
		return 0;
	}
	
	return 1;
}

#----------------------------(  promptUser  )-----------------------------#
#                                                                         #
#  FUNCTION:	promptUser                                                #
#                                                                         #
#  PURPOSE:	Prompt the user for some type of input, and return the    #
#		input back to the calling program.                        #
#                                                                         #
#  ARGS:	$promptString - what you want to prompt the user with     #
#		$defaultValue - (optional) a default value for the prompt #
#                                                                         #
#-------------------------------------------------------------------------#

sub promptUser {

   #-------------------------------------------------------------------#
   #  two possible input arguments - $promptString, and $defaultValue  #
   #  make the input arguments local variables.                        #
   #-------------------------------------------------------------------#

   local($promptString,$defaultValue) = @_;

   #-------------------------------------------------------------------#
   #  if there is a default value, use the first print statement; if   #
   #  no default is provided, print the second string.                 #
   #-------------------------------------------------------------------#

   if ($defaultValue) {
      print $promptString, "[", $defaultValue, "]: ";
   } else {
      print $promptString, ": ";
   }

   $| = 1;               # force a flush after our print
   $_ = <STDIN>;         # get the input from STDIN (presumably the keyboard)


   #------------------------------------------------------------------#
   # remove the newline character from the end of the input the user  #
   # gave us.                                                         #
   #------------------------------------------------------------------#

   chomp;

   #-----------------------------------------------------------------#
   #  if we had a $default value, and the user gave us input, then   #
   #  return the input; if we had a default, and they gave us no     #
   #  no input, return the $defaultValue.                            #
   #                                                                 # 
   #  if we did not have a default value, then just return whatever  #
   #  the user gave us.  if they just hit the <enter> key,           #
   #  the calling routine will have to deal with that.               #
   #-----------------------------------------------------------------#

   if ("$defaultValue") {
      return $_ ? $_ : $defaultValue;    # return $_ if it has a value
   } else {
      return $_;
   }
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

$prompt = promptUser("Are you sur you want to delete de robot [$robotName] (y/n) ? ", "n");
if ($prompt ne "y") {
	exit;
}

print "\nDeleting sympa robot [$robotName] ...\n";

$listDataDir = "$sympa_home/list_data/$robotName";
$robotConfDir = "$sympa_home/etc/$robotName";

# (2) deleting list data directory
$listDataDirDeletion = deleteDir($listDataDir);
if ($listDataDirDeletion) {
	print "- list_data directory deleted.\n";	
}


# (3) deleting robot conf directory
$robotConfDirDeletion = deleteDir($robotConfDir);
if ($robotConfDirDeletion) {
	print "- config directory deleted.\n";
}

# (4) deleting robot domain in aliases file
$regexp = "^$robotName.*";
$aliasDeleted = delUniqueLineInFile($aliases_file, $regexp);
if ($aliasDeleted) {
	print "- alias deleted in $aliases_file.\n";
} else {
	print "* alias not found in $aliases_file.\n";
}

system($newAliases);

if (!$listDataDirDeletion && !$robotConfDirDeletion && !$aliasDeleted) {
	print "Sympa robot [$robotName] wasn't deleted !\n";
	exit;
}

print "\nSympa robot [$robotName] deleted.\n";
