CREATE TABLE users (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
, last_name_kana VARCHAR(64)
, first_name_kana VARCHAR(64)
, last_name VARCHAR(64)
, first_name VARCHAR(64)
, password VARCHAR(256)
, tmp_password VARCHAR(256)
, email VARCHAR(256) UNIQUE
, tel FLOAT8
, birthday_at TIMESTAMP NULL DEFAULT NULL 
, gender VARCHAR(8)
, memo TEXT
);

CREATE TABLE admins (
id SERIAL PRIMARY KEY NOT NULL
, created_at TIMESTAMP NULL DEFAULT NULL 
, updated_at TIMESTAMP NULL DEFAULT NULL 
, sort_order INT2
, login_name VARCHAR(256) UNIQUE
, email VARCHAR(256) UNIQUE
, last_name VARCHAR(64)
, first_name VARCHAR(64)
, password VARCHAR(256)
, tmp_password VARCHAR(256)
, memo TEXT
);
