SET NAMES 'latin1';

# MODELE 6
INSERT INTO model(id,modelname,listname,description,topics,subject,family,need_parameter,creation_date,end_date,pattern)
VALUES(6,'profs_classe','profs{CLASSE}','Tous les profs de la classe {CLASSE}','computing','Tous les profs de la classe {CLASSE}','profs_de_classe',true,now(),null,'[^:]:Etablissements:[^:]_{RNE}:[^:]+:Profs_([\\ -]|\\w+)');

# ASSOCIATION DU MODELE AUX REQUETES PREPAREES
INSERT INTO j_model_request(id_model,id_request,category) VALUES(6,1,"MANDATORY");
INSERT INTO j_model_request(id_model,id_request,category) VALUES(6,2,"UNCHECKED");

# ASSOCIATION DU MODELE AU GROUPES D'ABONNES
INSERT INTO model_subscribers(id,group_filter) VALUES(6,'esco:Etablissements:*_{RNE}:*:Profs_{CLASSE}');
