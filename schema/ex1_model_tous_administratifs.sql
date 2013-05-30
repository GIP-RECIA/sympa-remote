SET NAMES 'latin1';

# MODELE 1
INSERT INTO model(id,modelname,listname,description,topics,subject,family,need_parameter,creation_date,end_date,pattern)
VALUES(1,'tous_administratifs','administratifs','Tous les personnels administratifs de l\'établissement','computing','Tous les personnels administratifs de l\'établissement','tous_les_administratifs_etab',false,now(),null,'[^:]:Etablissements:[^:]_{RNE}:Administratifs:Tous_Administratifs');

# ASSOCIATION DU MODELE AUX REQUETES PREPAREES
INSERT INTO j_model_request(id_model,id_request,category) VALUES(1,1,"MANDATORY");
INSERT INTO j_model_request(id_model,id_request,category) VALUES(1,2,"UNCHECKED");

# ASSOCIATION DU MODELE AU GROUPES D'ABONNES
INSERT INTO model_subscribers(id,group_filter) VALUES(1,'esco:Etablissements:*_{RNE}:Administratifs:Tous_Administratifs');
