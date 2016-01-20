#!/usr/bin/perl

# pour insérer des mails de fonction dans la branche établissement du ldap
# prend en entrée un fichier.ldif de la liste des dn et uai des étabs à traiter.
# et donne en sortie le code ldif des ajouts à faire.

# Exemple de requête pour récupérer le fichier d'entrée.
#ldapsearch -L -H  ldap://corbeau.giprecia.net:389 -x -D "cn=admin,ou=administrateurs,dc=esco-centre,dc=fr" -W -b "ou=structures,dc=esco-centre,dc=fr" -s sub -z 0 "(&(objectClass=ENTEtablissement)(ENTStructureUAI=*)(ENTEtablissementMinistereTutelle=MINISTERE DE L'EDUCATION NATIONALE))" "ENTStructureUAI" > allEtab.ldif
# ./insertMailFonction.pl allEtab.ldif > addMail.ldif
#ldapadd  -H  ldap://corbeau.giprecia.net:389 -x -W -D 'cn=admin,ou=administrateurs,dc=esco-centre,dc=fr' -f addMail.ldif -c


#mettre le cn
#$cn="CHEF ETABLISSEMENT";
$cn="DOCUMENTATION";
#mettre le format de création de mail %s sera remplacé par l'uai
#$fmail = 'ce.%s@ac-orleans-tours.fr';
$fmail = 'cdi.%s@ac-orleans-tours.fr';

while (<>) {
		if (/dn: (ENTStructureSIREN=(\d+),ou=structures,dc=esco-centre,dc=fr)/) {
			$dnEtab=$1;
			$siren = $2;			
			#	print $siren."\n";
		#	unless ($siren =~ m/^1937\d+0013$/) {
		#		$siren = '';
		#	}
		} elsif (/ENTStructureUAI: (\w+)/) {
			if ($siren) {
				$uai=lc $1;
				$mail= sprintf($fmail, $uai);
				write;
				$siren = '';
			} 
			
		} else {
			if (/^(version|\#)/){ #pour supprimer les lignes qui ne nous concerne pas
				print ;
			}
		}
}

format =
dn: cn=@*,@*
$cn,$dnEtab
changetype: add
objectClass: ESCOFunctionEmail
objectClass: top
mail: @*
$mail
cn: @*
$cn

.
