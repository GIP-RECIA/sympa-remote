SET NAMES 'latin1';

# MODELE 5
INSERT INTO model(id,modelname,listname,description,topics,subject,family,need_parameter,creation_date,end_date,pattern)
VALUES(5,'parents_classe','parents{CLASSE}','Tous les parents d\'élèves de la classe {CLASSE}','computing','Tous les parents d\'élèves de la classe {CLASSE}','parents_de_classe',true,now(),null,'[^:]:Etablissements:[^:]_{RNE}:[^:]+:Parents_([\\ -]|\\w+)');

# ASSOCIATION DU MODELE AUX REQUETES PREPAREES
INSERT INTO j_model_request(id_model,id_request,category) VALUES(5,1,"MANDATORY");
INSERT INTO j_model_request(id_model,id_request,category) VALUES(5,2,"UNCHECKED");

# ASSOCIATION DU MODELE AU GROUPES D'ABONNES
INSERT INTO model_subscribers(id,group_filter) VALUES(5,'esco:Etablissements:*_{RNE}:Groupes_Parents:Parents_{CLASSE}');
