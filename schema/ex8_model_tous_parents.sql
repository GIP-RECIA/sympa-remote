SET NAMES 'latin1';

# MODELE 3
INSERT INTO model(id,modelname,listname,description,topics,subject,family,need_parameter,creation_date,end_date,pattern)
VALUES(8,'tous_parents','parents','Tous les parents de l\'établissement','computing','Tous les parents de l\'établissement','tous_les_parents_etab',false,now(),null,'[^:]:Etablissements:[^:]_{RNE}:Parents');

# ASSOCIATION DU MODELE AUX REQUETES PREPAREES
INSERT INTO j_model_request(id_model,id_request,category) VALUES(8,1,"MANDATORY");
INSERT INTO j_model_request(id_model,id_request,category) VALUES(8,2,"UNCHECKED");

# ASSOCIATION DU MODELE AU GROUPES D'ABONNES
INSERT INTO model_subscribers(id,group_filter) VALUES(8,'esco:Etablissements:*_{RNE}:Parents');
