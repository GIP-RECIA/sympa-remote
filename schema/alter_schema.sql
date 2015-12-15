

-- ajout du data source et de la branche de recherche ldap
alter table prepared_request
ADD COLUMN data_source varchar(100) not null,
Add COLUMN ldap_suffix varchar(100) not null;

-- modification de la vue ?

create or replace VIEW v_model_editors 
AS SELECT 	modelname, 
prepared_request.id_request, 
display_name, 
ldapfilter,
category,
data_source,
ldap_suffix
FROM model, prepared_request,j_model_request 
WHERE j_model_request.id_request=prepared_request.id_request 
AND model.id=j_model_request.id_model 
ORDER BY modelname;


-- on complete les donnée déjà existantes.
update prepared_request set data_source = 'ldap_request', ldap_suffix='ou=people,dc=esco-centre,dc=fr';

