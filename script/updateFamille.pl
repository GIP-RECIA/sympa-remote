#!/usr/bin/perl -p -i.back

	s/source ldap_request/source [% er.source %]/;
	s/source_parameters \[% er.request %\]/source_parameters [% er.request %] [% er.suffix %]/;
	

