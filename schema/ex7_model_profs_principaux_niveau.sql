SET NAMES 'latin1';

# MODELE 6
INSERT INTO model(id,modelname,listname,description,topics,subject,family,need_parameter,creation_date,end_date,pattern)
VALUES(7,'profs_princ_niveau','profs_principaux{NIVEAU}','Tous les profs principaux de {NIVEAU}','computing','Tous les profs principaux de {NIVEAU}','profs_princ_de_niveau',true,now(),null,'');

# ASSOCIATION DU MODELE AUX REQUETES PREPAREES
INSERT INTO j_model_request(id_model,id_request,category) VALUES(7,1,"MANDATORY");
INSERT INTO j_model_request(id_model,id_request,category) VALUES(7,2,"UNCHECKED");

# ASSOCIATION DU MODELE AU GROUPES D'ABONNES
INSERT INTO model_subscribers(id,group_filter) VALUES(7,'esco:Etablissements:*_{RNE}:*:Profs_Principaux_{NIVEAU}');
