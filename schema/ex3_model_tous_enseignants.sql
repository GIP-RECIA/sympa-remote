SET NAMES 'latin1';

# MODELE 3
INSERT INTO model(id,modelname,listname,description,topics,subject,family,need_parameter,creation_date,end_date,pattern)
VALUES(3,'tous_enseignants','enseignants','Tous les enseignants de l\'établissement','computing','Tous les enseignants de l\'établissement','tous_les_enseignants_etab',false,now(),null,'[^:]:Etablissements:[^:]_{RNE}:Profs');

# ASSOCIATION DU MODELE AUX REQUETES PREPAREES
INSERT INTO j_model_request(id_model,id_request,category) VALUES(3,1,"MANDATORY");
INSERT INTO j_model_request(id_model,id_request,category) VALUES(3,2,"UNCHECKED");

# ASSOCIATION DU MODELE AU GROUPES D'ABONNES
INSERT INTO model_subscribers(id,group_filter) VALUES(3,'esco:Etablissements:*_{RNE}:Profs');
