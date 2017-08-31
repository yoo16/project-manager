/*** create table ***/
CREATE TABLE IF NOT EXISTS "attributes" (
id SERIAL PRIMARY KEY NOT NULL,
created_at TIMESTAMP,
updated_at TIMESTAMP,
sort_order INT4,
is_primary_key BOOL,
is_required BOOL,
is_unique BOOL,
length INT4,
name VARCHARNOT NULL,
type VARCHARNOT NULL,
default_value VARCHAR,
is_array BOOL,
attnum INT4NOT NULL,
model_id INT4NOT NULL,
fk_attribute_id INT4,
is_lock BOOL,
label VARCHAR,
note TEXT,
attrelid INT4NOT NULL
);

CREATE TABLE IF NOT EXISTS "databases" (
id SERIAL PRIMARY KEY NOT NULL,
created_at TIMESTAMP,
updated_at TIMESTAMP,
name VARCHARNOT NULL,
hostname VARCHARNOT NULL,
user_name VARCHARNOT NULL,
port INT4NOT NULL,
type VARCHAR,
current_version INT4,
is_lock BOOL
);

CREATE TABLE IF NOT EXISTS "models" (
id SERIAL PRIMARY KEY NOT NULL,
created_at TIMESTAMP,
updated_at TIMESTAMP,
sort_order INT4,
name VARCHARNOT NULL,
label VARCHAR,
pg_class_id INT4NOT NULL,
project_id INT4NOT NULL,
relfilenode INT4NOT NULL,
database_id INT4NOT NULL,
entity_name VARCHARNOT NULL,
class_name VARCHARNOT NULL,
is_unenable BOOL,
id_column_name VARCHAR,
is_none_id_column BOOL,
sub_table_name VARCHAR,
is_lock BOOL
);

CREATE TABLE IF NOT EXISTS "pages" (
id SERIAL PRIMARY KEY NOT NULL,
created_at TIMESTAMP,
updated_at TIMESTAMP,
sort_order INT4,
project_id INT4NOT NULL,
model_id INT4,
name VARCHAR(256)NOT NULL,
entity_name VARCHAR(256)NOT NULL,
class_name VARCHAR(256)NOT NULL,
label VARCHAR(256),
is_overwrite BOOL
);

CREATE TABLE IF NOT EXISTS "projects" (
id SERIAL PRIMARY KEY NOT NULL,
created_at TIMESTAMP,
updated_at TIMESTAMP,
sort_order INT4,
name VARCHAR(256)NOT NULL,
database_id INT4NOT NULL,
entity_name VARCHAR(256),
url VARCHAR(256),
external_project_id INT4,
is_export_external_model BOOL
);

CREATE TABLE IF NOT EXISTS "users" (
id SERIAL PRIMARY KEY NOT NULL,
created_at TIMESTAMP,
updated_at TIMESTAMP,
sort_order INT4,
login_name VARCHAR(256)NOT NULL,
htaccess_name VARCHAR(256),
last_name VARCHAR(256),
first_name VARCHAR(256),
password VARCHAR(256),
email VARCHAR(256),
default_dev_url VARCHAR(256),
default_project_path VARCHAR(256),
default_db_host VARCHAR(256)
);

CREATE TABLE IF NOT EXISTS "user_project_settings" (
id SERIAL PRIMARY KEY NOT NULL,
created_at TIMESTAMP,
updated_at TIMESTAMP,
sort_order INT4,
user_id INT4,
project_id INT4NOT NULL,
project_path VARCHAR(256)NOT NULL,
group_name VARCHAR(256),
user_name VARCHAR(256)
);

CREATE TABLE IF NOT EXISTS "views" (
id SERIAL PRIMARY KEY NOT NULL,
created_at TIMESTAMP,
updated_at TIMESTAMP,
sort_order INT4,
page_id INT4NOT NULL,
name VARCHAR(256)NOT NULL,
label VARCHAR(256),
is_overwrite BOOL
);

CREATE TABLE IF NOT EXISTS "view_items" (
id SERIAL PRIMARY KEY NOT NULL,
created_at TIMESTAMP,
updated_at TIMESTAMP,
attribute_id INT4,
view_id INT4NOT NULL,
label VARCHAR(256),
form_type VARCHAR(256),
css_class BOOL
);

/*** constraint ***/
