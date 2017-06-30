/*
 2011-01-19 15:28
 version:0.2
*/
ALTER TABLE attribures RENAME COLUMN table_id TO model_id;

/*
 2011-01-19 15:29
 version:0.2
*/
ALTER TABLE links RENAME COLUMN action TO action_id;ALTER TABLE links ALTER COLUMN action_id TYPE INT4  USING CASE WHEN action_id IS NOT NULL THEN 1 ELSE 0 END;

/*
 2011-01-19 15:30
 version:0.2
*/
ALTER TABLE links RENAME COLUMN controller TO page_id;ALTER TABLE links ALTER COLUMN page_id TYPE INT4  USING CASE WHEN page_id IS NOT NULL THEN 1 ELSE 0 END;

/*
 2011-01-19 15:38
 version:0.2
*/
CREATE TABLE databases (
name VARCHAR(64)
, hostname VARCHAR(32)
, user_name VARCHAR(32)
, port INT2
, project_id INT4
, type INT2
, created_at TIMESTAMP NULL DEFAULT NULL 
, id SERIAL PRIMARY KEY NOT NULL
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


/*
 2011-01-19 17:42
 version:0.2
*/
ALTER TABLE databases DROP COLUMN project_id;

/*
 2011-01-19 19:01
 version:0.2
*/
ALTER TABLE models DROP COLUMN project_id;

/*
 2011-01-19 22:20
 version:0.2
*/
ALTER TABLE attributes ALTER COLUMN default_value TYPE VARCHAR(256)  USING CASE WHEN default_value IS TRUE THEN '' ELSE '' END;

/*
 2011-01-26 19:02
 version:0.2
*/
ALTER TABLE users ADD COLUMN svn_username VARCHAR(64)  ;

/*
 2011-01-26 19:02
 version:0.2
*/
ALTER TABLE users ADD COLUMN svn_password VARCHAR(64)  ;

/*
 2011-01-26 19:38
 version:0.2
*/
ALTER TABLE page_layouts ADD COLUMN is_default BOOL  ;

/*
 2011-01-27 11:14
 version:0.2
*/
CREATE TABLE stylesheets (
name VARCHAR(64)
, label VARCHAR(64)
, created_at TIMESTAMP NULL DEFAULT NULL 
, id SERIAL PRIMARY KEY NOT NULL
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


/*
 2011-01-27 11:38
 version:0.2
*/
CREATE TABLE layoutStyleseets (
layout_id INT4 UNIQUE
, stylesheet_id INT4 UNIQUE
, created_at TIMESTAMP NULL DEFAULT NULL 
, id SERIAL PRIMARY KEY NOT NULL
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


/*
 2011-01-27 11:39
 version:0.2
*/
CREATE TABLE layout_styleseets (
layout_id INT4 UNIQUE
, stylesheet_id INT4 UNIQUE
, created_at TIMESTAMP NULL DEFAULT NULL 
, id SERIAL PRIMARY KEY NOT NULL
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


/*
 2011-01-27 13:29
 version:0.2
*/
CREATE TABLE menus (
name VARCHAR(64)
, label VARCHAR(64)
, project_id INT4
, created_at TIMESTAMP NULL DEFAULT NULL 
, id SERIAL PRIMARY KEY NOT NULL
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


/*
 2011-01-28 17:01
 version:0.2
*/
CREATE TABLE images (
name VARCHAR(64)
, label VARCHAR(64)
, class_name VARCHAR
, created_at TIMESTAMP NULL DEFAULT NULL 
, id SERIAL PRIMARY KEY NOT NULL
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


/*
 2011-01-31 14:54
 version:0.2
*/
ALTER TABLE images ADD COLUMN ext VARCHAR(8)  ;

/*
 2011-01-31 15:03
 version:0.2
*/
ALTER TABLE images ADD COLUMN file_type VARCHAR(16)  ;

/*
 2011-01-31 16:40
 version:0.2
*/
ALTER TABLE images ADD COLUMN path VARCHAR(256)  ;

/*
 2011-01-31 16:57
 version:0.2
*/
ALTER TABLE images ADD COLUMN project_id INT4  ;

/*
 2011-02-14 17:46
 version:0.2
*/
CREATE TABLE inquiries (
project_id INT4
, field_name VARCHAR(64)
, field_type VARCHAR(16)
, created_at TIMESTAMP NULL DEFAULT NULL 
, id SERIAL PRIMARY KEY NOT NULL
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


/*
 2011-02-14 17:53
 version:0.2
*/
ALTER TABLE inquiries ADD COLUMN opened BOOL  ;

/*
 2011-02-14 19:01
 version:0.2
*/
ALTER TABLE inquiries ADD COLUMN label VARCHAR(64)  ;

/*
 2013-03-07 16:35
 version:0.2
*/
ALTER TABLE user_project_settings ADD COLUMN user_name VARCHAR(32)  ;

/*
 2013-03-07 16:35
 version:0.2
*/
ALTER TABLE user_project_settings ADD COLUMN group_name VARCHAR(32)  ;

/*
 2013-04-01 14:37
 version:0.2
*/
ALTER TABLE projects DROP COLUMN path;

