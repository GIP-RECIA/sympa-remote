SET NAMES 'latin1';

CREATE TABLE IF NOT EXISTS model
(id integer not null auto_increment,
modelname varchar(30) not null unique,
listname varchar(50) not null,
description varchar(200),
topics varchar(50),
subject varchar(200),
family varchar(50) not null,
need_parameter boolean not null,
creation_date date not null,
end_date date,
pattern varchar(250),
PRIMARY KEY (id))
DEFAULT CHARACTER SET latin1;

CREATE TABLE IF NOT EXISTS j_model_request
(id_model integer not null,
id_request integer not null,
category ENUM("MANDATORY", "CHECKED", "UNCHECKED") not null,
PRIMARY KEY (id_model,id_request))
DEFAULT CHARACTER SET latin1;

CREATE TABLE IF NOT EXISTS prepared_request
(id_request integer not null auto_increment,
display_name varchar(100) not null,
ldapfilter varchar(250) not null,
PRIMARY KEY (id_request))
DEFAULT CHARACTER SET latin1;

CREATE TABLE IF NOT EXISTS model_subscribers
(id integer auto_increment,
group_filter varchar(250),
PRIMARY KEY (id,group_filter))
DEFAULT CHARACTER SET latin1;

CREATE VIEW v_model_editors AS
SELECT modelname, prepared_request.id_request, display_name, ldapfilter,category
FROM model, prepared_request,j_model_request
WHERE j_model_request.id_request=prepared_request.id_request
AND model.id=j_model_request.id_model
ORDER BY modelname;
