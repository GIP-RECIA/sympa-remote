#!/usr/bin/perl -p -i.back

# script de remplacement des groupes du type
# esco:admin:local:admin_FICTIF_0450822X
#par :
# esco:admin:Listes_Diffusion:local:FICTIF_0450822X

if ($inowner and /^source_parameters\s+([^:]+):admin:local:admin_(.*)$/) {
	$_ = "source_parameters $1:admin:Listes_Diffusion:local:$2\n";		
} 

if (/^owner_include/) {
			$inowner= 1;
}

if (/^\s*$/) {
		$inowner=0;
}

