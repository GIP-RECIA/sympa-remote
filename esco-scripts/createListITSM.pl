#!/usr/bin/perl

# script de creation des listes itsm
# permet la creation d'un fichier createList.xml
# ldapsearch  -h ldap-sympa.giprecia.net -p 389  -W -D 'cn=sympa,ou=administrateurs,dc=esco-centre,dc=fr' -b 'ou=groups,dc=esco-centre,dc=fr' '(cn=*ITSM*)' cn

#ldapsearch -LLL -h ldap-sympa.giprecia.net -x -D "cn=sympa,ou=administrateurs,dc=esco-centre,dc=fr" -W -b "ou=groups,dc=esco-centre,dc=fr" -s sub -a always -z 1000 "(cn=*ITSM*)" "cv" "objectClass"

#ldapsearch  -h ldap-sympa.giprecia.net -p 389  -W -D 'cn=sympa,ou=administrateurs,dc=esco-centre,dc=fr' -b 'ou=people,dc=esco-centre,dc=fr' '(ismemberof=cfa:admin:ITSM:*user:CEMEA Centre \28Tours\29_0371715N)'  mail

#ldapsearch  -h ldap-sympa.giprecia.net -p 389  -W -D 'cn=sympa,ou=administrateurs,dc=esco-centre,dc=fr' -b 'ou=people,dc=esco-centre,dc=fr' '(ismemberof=cfa:admin:ITSM:*user:CEMEA Centre*0371715N)'  mail

#dans les requete ldap il faut remplacer les () par \28\29
use Net::LDAP;


$ldap = Net::LDAP->new( 'ldap-sympa.giprecia.net' ) or die "$@";


$mesg = $ldap->bind("cn=sympa,ou=administrateurs,dc=esco-centre,dc=fr", password => 'F9vCShURYXAr') ; 

# recherche des groupes ITSM dans le ldap (dans ldap les groupes ne peuvent pas être vide).
$mesg = $ldap->search( # perform a search
                       base   => "ou=groups,dc=esco-centre,dc=fr",
                       filter => "(cn=*admin:ITSM*)",
                       attrs => ['cn']
                     );
              

#les branches par tab
%branche;

#les noms par étab
%noms;

#l'ensemble des branches concernées.
%branchSet;

$recia = '@recia.fr';
$group_gsi='coll:Collectivites:GIP-RECIA:PERSONNELS:GSI';

#pour chaque groupes ITSM non vide du ldap.
foreach $entry ($mesg->entries) {	
	$cn=$entry->get_value('cn');
	
	next if $cn =~ /FICTIF/;
	# print STDERR "> $cn", "\n";
	
	# on recupere la branche (esco, clg.. ), le nom et l'uai
	if ($cn =~/^(.*):admin:ITSM:[^:]+user:([^:]+)\_(\d{7}.)$/) {
		$noms{"$3"} = $2;
		$branche{"$3"} = $1 ;
		$branchSet{$1} = 1;
		# print STDERR "> $1" ,"\t$2" , "\t$3\n";
	}
}

print '<?xml version="1.0" ?>', "\n";
print '<family>',"\n";

$cpt=0;
#creation des listes par étab
$~ ="ETAB";
while (($uai, $nom) = each(%noms) ) {
#	write LIST ;
	
	$nomSansParenthese = $nom;
	
	if ($nom =~ m/\(/) {
		$nomSansParenthese =~ s/\(/\\28/;
		$nomSansParenthese =~ s/\)/\\29/;
	}	
	$group_filter = "$branche{$uai}:admin:ITSM:*user:${nomSansParenthese}_$uai";
	# print STDERR "$uai $group_filter \n";
	write  ;
	$cpt++;
}




#creation des listes de branche
# le noms par branches
%nomBranch = (
	'esco' => 'LEN'
	);
%textBranch = (
	'esco' => 'LEN',
	'agri'=> "établissement de l'enseignement agricole"
	);

$~ ="BRANCH";
foreach $branch (keys %branchSet) {
	unless ($nom = $nomBranch{$branch}) {
		$nom = $branch;
	}
	unless ($text = $textBranch{$branch}) {
		$text = uc($branch);
	}
	
	$group_filter = "$branch:admin:ITSM:*user:*";
	write  ;
}

$~ = "ALL";
write;

print STDERR "> $cpt\n" ;

print '</family>',"\n";

print STDERR "> sympa.pl --instantiate_family ITSM --robot list-ent.recia.fr --input_file createListITSM.xml";
format ETAB  =
<list>
<listname>itsm_@*</listname>
$uai
<subject>Liste d'information du GIP vers les Correspondant Support Locaux de l'établissement: @* (@*)</subject>
$nom, $uai
<description>Liste d'information du GIP vers les Correspondant Support Locaux de l'établissement: @* (@*)</description>
$nom, $uai
<language>fr</language>
<owner multiple="1">
	<email>chloe.fonck@*</email>
$recia
</owner>
<owner multiple="1">
	<email>nicolas.lebrun@*</email>
$recia
</owner>
<owner multiple="1">
	<email>xavier.le-ho@*</email>
$recia
</owner>
<editors_from_group multiple="1">
	<groupname>@*</groupname>
$group_gsi
</editors_from_group>


<subscribers_group>@*</subscribers_group>
$group_filter

</list>
.

format  BRANCH =
<list>
<listname>itsm_@*</listname>
$nom
<subject>Liste d'information du GIP vers les Correspondant Support Locaux des @*</subject>
$text
<description>Liste d'information du GIP vers les Correspondant Support Locaux des @*</description>
$text
<language>fr</language>
<owner multiple="1">
	<email>chloe.fonck@*</email>
$recia
</owner>
<owner multiple="1">
	<email>nicolas.lebrun@*</email>
$recia
</owner>
<owner multiple="1">
	<email>xavier.le-ho@*</email>
$recia
</owner>
<editors_from_group multiple="1">
	<groupname>@*</groupname>
$group_gsi
</editors_from_group>


<subscribers_group>@*</subscribers_group>
$group_filter

</list>
.

format ALL = 
<list>
<listname>itsm_all</listname>
<subject>Liste d'information du GIP vers les Correspondant Support Locaux de tous les &#xE9;tablissements</subject>
<description>Liste d'information du GIP vers les Correspondant Support Locaux de tous les &#xE9;tablissements</description>
<language>fr</language>
<owner multiple="1">
	<email>chloe.fonck@*</email>
$recia
</owner>
<owner multiple="1">
	<email>nicolas.lebrun@*</email>
$recia
</owner>
<owner multiple="1">
	<email>xavier.le-ho@*</email>
$recia
</owner>
<editors_from_group multiple="1">
	<groupname>@*</groupname>
$group_gsi
</editors_from_group>
<subscribers_group>*:admin:ITSM:*user:*</subscribers_group>

</list>
.


