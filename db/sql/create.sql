DROP SCHEMA public CASCADE;
CREATE SCHEMA public;

CREATE TABLE projects (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT4
, database_id INT4
, name VARCHAR(64)
, user_id INT4
, url VARCHAR(256)
, dev_url VARCHAR(256)
, entity_name VARCHAR(64)
, is_public BOOL
, external_project_id INT4
, is_export_external_model BOOL
, is_autoload_model BOOL
);



CREATE TABLE actions (
name VARCHAR(256)
, page_id INT4
, type INT2
, id SERIAL PRIMARY KEY NOT NULL
, updated_at TIMESTAMP NULL DEFAULT NULL 
, created_at TIMESTAMP NULL DEFAULT NULL 
);



CREATE TABLE models (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
, name VARCHAR(256)
, label VARCHAR(256)
, database_id INT4
, entity_name VARCHAR(256)
, auth_type VARCHAR(64)
, creator_user_id INT4
, is_admin BOOL
, is_change BOOL
, is_output_controller BOOL
, is_output_view BOOL
, is_staff BOOL
, is_unenable BOOL
, is_user BOOL
, relfilenode INT4
, id_column_name VARCHAR(16)
, is_none_id_column BOOL
, sub_table_name VARCHAR(255)
);



CREATE TABLE attributes (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
, model_id INT4
, attname VARCHAR(64)
, label VARCHAR
, address_type VARCHAR(64)
, attnotnull BOOL
, attnum FLOAT4
, attrelid INT4
, atttypmod INT4
, default_value VARCHAR(256)
, form_type VARCHAR
, html_id_name VARCHAR(64)
, input_type VARCHAR(64)
, input_type_id INT4
, input_type_label VARCHAR(256)
, is_array BOOL
, is_change BOOL
, is_primary_key BOOL
, is_require BOOL
, is_send_mail BOOL
, is_show_edit BOOL
, is_show_list BOOL
, is_unique BOOL
, note TEXT
, typname VARCHAR(64)
, fk_attribute_id INT4
, fk_database_id INT4
);



CREATE TABLE databases (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, current_version INT2
, hostname VARCHAR(32)
, name VARCHAR(64) UNIQUE
, port INT2
, type VARCHAR(16)
, user_name VARCHAR(32)
);



CREATE TABLE elements (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, class_name VARCHAR(64)
, form_type INT2
, model_id INT4
);



CREATE TABLE images (
name VARCHAR(64)
, label VARCHAR(64)
, ext VARCHAR(8)
, file_type VARCHAR(16)
, path VARCHAR(256)
, class_name VARCHAR
, project_id INT4
, id SERIAL PRIMARY KEY NOT NULL
, updated_at TIMESTAMP NULL DEFAULT NULL 
, created_at TIMESTAMP NULL DEFAULT NULL 
);



CREATE TABLE input_type_values (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, value VARCHAR(64)
, input_type_id INT4
, label VARCHAR(256)
, sort_order INT2
);



CREATE TABLE input_types (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, attribute_id INT4
, comment TEXT
, csv_name VARCHAR(64)
, name VARCHAR(64)
, project_id INT4
, use_csv BOOL
, use_db BOOL
, csv_values TEXT
);



CREATE TABLE layout_styleseets (
layout_id INT4 UNIQUE
, stylesheet_id INT4 UNIQUE
, id SERIAL PRIMARY KEY NOT NULL
, updated_at TIMESTAMP NULL DEFAULT NULL 
, created_at TIMESTAMP NULL DEFAULT NULL 
);



CREATE TABLE links (
page_id INT4
, action_id INT4
, ssl BOOL
, width INT2
, height INT2
, is_image BOOL
, id SERIAL PRIMARY KEY NOT NULL
, updated_at TIMESTAMP NULL DEFAULT NULL 
, src VARCHAR(64)
, alt VARCHAR(128)
, created_at TIMESTAMP NULL DEFAULT NULL 
);



CREATE TABLE menus (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT4
, name VARCHAR(256)
, project_id INT4
, id_name VARCHAR(128)
, class_name VARCHAR(128)
, label VARCHAR(256)
);



CREATE TABLE menu_items (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
, menu_id INT4
, model_id INT4
, page_id INT4
, label VARCHAR(256)
, name VARCHAR(256)
, controller VARCHAR(64)
, action VARCHAR(64)
, id_name VARCHAR(128)
, class_name VARCHAR(128)
, src VARCHAR(256)
, is_highlight_except_action BOOL
, is_ssl BOOL
);



CREATE TABLE monitors (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
, project_id INT4
, domain VARCHAR(256)
, host_name VARCHAR(64)
);



CREATE TABLE pages (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, project_id INT4
, layout_id INT4
, model_id INT4
, label VARCHAR(256)
, name VARCHAR(256)
, class_name VARCHAR(256)
, extends_class_name VARCHAR(256)
, type INT2
, contents TEXT
, is_default BOOL
, is_show BOOL
, is_static BOOL
, is_update BOOL
);



CREATE TABLE page_actions (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
, page_id INT4
, label VARCHAR(256)
, name VARCHAR(256)
, is_update BOOL
);



CREATE TABLE page_items (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
, page_id INT4
, model_id INT4
, attribute_id INT4
, page_action_id INT4
, contents TEXT
);



CREATE TABLE page_layouts (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, project_id INT4
, label VARCHAR(64)
, name VARCHAR(64)
, is_default BOOL
, contents TEXT
);



CREATE TABLE project_users (
id SERIAL PRIMARY KEY NOT NULL
, project_id INT4
, user_id INT4
, updated_at TIMESTAMP NULL DEFAULT NULL 
, created_at TIMESTAMP NULL DEFAULT NULL 
);



CREATE TABLE relations (
attribute_from_id INT4
, attribute_to_id INT4
, updated_at TIMESTAMP NULL DEFAULT NULL 
, id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
);



CREATE TABLE schema_info (
version INT4 NOT NULL
);



CREATE TABLE stylesheets (
name VARCHAR(64)
, label VARCHAR(64)
, updated_at TIMESTAMP NULL DEFAULT NULL 
, created_at TIMESTAMP NULL DEFAULT NULL 
, id SERIAL PRIMARY KEY NOT NULL
);



CREATE TABLE user_configs (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, user_id INT4 UNIQUE
, default_dev_url VARCHAR(256)
, default_project_path VARCHAR(256)
, default_db_host VARCHAR(64)
);



CREATE TABLE user_project_settings (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
, project_id INT4
, user_id INT4
, project_path VARCHAR(255)
, group_name VARCHAR(32)
, user_name VARCHAR(32)
);



CREATE TABLE users (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, email VARCHAR(256) UNIQUE
, first_name VARCHAR(64)
, last_name VARCHAR(64)
, login_name VARCHAR(64) UNIQUE
, password VARCHAR(256)
, sort_order INT2
, htaccess_name VARCHAR(64)
);



CREATE TABLE views (
name VARCHAR(64)
, label VARCHAR(64)
, updated_at TIMESTAMP NULL DEFAULT NULL 
, page_id INT4
, created_at TIMESTAMP NULL DEFAULT NULL 
, contents TEXT
, is_default BOOL
, id SERIAL PRIMARY KEY NOT NULL
);



CREATE TABLE javascripts (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
, name VARCHAR(256)
, dir_path VARCHAR(256)
, site_url TEXT
);



CREATE TABLE project_servers (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
, name VARCHAR(64)
, fqdn VARCHAR(256)
);



CREATE TABLE svns (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
);



CREATE TABLE test_actions (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
, project_id INT4
, page_action_id INT4
, action_id INT4
, params TEXT
, script_path TEXT
);



CREATE TABLE localizes (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
, name VARCHAR(64)
, lang VARCHAR(8)
);



CREATE TABLE localize_labels (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
, label TEXT
, key VARCHAR(256)
, project_id INT4
, attribute_id INT4
, model_id INT4
);



CREATE TABLE apis (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
, project_id INT4
, name VARCHAR(256)
, comment TEXT
, api_base_name VARCHAR(64)
);



CREATE TABLE api_params (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
, api_id INT4
, name VARCHAR(256)
, type VARCHAR(16)
, comment TEXT
, example VARCHAR(256)
, is_required BOOL
);
