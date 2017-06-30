CREATE TABLE actions (
name VARCHAR(256)
, type INT2
, page_id INT4
, id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


CREATE TABLE databases (
port INT2
, id SERIAL PRIMARY KEY NOT NULL
, name VARCHAR(64)
, hostname VARCHAR(32)
, type VARCHAR(16)
, user_name VARCHAR(32)
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, current_version INT2
);


CREATE TABLE elements (
class_name VARCHAR(64)
, form_type INT2
, model_id INT4
, id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


CREATE TABLE images (
name VARCHAR(64)
, label VARCHAR(64)
, ext VARCHAR(8)
, file_type VARCHAR(16)
, path VARCHAR(256)
, id SERIAL PRIMARY KEY NOT NULL
, project_id INT4
, class_name VARCHAR
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


CREATE TABLE input_types (
use_csv BOOL
, use_db BOOL
, id SERIAL PRIMARY KEY NOT NULL
, project_id INT4
, attribute_id INT4
, comment TEXT
, name VARCHAR(64)
, csv_name VARCHAR(64)
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


CREATE TABLE inquiries (
opened BOOL
, id SERIAL PRIMARY KEY NOT NULL
, project_id INT4
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, label VARCHAR(64)
, is_mail_to BOOL
, is_mail_from BOOL
, input_type_id INT4
, attribute_id INT4
, mail_id INT4
, name VARCHAR(64)
, input_type VARCHAR(16)
);


CREATE TABLE layout_styleseets (
layout_id INT4 UNIQUE
, stylesheet_id INT4 UNIQUE
, id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


CREATE TABLE links (
ssl BOOL
, is_image BOOL
, width INT2
, height INT2
, page_id INT4
, action_id INT4
, id SERIAL PRIMARY KEY NOT NULL
, src VARCHAR(64)
, alt VARCHAR(128)
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


CREATE TABLE menus (
project_id INT4
, id SERIAL PRIMARY KEY NOT NULL
, name VARCHAR(64)
, label VARCHAR(64)
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


CREATE TABLE page_layouts (
is_default BOOL
, id SERIAL PRIMARY KEY NOT NULL
, project_id INT4
, contents TEXT
, name VARCHAR(64)
, label VARCHAR(64)
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


CREATE TABLE pages (
is_show BOOL
, is_static BOOL
, is_default BOOL
, type INT2
, project_id INT4
, layout_id INT4
, id SERIAL PRIMARY KEY NOT NULL
, contents TEXT
, name VARCHAR(256)
, label VARCHAR(256)
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


CREATE TABLE project_users (
project_id INT4
, user_id INT4
, id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


CREATE TABLE projects (
is_public BOOL
, id SERIAL PRIMARY KEY NOT NULL
, database_id INT4
, user_id INT4
, name VARCHAR(64)
, path VARCHAR(256)
, url VARCHAR(256)
, dev_url VARCHAR(256)
, entity_name VARCHAR(64)
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


CREATE TABLE relations (
id SERIAL PRIMARY KEY NOT NULL
, attribute_from_id INT4
, attribute_to_id INT4
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


CREATE TABLE stylesheets (
id SERIAL PRIMARY KEY NOT NULL
, name VARCHAR(64)
, label VARCHAR(64)
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


CREATE TABLE user_configs (
id SERIAL PRIMARY KEY NOT NULL
, user_id INT4 UNIQUE
, default_project_path VARCHAR(256)
, default_dev_url VARCHAR(256)
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


CREATE TABLE users (
email VARCHAR(256)
, password VARCHAR(32)
, tmp_password VARCHAR(32)
, login_name VARCHAR(32)
, svn_username VARCHAR(64)
, svn_password VARCHAR(64)
, id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, name VARCHAR(32)
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


CREATE TABLE views (
name VARCHAR(64)
, is_default BOOL
, page_id INT4
, contents TEXT
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, id SERIAL PRIMARY KEY NOT NULL
, label VARCHAR(64)
);


CREATE TABLE models (
is_output_controller BOOL
, is_output_view BOOL
, is_send_mail BOOL
, is_unenable BOOL
, is_change BOOL
, is_sort BOOL
, sort_order INT2
, database_id INT4
, id SERIAL PRIMARY KEY NOT NULL
, creator_user_id INT4
, name VARCHAR(32)
, label VARCHAR(64)
, entity_name VARCHAR(64)
, relfilenode FLOAT4
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
);


CREATE TABLE attributes (
sort_order INT2
, is_primary_key BOOL
, is_unique BOOL
, is_require BOOL
, is_array BOOL
, attnotnull BOOL
, is_show_list BOOL
, is_show_edit BOOL
, is_send_mail BOOL
, is_change BOOL
, model_id INT4
, attrelid INT4
, atttypmod INT4
, attname VARCHAR(64)
, typname VARCHAR(64)
, label VARCHAR
, default_value VARCHAR(256)
, html_id_name VARCHAR(64)
, form_type VARCHAR
, attnum FLOAT4
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, address_type VARCHAR(64)
, input_type VARCHAR(64)
, id SERIAL PRIMARY KEY NOT NULL
, input_type_id INT4
, fk_attribute_id INT4
);


CREATE TABLE input_type_values (
id SERIAL PRIMARY KEY NOT NULL
, input_type_id INT4
, value VARCHAR(64)
, label VARCHAR(256)
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
);


