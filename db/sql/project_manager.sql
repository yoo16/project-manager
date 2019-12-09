CREATE TABLE IF NOT EXISTS "admins" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, email VARCHAR(256)
, first_name VARCHAR(64)
, last_name VARCHAR(64)
, login_name VARCHAR(256)
, memo TEXT
, password VARCHAR(256)
, sort_order INT2
, tmp_password VARCHAR(256)
, updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "apis" (
id SERIAL PRIMARY KEY NOT NULL
, api_group_id INT4
, created_at TIMESTAMP
, label VARCHAR(256)
, name VARCHAR(256) NOT NULL
, note TEXT
, project_id INT4 NOT NULL
, sort_order INT4
, updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "api_actions" (
id SERIAL PRIMARY KEY NOT NULL
, api_id INT4 NOT NULL
, created_at TIMESTAMP
, label VARCHAR(256)
, name VARCHAR(256) NOT NULL
, note TEXT
, sort_order INT4
, updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "api_groups" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, name VARCHAR(64)
, sort_order INT4
, updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "api_params" (
id SERIAL PRIMARY KEY NOT NULL
, api_action_id INT4 NOT NULL
, created_at TIMESTAMP
, name VARCHAR(256) NOT NULL
, note TEXT
, sort_order INT4
, type VARCHAR(16)
, updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "attributes" (
id SERIAL PRIMARY KEY NOT NULL
, attnum INT4
, attrelid INT4
, created_at TIMESTAMP
, csv VARCHAR(256)
, default_value VARCHAR
, delete_action VARCHAR(32)
, fk_attribute_id INT4
, is_array BOOL
, is_lock BOOL
, is_primary_key BOOL
, is_required BOOL
, is_unique BOOL
, label VARCHAR
, length INT4
, model_id INT4 NOT NULL
, name VARCHAR NOT NULL
, note TEXT
, old_attribute_id INT4
, old_name VARCHAR(256)
, sort_order INT4
, type VARCHAR NOT NULL
, update_action VARCHAR(32)
, updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "databases" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, current_version INT4
, hostname VARCHAR NOT NULL
, is_lock BOOL
, name VARCHAR NOT NULL
, port INT4 NOT NULL
, type VARCHAR
, updated_at TIMESTAMP
, user_name VARCHAR NOT NULL
);

CREATE TABLE IF NOT EXISTS "langs" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, lang VARCHAR(8) NOT NULL
, name VARCHAR(256) NOT NULL
, sort_order INT4
, updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "localize_strings" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, label TEXT
, name VARCHAR(256) NOT NULL
, project_id INT4 NOT NULL
, sort_order INT4
, updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "menus" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, is_provide BOOL
, name VARCHAR(256) NOT NULL
, sort_order INT4
, updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "menu_items" (
id SERIAL PRIMARY KEY NOT NULL
, action VARCHAR(256)
, controller VARCHAR(256)
, created_at TIMESTAMP
, is BOOL
, is_provide BOOL
, menu_id INT4 NOT NULL
, name VARCHAR(256) NOT NULL
, sort_order INT4
, updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "models" (
id SERIAL PRIMARY KEY NOT NULL
, class_name VARCHAR NOT NULL
, created_at TIMESTAMP
, csv VARCHAR(256)
, entity_name VARCHAR NOT NULL
, id_column_name VARCHAR
, is_lock BOOL
, is_none_id_column BOOL
, is_unenable BOOL
, label VARCHAR
, name VARCHAR NOT NULL
, note TEXT
, old_database_id INT4
, old_name VARCHAR(256)
, pg_class_id INT4
, project_id INT4 NOT NULL
, relfilenode INT4
, sort_order INT4
, sub_table_name VARCHAR
, updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "pages" (
id SERIAL PRIMARY KEY NOT NULL
, class_name VARCHAR(256) NOT NULL
, created_at TIMESTAMP
, entity_name VARCHAR(256) NOT NULL
, is_overwrite BOOL
, label VARCHAR(256)
, list_sort_order_columns TEXT
, model_id INT4
, name VARCHAR(256) NOT NULL
, note TEXT
, parent_page_id INT4
, project_id INT4 NOT NULL
, sort_order INT4
, updated_at TIMESTAMP
, view_name VARCHAR(256)
, where_model_id INT4
);

CREATE TABLE IF NOT EXISTS "page_filters" (
id SERIAL PRIMARY KEY NOT NULL
, attribute_id INT4 NOT NULL
, created_at TIMESTAMP
, equal_sign VARCHAR(8)
, page_id INT4 NOT NULL
, sort_order INT4
, updated_at TIMESTAMP
, value VARCHAR(256) NOT NULL
);

CREATE TABLE IF NOT EXISTS "page_models" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, is_fetch_list_values BOOL
, is_request_session BOOL
, model_id INT4 NOT NULL
, page_id INT4 NOT NULL
, sort_order INT4
, updated_at TIMESTAMP
, where_model_id INT4
);

CREATE TABLE IF NOT EXISTS "projects" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, database_id INT4 NOT NULL
, entity_name VARCHAR(256)
, external_project_id INT4
, is_export_external_model BOOL
, name VARCHAR(256) NOT NULL
, sort_order INT4
, updated_at TIMESTAMP
, url VARCHAR(256)
);

CREATE TABLE IF NOT EXISTS "public_localize_strings" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, label TEXT
, name VARCHAR(256)
, sort_order INT4
, updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "records" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, label VARCHAR(256) NOT NULL
, laben_en BOOL
, name VARCHAR(256) NOT NULL
, note TEXT
, project_id INT4 NOT NULL
, sort_order INT4
, updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "record_items" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, key VARCHAR(256) NOT NULL
, record_id INT4 NOT NULL
, sort_order INT4
, updated_at TIMESTAMP
, value VARCHAR(256) NOT NULL
, value_en VARCHAR(256)
);

CREATE TABLE IF NOT EXISTS "relation_databases" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, old_database_id INT4 NOT NULL
, project_id INT4 NOT NULL
, sort_order INT4
, updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "routes" (
id SERIAL PRIMARY KEY NOT NULL
, action VARCHAR(256)
, controller VARCHAR
, created_at TIMESTAMP
, method VARCHAR(8) NOT NULL
, middleware VARCHAR(256)
, page_id INT4 NOT NULL
, sort_order INT4
, updated_at TIMESTAMP
, uri VARCHAR(256)
);

CREATE TABLE IF NOT EXISTS "users" (
id SERIAL PRIMARY KEY NOT NULL
, birthday_at TIMESTAMP
, created_at TIMESTAMP
, email VARCHAR(256)
, first_name VARCHAR(64)
, first_name_kana VARCHAR(64)
, last_name VARCHAR(64)
, last_name_kana VARCHAR(64)
, login_name VARCHAR(64)
, memo TEXT
, password VARCHAR(256)
, sort_order INT2
, tmp_password VARCHAR(256)
, updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "user_project_settings" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, group_name VARCHAR(256)
, project_id INT4 NOT NULL
, project_path VARCHAR(256) NOT NULL
, sort_order INT4
, updated_at TIMESTAMP
, user_id INT4 NOT NULL
, user_name VARCHAR(256)
);

CREATE TABLE IF NOT EXISTS "views" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, is_overwrite BOOL
, label VARCHAR(256)
, label_width INT4
, name VARCHAR(256) NOT NULL
, note TEXT
, page_id INT4 NOT NULL
, sort_order INT4
, updated_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "view_items" (
id SERIAL PRIMARY KEY NOT NULL
, attribute_id INT4
, created_at TIMESTAMP
, css_class BOOL
, csv VARCHAR(256)
, form_model_id INT4
, form_type VARCHAR(256)
, label VARCHAR(256)
, label_column TEXT
, link VARCHAR(256)
, link_param_id_attribute_id INT4
, localize_string_id INT4
, note TEXT
, page_id INT4
, sort_order INT4
, updated_at TIMESTAMP
, value_column VARCHAR(256)
, view_id INT4 NOT NULL
, where_attribute_id INT4
, where_model_id INT4
, where_order TEXT
, where_string TEXT
);

CREATE TABLE IF NOT EXISTS "view_item_groups" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, name VARCHAR(256)
, sort_order INT4
, updated_at TIMESTAMP
, view_id INT4 NOT NULL
);

CREATE TABLE IF NOT EXISTS "view_item_group_members" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, sort_order INT4
, updated_at TIMESTAMP
, view_item_group_id INT4 NOT NULL
, view_item_id INT4 NOT NULL
);

CREATE TABLE IF NOT EXISTS "view_item_models" (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP
, is_id_index BOOL
, page_id INT4
, sort_order INT4
, updated_at TIMESTAMP
, value_model_id INT4
, view_item_id INT4
, where_model_id INT4
);

ALTER TABLE admins
      ADD CONSTRAINT admins_email_key
      UNIQUE (email);

ALTER TABLE admins
      ADD CONSTRAINT admins_login_name_key
      UNIQUE (login_name);

ALTER TABLE apis
      ADD CONSTRAINT apis_project_id_fkey FOREIGN KEY (project_id)
      REFERENCES projects(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE apis
      ADD CONSTRAINT apis_api_group_id_fkey FOREIGN KEY (api_group_id)
      REFERENCES api_groups(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE api_actions
      ADD CONSTRAINT api_actions_api_id_fkey FOREIGN KEY (api_id)
      REFERENCES apis(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE api_actions
      ADD CONSTRAINT api_actions_name_api_id_key
      UNIQUE (name, api_id);

ALTER TABLE attributes
      ADD CONSTRAINT attributes_model_id_fkey FOREIGN KEY (model_id)
      REFERENCES models(id)
      ON UPDATE NO ACTION
      ON DELETE CASCADE
;
ALTER TABLE attributes
      ADD CONSTRAINT attributes_name_model_id_key
      UNIQUE (name, model_id);

ALTER TABLE databases
      ADD CONSTRAINT databases_name_hostname_key
      UNIQUE (name, hostname);

ALTER TABLE langs
      ADD CONSTRAINT langs_lang_key
      UNIQUE (lang);

ALTER TABLE langs
      ADD CONSTRAINT langs_name_key
      UNIQUE (name);

ALTER TABLE localize_strings
      ADD CONSTRAINT localize_strings_project_id_id_fkey FOREIGN KEY (project_id)
      REFERENCES projects(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE localize_strings
      ADD CONSTRAINT localize_strings_name_project_id_key
      UNIQUE (project_id, name);

ALTER TABLE models
      ADD CONSTRAINT models_project_id_fkey FOREIGN KEY (project_id)
      REFERENCES projects(id)
      ON UPDATE NO ACTION
      ON DELETE CASCADE
;
ALTER TABLE models
      ADD CONSTRAINT models_name_project_id_key
      UNIQUE (name, project_id);

ALTER TABLE pages
      ADD CONSTRAINT pages_model_id_fkey FOREIGN KEY (model_id)
      REFERENCES models(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE pages
      ADD CONSTRAINT pages_parent_page_id_fkey FOREIGN KEY (parent_page_id)
      REFERENCES pages(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE pages
      ADD CONSTRAINT pages_project_id_fkey FOREIGN KEY (project_id)
      REFERENCES projects(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE pages
      ADD CONSTRAINT pages_where_model_id_fkey FOREIGN KEY (where_model_id)
      REFERENCES models(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE page_filters
      ADD CONSTRAINT page_filters_attribute_id_fkey FOREIGN KEY (attribute_id)
      REFERENCES attributes(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE page_filters
      ADD CONSTRAINT page_filters_page_id_fkey FOREIGN KEY (page_id)
      REFERENCES pages(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE page_models
      ADD CONSTRAINT page_models_model_id_fkey FOREIGN KEY (model_id)
      REFERENCES models(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE page_models
      ADD CONSTRAINT page_models_page_id_fkey FOREIGN KEY (page_id)
      REFERENCES pages(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE page_models
      ADD CONSTRAINT page_models_model_id_page_id_key
      UNIQUE (model_id, page_id);

ALTER TABLE records
      ADD CONSTRAINT records_project_id_fkey FOREIGN KEY (project_id)
      REFERENCES projects(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE record_items
      ADD CONSTRAINT record_items_record_id_fkey FOREIGN KEY (record_id)
      REFERENCES records(id)
      ON UPDATE NO ACTION
      ON DELETE CASCADE
;
ALTER TABLE relation_databases
      ADD CONSTRAINT relation_databases_old_database_id_fkey FOREIGN KEY (old_database_id)
      REFERENCES databases(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE relation_databases
      ADD CONSTRAINT relation_databases_project_id_fkey FOREIGN KEY (project_id)
      REFERENCES projects(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE routes
      ADD CONSTRAINT routes_page_id_fkey FOREIGN KEY (page_id)
      REFERENCES pages(id)
      ON UPDATE NO ACTION
      ON DELETE CASCADE
;
ALTER TABLE users
      ADD CONSTRAINT users_email_key
      UNIQUE (email);

ALTER TABLE view_items
      ADD CONSTRAINT view_items_view_id_fkey FOREIGN KEY (view_id)
      REFERENCES views(id)
      ON UPDATE NO ACTION
      ON DELETE CASCADE
;
ALTER TABLE view_items
      ADD CONSTRAINT view_items_link_param_id_attribute_id_fkey FOREIGN KEY (link_param_id_attribute_id)
      REFERENCES attributes(id)
      ON UPDATE NO ACTION
      ON DELETE CASCADE
;
ALTER TABLE view_items
      ADD CONSTRAINT view_items_where_attribute_id_fkey FOREIGN KEY (where_attribute_id)
      REFERENCES attributes(id)
      ON UPDATE NO ACTION
      ON DELETE CASCADE
;
ALTER TABLE view_items
      ADD CONSTRAINT view_items_attribute_id_fkey FOREIGN KEY (attribute_id)
      REFERENCES attributes(id)
      ON UPDATE NO ACTION
      ON DELETE CASCADE
;
ALTER TABLE view_items
      ADD CONSTRAINT view_items_where_model_id_fkey FOREIGN KEY (where_model_id)
      REFERENCES models(id)
      ON UPDATE NO ACTION
      ON DELETE CASCADE
;
ALTER TABLE view_items
      ADD CONSTRAINT view_items_page_id_fkey FOREIGN KEY (page_id)
      REFERENCES pages(id)
      ON UPDATE NO ACTION
      ON DELETE CASCADE
;
ALTER TABLE view_items
      ADD CONSTRAINT view_items_localize_string_id_fkey FOREIGN KEY (localize_string_id)
      REFERENCES localize_strings(id)
      ON UPDATE NO ACTION
      ON DELETE CASCADE
;
ALTER TABLE view_item_groups
      ADD CONSTRAINT view_item_groups_view_id_fkey FOREIGN KEY (view_id)
      REFERENCES views(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE view_item_group_members
      ADD CONSTRAINT view_item_group_members_view_item_group_id_fkey FOREIGN KEY (view_item_group_id)
      REFERENCES view_item_groups(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE view_item_group_members
      ADD CONSTRAINT view_item_group_members_view_item_id_fkey1 FOREIGN KEY (view_item_id)
      REFERENCES view_items(id)
      ON UPDATE NO ACTION
      ON DELETE NO ACTION
;
ALTER TABLE view_item_group_members
      ADD CONSTRAINT view_item_group_members_view_item_group_id_view_item_id_key
      UNIQUE (view_item_group_id, view_item_id);

