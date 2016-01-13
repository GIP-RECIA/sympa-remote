
#exemple d'ajout de prepared request
# ne pas oublier la transaction
 

begin;

insert into prepared_request values (5, 'Chef d''établissement' , '(cn=CHEF ETABLISSEMENT)', 'ldap_mailFunction_request', 'ENTStructureSIREN={SIREN},ou=structures,dc=esco-centre,dc=fr');

insert into j_model_request values (1, 5, 'UNCHECKED');
insert into j_model_request values (2, 5, 'UNCHECKED');
insert into j_model_request values (3, 5, 'UNCHECKED');
insert into j_model_request values (4, 5, 'UNCHECKED');
insert into j_model_request values (5, 5, 'UNCHECKED');
insert into j_model_request values (6, 5, 'UNCHECKED');
insert into j_model_request values (7, 5, 'UNCHECKED');
insert into j_model_request values (8, 5, 'UNCHECKED');
insert into j_model_request values (9, 5, 'UNCHECKED');
insert into j_model_request values (10, 5, 'UNCHECKED');
insert into j_model_request values (11, 5, 'UNCHECKED');
insert into j_model_request values (12, 5, 'UNCHECKED');

commit;

delete from 
