SET NAMES 'latin1';

# MODELE 4
INSERT INTO model(id,modelname,listname,description,topics,subject,family,need_parameter,creation_date,end_date,pattern)
VALUES(4,'eleves_classe','eleves{CLASSE}','Elèves de la classe {CLASSE}','computing','Les élèves de la classe {CLASSE}','eleves_classe_etab',true,now(),null,'[^:]:Etablissements:[^:]_{RNE}:[^:]+:Eleves_([\\ -]|\\w+)');

# ASSOCIATION DU MODELE AUX REQUETES PREPAREES
INSERT INTO j_model_request(id_model,id_request,category) VALUES(4,1,"MANDATORY");
INSERT INTO j_model_request(id_model,id_request,category) VALUES(4,2,"CHECKED");

# ASSOCIATION DU MODELE AU GROUPES D'ABONNES
INSERT INTO model_subscribers(id,group_filter) VALUES(4,'esco:Etablissements:*_{RNE}:*:Eleves_{CLASSE}');
