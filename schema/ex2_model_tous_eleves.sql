SET NAMES 'latin1';

# MODELE 2
INSERT INTO model(id,modelname,listname,description,topics,subject,family,need_parameter,creation_date,end_date,pattern)
VALUES(2,'tous_eleves','eleves','Tous les élèves de l\'établissement','computing','Tous les élèves de l\'établissement','tous_les_eleves_etab',false,now(),null,'[^:]:Etablissements:[^:]_{RNE}:Eleves');

# ASSOCIATION DU MODELE AUX REQUETES PREPAREES
INSERT INTO j_model_request(id_model,id_request,category) VALUES(2,1,"MANDATORY");
INSERT INTO j_model_request(id_model,id_request,category) VALUES(2,2,"UNCHECKED");

# ASSOCIATION DU MODELE AU GROUPES D'ABONNES
INSERT INTO model_subscribers(id,group_filter) VALUES(2,'esco:Etablissements:*_{RNE}:Elèves');
