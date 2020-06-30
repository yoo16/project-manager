--
-- PostgreSQL database dump
--

-- Dumped from database version 10.10 (Ubuntu 10.10-0ubuntu0.18.04.1)
-- Dumped by pg_dump version 10.10 (Ubuntu 10.10-0ubuntu0.18.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: admins; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.admins (
    id integer NOT NULL,
    created_at timestamp without time zone,
    email character varying(256),
    first_name character varying(64),
    last_name character varying(64),
    login_name character varying(256),
    memo text,
    password character varying(256),
    sort_order smallint,
    tmp_password character varying(256),
    updated_at timestamp without time zone
);


ALTER TABLE public.admins OWNER TO default;

--
-- Name: admins_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.admins_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.admins_id_seq OWNER TO default;

--
-- Name: admins_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.admins_id_seq OWNED BY public.admins.id;


--
-- Name: api_actions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.api_actions (
    id integer NOT NULL,
    api_id integer NOT NULL,
    created_at timestamp without time zone,
    label character varying(256),
    name character varying(256) NOT NULL,
    note text,
    sort_order integer,
    updated_at timestamp without time zone
);


ALTER TABLE public.api_actions OWNER TO default;

--
-- Name: api_actions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.api_actions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.api_actions_id_seq OWNER TO default;

--
-- Name: api_actions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.api_actions_id_seq OWNED BY public.api_actions.id;


--
-- Name: api_groups; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.api_groups (
    id integer NOT NULL,
    created_at timestamp without time zone,
    name character varying(64),
    sort_order integer,
    updated_at timestamp without time zone
);


ALTER TABLE public.api_groups OWNER TO default;

--
-- Name: api_groups_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.api_groups_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.api_groups_id_seq OWNER TO default;

--
-- Name: api_groups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.api_groups_id_seq OWNED BY public.api_groups.id;


--
-- Name: api_params; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.api_params (
    id integer NOT NULL,
    api_action_id integer NOT NULL,
    created_at timestamp without time zone,
    name character varying(256) NOT NULL,
    note text,
    sort_order integer,
    type character varying(16),
    updated_at timestamp without time zone
);


ALTER TABLE public.api_params OWNER TO default;

--
-- Name: api_params_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.api_params_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.api_params_id_seq OWNER TO default;

--
-- Name: api_params_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.api_params_id_seq OWNED BY public.api_params.id;


--
-- Name: apis; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.apis (
    id integer NOT NULL,
    api_group_id integer,
    created_at timestamp without time zone,
    label character varying(256),
    name character varying(256) NOT NULL,
    note text,
    project_id integer NOT NULL,
    sort_order integer,
    updated_at timestamp without time zone
);


ALTER TABLE public.apis OWNER TO default;

--
-- Name: apis_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.apis_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.apis_id_seq OWNER TO default;

--
-- Name: apis_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.apis_id_seq OWNED BY public.apis.id;


--
-- Name: attributes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.attributes (
    id integer NOT NULL,
    attnum integer,
    attrelid integer,
    created_at timestamp without time zone,
    csv character varying(256),
    default_value character varying,
    delete_action character varying(32),
    fk_attribute_id integer,
    is_array boolean,
    is_lock boolean,
    is_primary_key boolean,
    is_required boolean,
    is_unique boolean,
    label character varying,
    length integer,
    model_id integer NOT NULL,
    name character varying NOT NULL,
    note text,
    old_attribute_id integer,
    old_name character varying(256),
    sort_order integer,
    type character varying NOT NULL,
    update_action character varying(32),
    updated_at timestamp without time zone
);


ALTER TABLE public.attributes OWNER TO default;

--
-- Name: attributes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.attributes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.attributes_id_seq OWNER TO default;

--
-- Name: attributes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.attributes_id_seq OWNED BY public.attributes.id;


--
-- Name: databases; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.databases (
    id integer NOT NULL,
    created_at timestamp without time zone,
    current_version integer,
    hostname character varying NOT NULL,
    is_lock boolean,
    name character varying NOT NULL,
    port integer NOT NULL,
    type character varying,
    updated_at timestamp without time zone,
    user_name character varying NOT NULL
);


ALTER TABLE public.databases OWNER TO default;

--
-- Name: databases_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.databases_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.databases_id_seq OWNER TO default;

--
-- Name: databases_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.databases_id_seq OWNED BY public.databases.id;


--
-- Name: langs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.langs (
    id integer NOT NULL,
    created_at timestamp without time zone,
    lang character varying(8) NOT NULL,
    name character varying(256) NOT NULL,
    sort_order integer,
    updated_at timestamp without time zone
);


ALTER TABLE public.langs OWNER TO default;

--
-- Name: langs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.langs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.langs_id_seq OWNER TO default;

--
-- Name: langs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.langs_id_seq OWNED BY public.langs.id;


--
-- Name: localize_strings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.localize_strings (
    id integer NOT NULL,
    created_at timestamp without time zone,
    label text,
    name character varying(256) NOT NULL,
    project_id integer NOT NULL,
    sort_order integer,
    updated_at timestamp without time zone
);


ALTER TABLE public.localize_strings OWNER TO default;

--
-- Name: localize_strings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.localize_strings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.localize_strings_id_seq OWNER TO default;

--
-- Name: localize_strings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.localize_strings_id_seq OWNED BY public.localize_strings.id;


--
-- Name: menus; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.menus (
    id integer NOT NULL,
    created_at timestamp without time zone,
    is_provide boolean,
    name character varying(256) NOT NULL,
    sort_order integer,
    updated_at timestamp without time zone
);


ALTER TABLE public.menus OWNER TO default;

--
-- Name: menus_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.menus_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.menus_id_seq OWNER TO default;

--
-- Name: menus_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.menus_id_seq OWNED BY public.menus.id;


--
-- Name: models; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.models (
    id integer NOT NULL,
    class_name character varying(256) NOT NULL,
    created_at timestamp without time zone,
    csv character varying(256),
    entity_name character varying NOT NULL,
    id_column_name character varying(256),
    is_lock boolean,
    is_none_id_column boolean,
    is_unenable boolean,
    label character varying,
    name character varying NOT NULL,
    note text,
    old_database_id integer,
    old_name character varying(256),
    pg_class_id integer,
    project_id integer NOT NULL,
    relfilenode integer,
    sort_order integer,
    sub_table_name character varying,
    updated_at timestamp without time zone
);


ALTER TABLE public.models OWNER TO default;

--
-- Name: models_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.models_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.models_id_seq OWNER TO default;

--
-- Name: models_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.models_id_seq OWNED BY public.models.id;


--
-- Name: page_filters; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.page_filters (
    id integer NOT NULL,
    attribute_id integer NOT NULL,
    created_at timestamp without time zone,
    equal_sign character varying(8),
    page_id integer NOT NULL,
    sort_order integer,
    updated_at timestamp without time zone,
    value character varying(256) NOT NULL
);


ALTER TABLE public.page_filters OWNER TO default;

--
-- Name: page_filters_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.page_filters_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.page_filters_id_seq OWNER TO default;

--
-- Name: page_filters_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.page_filters_id_seq OWNED BY public.page_filters.id;


--
-- Name: page_models; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.page_models (
    id integer NOT NULL,
    created_at timestamp without time zone,
    is_fetch_list_values boolean,
    is_request_session boolean,
    model_id integer NOT NULL,
    page_id integer NOT NULL,
    sort_order integer,
    updated_at timestamp without time zone,
    where_model_id integer
);


ALTER TABLE public.page_models OWNER TO default;

--
-- Name: page_models_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.page_models_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.page_models_id_seq OWNER TO default;

--
-- Name: page_models_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.page_models_id_seq OWNED BY public.page_models.id;


--
-- Name: pages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pages (
    id integer NOT NULL,
    class_name character varying(256) NOT NULL,
    created_at timestamp without time zone,
    entity_name character varying(256) NOT NULL,
    is_overwrite boolean,
    label character varying(256),
    list_sort_order_columns text,
    model_id integer,
    name character varying(256) NOT NULL,
    note text,
    parent_page_id integer,
    project_id integer NOT NULL,
    sort_order integer,
    updated_at timestamp without time zone,
    view_name character varying(256),
    where_model_id integer
);


ALTER TABLE public.pages OWNER TO default;

--
-- Name: pages_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pages_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pages_id_seq OWNER TO default;

--
-- Name: pages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pages_id_seq OWNED BY public.pages.id;


--
-- Name: projects; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.projects (
    id integer NOT NULL,
    created_at timestamp without time zone,
    database_id integer NOT NULL,
    entity_name character varying(256),
    external_project_id integer,
    is_export_external_model boolean,
    name character varying(256) NOT NULL,
    sort_order integer,
    updated_at timestamp without time zone,
    url character varying(256)
);


ALTER TABLE public.projects OWNER TO default;

--
-- Name: projects_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.projects_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.projects_id_seq OWNER TO default;

--
-- Name: projects_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.projects_id_seq OWNED BY public.projects.id;


--
-- Name: public_localize_strings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.public_localize_strings (
    id integer NOT NULL,
    created_at timestamp without time zone,
    label text,
    name character varying(256),
    sort_order integer,
    updated_at timestamp without time zone
);


ALTER TABLE public.public_localize_strings OWNER TO default;

--
-- Name: public_localize_strings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.public_localize_strings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.public_localize_strings_id_seq OWNER TO default;

--
-- Name: public_localize_strings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.public_localize_strings_id_seq OWNED BY public.public_localize_strings.id;


--
-- Name: record_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.record_items (
    id integer NOT NULL,
    created_at timestamp without time zone,
    key character varying(256) NOT NULL,
    record_id integer NOT NULL,
    sort_order integer,
    updated_at timestamp without time zone,
    value character varying(256) NOT NULL,
    value_en character varying(256)
);


ALTER TABLE public.record_items OWNER TO default;

--
-- Name: record_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.record_items_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.record_items_id_seq OWNER TO default;

--
-- Name: record_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.record_items_id_seq OWNED BY public.record_items.id;


--
-- Name: records; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.records (
    id integer NOT NULL,
    created_at timestamp without time zone,
    label character varying(256) NOT NULL,
    laben_en boolean,
    name character varying(256) NOT NULL,
    note text,
    project_id integer NOT NULL,
    sort_order integer,
    updated_at timestamp without time zone
);


ALTER TABLE public.records OWNER TO default;

--
-- Name: records_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.records_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.records_id_seq OWNER TO default;

--
-- Name: records_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.records_id_seq OWNED BY public.records.id;


--
-- Name: relation_databases; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.relation_databases (
    id integer NOT NULL,
    created_at timestamp without time zone,
    old_database_id integer NOT NULL,
    project_id integer NOT NULL,
    sort_order integer,
    updated_at timestamp without time zone
);


ALTER TABLE public.relation_databases OWNER TO default;

--
-- Name: relation_databases_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.relation_databases_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.relation_databases_id_seq OWNER TO default;

--
-- Name: relation_databases_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.relation_databases_id_seq OWNED BY public.relation_databases.id;


--
-- Name: routes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.routes (
    id integer NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone,
    sort_order integer,
    controller character varying,
    action character varying(256),
    method character varying(8) NOT NULL,
    uri character varying(256),
    page_id integer NOT NULL,
    middleware character varying(256)
);


ALTER TABLE public.routes OWNER TO default;

--
-- Name: TABLE routes; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.routes IS 'ルート';


--
-- Name: COLUMN routes.id; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.routes.id IS 'ID';


--
-- Name: COLUMN routes.created_at; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.routes.created_at IS '作成日';


--
-- Name: COLUMN routes.updated_at; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.routes.updated_at IS '更新日';


--
-- Name: COLUMN routes.sort_order; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.routes.sort_order IS '並び順';


--
-- Name: COLUMN routes.controller; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.routes.controller IS 'Controller';


--
-- Name: COLUMN routes.action; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.routes.action IS 'Action';


--
-- Name: COLUMN routes.method; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.routes.method IS 'メソッド';


--
-- Name: COLUMN routes.uri; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.routes.uri IS 'URI';


--
-- Name: COLUMN routes.page_id; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.routes.page_id IS 'ページ';


--
-- Name: COLUMN routes.middleware; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.routes.middleware IS 'MIddleware';


--
-- Name: routes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.routes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.routes_id_seq OWNER TO default;

--
-- Name: routes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.routes_id_seq OWNED BY public.routes.id;


--
-- Name: user_project_settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_project_settings (
    id integer NOT NULL,
    created_at timestamp without time zone,
    group_name character varying(256),
    project_id integer NOT NULL,
    project_path character varying(256) NOT NULL,
    sort_order integer,
    updated_at timestamp without time zone,
    user_id integer NOT NULL,
    user_name character varying(256)
);


ALTER TABLE public.user_project_settings OWNER TO default;

--
-- Name: user_project_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.user_project_settings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.user_project_settings_id_seq OWNER TO default;

--
-- Name: user_project_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.user_project_settings_id_seq OWNED BY public.user_project_settings.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id integer NOT NULL,
    birthday_at timestamp without time zone,
    created_at timestamp without time zone,
    email character varying(256),
    first_name character varying(64),
    first_name_kana character varying(64),
    last_name character varying(64),
    last_name_kana character varying(64),
    login_name character varying(64),
    memo text,
    password character varying(256),
    sort_order smallint,
    tmp_password character varying(256),
    updated_at timestamp without time zone
);


ALTER TABLE public.users OWNER TO default;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO default;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: view_item_group_members; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.view_item_group_members (
    id integer NOT NULL,
    created_at timestamp without time zone,
    sort_order integer,
    updated_at timestamp without time zone,
    view_item_group_id integer NOT NULL,
    view_item_id integer NOT NULL
);


ALTER TABLE public.view_item_group_members OWNER TO default;

--
-- Name: view_item_group_members_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.view_item_group_members_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.view_item_group_members_id_seq OWNER TO default;

--
-- Name: view_item_group_members_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.view_item_group_members_id_seq OWNED BY public.view_item_group_members.id;


--
-- Name: view_item_groups; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.view_item_groups (
    id integer NOT NULL,
    created_at timestamp without time zone,
    name character varying(256),
    sort_order integer,
    updated_at timestamp without time zone,
    view_id integer NOT NULL
);


ALTER TABLE public.view_item_groups OWNER TO default;

--
-- Name: view_item_groups_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.view_item_groups_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.view_item_groups_id_seq OWNER TO default;

--
-- Name: view_item_groups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.view_item_groups_id_seq OWNED BY public.view_item_groups.id;


--
-- Name: view_item_models; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.view_item_models (
    id integer NOT NULL,
    created_at timestamp without time zone,
    is_id_index boolean,
    page_id integer,
    sort_order integer,
    updated_at timestamp without time zone,
    value_model_id integer,
    view_item_id integer,
    where_model_id integer
);


ALTER TABLE public.view_item_models OWNER TO default;

--
-- Name: view_item_models_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.view_item_models_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.view_item_models_id_seq OWNER TO default;

--
-- Name: view_item_models_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.view_item_models_id_seq OWNED BY public.view_item_models.id;


--
-- Name: view_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.view_items (
    id integer NOT NULL,
    attribute_id integer,
    created_at timestamp without time zone,
    css_class boolean,
    csv character varying(256),
    form_model_id integer,
    form_type character varying(256),
    label character varying(256),
    label_column text,
    link character varying(256),
    link_param_id_attribute_id integer,
    localize_string_id integer,
    note text,
    page_id integer,
    sort_order integer,
    updated_at timestamp without time zone,
    value_column character varying(256),
    view_id integer NOT NULL,
    where_attribute_id integer,
    where_model_id integer,
    where_order text,
    where_string text
);


ALTER TABLE public.view_items OWNER TO default;

--
-- Name: view_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.view_items_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.view_items_id_seq OWNER TO default;

--
-- Name: view_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.view_items_id_seq OWNED BY public.view_items.id;


--
-- Name: views; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.views (
    id integer NOT NULL,
    created_at timestamp without time zone,
    is_overwrite boolean,
    label character varying(256),
    label_width integer,
    name character varying(256) NOT NULL,
    note text,
    page_id integer NOT NULL,
    sort_order integer,
    updated_at timestamp without time zone
);


ALTER TABLE public.views OWNER TO default;

--
-- Name: views_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.views_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.views_id_seq OWNER TO default;

--
-- Name: views_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.views_id_seq OWNED BY public.views.id;


--
-- Name: admins id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admins ALTER COLUMN id SET DEFAULT nextval('public.admins_id_seq'::regclass);


--
-- Name: api_actions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.api_actions ALTER COLUMN id SET DEFAULT nextval('public.api_actions_id_seq'::regclass);


--
-- Name: api_groups id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.api_groups ALTER COLUMN id SET DEFAULT nextval('public.api_groups_id_seq'::regclass);


--
-- Name: api_params id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.api_params ALTER COLUMN id SET DEFAULT nextval('public.api_params_id_seq'::regclass);


--
-- Name: apis id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.apis ALTER COLUMN id SET DEFAULT nextval('public.apis_id_seq'::regclass);


--
-- Name: attributes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attributes ALTER COLUMN id SET DEFAULT nextval('public.attributes_id_seq'::regclass);


--
-- Name: databases id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.databases ALTER COLUMN id SET DEFAULT nextval('public.databases_id_seq'::regclass);


--
-- Name: langs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.langs ALTER COLUMN id SET DEFAULT nextval('public.langs_id_seq'::regclass);


--
-- Name: localize_strings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.localize_strings ALTER COLUMN id SET DEFAULT nextval('public.localize_strings_id_seq'::regclass);


--
-- Name: menus id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.menus ALTER COLUMN id SET DEFAULT nextval('public.menus_id_seq'::regclass);


--
-- Name: models id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.models ALTER COLUMN id SET DEFAULT nextval('public.models_id_seq'::regclass);


--
-- Name: page_filters id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.page_filters ALTER COLUMN id SET DEFAULT nextval('public.page_filters_id_seq'::regclass);


--
-- Name: page_models id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.page_models ALTER COLUMN id SET DEFAULT nextval('public.page_models_id_seq'::regclass);


--
-- Name: pages id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pages ALTER COLUMN id SET DEFAULT nextval('public.pages_id_seq'::regclass);


--
-- Name: projects id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.projects ALTER COLUMN id SET DEFAULT nextval('public.projects_id_seq'::regclass);


--
-- Name: public_localize_strings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.public_localize_strings ALTER COLUMN id SET DEFAULT nextval('public.public_localize_strings_id_seq'::regclass);


--
-- Name: record_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.record_items ALTER COLUMN id SET DEFAULT nextval('public.record_items_id_seq'::regclass);


--
-- Name: records id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.records ALTER COLUMN id SET DEFAULT nextval('public.records_id_seq'::regclass);


--
-- Name: relation_databases id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.relation_databases ALTER COLUMN id SET DEFAULT nextval('public.relation_databases_id_seq'::regclass);


--
-- Name: routes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.routes ALTER COLUMN id SET DEFAULT nextval('public.routes_id_seq'::regclass);


--
-- Name: user_project_settings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_project_settings ALTER COLUMN id SET DEFAULT nextval('public.user_project_settings_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: view_item_group_members id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_item_group_members ALTER COLUMN id SET DEFAULT nextval('public.view_item_group_members_id_seq'::regclass);


--
-- Name: view_item_groups id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_item_groups ALTER COLUMN id SET DEFAULT nextval('public.view_item_groups_id_seq'::regclass);


--
-- Name: view_item_models id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_item_models ALTER COLUMN id SET DEFAULT nextval('public.view_item_models_id_seq'::regclass);


--
-- Name: view_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_items ALTER COLUMN id SET DEFAULT nextval('public.view_items_id_seq'::regclass);


--
-- Name: views id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.views ALTER COLUMN id SET DEFAULT nextval('public.views_id_seq'::regclass);


--
-- Data for Name: admins; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.admins (id, created_at, email, first_name, last_name, login_name, memo, password, sort_order, tmp_password, updated_at) FROM stdin;
\.


--
-- Data for Name: api_actions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.api_actions (id, api_id, created_at, label, name, note, sort_order, updated_at) FROM stdin;
\.


--
-- Data for Name: api_groups; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.api_groups (id, created_at, name, sort_order, updated_at) FROM stdin;
\.


--
-- Data for Name: api_params; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.api_params (id, api_action_id, created_at, name, note, sort_order, type, updated_at) FROM stdin;
\.


--
-- Data for Name: apis; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.apis (id, api_group_id, created_at, label, name, note, project_id, sort_order, updated_at) FROM stdin;
\.


--
-- Data for Name: attributes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.attributes (id, attnum, attrelid, created_at, csv, default_value, delete_action, fk_attribute_id, is_array, is_lock, is_primary_key, is_required, is_unique, label, length, model_id, name, note, old_attribute_id, old_name, sort_order, type, update_action, updated_at) FROM stdin;
14	2	16387	2019-08-29 12:24:05.767127	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	3	created_at	\N	\N	\N	\N	timestamp	\N	\N
15	3	16387	2019-08-29 12:24:05.784728	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	3	email	\N	\N	\N	\N	varchar	\N	\N
16	4	16387	2019-08-29 12:24:05.809306	\N	\N	\N	\N	\N	\N	f	f	\N	\N	64	3	first_name	\N	\N	\N	\N	varchar	\N	\N
17	1	16387	2019-08-29 12:24:05.829509	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	3	id	\N	\N	\N	\N	int4	\N	\N
18	5	16387	2019-08-29 12:24:05.848832	\N	\N	\N	\N	\N	\N	f	f	\N	\N	64	3	last_name	\N	\N	\N	\N	varchar	\N	\N
19	6	16387	2019-08-29 12:24:05.880898	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	3	login_name	\N	\N	\N	\N	varchar	\N	\N
20	7	16387	2019-08-29 12:24:05.897371	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	3	memo	\N	\N	\N	\N	text	\N	\N
21	8	16387	2019-08-29 12:24:05.91585	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	3	password	\N	\N	\N	\N	varchar	\N	\N
22	9	16387	2019-08-29 12:24:05.931375	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	3	sort_order	\N	\N	\N	\N	int2	\N	\N
23	10	16387	2019-08-29 12:24:05.947398	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	3	tmp_password	\N	\N	\N	\N	varchar	\N	\N
24	11	16387	2019-08-29 12:24:05.962935	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	3	updated_at	\N	\N	\N	\N	timestamp	\N	\N
26	3	16409	2019-08-29 12:24:06.043122	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	4	created_at	\N	\N	\N	\N	timestamp	\N	\N
27	1	16409	2019-08-29 12:24:06.056807	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	4	id	\N	\N	\N	\N	int4	\N	\N
28	4	16409	2019-08-29 12:24:06.070888	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	4	label	\N	\N	\N	\N	varchar	\N	\N
29	5	16409	2019-08-29 12:24:06.084338	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	4	name	\N	\N	\N	\N	varchar	\N	\N
30	6	16409	2019-08-29 12:24:06.098213	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	4	note	\N	\N	\N	\N	text	\N	\N
31	7	16409	2019-08-29 12:24:06.112524	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	4	sort_order	\N	\N	\N	\N	int4	\N	\N
32	8	16409	2019-08-29 12:24:06.126052	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	4	updated_at	\N	\N	\N	\N	timestamp	\N	\N
33	2	16420	2019-08-29 12:24:06.181465	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	5	created_at	\N	\N	\N	\N	timestamp	\N	\N
34	1	16420	2019-08-29 12:24:06.195677	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	5	id	\N	\N	\N	\N	int4	\N	\N
35	3	16420	2019-08-29 12:24:06.210333	\N	\N	\N	\N	\N	\N	f	f	\N	\N	64	5	name	\N	\N	\N	\N	varchar	\N	\N
36	4	16420	2019-08-29 12:24:06.224485	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	5	sort_order	\N	\N	\N	\N	int4	\N	\N
37	5	16420	2019-08-29 12:24:06.237096	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	5	updated_at	\N	\N	\N	\N	timestamp	\N	\N
38	2	16428	2019-08-29 12:24:06.29434	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	6	api_action_id	\N	\N	\N	\N	int4	\N	\N
39	3	16428	2019-08-29 12:24:06.309626	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	6	created_at	\N	\N	\N	\N	timestamp	\N	\N
40	1	16428	2019-08-29 12:24:06.323669	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	6	id	\N	\N	\N	\N	int4	\N	\N
41	4	16428	2019-08-29 12:24:06.33739	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	6	name	\N	\N	\N	\N	varchar	\N	\N
42	5	16428	2019-08-29 12:24:06.350849	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	6	note	\N	\N	\N	\N	text	\N	\N
43	6	16428	2019-08-29 12:24:06.364604	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	6	sort_order	\N	\N	\N	\N	int4	\N	\N
44	7	16428	2019-08-29 12:24:06.378773	\N	\N	\N	\N	\N	\N	f	f	\N	\N	16	6	type	\N	\N	\N	\N	varchar	\N	\N
45	8	16428	2019-08-29 12:24:06.391562	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	6	updated_at	\N	\N	\N	\N	timestamp	\N	\N
47	3	16398	2019-08-29 12:24:06.460829	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	7	created_at	\N	\N	\N	\N	timestamp	\N	\N
48	1	16398	2019-08-29 12:24:06.476034	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	7	id	\N	\N	\N	\N	int4	\N	\N
49	4	16398	2019-08-29 12:24:06.489913	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	7	label	\N	\N	\N	\N	varchar	\N	\N
50	5	16398	2019-08-29 12:24:06.502373	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	7	name	\N	\N	\N	\N	varchar	\N	\N
51	6	16398	2019-08-29 12:24:06.516041	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	7	note	\N	\N	\N	\N	text	\N	\N
53	8	16398	2019-08-29 12:24:06.54324	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	7	sort_order	\N	\N	\N	\N	int4	\N	\N
54	9	16398	2019-08-29 12:24:06.557305	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	7	updated_at	\N	\N	\N	\N	timestamp	\N	\N
57	6	16439	2019-08-29 12:24:06.644962	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	8	default_value	\N	\N	\N	\N	varchar	\N	\N
58	4	16439	2019-08-29 12:24:06.658961	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	8	created_at	\N	\N	\N	\N	timestamp	\N	\N
59	23	16439	2019-08-29 12:24:06.673512	\N	\N	\N	\N	\N	\N	f	f	\N	\N	32	8	update_action	\N	\N	\N	\N	varchar	\N	\N
61	24	16439	2019-08-29 12:24:06.700339	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	8	updated_at	\N	\N	\N	\N	timestamp	\N	\N
62	9	16439	2019-08-29 12:24:06.714264	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	8	is_array	\N	\N	\N	\N	bool	\N	\N
63	10	16439	2019-08-29 12:24:06.727043	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	8	is_lock	\N	\N	\N	\N	bool	\N	\N
64	11	16439	2019-08-29 12:24:06.741555	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	8	is_primary_key	\N	\N	\N	\N	bool	\N	\N
65	13	16439	2019-08-29 12:24:06.754559	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	8	is_unique	\N	\N	\N	\N	bool	\N	\N
66	12	16439	2019-08-29 12:24:06.767279	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	8	is_required	\N	\N	\N	\N	bool	\N	\N
67	14	16439	2019-08-29 12:24:06.781746	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	8	label	\N	\N	\N	\N	varchar	\N	\N
69	15	16439	2019-08-29 12:24:06.810162	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	8	length	\N	\N	\N	\N	int4	\N	\N
71	8	16439	2019-08-29 12:24:06.83756	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	8	fk_attribute_id	\N	\N	\N	\N	int4	\N	\N
72	20	16439	2019-08-29 12:24:06.850868	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	8	old_name	\N	\N	\N	\N	varchar	\N	\N
76	22	16439	2019-08-29 12:24:06.908674	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	8	type	\N	\N	\N	\N	varchar	\N	\N
77	21	16439	2019-08-29 12:24:06.922175	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	8	sort_order	\N	\N	\N	\N	int4	\N	\N
82	19	16439	2019-08-29 12:24:06.995337	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	8	old_attribute_id	\N	\N	\N	\N	int4	\N	\N
25	2	16409	2019-08-29 12:24:06.026513	\N	\N	a	48	\N	\N	f	t	\N	\N	\N	4	api_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:10.701716
52	7	16398	2019-08-29 12:24:06.530221	\N	\N	a	172	\N	\N	f	t	\N	\N	\N	7	project_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:10.759755
46	2	16398	2019-08-29 12:24:06.446824	\N	\N	a	34	\N	\N	f	f	\N	\N	\N	7	api_group_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:10.79143
68	16	16439	2019-08-29 12:24:06.795663	\N	\N	c	119	\N	\N	f	t	\N	\N	\N	8	model_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:10.829936
86	2	16450	2019-08-29 12:24:07.094223	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	9	created_at	\N	\N	\N	\N	timestamp	\N	\N
87	3	16450	2019-08-29 12:24:07.109361	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	9	current_version	\N	\N	\N	\N	int4	\N	\N
88	4	16450	2019-08-29 12:24:07.122034	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	9	hostname	\N	\N	\N	\N	varchar	\N	\N
89	1	16450	2019-08-29 12:24:07.135772	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	9	id	\N	\N	\N	\N	int4	\N	\N
90	5	16450	2019-08-29 12:24:07.149158	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	9	is_lock	\N	\N	\N	\N	bool	\N	\N
91	6	16450	2019-08-29 12:24:07.162034	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	9	name	\N	\N	\N	\N	varchar	\N	\N
92	7	16450	2019-08-29 12:24:07.175446	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	9	port	\N	\N	\N	\N	int4	\N	\N
93	8	16450	2019-08-29 12:24:07.192427	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	9	type	\N	\N	\N	\N	varchar	\N	\N
94	9	16450	2019-08-29 12:24:07.206442	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	9	updated_at	\N	\N	\N	\N	timestamp	\N	\N
95	10	16450	2019-08-29 12:24:07.219668	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	9	user_name	\N	\N	\N	\N	varchar	\N	\N
96	2	16461	2019-08-29 12:24:07.27527	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	10	created_at	\N	\N	\N	\N	timestamp	\N	\N
97	1	16461	2019-08-29 12:24:07.288638	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	10	id	\N	\N	\N	\N	int4	\N	\N
98	3	16461	2019-08-29 12:24:07.302374	\N	\N	\N	\N	\N	\N	f	t	\N	\N	8	10	lang	\N	\N	\N	\N	varchar	\N	\N
99	4	16461	2019-08-29 12:24:07.316875	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	10	name	\N	\N	\N	\N	varchar	\N	\N
100	5	16461	2019-08-29 12:24:07.33108	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	10	sort_order	\N	\N	\N	\N	int4	\N	\N
101	6	16461	2019-08-29 12:24:07.344477	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	10	updated_at	\N	\N	\N	\N	timestamp	\N	\N
102	2	16469	2019-08-29 12:24:07.398765	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	11	created_at	\N	\N	\N	\N	timestamp	\N	\N
103	1	16469	2019-08-29 12:24:07.412877	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	11	id	\N	\N	\N	\N	int4	\N	\N
104	3	16469	2019-08-29 12:24:07.426425	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	11	label	\N	\N	\N	\N	text	\N	\N
105	4	16469	2019-08-29 12:24:07.442537	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	11	name	\N	\N	\N	\N	varchar	\N	\N
107	6	16469	2019-08-29 12:24:07.470534	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	11	sort_order	\N	\N	\N	\N	int4	\N	\N
108	7	16469	2019-08-29 12:24:07.48355	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	11	updated_at	\N	\N	\N	\N	timestamp	\N	\N
109	2	16480	2019-08-29 12:24:07.54174	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	12	created_at	\N	\N	\N	\N	timestamp	\N	\N
110	1	16480	2019-08-29 12:24:07.559173	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	12	id	\N	\N	\N	\N	int4	\N	\N
111	3	16480	2019-08-29 12:24:07.573944	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	12	is_provide	\N	\N	\N	\N	bool	\N	\N
112	4	16480	2019-08-29 12:24:07.587691	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	12	name	\N	\N	\N	\N	varchar	\N	\N
113	5	16480	2019-08-29 12:24:07.600406	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	12	sort_order	\N	\N	\N	\N	int4	\N	\N
114	6	16480	2019-08-29 12:24:07.614727	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	12	updated_at	\N	\N	\N	\N	timestamp	\N	\N
116	3	16488	2019-08-29 12:24:07.68471	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	13	created_at	\N	\N	\N	\N	timestamp	\N	\N
117	4	16488	2019-08-29 12:24:07.69929	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	13	csv	\N	\N	\N	\N	varchar	\N	\N
118	5	16488	2019-08-29 12:24:07.712358	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	13	entity_name	\N	\N	\N	\N	varchar	\N	\N
119	1	16488	2019-08-29 12:24:07.725432	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	13	id	\N	\N	\N	\N	int4	\N	\N
121	7	16488	2019-08-29 12:24:07.752524	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	13	is_lock	\N	\N	\N	\N	bool	\N	\N
122	8	16488	2019-08-29 12:24:07.76575	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	13	is_none_id_column	\N	\N	\N	\N	bool	\N	\N
123	9	16488	2019-08-29 12:24:07.77979	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	13	is_unenable	\N	\N	\N	\N	bool	\N	\N
124	10	16488	2019-08-29 12:24:07.792464	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	13	label	\N	\N	\N	\N	varchar	\N	\N
125	11	16488	2019-08-29 12:24:07.805755	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	13	name	\N	\N	\N	\N	varchar	\N	\N
126	12	16488	2019-08-29 12:24:07.818779	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	13	note	\N	\N	\N	\N	text	\N	\N
127	13	16488	2019-08-29 12:24:07.83188	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	13	old_database_id	\N	\N	\N	\N	int4	\N	\N
128	14	16488	2019-08-29 12:24:07.845944	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	13	old_name	\N	\N	\N	\N	varchar	\N	\N
129	15	16488	2019-08-29 12:24:07.860496	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	13	pg_class_id	\N	\N	\N	\N	int4	\N	\N
131	17	16488	2019-08-29 12:24:07.889114	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	13	relfilenode	\N	\N	\N	\N	int4	\N	\N
132	18	16488	2019-08-29 12:24:07.90162	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	13	sort_order	\N	\N	\N	\N	int4	\N	\N
133	19	16488	2019-08-29 12:24:07.915678	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	13	sub_table_name	\N	\N	\N	\N	varchar	\N	\N
134	20	16488	2019-08-29 12:24:07.930331	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	13	updated_at	\N	\N	\N	\N	timestamp	\N	\N
135	2	16510	2019-08-29 12:24:07.988119	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	14	attribute_id	\N	\N	\N	\N	int4	\N	\N
136	3	16510	2019-08-29 12:24:08.001283	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	14	created_at	\N	\N	\N	\N	timestamp	\N	\N
137	4	16510	2019-08-29 12:24:08.014394	\N	\N	\N	\N	\N	\N	f	f	\N	\N	8	14	equal_sign	\N	\N	\N	\N	varchar	\N	\N
138	1	16510	2019-08-29 12:24:08.029061	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	14	id	\N	\N	\N	\N	int4	\N	\N
140	6	16510	2019-08-29 12:24:08.056746	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	14	sort_order	\N	\N	\N	\N	int4	\N	\N
141	7	16510	2019-08-29 12:24:08.070522	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	14	updated_at	\N	\N	\N	\N	timestamp	\N	\N
142	8	16510	2019-08-29 12:24:08.083464	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	14	value	\N	\N	\N	\N	varchar	\N	\N
143	2	16518	2019-08-29 12:24:08.140879	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	15	created_at	\N	\N	\N	\N	timestamp	\N	\N
144	1	16518	2019-08-29 12:24:08.154361	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	15	id	\N	\N	\N	\N	int4	\N	\N
145	3	16518	2019-08-29 12:24:08.16846	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	15	is_fetch_list_values	\N	\N	\N	\N	bool	\N	\N
146	4	16518	2019-08-29 12:24:08.18216	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	15	is_request_session	\N	\N	\N	\N	bool	\N	\N
149	7	16518	2019-08-29 12:24:08.228474	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	15	sort_order	\N	\N	\N	\N	int4	\N	\N
150	8	16518	2019-08-29 12:24:08.241628	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	15	updated_at	\N	\N	\N	\N	timestamp	\N	\N
151	9	16518	2019-08-29 12:24:08.254506	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	15	where_model_id	\N	\N	\N	\N	int4	\N	\N
152	2	16499	2019-08-29 12:24:08.311886	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	16	class_name	\N	\N	\N	\N	varchar	\N	\N
153	3	16499	2019-08-29 12:24:08.32594	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	16	created_at	\N	\N	\N	\N	timestamp	\N	\N
154	4	16499	2019-08-29 12:24:08.340396	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	16	entity_name	\N	\N	\N	\N	varchar	\N	\N
155	1	16499	2019-08-29 12:24:08.354159	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	16	id	\N	\N	\N	\N	int4	\N	\N
156	5	16499	2019-08-29 12:24:08.367883	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	16	is_overwrite	\N	\N	\N	\N	bool	\N	\N
157	6	16499	2019-08-29 12:24:08.381153	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	16	label	\N	\N	\N	\N	varchar	\N	\N
158	7	16499	2019-08-29 12:24:08.395484	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	16	list_sort_order_columns	\N	\N	\N	\N	text	\N	\N
160	9	16499	2019-08-29 12:24:08.423371	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	16	name	\N	\N	\N	\N	varchar	\N	\N
161	10	16499	2019-08-29 12:24:08.43661	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	16	note	\N	\N	\N	\N	text	\N	\N
164	13	16499	2019-08-29 12:24:08.480822	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	16	sort_order	\N	\N	\N	\N	int4	\N	\N
165	14	16499	2019-08-29 12:24:08.494009	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	16	updated_at	\N	\N	\N	\N	timestamp	\N	\N
166	15	16499	2019-08-29 12:24:08.508386	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	16	view_name	\N	\N	\N	\N	varchar	\N	\N
168	2	16526	2019-08-29 12:24:08.579969	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	17	created_at	\N	\N	\N	\N	timestamp	\N	\N
169	3	16526	2019-08-29 12:24:08.5936	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	17	database_id	\N	\N	\N	\N	int4	\N	\N
170	4	16526	2019-08-29 12:24:08.607885	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	17	entity_name	\N	\N	\N	\N	varchar	\N	\N
171	5	16526	2019-08-29 12:24:08.621673	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	17	external_project_id	\N	\N	\N	\N	int4	\N	\N
172	1	16526	2019-08-29 12:24:08.636021	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	17	id	\N	\N	\N	\N	int4	\N	\N
173	6	16526	2019-08-29 12:24:08.652617	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	17	is_export_external_model	\N	\N	\N	\N	bool	\N	\N
174	7	16526	2019-08-29 12:24:08.665921	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	17	name	\N	\N	\N	\N	varchar	\N	\N
175	8	16526	2019-08-29 12:24:08.67997	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	17	sort_order	\N	\N	\N	\N	int4	\N	\N
176	9	16526	2019-08-29 12:24:08.695542	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	17	updated_at	\N	\N	\N	\N	timestamp	\N	\N
177	10	16526	2019-08-29 12:24:08.70871	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	17	url	\N	\N	\N	\N	varchar	\N	\N
130	16	16488	2019-08-29 12:24:07.875878	\N	\N	c	172	\N	\N	f	t	\N	\N	\N	13	project_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:10.933326
147	5	16518	2019-08-29 12:24:08.195504	\N	\N	a	119	\N	\N	f	t	\N	\N	\N	15	model_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:11.038304
148	6	16518	2019-08-29 12:24:08.21332	\N	\N	a	155	\N	\N	f	t	\N	\N	\N	15	page_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:11.071346
159	8	16499	2019-08-29 12:24:08.408816	\N	\N	a	119	\N	\N	f	f	\N	\N	\N	16	model_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:11.109657
162	11	16499	2019-08-29 12:24:08.451025	\N	\N	a	155	\N	\N	f	f	\N	\N	\N	16	parent_page_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:11.141486
167	16	16499	2019-08-29 12:24:08.521756	\N	\N	a	119	\N	\N	f	f	\N	\N	\N	16	where_model_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:11.203771
115	2	16488	2019-08-29 12:24:07.671358	\N		\N	\N	\N	\N	f	t	\N		256	13	class_name	\N	\N		\N	varchar	\N	2019-12-15 21:43:47.297875
120	6	16488	2019-08-29 12:24:07.738431	\N		\N	\N	\N	\N	f	f	\N		256	13	id_column_name	\N	\N		\N	varchar	\N	2019-12-15 21:44:14.4151
178	2	16537	2019-08-29 12:24:08.764306	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	18	created_at	\N	\N	\N	\N	timestamp	\N	\N
179	1	16537	2019-08-29 12:24:08.77709	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	18	id	\N	\N	\N	\N	int4	\N	\N
180	3	16537	2019-08-29 12:24:08.791393	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	18	label	\N	\N	\N	\N	text	\N	\N
181	4	16537	2019-08-29 12:24:08.804676	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	18	name	\N	\N	\N	\N	varchar	\N	\N
182	5	16537	2019-08-29 12:24:08.819003	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	18	sort_order	\N	\N	\N	\N	int4	\N	\N
183	6	16537	2019-08-29 12:24:08.831822	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	18	updated_at	\N	\N	\N	\N	timestamp	\N	\N
184	2	16559	2019-08-29 12:24:08.890844	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	19	created_at	\N	\N	\N	\N	timestamp	\N	\N
185	1	16559	2019-08-29 12:24:08.904196	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	19	id	\N	\N	\N	\N	int4	\N	\N
186	3	16559	2019-08-29 12:24:08.918322	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	19	key	\N	\N	\N	\N	varchar	\N	\N
188	5	16559	2019-08-29 12:24:08.945257	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	19	sort_order	\N	\N	\N	\N	int4	\N	\N
189	6	16559	2019-08-29 12:24:08.959421	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	19	updated_at	\N	\N	\N	\N	timestamp	\N	\N
190	7	16559	2019-08-29 12:24:08.974027	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	19	value	\N	\N	\N	\N	varchar	\N	\N
191	8	16559	2019-08-29 12:24:08.988746	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	19	value_en	\N	\N	\N	\N	varchar	\N	\N
192	2	16548	2019-08-29 12:24:09.044883	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	20	created_at	\N	\N	\N	\N	timestamp	\N	\N
193	1	16548	2019-08-29 12:24:09.062144	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	20	id	\N	\N	\N	\N	int4	\N	\N
194	3	16548	2019-08-29 12:24:09.076576	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	20	label	\N	\N	\N	\N	varchar	\N	\N
195	4	16548	2019-08-29 12:24:09.090467	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	20	laben_en	\N	\N	\N	\N	bool	\N	\N
196	5	16548	2019-08-29 12:24:09.103621	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	20	name	\N	\N	\N	\N	varchar	\N	\N
197	6	16548	2019-08-29 12:24:09.116615	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	20	note	\N	\N	\N	\N	text	\N	\N
199	8	16548	2019-08-29 12:24:09.144004	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	20	sort_order	\N	\N	\N	\N	int4	\N	\N
200	9	16548	2019-08-29 12:24:09.156809	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	20	updated_at	\N	\N	\N	\N	timestamp	\N	\N
201	2	16570	2019-08-29 12:24:09.210505	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	21	created_at	\N	\N	\N	\N	timestamp	\N	\N
202	1	16570	2019-08-29 12:24:09.224251	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	21	id	\N	\N	\N	\N	int4	\N	\N
205	5	16570	2019-08-29 12:24:09.268198	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	21	sort_order	\N	\N	\N	\N	int4	\N	\N
206	6	16570	2019-08-29 12:24:09.281598	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	21	updated_at	\N	\N	\N	\N	timestamp	\N	\N
207	2	16589	2019-08-29 12:24:09.338042	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	22	created_at	\N	\N	\N	\N	timestamp	\N	\N
208	3	16589	2019-08-29 12:24:09.351282	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	22	group_name	\N	\N	\N	\N	varchar	\N	\N
209	1	16589	2019-08-29 12:24:09.366365	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	22	id	\N	\N	\N	\N	int4	\N	\N
210	4	16589	2019-08-29 12:24:09.379862	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	22	project_id	\N	\N	\N	\N	int4	\N	\N
211	5	16589	2019-08-29 12:24:09.394763	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	22	project_path	\N	\N	\N	\N	varchar	\N	\N
212	6	16589	2019-08-29 12:24:09.408425	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	22	sort_order	\N	\N	\N	\N	int4	\N	\N
213	7	16589	2019-08-29 12:24:09.422339	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	22	updated_at	\N	\N	\N	\N	timestamp	\N	\N
214	8	16589	2019-08-29 12:24:09.435817	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	22	user_id	\N	\N	\N	\N	int4	\N	\N
215	9	16589	2019-08-29 12:24:09.4488	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	22	user_name	\N	\N	\N	\N	varchar	\N	\N
216	2	16578	2019-08-29 12:24:09.505528	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	23	birthday_at	\N	\N	\N	\N	timestamp	\N	\N
217	3	16578	2019-08-29 12:24:09.5184	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	23	created_at	\N	\N	\N	\N	timestamp	\N	\N
218	4	16578	2019-08-29 12:24:09.532869	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	23	email	\N	\N	\N	\N	varchar	\N	\N
219	5	16578	2019-08-29 12:24:09.548328	\N	\N	\N	\N	\N	\N	f	f	\N	\N	64	23	first_name	\N	\N	\N	\N	varchar	\N	\N
220	6	16578	2019-08-29 12:24:09.561465	\N	\N	\N	\N	\N	\N	f	f	\N	\N	64	23	first_name_kana	\N	\N	\N	\N	varchar	\N	\N
221	1	16578	2019-08-29 12:24:09.576206	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	23	id	\N	\N	\N	\N	int4	\N	\N
222	7	16578	2019-08-29 12:24:09.589403	\N	\N	\N	\N	\N	\N	f	f	\N	\N	64	23	last_name	\N	\N	\N	\N	varchar	\N	\N
223	8	16578	2019-08-29 12:24:09.60356	\N	\N	\N	\N	\N	\N	f	f	\N	\N	64	23	last_name_kana	\N	\N	\N	\N	varchar	\N	\N
224	9	16578	2019-08-29 12:24:09.616991	\N	\N	\N	\N	\N	\N	f	f	\N	\N	64	23	login_name	\N	\N	\N	\N	varchar	\N	\N
225	10	16578	2019-08-29 12:24:09.630286	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	23	memo	\N	\N	\N	\N	text	\N	\N
226	11	16578	2019-08-29 12:24:09.645488	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	23	password	\N	\N	\N	\N	varchar	\N	\N
227	12	16578	2019-08-29 12:24:09.658552	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	23	sort_order	\N	\N	\N	\N	int2	\N	\N
228	13	16578	2019-08-29 12:24:09.672302	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	23	tmp_password	\N	\N	\N	\N	varchar	\N	\N
229	14	16578	2019-08-29 12:24:09.68571	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	23	updated_at	\N	\N	\N	\N	timestamp	\N	\N
230	2	16630	2019-08-29 12:24:09.743529	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	24	created_at	\N	\N	\N	\N	timestamp	\N	\N
231	1	16630	2019-08-29 12:24:09.757615	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	24	id	\N	\N	\N	\N	int4	\N	\N
232	3	16630	2019-08-29 12:24:09.771275	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	24	sort_order	\N	\N	\N	\N	int4	\N	\N
233	4	16630	2019-08-29 12:24:09.786719	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	24	updated_at	\N	\N	\N	\N	timestamp	\N	\N
236	2	16622	2019-08-29 12:24:09.873003	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	25	created_at	\N	\N	\N	\N	timestamp	\N	\N
237	1	16622	2019-08-29 12:24:09.890585	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	25	id	\N	\N	\N	\N	int4	\N	\N
238	3	16622	2019-08-29 12:24:09.904993	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	25	name	\N	\N	\N	\N	varchar	\N	\N
239	4	16622	2019-08-29 12:24:09.918907	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	25	sort_order	\N	\N	\N	\N	int4	\N	\N
240	5	16622	2019-08-29 12:24:09.93201	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	25	updated_at	\N	\N	\N	\N	timestamp	\N	\N
241	6	16622	2019-08-29 12:24:09.94691	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	25	view_id	\N	\N	\N	\N	int4	\N	\N
242	2	16638	2019-08-29 12:24:10.005939	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	26	created_at	\N	\N	\N	\N	timestamp	\N	\N
243	1	16638	2019-08-29 12:24:10.020489	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	26	id	\N	\N	\N	\N	int4	\N	\N
244	3	16638	2019-08-29 12:24:10.038785	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	26	is_id_index	\N	\N	\N	\N	bool	\N	\N
245	4	16638	2019-08-29 12:24:10.054782	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	26	page_id	\N	\N	\N	\N	int4	\N	\N
246	5	16638	2019-08-29 12:24:10.071151	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	26	sort_order	\N	\N	\N	\N	int4	\N	\N
247	6	16638	2019-08-29 12:24:10.087601	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	26	updated_at	\N	\N	\N	\N	timestamp	\N	\N
248	7	16638	2019-08-29 12:24:10.102398	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	26	value_model_id	\N	\N	\N	\N	int4	\N	\N
249	8	16638	2019-08-29 12:24:10.11633	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	26	view_item_id	\N	\N	\N	\N	int4	\N	\N
250	9	16638	2019-08-29 12:24:10.129361	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	26	where_model_id	\N	\N	\N	\N	int4	\N	\N
251	2	16611	2019-08-29 12:24:10.184106	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	27	attribute_id	\N	\N	\N	\N	int4	\N	\N
252	3	16611	2019-08-29 12:24:10.196964	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	27	created_at	\N	\N	\N	\N	timestamp	\N	\N
253	4	16611	2019-08-29 12:24:10.210891	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	27	css_class	\N	\N	\N	\N	bool	\N	\N
254	5	16611	2019-08-29 12:24:10.223419	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	27	csv	\N	\N	\N	\N	varchar	\N	\N
255	6	16611	2019-08-29 12:24:10.237818	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	27	form_model_id	\N	\N	\N	\N	int4	\N	\N
256	7	16611	2019-08-29 12:24:10.251102	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	27	form_type	\N	\N	\N	\N	varchar	\N	\N
257	1	16611	2019-08-29 12:24:10.264511	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	27	id	\N	\N	\N	\N	int4	\N	\N
258	8	16611	2019-08-29 12:24:10.277955	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	27	label	\N	\N	\N	\N	varchar	\N	\N
259	9	16611	2019-08-29 12:24:10.290384	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	27	label_column	\N	\N	\N	\N	text	\N	\N
260	10	16611	2019-08-29 12:24:10.303953	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	27	link	\N	\N	\N	\N	varchar	\N	\N
261	11	16611	2019-08-29 12:24:10.317191	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	27	link_param_id_attribute_id	\N	\N	\N	\N	int4	\N	\N
263	13	16611	2019-08-29 12:24:10.343423	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	27	note	\N	\N	\N	\N	text	\N	\N
265	15	16611	2019-08-29 12:24:10.369764	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	27	sort_order	\N	\N	\N	\N	int4	\N	\N
266	16	16611	2019-08-29 12:24:10.382749	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	27	updated_at	\N	\N	\N	\N	timestamp	\N	\N
267	17	16611	2019-08-29 12:24:10.395411	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	27	value_column	\N	\N	\N	\N	varchar	\N	\N
268	18	16611	2019-08-29 12:24:10.409229	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	27	view_id	\N	\N	\N	\N	int4	\N	\N
269	19	16611	2019-08-29 12:24:10.423649	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	27	where_attribute_id	\N	\N	\N	\N	int4	\N	\N
198	7	16548	2019-08-29 12:24:09.130252	\N	\N	a	172	\N	\N	f	t	\N	\N	\N	20	project_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:11.297584
204	4	16570	2019-08-29 12:24:09.254482	\N	\N	a	172	\N	\N	f	t	\N	\N	\N	21	project_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:11.368185
234	5	16630	2019-08-29 12:24:09.800761	\N	\N	a	237	\N	\N	f	t	\N	\N	\N	24	view_item_group_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:11.431172
235	6	16630	2019-08-29 12:24:09.815468	\N	\N	a	257	\N	\N	f	t	\N	\N	\N	24	view_item_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:11.475065
264	14	16611	2019-08-29 12:24:10.356223	\N	\N	c	155	\N	\N	f	f	\N	\N	\N	27	page_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:11.71163
271	21	16611	2019-08-29 12:24:10.449288	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	27	where_order	\N	\N	\N	\N	text	\N	\N
272	22	16611	2019-08-29 12:24:10.462953	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	27	where_string	\N	\N	\N	\N	text	\N	\N
273	5	16600	2019-08-29 12:24:10.520492	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	28	label_width	\N	\N	\N	\N	int4	\N	\N
276	7	16600	2019-08-29 12:24:10.561269	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	28	note	\N	\N	\N	\N	text	\N	\N
278	9	16600	2019-08-29 12:24:10.587888	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	28	sort_order	\N	\N	\N	\N	int4	\N	\N
279	10	16600	2019-08-29 12:24:10.600661	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	28	updated_at	\N	\N	\N	\N	timestamp	\N	\N
280	8	16600	2019-08-29 12:24:10.615618	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	28	page_id	\N	\N	\N	\N	int4	\N	\N
281	6	16600	2019-08-29 12:24:10.628234	\N	\N	\N	\N	\N	\N	f	t	\N	\N	256	28	name	\N	\N	\N	\N	varchar	\N	\N
106	5	16469	2019-08-29 12:24:07.456518	\N	\N	a	172	\N	\N	f	t	\N	\N	\N	11	project_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:10.887783
139	5	16510	2019-08-29 12:24:08.042636	\N	\N	a	155	\N	\N	f	t	\N	\N	\N	14	page_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:10.99638
163	12	16499	2019-08-29 12:24:08.465751	\N	\N	a	172	\N	\N	f	t	\N	\N	\N	16	project_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:11.171848
187	4	16559	2019-08-29 12:24:08.931445	\N	\N	c	193	\N	\N	f	t	\N	\N	\N	19	record_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:11.25811
203	3	16570	2019-08-29 12:24:09.240737	\N	\N	a	89	\N	\N	f	t	\N	\N	\N	21	old_database_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:11.336741
262	12	16611	2019-08-29 12:24:10.329897	\N	\N	c	103	\N	\N	f	f	\N	\N	\N	27	localize_string_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:11.64479
270	20	16611	2019-08-29 12:24:10.436768	\N	\N	c	119	\N	\N	f	f	\N	\N	\N	27	where_model_id	\N	\N	\N	\N	int4	a	2019-08-29 12:24:11.676112
283	2	16833	2019-08-29 12:25:38.950756	\N	\N	\N	\N	\N	\N	f	t	\N	作成日	\N	29	created_at	\N	\N	\N	\N	timestamp	\N	\N
284	1	16833	2019-08-29 12:25:38.969218	\N	\N	\N	\N	\N	\N	f	t	\N	ID	\N	29	id	\N	\N	\N	\N	int4	\N	\N
285	4	16833	2019-08-29 12:25:38.989497	\N	\N	\N	\N	\N	\N	f	f	\N	並び順	\N	29	sort_order	\N	\N	\N	\N	int4	\N	\N
286	3	16833	2019-08-29 12:25:39.005552	\N	\N	\N	\N	\N	\N	f	f	\N	更新日	\N	29	updated_at	\N	\N	\N	\N	timestamp	\N	\N
287	5	16833	2019-08-29 12:26:13.34796	\N	\N	\N	\N	\N	\N	\N	\N	\N	Controller	\N	29	controller	\N	\N	\N	\N	varchar	\N	\N
288	6	16833	2019-08-29 12:26:26.201163	\N	\N	\N	\N	\N	\N	\N	\N	\N	Action	256	29	action	\N	\N	\N	\N	varchar	\N	\N
320	1	25014	2019-12-14 10:09:58.506089	\N	\N	\N	\N	\N	\N	f	t	\N	ID	\N	35	id	\N	\N	\N	\N	int4	\N	\N
289	7	16833	2019-08-29 12:26:53.039607	\N	\N	\N	\N	\N	\N	\N	t	\N	メソッド	8	29	method	\N	\N	\N	\N	varchar	\N	2019-08-29 23:51:08.877165
291	9	16833	2019-08-29 23:50:59.993643	\N	\N	\N	155	\N	\N	\N	t	\N	ページ	\N	29	page_id	\N	\N	\N	\N	int4	\N	2019-08-29 23:51:26.653376
290	8	16833	2019-08-29 12:30:28.370862			\N	\N	\N	\N	\N	\N	\N	URI	256	29	uri		\N		\N	varchar	\N	2019-08-30 00:06:09.973899
292	10	16833	2019-08-30 00:12:24.414547	\N	\N	\N	\N	\N	\N	\N	\N	\N	MIddleware	256	29	middleware	\N	\N	\N	\N	varchar	\N	\N
321	5	25014	2019-12-14 10:09:58.525865	\N	\N	\N	\N	\N	\N	f	t	\N	名	64	35	last_name	\N	\N	\N	\N	varchar	\N	\N
322	8	25014	2019-12-14 10:09:58.55135	\N	\N	\N	\N	\N	\N	f	f	\N	せい	64	35	last_name_kana	\N	\N	\N	\N	varchar	\N	\N
323	4	25014	2019-12-14 10:09:58.571118	\N	\N	\N	\N	\N	\N	f	f	\N	並び順	\N	35	sort_order	\N	\N	\N	\N	int4	\N	\N
324	3	25014	2019-12-14 10:09:58.595592	\N	\N	\N	\N	\N	\N	f	f	\N	更新日	\N	35	updated_at	\N	\N	\N	\N	timestamp	\N	\N
325	2	25025	2019-12-14 11:18:39.02269	\N	\N	\N	\N	\N	\N	f	t	\N	作成日	\N	36	created_at	\N	\N	\N	\N	timestamp	\N	\N
326	1	25025	2019-12-14 11:18:39.044655	\N	\N	\N	\N	\N	\N	f	t	\N	ID	\N	36	id	\N	\N	\N	\N	int4	\N	\N
327	4	25025	2019-12-14 11:18:39.065605	\N	\N	\N	\N	\N	\N	f	f	\N	並び順	\N	36	sort_order	\N	\N	\N	\N	int4	\N	\N
328	3	25025	2019-12-14 11:18:39.085239	\N	\N	\N	\N	\N	\N	f	f	\N	更新日	\N	36	updated_at	\N	\N	\N	\N	timestamp	\N	\N
315	10	25014	2019-12-14 10:09:58.369976	\N	\N	\N	\N	\N	\N	f	t	\N	生年月日	\N	35	birthday_at	\N	\N	\N	\N	timestamp	\N	\N
316	11	25014	2019-12-14 10:09:58.40246	\N	\N	\N	\N	\N	\N	f	t	\N	生徒番号	64	35	code	\N	\N	\N	\N	varchar	\N	\N
317	2	25014	2019-12-14 10:09:58.425943	\N	\N	\N	\N	\N	\N	f	t	\N	作成日	\N	35	created_at	\N	\N	\N	\N	timestamp	\N	\N
318	6	25014	2019-12-14 10:09:58.453501	\N	\N	\N	\N	\N	\N	f	t	\N	姓	64	35	first_name	\N	\N	\N	\N	varchar	\N	\N
319	7	25014	2019-12-14 10:09:58.473222	\N	\N	\N	\N	\N	\N	f	f	\N	めい	64	35	first_name_kana	\N	\N	\N	\N	varchar	\N	\N
330	6	25025	2019-12-14 11:20:09.540107	\N	\N	\N	\N	\N	\N	f	f	\N	レポート内容	\N	36	report	\N	\N	\N	\N	text	\N	2019-12-15 22:07:56.430899
331	7	25025	2019-12-14 11:20:49.007541	\N	\N	\N	\N	\N	\N	f	t	\N	報告日	\N	36	reported_at	\N	\N	\N	\N	timestamp	\N	2019-12-15 22:07:56.472195
329	5	25025	2019-12-14 11:19:02.37366	\N	\N	\N	320	\N	\N	f	t	\N	生徒	\N	36	student_id	\N	\N	\N	\N	int4	\N	2019-12-15 22:07:56.550604
341	8	\N	2019-12-15 23:04:22.695075	\N	\N	\N	\N	\N	\N	f	f	\N	確認	\N	36	is_confirm	\N	\N	\N	\N	bool	\N	2019-12-15 23:14:25.510722
334	2	16439	2019-12-15 22:52:36.911366	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	8	attnum	\N	\N	\N	\N	int4	\N	2019-12-15 23:15:47.614835
335	3	16439	2019-12-15 22:52:47.297845	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	8	attrelid	\N	\N	\N	\N	int4	\N	2019-12-15 23:15:47.675557
340	5	16439	2019-12-15 23:01:36.00605	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	8	csv	\N	\N	\N	\N	varchar	\N	2019-12-15 23:15:47.738408
342	7	16439	2019-12-15 23:15:47.781315	\N	\N	\N	\N	\N	\N	f	f	\N	\N	32	8	delete_action	\N	\N	\N	\N	varchar	\N	\N
343	1	16439	2019-12-15 23:15:47.834419	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	8	id	\N	\N	\N	\N	int4	\N	\N
344	17	16439	2019-12-15 23:15:48.06302	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	8	name	\N	\N	\N	\N	varchar	\N	\N
345	18	16439	2019-12-15 23:15:48.079538	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	8	note	\N	\N	\N	\N	text	\N	\N
336	2	16600	2019-12-15 23:00:38.628099	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	28	created_at	\N	\N	\N	\N	timestamp	\N	2019-12-15 23:27:12.0031
337	1	16600	2019-12-15 23:00:42.412856	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	28	id	\N	\N	\N	\N	int4	\N	2019-12-15 23:27:12.132342
338	3	16600	2019-12-15 23:00:44.468324	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	28	is_overwrite	\N	\N	\N	\N	bool	\N	2019-12-15 23:27:12.174772
339	4	16600	2019-12-15 23:01:01.919456	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	28	label	\N	\N	\N	\N	varchar	\N	2019-12-15 23:27:12.211306
346	2	25076	2019-12-22 16:06:43.973571	\N	\N	\N	\N	\N	\N	f	t	\N	作成日	\N	40	created_at	\N	\N	\N	\N	timestamp	\N	\N
347	1	25076	2019-12-22 16:06:43.994071	\N	\N	\N	\N	\N	\N	f	t	\N	ID	\N	40	id	\N	\N	\N	\N	int4	\N	\N
348	4	25076	2019-12-22 16:06:44.013552	\N	\N	\N	\N	\N	\N	f	f	\N	並び順	\N	40	sort_order	\N	\N	\N	\N	int4	\N	\N
349	3	25076	2019-12-22 16:06:44.032677	\N	\N	\N	\N	\N	\N	f	f	\N	更新日	\N	40	updated_at	\N	\N	\N	\N	timestamp	\N	\N
350	2	25085	2019-12-22 16:07:16.891498	\N	\N	\N	\N	\N	\N	f	t	\N	作成日	\N	41	created_at	\N	\N	\N	\N	timestamp	\N	\N
351	1	25085	2019-12-22 16:07:16.907771	\N	\N	\N	\N	\N	\N	f	t	\N	ID	\N	41	id	\N	\N	\N	\N	int4	\N	\N
352	4	25085	2019-12-22 16:07:16.927016	\N	\N	\N	\N	\N	\N	f	f	\N	並び順	\N	41	sort_order	\N	\N	\N	\N	int4	\N	\N
353	3	25085	2019-12-22 16:07:16.943124	\N	\N	\N	\N	\N	\N	f	f	\N	更新日	\N	41	updated_at	\N	\N	\N	\N	timestamp	\N	\N
354	2	25094	2019-12-22 16:08:30.687741	\N	\N	\N	\N	\N	\N	f	t	\N	作成日	\N	42	created_at	\N	\N	\N	\N	timestamp	\N	\N
355	1	25094	2019-12-22 16:08:30.711068	\N	\N	\N	\N	\N	\N	f	t	\N	ID	\N	42	id	\N	\N	\N	\N	int4	\N	\N
356	4	25094	2019-12-22 16:08:30.730579	\N	\N	\N	\N	\N	\N	f	f	\N	並び順	\N	42	sort_order	\N	\N	\N	\N	int4	\N	\N
357	3	25094	2019-12-22 16:08:30.753972	\N	\N	\N	\N	\N	\N	f	f	\N	更新日	\N	42	updated_at	\N	\N	\N	\N	timestamp	\N	\N
358	2	25103	2019-12-22 16:21:30.626585	\N	\N	\N	\N	\N	\N	f	t	\N	作成日	\N	43	created_at	\N	\N	\N	\N	timestamp	\N	\N
359	1	25103	2019-12-22 16:21:30.647287	\N	\N	\N	\N	\N	\N	f	t	\N	ID	\N	43	id	\N	\N	\N	\N	int4	\N	\N
360	4	25103	2019-12-22 16:21:30.665767	\N	\N	\N	\N	\N	\N	f	f	\N	並び順	\N	43	sort_order	\N	\N	\N	\N	int4	\N	\N
361	3	25103	2019-12-22 16:21:30.685471	\N	\N	\N	\N	\N	\N	f	f	\N	更新日	\N	43	updated_at	\N	\N	\N	\N	timestamp	\N	\N
391	16	25135	2019-12-23 01:06:07.566965	\N	\N	\N	\N	\N	\N	\N	\N	\N	問い合わせ番号	\N	45	no	\N	\N	\N	\N	int8	\N	\N
365	8	25094	2019-12-23 00:48:24.626018	\N	\N	\N	\N	\N	\N	\N	\N	\N	氏名（めい）	64	42	lastname_kana	\N	\N	\N	\N	varchar	\N	\N
364	7	25094	2019-12-23 00:47:43.604466	\N	\N	\N	\N	\N	\N	\N	\N	\N	氏名（せい）	64	42	firstname_kana	\N	\N	\N	\N	varchar	\N	2019-12-23 00:48:30.546653
362	5	25094	2019-12-23 00:46:43.004089			\N	\N	\N	\N	\N	t	\N	氏名（姓）	64	42	firstname		\N		\N	varchar	\N	2019-12-23 00:48:34.507662
363	6	25094	2019-12-23 00:47:08.235242			\N	\N	\N	\N	\N	t	\N	氏名（名）	64	42	lastname		\N		\N	varchar	\N	2019-12-23 00:48:37.63051
366	9	25094	2019-12-23 00:49:27.253868	\N	\N	\N	\N	\N	\N	\N	\N	\N	郵便番号	16	42	postal_code	\N	\N	\N	\N	varchar	\N	\N
367	10	25094	2019-12-23 00:50:25.908088	\N	\N	\N	\N	\N	\N	\N	t	\N	都道府県	\N	42	prefecture	\N	\N	\N	\N	int2	\N	2019-12-23 00:50:33.566112
368	11	25094	2019-12-23 00:51:28.7095	\N	\N	\N	\N	\N	\N	\N	\N	\N	市区町村	64	42	city	\N	\N	\N	\N	varchar	\N	\N
369	12	25094	2019-12-23 00:54:16.92777	\N	\N	\N	\N	\N	\N	\N	\N	\N	電話番号	64	42	 tel	\N	\N	\N	\N	varchar	\N	\N
370	13	25094	2019-12-23 00:54:45.483035	\N	\N	\N	\N	\N	\N	\N	\N	\N	Email	256	42	email	\N	\N	\N	\N	varchar	\N	2019-12-23 00:54:53.153312
381	7	25135	2019-12-23 00:56:07.68109	\N	\N	\N	\N	\N	\N	f	f	\N	氏名（せい）	64	45	firstname_kana	\N	\N	\N	\N	varchar	\N	2019-12-23 00:56:08.026761
392	17	25135	2019-12-23 01:07:26.768465	\N	\N	\N	\N	\N	\N	\N	f	\N	学年	\N	45	grade	\N	\N	\N	\N	int2	\N	2019-12-23 01:09:13.187083
393	18	25135	2019-12-23 01:08:26.657692	\N	\N	\N	\N	\N	\N	\N	t	\N	学校種別	16	45	grade_type	\N	\N	\N	\N	varchar	\N	2019-12-23 01:09:24.096995
382	8	25135	2019-12-23 00:56:07.691521	\N	\N	\N	\N	\N	\N	f	f	\N	氏名（めい）	64	45	lastname_kana	\N	\N	\N	\N	varchar	\N	2019-12-23 00:56:08.131408
394	1	25146	2019-12-28 13:38:04.597475	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	107	contents	\N	\N	\N	\N	text	\N	\N
395	4	25146	2019-12-28 13:38:04.620417	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	107	created_at	\N	\N	\N	\N	timestamp	\N	\N
396	3	25146	2019-12-28 13:38:04.64286	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	107	id	\N	\N	\N	\N	int4	\N	\N
397	2	25146	2019-12-28 13:38:04.666788	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	107	title	\N	\N	\N	\N	varchar	\N	\N
398	5	25146	2019-12-28 13:38:04.689483	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	107	updated_at	\N	\N	\N	\N	timestamp	\N	\N
399	3	25154	2019-12-28 13:38:04.780487	\N	\N	\N	\N	\N	\N	f	t	\N	\N	64	108	admin	\N	\N	\N	\N	varchar	\N	\N
400	9	25154	2019-12-28 13:38:04.800287	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	108	created_at	\N	\N	\N	\N	timestamp	\N	\N
401	1	25154	2019-12-28 13:38:04.818617	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	108	id	\N	\N	\N	\N	int4	\N	\N
375	2	25135	2019-12-23 00:56:07.577769	\N	\N	\N	\N	\N	\N	f	t	\N	作成日	\N	45	created_at	\N	\N	\N	\N	timestamp	\N	2019-12-23 01:00:04.598402
376	1	25135	2019-12-23 00:56:07.609559	\N	\N	\N	\N	\N	\N	f	t	\N	ID	\N	45	id	\N	\N	\N	\N	int4	\N	2019-12-23 01:00:04.70863
377	4	25135	2019-12-23 00:56:07.627793	\N	\N	\N	\N	\N	\N	f	f	\N	並び順	\N	45	sort_order	\N	\N	\N	\N	int4	\N	2019-12-23 01:00:04.847922
378	3	25135	2019-12-23 00:56:07.646185	\N	\N	\N	\N	\N	\N	f	f	\N	更新日	\N	45	updated_at	\N	\N	\N	\N	timestamp	\N	2019-12-23 01:00:04.90645
388	12	25135	2019-12-23 00:56:08.308236	\N	\N	\N	\N	\N	\N	f	f	\N	電話番号	64	45	tel	\N	\N	\N	\N	varchar	\N	2019-12-23 01:01:04.586063
379	5	25135	2019-12-23 00:56:07.659132			\N	\N	\N	\N	f	t	\N	氏名（姓）	64	45	firstname		\N		\N	varchar	\N	2019-12-23 01:01:10.166197
380	6	25135	2019-12-23 00:56:07.669828			\N	\N	\N	\N	f	t	\N	氏名（名）	64	45	lastname		\N		\N	varchar	\N	2019-12-23 01:01:13.066917
387	13	25135	2019-12-23 00:56:07.742768	\N	\N	\N	\N	\N	\N	f	t	\N	Email	256	45	email	\N	\N	\N	\N	varchar	\N	2019-12-23 01:01:17.345872
389	14	25135	2019-12-23 01:03:08.831853	\N	\N	\N	\N	\N	\N	\N	\N	\N	お問い合わせ内容	\N	45	content	\N	\N	\N	\N	text	\N	\N
390	15	25135	2019-12-23 01:03:54.345731	\N	\N	\N	\N	\N	\N	\N	t	\N	予約日	\N	45	reservation_at	\N	\N	\N	\N	timestamp	\N	2019-12-23 01:04:00.880419
384	10	25135	2019-12-23 00:56:07.710888	\N	\N	\N	\N	\N	\N	f	t	\N	都道府県	\N	45	prefecture	\N	\N	\N	\N	int2	\N	2019-12-23 01:04:07.183227
383	9	25135	2019-12-23 00:56:07.701239	\N	\N	\N	\N	\N	\N	f	t	\N	郵便番号	16	45	postal_code	\N	\N	\N	\N	varchar	\N	2019-12-23 01:04:10.739159
385	11	25135	2019-12-23 00:56:07.721992	\N	\N	\N	\N	\N	\N	f	t	\N	市区町村	64	45	city	\N	\N	\N	\N	varchar	\N	2019-12-23 01:04:20.768919
402	7	25154	2019-12-28 13:38:04.839192	\N	\N	\N	\N	\N	\N	f	f	\N	\N	255	108	logo	\N	\N	\N	\N	varchar	\N	\N
403	5	25154	2019-12-28 13:38:04.859533	\N	\N	\N	\N	\N	\N	f	t	\N	\N	64	108	name	\N	\N	\N	\N	varchar	\N	\N
404	4	25154	2019-12-28 13:38:04.877456	\N	\N	\N	\N	\N	\N	f	t	\N	\N	64	108	pass	\N	\N	\N	\N	varchar	\N	\N
405	6	25154	2019-12-28 13:38:04.896948	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	108	profile	\N	\N	\N	\N	text	\N	\N
406	8	25154	2019-12-28 13:38:04.91611	\N	\N	\N	\N	\N	\N	f	f	\N	\N	255	108	style	\N	\N	\N	\N	varchar	\N	\N
407	2	25154	2019-12-28 13:38:04.934587	\N	\N	\N	\N	\N	\N	f	t	\N	\N	128	108	title	\N	\N	\N	\N	varchar	\N	\N
408	10	25154	2019-12-28 13:38:04.954619	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	108	updated_at	\N	\N	\N	\N	timestamp	\N	\N
409	8	25162	2019-12-28 13:38:05.036887	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	109	advertising_id	\N	\N	\N	\N	int4	\N	\N
410	4	25162	2019-12-28 13:38:05.057028	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	109	created_at	\N	\N	\N	\N	timestamp	\N	\N
411	1	25162	2019-12-28 13:38:05.081726	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	109	id	\N	\N	\N	\N	int4	\N	\N
412	7	25162	2019-12-28 13:38:05.102229	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	109	is_labs	\N	\N	\N	\N	bool	\N	\N
413	3	25162	2019-12-28 13:38:05.120694	\N	\N	\N	\N	\N	\N	f	t	\N	\N	255	109	name	\N	\N	\N	\N	varchar	\N	\N
414	6	25162	2019-12-28 13:38:05.140626	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	109	opened	\N	\N	\N	\N	bool	\N	\N
415	2	25162	2019-12-28 13:38:05.159276	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	109	sort	\N	\N	\N	\N	int2	\N	\N
416	5	25162	2019-12-28 13:38:05.17874	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	109	updated_at	\N	\N	\N	\N	timestamp	\N	\N
417	4	25167	2019-12-28 13:38:05.252378	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	110	body	\N	\N	\N	\N	text	\N	\N
418	6	25167	2019-12-28 13:38:05.271079	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	110	created_at	\N	\N	\N	\N	timestamp	\N	\N
419	1	25167	2019-12-28 13:38:05.289698	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	110	id	\N	\N	\N	\N	int4	\N	\N
420	8	25167	2019-12-28 13:38:05.309563	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	110	opened	\N	\N	\N	\N	bool	\N	\N
421	5	25167	2019-12-28 13:38:05.328807	\N	\N	\N	\N	\N	\N	f	t	\N	\N	50	110	password	\N	\N	\N	\N	varchar	\N	\N
422	9	25167	2019-12-28 13:38:05.347675	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	110	posted_at	\N	\N	\N	\N	timestamp	\N	\N
423	3	25167	2019-12-28 13:38:05.366768	\N	\N	\N	\N	\N	\N	f	t	\N	\N	255	110	poster	\N	\N	\N	\N	varchar	\N	\N
424	2	25167	2019-12-28 13:38:05.385147	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	110	topic_id	\N	\N	\N	\N	int4	\N	\N
425	7	25167	2019-12-28 13:38:05.405985	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	110	updated_at	\N	\N	\N	\N	timestamp	\N	\N
426	3	25175	2019-12-28 13:38:05.477316	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	111	body	\N	\N	\N	\N	text	\N	\N
427	6	25175	2019-12-28 13:38:05.496336	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	111	created_at	\N	\N	\N	\N	timestamp	\N	\N
428	5	25175	2019-12-28 13:38:05.514771	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	111	id	\N	\N	\N	\N	int4	\N	\N
429	4	25175	2019-12-28 13:38:05.533507	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	111	opened	\N	\N	\N	\N	bool	\N	\N
430	2	25175	2019-12-28 13:38:05.553016	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	111	posted_at	\N	\N	\N	\N	timestamp	\N	\N
431	1	25175	2019-12-28 13:38:05.573885	\N	\N	\N	\N	\N	\N	f	f	\N	\N	32	111	title	\N	\N	\N	\N	varchar	\N	\N
432	7	25175	2019-12-28 13:38:05.592634	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	111	updated_at	\N	\N	\N	\N	timestamp	\N	\N
433	1	25183	2019-12-28 13:38:05.682233	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	112	body	\N	\N	\N	\N	text	\N	\N
434	9	25183	2019-12-28 13:38:05.704893	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	112	created_at	\N	\N	\N	\N	timestamp	\N	\N
435	7	25183	2019-12-28 13:38:05.724329	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	112	duration	\N	\N	\N	\N	int4	\N	\N
436	11	25183	2019-12-28 13:38:05.743329	\N	\N	\N	\N	\N	\N	f	f	\N	\N	16	112	font_color	\N	\N	\N	\N	varchar	\N	\N
437	12	25183	2019-12-28 13:38:05.761781	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	112	font_size	\N	\N	\N	\N	int4	\N	\N
438	8	25183	2019-12-28 13:38:05.781532	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	112	id	\N	\N	\N	\N	int4	\N	\N
439	6	25183	2019-12-28 13:38:05.800564	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	112	movie_id	\N	\N	\N	\N	int4	\N	\N
440	2	25183	2019-12-28 13:38:05.819372	\N	\N	\N	\N	\N	\N	f	f	\N	\N	16	112	poster	\N	\N	\N	\N	varchar	\N	\N
441	3	25183	2019-12-28 13:38:05.838976	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	112	second	\N	\N	\N	\N	int4	\N	\N
442	10	25183	2019-12-28 13:38:05.857662	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	112	updated_at	\N	\N	\N	\N	timestamp	\N	\N
443	4	25183	2019-12-28 13:38:05.876171	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	112	x	\N	\N	\N	\N	int4	\N	\N
444	5	25183	2019-12-28 13:38:05.89448	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	112	y	\N	\N	\N	\N	int4	\N	\N
445	5	25191	2019-12-28 13:38:05.968755	\N	\N	\N	\N	\N	\N	f	f	\N	\N	64	113	artist	\N	\N	\N	\N	varchar	\N	\N
446	3	25191	2019-12-28 13:38:05.99183	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	113	category_id	\N	\N	\N	\N	int4	\N	\N
447	6	25191	2019-12-28 13:38:06.013634	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	113	comments	\N	\N	\N	\N	text	\N	\N
448	10	25191	2019-12-28 13:38:06.032792	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	113	created_at	\N	\N	\N	\N	timestamp	\N	\N
449	9	25191	2019-12-28 13:38:06.052149	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	113	id	\N	\N	\N	\N	int4	\N	\N
450	4	25191	2019-12-28 13:38:06.07069	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	113	is_youtube	\N	\N	\N	\N	bool	\N	\N
451	7	25191	2019-12-28 13:38:06.090195	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	113	lyric	\N	\N	\N	\N	text	\N	\N
452	8	25191	2019-12-28 13:38:06.109475	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	113	thumbnail_url	\N	\N	\N	\N	varchar	\N	\N
453	1	25191	2019-12-28 13:38:06.128503	\N	\N	\N	\N	\N	\N	f	f	\N	\N	64	113	title	\N	\N	\N	\N	varchar	\N	\N
454	11	25191	2019-12-28 13:38:06.147962	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	113	updated_at	\N	\N	\N	\N	timestamp	\N	\N
455	2	25191	2019-12-28 13:38:06.166421	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	113	url	\N	\N	\N	\N	varchar	\N	\N
456	3	25199	2019-12-28 13:38:06.240886	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	114	created_at	\N	\N	\N	\N	timestamp	\N	\N
457	5	25199	2019-12-28 13:38:06.260622	\N	\N	\N	\N	\N	\N	f	f	\N	\N	8	114	ext	\N	\N	\N	\N	varchar	\N	\N
458	2	25199	2019-12-28 13:38:06.280359	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	114	id	\N	\N	\N	\N	int4	\N	\N
459	6	25199	2019-12-28 13:38:06.300237	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	114	name	\N	\N	\N	\N	varchar	\N	\N
460	1	25199	2019-12-28 13:38:06.320155	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	114	title	\N	\N	\N	\N	varchar	\N	\N
461	4	25199	2019-12-28 13:38:06.338707	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	114	updated_at	\N	\N	\N	\N	timestamp	\N	\N
462	6	25207	2019-12-28 13:38:06.414344	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	115	body	\N	\N	\N	\N	text	\N	\N
463	3	25207	2019-12-28 13:38:06.43253	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	115	created_at	\N	\N	\N	\N	timestamp	\N	\N
464	1	25207	2019-12-28 13:38:06.450693	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	115	id	\N	\N	\N	\N	int4	\N	\N
465	8	25207	2019-12-28 13:38:06.473646	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	115	image_id	\N	\N	\N	\N	int4	\N	\N
466	7	25207	2019-12-28 13:38:06.511783	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	115	movie_id	\N	\N	\N	\N	int4	\N	\N
467	9	25207	2019-12-28 13:38:06.531676	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	115	movie_url	\N	\N	\N	\N	varchar	\N	\N
468	4	25207	2019-12-28 13:38:06.551739	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	115	posted_at	\N	\N	\N	\N	timestamp	\N	\N
469	5	25207	2019-12-28 13:38:06.571358	\N	\N	\N	\N	\N	\N	f	f	\N	\N	64	115	title	\N	\N	\N	\N	varchar	\N	\N
470	2	25207	2019-12-28 13:38:06.592429	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	115	updated_at	\N	\N	\N	\N	timestamp	\N	\N
471	4	25215	2019-12-28 13:38:06.668452	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	116	body	\N	\N	\N	\N	text	\N	\N
472	6	25215	2019-12-28 13:38:06.689341	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	116	created_at	\N	\N	\N	\N	timestamp	\N	\N
473	1	25215	2019-12-28 13:38:06.711687	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	116	id	\N	\N	\N	\N	int4	\N	\N
474	5	25215	2019-12-28 13:38:06.731965	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	116	posted_at	\N	\N	\N	\N	timestamp	\N	\N
475	2	25215	2019-12-28 13:38:06.751091	\N	\N	\N	\N	\N	\N	f	f	\N	\N	128	116	title	\N	\N	\N	\N	varchar	\N	\N
476	7	25215	2019-12-28 13:38:06.771864	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	116	updated_at	\N	\N	\N	\N	timestamp	\N	\N
477	3	25215	2019-12-28 13:38:06.791629	\N	\N	\N	\N	\N	\N	f	f	\N	\N	255	116	url	\N	\N	\N	\N	varchar	\N	\N
478	6	25223	2019-12-28 13:38:06.872025	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	117	created_at	\N	\N	\N	\N	timestamp	\N	\N
479	5	25223	2019-12-28 13:38:06.892151	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	117	id	\N	\N	\N	\N	int4	\N	\N
480	1	25223	2019-12-28 13:38:06.915297	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	117	openid_identity	\N	\N	\N	\N	varchar	\N	\N
481	3	25223	2019-12-28 13:38:06.93623	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	117	openid_sig	\N	\N	\N	\N	varchar	\N	\N
482	2	25223	2019-12-28 13:38:06.960612	\N	\N	\N	\N	\N	\N	f	f	\N	\N	128	117	openid_sreg_nickname	\N	\N	\N	\N	varchar	\N	\N
483	4	25223	2019-12-28 13:38:06.98096	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	117	topic_id	\N	\N	\N	\N	int4	\N	\N
484	9	25223	2019-12-28 13:38:07.006015	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	117	twitter_screen_name	\N	\N	\N	\N	varchar	\N	\N
485	8	25223	2019-12-28 13:38:07.026476	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	117	twitter_user_id	\N	\N	\N	\N	varchar	\N	\N
486	7	25223	2019-12-28 13:38:07.045036	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	117	updated_at	\N	\N	\N	\N	timestamp	\N	\N
487	6	25231	2019-12-28 13:38:07.120315	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	118	body	\N	\N	\N	\N	text	\N	\N
488	3	25231	2019-12-28 13:38:07.138767	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	118	category_id	\N	\N	\N	\N	int4	\N	\N
489	8	25231	2019-12-28 13:38:07.158203	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	118	created_at	\N	\N	\N	\N	timestamp	\N	\N
490	1	25231	2019-12-28 13:38:07.178819	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	118	id	\N	\N	\N	\N	int4	\N	\N
491	10	25231	2019-12-28 13:38:07.203928	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	118	opened	\N	\N	\N	\N	bool	\N	\N
492	2	25231	2019-12-28 13:38:07.227922	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	118	posted_at	\N	\N	\N	\N	timestamp	\N	\N
494	5	25231	2019-12-28 13:38:07.274513	\N	\N	\N	\N	\N	\N	f	f	\N	\N	255	118	summary	\N	\N	\N	\N	varchar	\N	\N
495	4	25231	2019-12-28 13:38:07.296886	\N	\N	\N	\N	\N	\N	f	t	\N	\N	255	118	title	\N	\N	\N	\N	varchar	\N	\N
496	9	25231	2019-12-28 13:38:07.320459	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	118	updated_at	\N	\N	\N	\N	timestamp	\N	\N
493	7	25231	2019-12-28 13:38:07.25388	\N	\N	\N	\N	\N	\N	f	f	\N	\N	255	118	poster	\N	\N	\N	\N	varchar	\N	2019-12-28 13:42:39.071692
497	1	25495	2019-12-28 22:53:28.484875	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	173	contents	\N	\N	\N	\N	text	\N	\N
498	4	25495	2019-12-28 22:53:28.520702	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	173	created_at	\N	\N	\N	\N	timestamp	\N	\N
499	3	25495	2019-12-28 22:53:28.601484	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	173	id	\N	\N	\N	\N	int4	\N	\N
500	2	25495	2019-12-28 22:53:28.653936	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	173	title	\N	\N	\N	\N	varchar	\N	\N
501	5	25495	2019-12-28 22:53:28.679647	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	173	updated_at	\N	\N	\N	\N	timestamp	\N	\N
502	3	25503	2019-12-28 22:53:28.772937	\N	\N	\N	\N	\N	\N	f	t	\N	\N	64	174	admin	\N	\N	\N	\N	varchar	\N	\N
503	9	25503	2019-12-28 22:53:28.801418	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	174	created_at	\N	\N	\N	\N	timestamp	\N	\N
504	1	25503	2019-12-28 22:53:28.831689	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	174	id	\N	\N	\N	\N	int4	\N	\N
505	7	25503	2019-12-28 22:53:28.861101	\N	\N	\N	\N	\N	\N	f	f	\N	\N	255	174	logo	\N	\N	\N	\N	varchar	\N	\N
506	5	25503	2019-12-28 22:53:28.886044	\N	\N	\N	\N	\N	\N	f	t	\N	\N	64	174	name	\N	\N	\N	\N	varchar	\N	\N
507	4	25503	2019-12-28 22:53:28.90944	\N	\N	\N	\N	\N	\N	f	t	\N	\N	64	174	pass	\N	\N	\N	\N	varchar	\N	\N
508	6	25503	2019-12-28 22:53:28.934942	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	174	profile	\N	\N	\N	\N	text	\N	\N
509	8	25503	2019-12-28 22:53:28.956229	\N	\N	\N	\N	\N	\N	f	f	\N	\N	255	174	style	\N	\N	\N	\N	varchar	\N	\N
510	2	25503	2019-12-28 22:53:28.986391	\N	\N	\N	\N	\N	\N	f	t	\N	\N	128	174	title	\N	\N	\N	\N	varchar	\N	\N
511	10	25503	2019-12-28 22:53:29.009922	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	174	updated_at	\N	\N	\N	\N	timestamp	\N	\N
512	8	25511	2019-12-28 22:53:29.14189	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	175	advertising_id	\N	\N	\N	\N	int4	\N	\N
513	4	25511	2019-12-28 22:53:29.172135	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	175	created_at	\N	\N	\N	\N	timestamp	\N	\N
514	1	25511	2019-12-28 22:53:29.214458	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	175	id	\N	\N	\N	\N	int4	\N	\N
515	7	25511	2019-12-28 22:53:29.250532	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	175	is_labs	\N	\N	\N	\N	bool	\N	\N
516	3	25511	2019-12-28 22:53:29.315636	\N	\N	\N	\N	\N	\N	f	t	\N	\N	255	175	name	\N	\N	\N	\N	varchar	\N	\N
517	6	25511	2019-12-28 22:53:29.35696	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	175	opened	\N	\N	\N	\N	bool	\N	\N
519	5	25511	2019-12-28 22:53:29.447625	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	175	updated_at	\N	\N	\N	\N	timestamp	\N	\N
520	4	25516	2019-12-28 22:53:29.623688	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	176	body	\N	\N	\N	\N	text	\N	\N
521	6	25516	2019-12-28 22:53:29.657671	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	176	created_at	\N	\N	\N	\N	timestamp	\N	\N
522	1	25516	2019-12-28 22:53:29.689919	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	176	id	\N	\N	\N	\N	int4	\N	\N
523	8	25516	2019-12-28 22:53:29.717081	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	176	opened	\N	\N	\N	\N	bool	\N	\N
524	5	25516	2019-12-28 22:53:29.744833	\N	\N	\N	\N	\N	\N	f	t	\N	\N	50	176	password	\N	\N	\N	\N	varchar	\N	\N
525	9	25516	2019-12-28 22:53:29.838745	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	176	posted_at	\N	\N	\N	\N	timestamp	\N	\N
526	3	25516	2019-12-28 22:53:29.887159	\N	\N	\N	\N	\N	\N	f	t	\N	\N	255	176	poster	\N	\N	\N	\N	varchar	\N	\N
527	2	25516	2019-12-28 22:53:29.957863	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	176	topic_id	\N	\N	\N	\N	int4	\N	\N
528	7	25516	2019-12-28 22:53:30.0048	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	176	updated_at	\N	\N	\N	\N	timestamp	\N	\N
529	3	25524	2019-12-28 22:53:30.197922	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	177	body	\N	\N	\N	\N	text	\N	\N
518	2	25511	2019-12-28 22:53:29.407734			\N	\N	\N	\N	f	t	\N		\N	175	sort_order		\N		\N	int2	\N	2019-12-29 12:26:53.447184
530	6	25524	2019-12-28 22:53:30.233077	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	177	created_at	\N	\N	\N	\N	timestamp	\N	\N
531	5	25524	2019-12-28 22:53:30.310806	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	177	id	\N	\N	\N	\N	int4	\N	\N
532	4	25524	2019-12-28 22:53:30.353711	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	177	opened	\N	\N	\N	\N	bool	\N	\N
533	2	25524	2019-12-28 22:53:30.382508	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	177	posted_at	\N	\N	\N	\N	timestamp	\N	\N
534	1	25524	2019-12-28 22:53:30.414745	\N	\N	\N	\N	\N	\N	f	f	\N	\N	32	177	title	\N	\N	\N	\N	varchar	\N	\N
535	7	25524	2019-12-28 22:53:30.445598	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	177	updated_at	\N	\N	\N	\N	timestamp	\N	\N
536	1	25532	2019-12-28 22:53:30.593975	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	178	body	\N	\N	\N	\N	text	\N	\N
537	9	25532	2019-12-28 22:53:30.62551	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	178	created_at	\N	\N	\N	\N	timestamp	\N	\N
538	7	25532	2019-12-28 22:53:30.656438	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	178	duration	\N	\N	\N	\N	int4	\N	\N
539	11	25532	2019-12-28 22:53:30.681674	\N	\N	\N	\N	\N	\N	f	f	\N	\N	16	178	font_color	\N	\N	\N	\N	varchar	\N	\N
540	12	25532	2019-12-28 22:53:30.718628	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	178	font_size	\N	\N	\N	\N	int4	\N	\N
541	8	25532	2019-12-28 22:53:30.74763	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	178	id	\N	\N	\N	\N	int4	\N	\N
542	6	25532	2019-12-28 22:53:30.775584	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	178	movie_id	\N	\N	\N	\N	int4	\N	\N
543	2	25532	2019-12-28 22:53:30.823728	\N	\N	\N	\N	\N	\N	f	f	\N	\N	16	178	poster	\N	\N	\N	\N	varchar	\N	\N
544	3	25532	2019-12-28 22:53:30.866499	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	178	second	\N	\N	\N	\N	int4	\N	\N
545	10	25532	2019-12-28 22:53:30.890523	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	178	updated_at	\N	\N	\N	\N	timestamp	\N	\N
546	4	25532	2019-12-28 22:53:30.93551	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	178	x	\N	\N	\N	\N	int4	\N	\N
547	5	25532	2019-12-28 22:53:30.956878	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	178	y	\N	\N	\N	\N	int4	\N	\N
548	5	25540	2019-12-28 22:53:31.10772	\N	\N	\N	\N	\N	\N	f	f	\N	\N	64	179	artist	\N	\N	\N	\N	varchar	\N	\N
549	3	25540	2019-12-28 22:53:31.245949	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	179	category_id	\N	\N	\N	\N	int4	\N	\N
550	6	25540	2019-12-28 22:53:31.33254	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	179	comments	\N	\N	\N	\N	text	\N	\N
551	10	25540	2019-12-28 22:53:31.413953	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	179	created_at	\N	\N	\N	\N	timestamp	\N	\N
552	9	25540	2019-12-28 22:53:31.506334	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	179	id	\N	\N	\N	\N	int4	\N	\N
553	4	25540	2019-12-28 22:53:31.570111	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	179	is_youtube	\N	\N	\N	\N	bool	\N	\N
554	7	25540	2019-12-28 22:53:31.607292	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	179	lyric	\N	\N	\N	\N	text	\N	\N
555	8	25540	2019-12-28 22:53:31.63119	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	179	thumbnail_url	\N	\N	\N	\N	varchar	\N	\N
556	1	25540	2019-12-28 22:53:31.659461	\N	\N	\N	\N	\N	\N	f	f	\N	\N	64	179	title	\N	\N	\N	\N	varchar	\N	\N
557	11	25540	2019-12-28 22:53:31.685454	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	179	updated_at	\N	\N	\N	\N	timestamp	\N	\N
558	2	25540	2019-12-28 22:53:31.719618	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	179	url	\N	\N	\N	\N	varchar	\N	\N
559	3	25548	2019-12-28 22:53:31.847715	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	180	created_at	\N	\N	\N	\N	timestamp	\N	\N
560	5	25548	2019-12-28 22:53:31.88028	\N	\N	\N	\N	\N	\N	f	f	\N	\N	8	180	ext	\N	\N	\N	\N	varchar	\N	\N
561	2	25548	2019-12-28 22:53:31.911133	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	180	id	\N	\N	\N	\N	int4	\N	\N
562	6	25548	2019-12-28 22:53:31.936549	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	180	name	\N	\N	\N	\N	varchar	\N	\N
563	1	25548	2019-12-28 22:53:31.960776	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	180	title	\N	\N	\N	\N	varchar	\N	\N
564	4	25548	2019-12-28 22:53:31.986039	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	180	updated_at	\N	\N	\N	\N	timestamp	\N	\N
565	6	25556	2019-12-28 22:53:32.144375	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	181	body	\N	\N	\N	\N	text	\N	\N
566	3	25556	2019-12-28 22:53:32.173456	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	181	created_at	\N	\N	\N	\N	timestamp	\N	\N
567	1	25556	2019-12-28 22:53:32.214147	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	181	id	\N	\N	\N	\N	int4	\N	\N
568	8	25556	2019-12-28 22:53:32.244604	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	181	image_id	\N	\N	\N	\N	int4	\N	\N
569	7	25556	2019-12-28 22:53:32.318091	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	181	movie_id	\N	\N	\N	\N	int4	\N	\N
570	9	25556	2019-12-28 22:53:32.432513	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	181	movie_url	\N	\N	\N	\N	varchar	\N	\N
571	4	25556	2019-12-28 22:53:32.481749	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	181	posted_at	\N	\N	\N	\N	timestamp	\N	\N
572	5	25556	2019-12-28 22:53:32.515271	\N	\N	\N	\N	\N	\N	f	f	\N	\N	64	181	title	\N	\N	\N	\N	varchar	\N	\N
573	2	25556	2019-12-28 22:53:32.600419	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	181	updated_at	\N	\N	\N	\N	timestamp	\N	\N
574	4	25564	2019-12-28 22:53:32.713452	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	182	body	\N	\N	\N	\N	text	\N	\N
575	6	25564	2019-12-28 22:53:32.745451	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	182	created_at	\N	\N	\N	\N	timestamp	\N	\N
576	1	25564	2019-12-28 22:53:32.770738	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	182	id	\N	\N	\N	\N	int4	\N	\N
577	5	25564	2019-12-28 22:53:32.798505	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	182	posted_at	\N	\N	\N	\N	timestamp	\N	\N
578	2	25564	2019-12-28 22:53:32.830228	\N	\N	\N	\N	\N	\N	f	f	\N	\N	128	182	title	\N	\N	\N	\N	varchar	\N	\N
579	7	25564	2019-12-28 22:53:32.860739	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	182	updated_at	\N	\N	\N	\N	timestamp	\N	\N
580	3	25564	2019-12-28 22:53:32.881982	\N	\N	\N	\N	\N	\N	f	f	\N	\N	255	182	url	\N	\N	\N	\N	varchar	\N	\N
581	6	25572	2019-12-28 22:53:32.970629	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	183	created_at	\N	\N	\N	\N	timestamp	\N	\N
582	5	25572	2019-12-28 22:53:32.996871	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	183	id	\N	\N	\N	\N	int4	\N	\N
583	1	25572	2019-12-28 22:53:33.024618	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	183	openid_identity	\N	\N	\N	\N	varchar	\N	\N
584	3	25572	2019-12-28 22:53:33.054045	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	183	openid_sig	\N	\N	\N	\N	varchar	\N	\N
585	2	25572	2019-12-28 22:53:33.076923	\N	\N	\N	\N	\N	\N	f	f	\N	\N	128	183	openid_sreg_nickname	\N	\N	\N	\N	varchar	\N	\N
586	4	25572	2019-12-28 22:53:33.108142	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	183	topic_id	\N	\N	\N	\N	int4	\N	\N
587	9	25572	2019-12-28 22:53:33.138796	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	183	twitter_screen_name	\N	\N	\N	\N	varchar	\N	\N
588	8	25572	2019-12-28 22:53:33.160991	\N	\N	\N	\N	\N	\N	f	f	\N	\N	256	183	twitter_user_id	\N	\N	\N	\N	varchar	\N	\N
589	7	25572	2019-12-28 22:53:33.183945	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	183	updated_at	\N	\N	\N	\N	timestamp	\N	\N
592	8	25580	2019-12-28 22:53:33.362512	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	184	created_at	\N	\N	\N	\N	timestamp	\N	\N
593	1	25580	2019-12-28 22:53:33.388882	\N	\N	\N	\N	\N	\N	f	t	\N	\N	\N	184	id	\N	\N	\N	\N	int4	\N	\N
597	5	25580	2019-12-28 22:53:33.477934	\N	\N	\N	\N	\N	\N	f	f	\N	\N	255	184	summary	\N	\N	\N	\N	varchar	\N	\N
599	9	25580	2019-12-28 22:53:33.526075	\N	\N	\N	\N	\N	\N	f	f	\N	\N	\N	184	updated_at	\N	\N	\N	\N	timestamp	\N	\N
594	10	25580	2019-12-28 22:53:33.413695			\N	\N	\N	\N	f	f	\N	公開	\N	184	is_release		\N		\N	bool	\N	2019-12-28 22:58:21.199786
595	2	25580	2019-12-28 22:53:33.436058	\N	\N	\N	\N	\N	\N	f	t	\N	投稿日時	\N	184	posted_at	\N	\N	\N	\N	timestamp	\N	2019-12-28 22:58:35.565362
596	7	25580	2019-12-28 22:53:33.45602	\N	\N	\N	\N	\N	\N	f	f	\N	\N	255	184	poster	\N	\N	\N	\N	varchar	\N	2019-12-28 22:59:25.516353
598	4	25580	2019-12-28 22:53:33.502299	\N	\N	\N	\N	\N	\N	f	t	\N	タイトル	255	184	title	\N	\N	\N	\N	varchar	\N	2019-12-28 22:59:39.742387
590	6	25580	2019-12-28 22:53:33.313255			\N	\N	\N	\N	f	t	\N	内容	\N	184	content		\N		\N	text	\N	2019-12-28 22:59:54.204432
591	3	25580	2019-12-28 22:53:33.338267	\N	\N	\N	\N	\N	\N	f	t	\N	カテゴリー	\N	184	category_id	\N	\N	\N	\N	int4	\N	2019-12-28 23:00:00.246348
\.


--
-- Data for Name: databases; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.databases (id, created_at, current_version, hostname, is_lock, name, port, type, updated_at, user_name) FROM stdin;
1	2019-08-27 13:23:15.664595	\N	localhost	t	la_blog	5432	\N	\N	postgres
2	2019-08-29 12:20:14.733582	\N	localhost	t	project_manager	5432	\N	2019-08-29 12:20:21.462554	postgres
3	2019-12-08 22:27:59.670726	\N	localhost	\N	school	5432	\N	\N	postgres
4	2019-12-15 12:53:09.796996	\N	localhost	\N	sample	5432	\N	\N	postgres
5	2019-12-22 15:59:45.213639	\N	localhost	\N	ict-kids	5432	\N	2019-12-22 23:27:46.446487	postgres
6	2019-12-28 12:56:45.019684	\N	localhost	\N	yoo_blog	5432	\N	\N	postgres
\.


--
-- Data for Name: langs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.langs (id, created_at, lang, name, sort_order, updated_at) FROM stdin;
1	2019-12-23 01:47:27.95885	ja	日本語	\N	\N
\.


--
-- Data for Name: localize_strings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.localize_strings (id, created_at, label, name, project_id, sort_order, updated_at) FROM stdin;
1	2019-08-29 12:38:40.029627	{"ja":"\\u30eb\\u30fc\\u30c8"}	LABEL_ROUTES	2	\N	\N
2	2019-08-29 12:38:40.064503	{"ja":"Controller"}	LABEL_ROUTES_CONTROLLER	2	\N	\N
3	2019-08-29 12:38:40.087322	{"ja":"Action"}	LABEL_ROUTES_ACTION	2	\N	\N
4	2019-08-29 12:38:40.107217	{"ja":"\\u30e1\\u30bd\\u30c3\\u30c9"}	LABEL_ROUTES_METHOD	2	\N	\N
5	2019-08-29 12:38:40.130628	{"ja":"\\u30a2\\u30c9\\u30ec\\u30b9"}	LABEL_ROUTES_ADDRESS	2	\N	\N
6	2019-08-29 19:53:45.719081	{"ja":"\\u30c8\\u30d4\\u30c3\\u30af"}	LABEL_TOPICS	1	\N	\N
7	2019-08-29 19:53:45.739652	{"ja":"\\u516c\\u958b"}	LABEL_TOPICS_IS_PROVIDE	1	\N	\N
8	2019-08-29 19:53:45.761674	{"ja":"\\u30bf\\u30a4\\u30c8\\u30eb"}	LABEL_TOPICS_TITLE	1	\N	\N
9	2019-08-29 19:53:45.782116	{"ja":"\\u6295\\u7a3f\\u65e5"}	LABEL_TOPICS_POST_AT	1	\N	\N
10	2019-08-29 19:53:45.804543	{"ja":"\\u30b3\\u30f3\\u30c6\\u30f3\\u30c4"}	LABEL_TOPICS_CONTENTS	1	\N	\N
11	2019-08-29 19:53:45.834758	{"ja":"\\u30c8\\u30d4\\u30c3\\u30af\\u30bf\\u30b0"}	LABEL_TOPIC_TAGS	1	\N	\N
12	2019-08-30 00:06:16.629165	{"ja":"URI"}	LABEL_ROUTES_URI	2	\N	\N
13	2019-08-30 00:12:36.956872	{"ja":"MIddleware"}	LABEL_ROUTES_MIDDLEWARE	2	\N	\N
14	2019-12-14 10:03:21.327285	{"ja":"\\u751f\\u5f92"}	LABEL_STUDENTS	3	\N	\N
15	2019-12-14 10:03:21.378862	{"ja":"\\u4f5c\\u6210\\u65e5"}	LABEL_STUDENTS_CREATED_AT	3	\N	\N
16	2019-12-14 10:03:21.403612	{"ja":"ID"}	LABEL_STUDENTS_ID	3	\N	\N
17	2019-12-14 10:03:21.42727	{"ja":"\\u4e26\\u3073\\u9806"}	LABEL_STUDENTS_SORT_ORDER	3	\N	\N
18	2019-12-14 10:03:21.451741	{"ja":"\\u66f4\\u65b0\\u65e5"}	LABEL_STUDENTS_UPDATED_AT	3	\N	\N
19	2019-12-14 10:03:21.473495	{"ja":"\\u540d"}	LABEL_STUDENTS_LAST_NAME	3	\N	\N
20	2019-12-14 10:03:21.503798	{"ja":"\\u59d3"}	LABEL_STUDENTS_FIRST_NAME	3	\N	\N
21	2019-12-14 10:03:21.532876	{"ja":"\\u3081\\u3044"}	LABEL_STUDENTS_FIRST_NAME_KANA	3	\N	\N
22	2019-12-14 10:03:21.54907	{"ja":"\\u305b\\u3044"}	LABEL_STUDENTS_LAST_NAME_KANA	3	\N	\N
23	2019-12-14 10:03:21.570142	{"ja":"\\u751f\\u5e74\\u6708\\u65e5"}	LABEL_STUDENTS_BIRTHDAY_AT	3	\N	\N
24	2019-12-14 10:03:21.589127	{"ja":"\\u751f\\u5f92\\u756a\\u53f7"}	LABEL_STUDENTS_CODE	3	\N	\N
25	2019-12-15 14:04:02.78175	{"ja":"\\u30ec\\u30dd\\u30fc\\u30c8"}	LABEL_REPORTS	3	\N	\N
26	2019-12-15 14:04:02.802578	{"ja":"\\u4f5c\\u6210\\u65e5"}	LABEL_REPORTS_CREATED_AT	3	\N	\N
27	2019-12-15 14:04:02.818944	{"ja":"ID"}	LABEL_REPORTS_ID	3	\N	\N
28	2019-12-15 14:04:02.83629	{"ja":"\\u4e26\\u3073\\u9806"}	LABEL_REPORTS_SORT_ORDER	3	\N	\N
29	2019-12-15 14:04:02.852737	{"ja":"\\u66f4\\u65b0\\u65e5"}	LABEL_REPORTS_UPDATED_AT	3	\N	\N
30	2019-12-15 14:04:02.869244	{"ja":"\\u30ec\\u30dd\\u30fc\\u30c8\\u5185\\u5bb9"}	LABEL_REPORTS_REPORT	3	\N	\N
31	2019-12-15 14:04:02.886338	{"ja":"\\u5831\\u544a\\u65e5"}	LABEL_REPORTS_REPORTED_AT	3	\N	\N
32	2019-12-15 23:16:32.534184	{"ja":null}	LABEL_ATTRIBUTES	2	\N	\N
33	2019-12-15 23:16:32.581012	{"ja":null}	LABEL_ATTRIBUTES_DEFAULT_VALUE	2	\N	\N
34	2019-12-15 23:16:32.616308	{"ja":null}	LABEL_ATTRIBUTES_UPDATE_ACTION	2	\N	\N
35	2019-12-15 23:16:32.63531	{"ja":null}	LABEL_ATTRIBUTES_IS_ARRAY	2	\N	\N
36	2019-12-15 23:16:32.654825	{"ja":null}	LABEL_ATTRIBUTES_IS_LOCK	2	\N	\N
37	2019-12-15 23:16:32.673548	{"ja":null}	LABEL_ATTRIBUTES_IS_PRIMARY_KEY	2	\N	\N
38	2019-12-15 23:16:32.693888	{"ja":null}	LABEL_ATTRIBUTES_IS_UNIQUE	2	\N	\N
39	2019-12-15 23:16:32.710321	{"ja":null}	LABEL_ATTRIBUTES_IS_REQUIRED	2	\N	\N
40	2019-12-15 23:16:32.729997	{"ja":null}	LABEL_ATTRIBUTES_LABEL	2	\N	\N
41	2019-12-15 23:16:32.748101	{"ja":null}	LABEL_ATTRIBUTES_LENGTH	2	\N	\N
42	2019-12-15 23:16:32.765598	{"ja":null}	LABEL_ATTRIBUTES_OLD_NAME	2	\N	\N
43	2019-12-15 23:16:32.785626	{"ja":null}	LABEL_ATTRIBUTES_TYPE	2	\N	\N
44	2019-12-15 23:16:32.803387	{"ja":null}	LABEL_ATTRIBUTES_ATTNUM	2	\N	\N
45	2019-12-15 23:16:32.824255	{"ja":null}	LABEL_ATTRIBUTES_ATTRELID	2	\N	\N
46	2019-12-15 23:16:32.840553	{"ja":null}	LABEL_ATTRIBUTES_CSV	2	\N	\N
47	2019-12-15 23:16:32.860015	{"ja":null}	LABEL_ATTRIBUTES_DELETE_ACTION	2	\N	\N
48	2019-12-15 23:16:32.878895	{"ja":null}	LABEL_ATTRIBUTES_NAME	2	\N	\N
49	2019-12-15 23:16:32.895778	{"ja":null}	LABEL_ATTRIBUTES_NOTE	2	\N	\N
50	2019-12-23 01:50:11.633969	{"ja":"\\u751f\\u5f92"}	LABEL_STUDENTS	5	\N	\N
51	2019-12-23 01:50:11.676016	{"ja":"\\u4e88\\u7d04"}	LABEL_RESERVATIONS	5	\N	\N
52	2019-12-23 01:50:11.719401	{"ja":"\\u30e6\\u30fc\\u30b6"}	LABEL_USERS	5	\N	\N
53	2019-12-23 01:50:11.748186	{"ja":"\\u6c0f\\u540d\\uff08\\u59d3\\uff09"}	LABEL_USERS_FIRSTNAME	5	\N	\N
54	2019-12-23 01:50:11.771565	{"ja":"\\u6c0f\\u540d\\uff08\\u540d\\uff09"}	LABEL_USERS_LASTNAME	5	\N	\N
55	2019-12-23 01:50:11.803221	{"ja":"\\u6c0f\\u540d\\uff08\\u305b\\u3044\\uff09"}	LABEL_USERS_FIRSTNAME_KANA	5	\N	\N
56	2019-12-23 01:50:11.826135	{"ja":"\\u6c0f\\u540d\\uff08\\u3081\\u3044\\uff09"}	LABEL_USERS_LASTNAME_KANA	5	\N	\N
57	2019-12-23 01:50:11.845345	{"ja":"\\u90f5\\u4fbf\\u756a\\u53f7"}	LABEL_USERS_POSTAL_CODE	5	\N	\N
58	2019-12-23 01:50:11.86611	{"ja":"\\u90fd\\u9053\\u5e9c\\u770c"}	LABEL_USERS_PREFECTURE	5	\N	\N
59	2019-12-23 01:50:11.888193	{"ja":"\\u5e02\\u533a\\u753a\\u6751"}	LABEL_USERS_CITY	5	\N	\N
60	2019-12-23 01:50:11.907221	{"ja":"\\u96fb\\u8a71\\u756a\\u53f7"}	LABEL_USERS_ TEL	5	\N	\N
61	2019-12-23 01:50:11.936982	{"ja":"Email"}	LABEL_USERS_EMAIL	5	\N	\N
62	2019-12-23 01:50:11.984742	{"ja":"\\u8acb\\u6c42"}	LABEL_BILLS	5	\N	\N
63	2019-12-23 01:50:12.083582	{"ja":"\\u304a\\u554f\\u3044\\u5408\\u308f\\u305b"}	LABEL_CONTACTS	5	\N	\N
64	2019-12-23 01:50:12.167769	{"ja":"\\u6c0f\\u540d\\uff08\\u59d3\\uff09"}	LABEL_CONTACTS_FIRSTNAME	5	\N	\N
65	2019-12-23 01:50:12.261586	{"ja":"\\u6c0f\\u540d\\uff08\\u540d\\uff09"}	LABEL_CONTACTS_LASTNAME	5	\N	\N
66	2019-12-23 01:50:12.296085	{"ja":"\\u6c0f\\u540d\\uff08\\u305b\\u3044\\uff09"}	LABEL_CONTACTS_FIRSTNAME_KANA	5	\N	\N
67	2019-12-23 01:50:12.322957	{"ja":"\\u6c0f\\u540d\\uff08\\u3081\\u3044\\uff09"}	LABEL_CONTACTS_LASTNAME_KANA	5	\N	\N
68	2019-12-23 01:50:12.363937	{"ja":"\\u90f5\\u4fbf\\u756a\\u53f7"}	LABEL_CONTACTS_POSTAL_CODE	5	\N	\N
69	2019-12-23 01:50:12.396061	{"ja":"\\u90fd\\u9053\\u5e9c\\u770c"}	LABEL_CONTACTS_PREFECTURE	5	\N	\N
70	2019-12-23 01:50:12.439141	{"ja":"\\u5e02\\u533a\\u753a\\u6751"}	LABEL_CONTACTS_CITY	5	\N	\N
71	2019-12-23 01:50:12.463822	{"ja":"Email"}	LABEL_CONTACTS_EMAIL	5	\N	\N
72	2019-12-23 01:50:12.501929	{"ja":"\\u96fb\\u8a71\\u756a\\u53f7"}	LABEL_CONTACTS_TEL	5	\N	\N
73	2019-12-23 01:50:12.52443	{"ja":"\\u304a\\u554f\\u3044\\u5408\\u308f\\u305b\\u5185\\u5bb9"}	LABEL_CONTACTS_CONTENT	5	\N	\N
74	2019-12-23 01:50:12.550851	{"ja":"\\u4e88\\u7d04\\u65e5"}	LABEL_CONTACTS_RESERVATION_AT	5	\N	\N
75	2019-12-23 01:50:12.571051	{"ja":"\\u554f\\u3044\\u5408\\u308f\\u305b\\u756a\\u53f7"}	LABEL_CONTACTS_NO	5	\N	\N
76	2019-12-23 01:50:12.597794	{"ja":"\\u5b66\\u5e74"}	LABEL_CONTACTS_GRADE	5	\N	\N
77	2019-12-23 01:50:12.621419	{"ja":"\\u5b66\\u6821\\u7a2e\\u5225"}	LABEL_CONTACTS_GRADE_TYPE	5	\N	\N
\.


--
-- Data for Name: menus; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.menus (id, created_at, is_provide, name, sort_order, updated_at) FROM stdin;
\.


--
-- Data for Name: models; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.models (id, class_name, created_at, csv, entity_name, id_column_name, is_lock, is_none_id_column, is_unenable, label, name, note, old_database_id, old_name, pg_class_id, project_id, relfilenode, sort_order, sub_table_name, updated_at) FROM stdin;
3	Admin	2019-08-29 12:24:05.683415	\N	admin	\N	\N	\N	\N	\N	admins	\N	\N	\N	16387	2	16387	\N	\N	\N
4	ApiAction	2019-08-29 12:24:05.982261	\N	api_action	\N	\N	\N	\N	\N	api_actions	\N	\N	\N	16409	2	16409	\N	\N	\N
5	ApiGroup	2019-08-29 12:24:06.139914	\N	api_group	\N	\N	\N	\N	\N	api_groups	\N	\N	\N	16420	2	16420	\N	\N	\N
6	ApiParam	2019-08-29 12:24:06.254008	\N	api_param	\N	\N	\N	\N	\N	api_params	\N	\N	\N	16428	2	16428	\N	\N	\N
7	Api	2019-08-29 12:24:06.405876	\N	api	\N	\N	\N	\N	\N	apis	\N	\N	\N	16398	2	16398	\N	\N	\N
8	Attribute	2019-08-29 12:24:06.571057	\N	attribute	\N	\N	\N	\N	\N	attributes	\N	\N	\N	16439	2	16439	\N	\N	\N
9	Database	2019-08-29 12:24:07.053741	\N	database	\N	\N	\N	\N	\N	databases	\N	\N	\N	16450	2	16450	\N	\N	\N
10	Lang	2019-08-29 12:24:07.23455	\N	lang	\N	\N	\N	\N	\N	langs	\N	\N	\N	16461	2	16461	\N	\N	\N
11	LocalizeString	2019-08-29 12:24:07.358645	\N	localize_string	\N	\N	\N	\N	\N	localize_strings	\N	\N	\N	16469	2	16469	\N	\N	\N
12	Menu	2019-08-29 12:24:07.497938	\N	menu	\N	\N	\N	\N	\N	menus	\N	\N	\N	16480	2	16480	\N	\N	\N
13	Model	2019-08-29 12:24:07.628892	\N	model	\N	\N	\N	\N	\N	models	\N	\N	\N	16488	2	16488	\N	\N	\N
14	PageFilter	2019-08-29 12:24:07.944741	\N	page_filter	\N	\N	\N	\N	\N	page_filters	\N	\N	\N	16510	2	16510	\N	\N	\N
15	PageModel	2019-08-29 12:24:08.097794	\N	page_model	\N	\N	\N	\N	\N	page_models	\N	\N	\N	16518	2	16518	\N	\N	\N
16	Page	2019-08-29 12:24:08.268821	\N	page	\N	\N	\N	\N	\N	pages	\N	\N	\N	16499	2	16499	\N	\N	\N
17	Project	2019-08-29 12:24:08.537195	\N	project	\N	\N	\N	\N	\N	projects	\N	\N	\N	16526	2	16526	\N	\N	\N
18	PublicLocalizeString	2019-08-29 12:24:08.723929	\N	public_localize_string	\N	\N	\N	\N	\N	public_localize_strings	\N	\N	\N	16537	2	16537	\N	\N	\N
19	RecordItem	2019-08-29 12:24:08.846676	\N	record_item	\N	\N	\N	\N	\N	record_items	\N	\N	\N	16559	2	16559	\N	\N	\N
20	Record	2019-08-29 12:24:09.004317	\N	record	\N	\N	\N	\N	\N	records	\N	\N	\N	16548	2	16548	\N	\N	\N
21	RelationDatabase	2019-08-29 12:24:09.170249	\N	relation_database	\N	\N	\N	\N	\N	relation_databases	\N	\N	\N	16570	2	16570	\N	\N	\N
22	UserProjectSetting	2019-08-29 12:24:09.295986	\N	user_project_setting	\N	\N	\N	\N	\N	user_project_settings	\N	\N	\N	16589	2	16589	\N	\N	\N
23	User	2019-08-29 12:24:09.463264	\N	user	\N	\N	\N	\N	\N	users	\N	\N	\N	16578	2	16578	\N	\N	\N
24	ViewItemGroupMember	2019-08-29 12:24:09.701025	\N	view_item_group_member	\N	\N	\N	\N	\N	view_item_group_members	\N	\N	\N	16630	2	16630	\N	\N	\N
25	ViewItemGroup	2019-08-29 12:24:09.829842	\N	view_item_group	\N	\N	\N	\N	\N	view_item_groups	\N	\N	\N	16622	2	16622	\N	\N	\N
26	ViewItemModel	2019-08-29 12:24:09.960859	\N	view_item_model	\N	\N	\N	\N	\N	view_item_models	\N	\N	\N	16638	2	16638	\N	\N	\N
27	ViewItem	2019-08-29 12:24:10.142815	\N	view_item	\N	\N	\N	\N	\N	view_items	\N	\N	\N	16611	2	16611	\N	\N	\N
28	View	2019-08-29 12:24:10.476846	\N	view	\N	\N	\N	\N	\N	views	\N	\N	\N	16600	2	16600	\N	\N	\N
29	Route	2019-08-29 12:25:38.901209	\N	route	\N	\N	\N	\N	ルート	routes	\N	\N	\N	16833	2	16833	\N	\N	\N
35	Student	2019-12-14 10:09:58.307853	\N	student	\N	\N	\N	\N	生徒	students	\N	\N	\N	25014	3	25014	\N	\N	\N
36	Report	2019-12-14 11:18:38.967773	\N	report	\N	\N	\N	\N	レポート	reports	\N	\N	\N	25025	3	25025	\N	\N	\N
40	Student	2019-12-22 16:06:43.92038	\N	student	\N	\N	\N	\N	生徒	students	\N	\N	\N	25076	5	25076	\N	\N	\N
41	Reservation	2019-12-22 16:07:16.827883	\N	reservation	\N	\N	\N	\N	予約	reservations	\N	\N	\N	25085	5	25085	\N	\N	\N
42	User	2019-12-22 16:08:30.626208	\N	user	\N	\N	\N	\N	ユーザ	users	\N	\N	\N	25094	5	25094	\N	\N	\N
43	Bill	2019-12-22 16:21:30.571538	\N	bill	\N	\N	\N	\N	請求	bills	\N	\N	\N	25103	5	25103	\N	\N	\N
173	Advertising	2019-12-28 22:53:28.326705	\N	advertising	\N	\N	\N	\N	\N	advertisings	\N	\N	\N	25495	1	25495	\N	\N	\N
174	Blog	2019-12-28 22:53:28.704025	\N	blog	\N	\N	\N	\N	\N	blogs	\N	\N	\N	25503	1	25503	\N	\N	\N
175	Category	2019-12-28 22:53:29.038341	\N	category	\N	\N	\N	\N	\N	categories	\N	\N	\N	25511	1	25511	\N	\N	\N
176	Comment	2019-12-28 22:53:29.499892	\N	comment	\N	\N	\N	\N	\N	comments	\N	\N	\N	25516	1	25516	\N	\N	\N
177	Information	2019-12-28 22:53:30.047042	\N	information	\N	\N	\N	\N	\N	informations	\N	\N	\N	25524	1	25524	\N	\N	\N
178	MovieComment	2019-12-28 22:53:30.479686	\N	movie_comment	\N	\N	\N	\N	\N	movie_comments	\N	\N	\N	25532	1	25532	\N	\N	\N
179	Movie	2019-12-28 22:53:30.989136	\N	movie	\N	\N	\N	\N	\N	movies	\N	\N	\N	25540	1	25540	\N	\N	\N
180	Photo	2019-12-28 22:53:31.747436	\N	photo	\N	\N	\N	\N	\N	photos	\N	\N	\N	25548	1	25548	\N	\N	\N
181	PickupInformation	2019-12-28 22:53:32.020067	\N	pickup_information	\N	\N	\N	\N	\N	pickup_informations	\N	\N	\N	25556	1	25556	\N	\N	\N
45	Contact	2019-12-23 00:56:07.525825	\N	contact	\N	\N	\N	\N	お問い合わせ	contacts		\N		25135	5	25094	\N	\N	2019-12-23 01:00:28.316062
107	Advertising	2019-12-28 13:15:11.023065	\N	advertising	\N	\N	\N	\N	\N	advertisings	\N	\N	\N	25146	6	25146	\N	\N	\N
108	Blog	2019-12-28 13:15:11.048176	\N	blog	\N	\N	\N	\N	\N	blogs	\N	\N	\N	25154	6	25154	\N	\N	\N
109	Category	2019-12-28 13:15:11.082502	\N	category	\N	\N	\N	\N	\N	categories	\N	\N	\N	25162	6	25162	\N	\N	\N
110	Comment	2019-12-28 13:15:11.103104	\N	comment	\N	\N	\N	\N	\N	comments	\N	\N	\N	25167	6	25167	\N	\N	\N
111	Information	2019-12-28 13:15:11.150416	\N	information	\N	\N	\N	\N	\N	informations	\N	\N	\N	25175	6	25175	\N	\N	\N
112	MovieComment	2019-12-28 13:15:11.16859	\N	movie_comment	\N	\N	\N	\N	\N	movie_comments	\N	\N	\N	25183	6	25183	\N	\N	\N
113	Movie	2019-12-28 13:15:11.186475	\N	movie	\N	\N	\N	\N	\N	movies	\N	\N	\N	25191	6	25191	\N	\N	\N
114	Photo	2019-12-28 13:15:11.205675	\N	photo	\N	\N	\N	\N	\N	photos	\N	\N	\N	25199	6	25199	\N	\N	\N
115	PickupInformation	2019-12-28 13:15:11.223795	\N	pickup_information	\N	\N	\N	\N	\N	pickup_informations	\N	\N	\N	25207	6	25207	\N	\N	\N
116	Promote	2019-12-28 13:15:11.242612	\N	promote	\N	\N	\N	\N	\N	promotes	\N	\N	\N	25215	6	25215	\N	\N	\N
117	TopicTrack	2019-12-28 13:15:11.261265	\N	topic_track	\N	\N	\N	\N	\N	topic_tracks	\N	\N	\N	25223	6	25223	\N	\N	\N
118	Topic	2019-12-28 13:15:11.27905	\N	topic	\N	\N	\N	\N	\N	topics	\N	\N	\N	25231	6	25231	\N	\N	\N
182	Promote	2019-12-28 22:53:32.632253	\N	promote	\N	\N	\N	\N	\N	promotes	\N	\N	\N	25564	1	25564	\N	\N	\N
183	TopicTrack	2019-12-28 22:53:32.905737	\N	topic_track	\N	\N	\N	\N	\N	topic_tracks	\N	\N	\N	25572	1	25572	\N	\N	\N
184	Topic	2019-12-28 22:53:33.217853	\N	topic	\N	\N	\N	\N	\N	topics	\N	\N	\N	25580	1	25580	\N	\N	\N
\.


--
-- Data for Name: page_filters; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.page_filters (id, attribute_id, created_at, equal_sign, page_id, sort_order, updated_at, value) FROM stdin;
\.


--
-- Data for Name: page_models; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.page_models (id, created_at, is_fetch_list_values, is_request_session, model_id, page_id, sort_order, updated_at, where_model_id) FROM stdin;
\.


--
-- Data for Name: pages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pages (id, class_name, created_at, entity_name, is_overwrite, label, list_sort_order_columns, model_id, name, note, parent_page_id, project_id, sort_order, updated_at, view_name, where_model_id) FROM stdin;
3	Project	2019-08-29 17:02:38.154402	project	t	\N	\N	17	Project	\N	\N	2	\N	\N	\N	\N
4	Page	2019-08-29 22:31:48.350679	page	t	\N	\N	16	Page	\N	\N	2	\N	\N	\N	\N
2	Route	2019-08-29 17:00:52.041253	route	t	ルート		29	Route		4	2	\N	2019-08-29 22:31:55.630762		\N
23	Student	2019-12-15 22:58:32.083105	student	t	生徒	\N	35	Student	\N	\N	3	\N	\N	\N	\N
24	Report	2019-12-15 23:26:12.926274	report	t	レポート	\N	36	Report	\N	\N	3	\N	\N	\N	\N
25	Contact	2019-12-23 01:34:10.726289	contact	t	お問い合わせ	\N	45	Contact	\N	\N	5	\N	\N	\N	\N
\.


--
-- Data for Name: projects; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.projects (id, created_at, database_id, entity_name, external_project_id, is_export_external_model, name, sort_order, updated_at, url) FROM stdin;
3	2019-12-08 22:28:36.264339	3	\N	\N	\N	school	1	2019-12-09 00:32:56.342764	\N
4	2019-12-15 12:53:32.714565	4	\N	\N	\N	sample	2	2019-12-15 13:25:48.14891	\N
1	2019-08-27 13:34:33.938567	1	\N	\N	\N	la_blog	3	2019-12-15 13:25:48.169734	\N
2	2019-08-29 12:21:10.870619	2	\N	\N	\N	project-manager	4	2019-12-15 13:25:48.189215	\N
5	2019-12-22 16:00:09.052177	5	\N	\N	\N	ict-kids	5	2019-12-22 23:27:27.622134	\N
6	2019-12-28 12:57:03.602025	6	\N	\N	\N	yoo_blog	6	\N	\N
\.


--
-- Data for Name: public_localize_strings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.public_localize_strings (id, created_at, label, name, sort_order, updated_at) FROM stdin;
\.


--
-- Data for Name: record_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.record_items (id, created_at, key, record_id, sort_order, updated_at, value, value_en) FROM stdin;
\.


--
-- Data for Name: records; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.records (id, created_at, label, laben_en, name, note, project_id, sort_order, updated_at) FROM stdin;
\.


--
-- Data for Name: relation_databases; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.relation_databases (id, created_at, old_database_id, project_id, sort_order, updated_at) FROM stdin;
\.


--
-- Data for Name: routes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.routes (id, created_at, updated_at, sort_order, controller, action, method, uri, page_id, middleware) FROM stdin;
\.


--
-- Data for Name: user_project_settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_project_settings (id, created_at, group_name, project_id, project_path, sort_order, updated_at, user_id, user_name) FROM stdin;
6	2019-08-29 12:22:26.166276	\N	2	/vagrant/www/project-manager/	\N	\N	1	\N
5	2019-08-27 14:03:59.971826		1	/vagrant/www/la_blog/	\N	2019-08-29 20:17:15.696324	1	\N
10	2019-12-08 22:42:59.526726	\N	3	/vagrant/www/school/	10	2019-12-08 23:17:14.721569	1	\N
11	2019-12-22 16:02:18.169969	\N	5	/vagrant/www/ict-kids/	11	2019-12-22 23:30:17.226494	1	\N
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, birthday_at, created_at, email, first_name, first_name_kana, last_name, last_name_kana, login_name, memo, password, sort_order, tmp_password, updated_at) FROM stdin;
1	\N	2019-08-27 13:47:37.486801	yohei.yoshikawa@gmail.com					yoo		\N	\N	\N	2019-08-29 19:59:16.895007
\.


--
-- Data for Name: view_item_group_members; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.view_item_group_members (id, created_at, sort_order, updated_at, view_item_group_id, view_item_id) FROM stdin;
\.


--
-- Data for Name: view_item_groups; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.view_item_groups (id, created_at, name, sort_order, updated_at, view_id) FROM stdin;
\.


--
-- Data for Name: view_item_models; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.view_item_models (id, created_at, is_id_index, page_id, sort_order, updated_at, value_model_id, view_item_id, where_model_id) FROM stdin;
\.


--
-- Data for Name: view_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.view_items (id, attribute_id, created_at, css_class, csv, form_model_id, form_type, label, label_column, link, link_param_id_attribute_id, localize_string_id, note, page_id, sort_order, updated_at, value_column, view_id, where_attribute_id, where_model_id, where_order, where_string) FROM stdin;
69	379	2019-12-23 01:34:11.260239	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	2	2019-12-23 02:45:33.234994	\N	16	\N	\N	\N	\N
1	287	2019-08-29 17:46:49.377028	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	2	2019-08-29 17:47:00.877809	\N	3	\N	\N	\N	\N
2	288	2019-08-29 17:46:49.400776	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	3	2019-08-29 17:47:00.900471	\N	3	\N	\N	\N	\N
70	380	2019-12-23 01:34:11.293577	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	3	2019-12-23 02:45:33.258056	\N	16	\N	\N	\N	\N
82	393	2019-12-23 01:34:11.527332	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	4	2019-12-23 02:45:33.292793	\N	16	\N	\N	\N	\N
81	392	2019-12-23 01:34:11.509289	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	5	2019-12-23 02:45:33.342011	\N	16	\N	\N	\N	\N
74	384	2019-12-23 01:34:11.382024	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	6	2019-12-23 02:45:33.37134	\N	16	\N	\N	\N	\N
8	290	2019-08-29 18:04:56.040496	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1	2019-08-29 18:05:18.500551	\N	4	\N	\N	\N	\N
5	287	2019-08-29 18:04:55.98593	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	2	2019-08-29 18:05:18.535518	\N	4	\N	\N	\N	\N
6	288	2019-08-29 18:04:56.004421	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	3	2019-08-29 18:05:18.557975	\N	4	\N	\N	\N	\N
7	289	2019-08-29 18:04:56.022691	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	4	2019-08-29 18:05:18.580262	\N	4	\N	\N	\N	\N
4	290	2019-08-29 17:46:49.440433	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1	2019-08-29 18:05:31.252665	\N	3	\N	\N	\N	\N
3	289	2019-08-29 17:46:49.420705	f	laravel_route_method	\N	radio	\N		\N	\N	\N		\N	4	2019-08-29 18:05:31.295636		3	\N	\N		\N
9	315	2019-12-14 11:30:55.396593	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	7	\N	\N	\N	\N
10	316	2019-12-14 11:30:55.423644	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	7	\N	\N	\N	\N
11	318	2019-12-14 11:30:55.456723	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	7	\N	\N	\N	\N
12	319	2019-12-14 11:30:55.482126	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	7	\N	\N	\N	\N
13	321	2019-12-14 11:30:55.501468	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	7	\N	\N	\N	\N
14	322	2019-12-14 11:30:55.520564	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	7	\N	\N	\N	\N
15	315	2019-12-14 11:30:55.548717	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	8	\N	\N	\N	\N
16	316	2019-12-14 11:30:55.564591	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	8	\N	\N	\N	\N
17	318	2019-12-14 11:30:55.580335	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	8	\N	\N	\N	\N
18	319	2019-12-14 11:30:55.598291	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	8	\N	\N	\N	\N
19	321	2019-12-14 11:30:55.615081	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	8	\N	\N	\N	\N
20	322	2019-12-14 11:30:55.633534	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	8	\N	\N	\N	\N
21	329	2019-12-14 11:30:55.725151	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	9	\N	\N	\N	\N
22	330	2019-12-14 11:30:55.74137	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	9	\N	\N	\N	\N
23	331	2019-12-14 11:30:55.758271	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	9	\N	\N	\N	\N
24	329	2019-12-14 11:30:55.782148	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	10	\N	\N	\N	\N
25	330	2019-12-14 11:30:55.798305	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	10	\N	\N	\N	\N
26	331	2019-12-14 11:30:55.814658	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	10	\N	\N	\N	\N
47	329	2019-12-17 00:26:44.478525	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	13	\N	\N	\N	\N
48	330	2019-12-17 00:26:44.497006	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	13	\N	\N	\N	\N
49	331	2019-12-17 00:26:44.520333	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	13	\N	\N	\N	\N
50	341	2019-12-17 00:26:44.542413	\N	active	\N	radio	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	13	\N	\N	\N	\N
51	329	2019-12-17 00:26:44.573319	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	14	\N	\N	\N	\N
52	330	2019-12-17 00:26:44.593929	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	14	\N	\N	\N	\N
53	331	2019-12-17 00:26:44.613074	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	14	\N	\N	\N	\N
54	341	2019-12-17 00:26:44.643452	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	14	\N	\N	\N	\N
75	385	2019-12-23 01:34:11.399064	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	7	2019-12-23 02:45:33.403822	\N	16	\N	\N	\N	\N
36	316	2019-12-17 00:26:44.090346	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	2	2019-12-17 00:27:03.415356	\N	11	\N	\N	\N	\N
37	318	2019-12-17 00:26:44.131169	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	3	2019-12-17 00:27:03.437117	\N	11	\N	\N	\N	\N
39	321	2019-12-17 00:26:44.190253	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	4	2019-12-17 00:27:03.456211	\N	11	\N	\N	\N	\N
40	322	2019-12-17 00:26:44.213445	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	5	2019-12-17 00:27:03.478177	\N	11	\N	\N	\N	\N
38	319	2019-12-17 00:26:44.165063	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	6	2019-12-17 00:27:03.498061	\N	11	\N	\N	\N	\N
41	315	2019-12-17 00:26:44.2698	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1	2019-12-17 00:29:25.847634	\N	12	\N	\N	\N	\N
42	316	2019-12-17 00:26:44.292957	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	2	2019-12-17 00:29:25.865785	\N	12	\N	\N	\N	\N
43	318	2019-12-17 00:26:44.317491	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	3	2019-12-17 00:29:25.884812	\N	12	\N	\N	\N	\N
45	321	2019-12-17 00:26:44.352985	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	4	2019-12-17 00:29:25.903595	\N	12	\N	\N	\N	\N
44	319	2019-12-17 00:26:44.336203	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	5	2019-12-17 00:29:25.921158	\N	12	\N	\N	\N	\N
46	322	2019-12-17 00:26:44.374156	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	6	2019-12-17 00:29:25.940556	\N	12	\N	\N	\N	\N
35	315	2019-12-17 00:26:44.059034	f		\N	selectdate	\N		\N	\N	\N		\N	1	2019-12-17 01:59:23.236395		11	\N	\N		\N
80	391	2019-12-23 01:34:11.490987	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1	2019-12-23 02:45:33.201746	\N	16	\N	\N	\N	\N
79	390	2019-12-23 01:34:11.473164	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	9	2019-12-23 02:45:48.219161	\N	16	\N	\N	\N	\N
66	391	2019-12-23 01:34:11.19083	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	1	2019-12-23 02:59:15.987741	\N	15	\N	\N	\N	\N
55	379	2019-12-23 01:34:10.928847	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	2	2019-12-23 02:59:16.012437	\N	15	\N	\N	\N	\N
56	380	2019-12-23 01:34:10.951762	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	3	2019-12-23 02:59:16.037088	\N	15	\N	\N	\N	\N
57	381	2019-12-23 01:34:10.97655	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	4	2019-12-23 02:59:16.084917	\N	15	\N	\N	\N	\N
58	382	2019-12-23 01:34:10.998872	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	5	2019-12-23 02:59:16.113396	\N	15	\N	\N	\N	\N
59	383	2019-12-23 01:34:11.023861	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	6	2019-12-23 02:59:16.171711	\N	15	\N	\N	\N	\N
60	384	2019-12-23 01:34:11.050673	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	7	2019-12-23 02:59:16.219978	\N	15	\N	\N	\N	\N
61	385	2019-12-23 01:34:11.073969	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	8	2019-12-23 02:59:16.252399	\N	15	\N	\N	\N	\N
68	393	2019-12-23 01:34:11.233444	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	9	2019-12-23 02:59:16.286194	\N	15	\N	\N	\N	\N
67	392	2019-12-23 01:34:11.209699	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	10	2019-12-23 02:59:16.325359	\N	15	\N	\N	\N	\N
62	387	2019-12-23 01:34:11.099296	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	11	2019-12-23 02:59:16.398826	\N	15	\N	\N	\N	\N
63	388	2019-12-23 01:34:11.125318	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	12	2019-12-23 02:59:16.428222	\N	15	\N	\N	\N	\N
64	389	2019-12-23 01:34:11.14702	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	13	2019-12-23 02:59:16.463655	\N	15	\N	\N	\N	\N
65	390	2019-12-23 01:34:11.169848	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	14	2019-12-23 02:59:16.487035	\N	15	\N	\N	\N	\N
\.


--
-- Data for Name: views; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.views (id, created_at, is_overwrite, label, label_width, name, note, page_id, sort_order, updated_at) FROM stdin;
1	2019-08-28 10:43:45.3535	t	編集	\N	edit	\N	1	\N	\N
3	2019-08-29 17:00:52.067035	t	編集	\N	edit	\N	2	\N	\N
4	2019-08-29 17:00:52.111729	t	一覧	\N	list	\N	2	\N	\N
5	2019-08-29 17:02:38.184261	t	編集	\N	edit	\N	3	\N	\N
6	2019-08-29 17:02:38.246379	t	一覧	\N	list	\N	3	\N	\N
2	2019-08-28 10:43:45.377422	t	一覧	\N	index		1	\N	2019-08-30 19:19:29.473842
7	2019-12-14 11:30:55.294696	t	編集	\N	edit	\N	5	\N	\N
8	2019-12-14 11:30:55.332767	t	一覧	\N	list	\N	5	\N	\N
9	2019-12-14 11:30:55.66856	t	編集	\N	edit	\N	6	\N	\N
10	2019-12-14 11:30:55.684155	t	一覧	\N	list	\N	6	\N	\N
11	\N	t	編集	\N	edit	\N	23	\N	\N
12	\N	t	一覧	\N	list	\N	23	\N	\N
13	\N	t	編集	\N	edit	\N	24	\N	\N
14	\N	t	一覧	\N	list	\N	24	\N	\N
15	\N	t	編集	\N	edit	\N	25	\N	\N
16	\N	t	一覧	\N	list	\N	25	\N	\N
\.


--
-- Name: admins_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.admins_id_seq', 1, false);


--
-- Name: api_actions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.api_actions_id_seq', 1, false);


--
-- Name: api_groups_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.api_groups_id_seq', 1, false);


--
-- Name: api_params_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.api_params_id_seq', 1, false);


--
-- Name: apis_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.apis_id_seq', 1, false);


--
-- Name: attributes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.attributes_id_seq', 599, true);


--
-- Name: databases_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.databases_id_seq', 6, true);


--
-- Name: langs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.langs_id_seq', 1, true);


--
-- Name: localize_strings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.localize_strings_id_seq', 77, true);


--
-- Name: menus_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.menus_id_seq', 1, false);


--
-- Name: models_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.models_id_seq', 184, true);


--
-- Name: page_filters_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.page_filters_id_seq', 1, false);


--
-- Name: page_models_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.page_models_id_seq', 1, false);


--
-- Name: pages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pages_id_seq', 25, true);


--
-- Name: projects_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.projects_id_seq', 6, true);


--
-- Name: public_localize_strings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.public_localize_strings_id_seq', 1, false);


--
-- Name: record_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.record_items_id_seq', 1, false);


--
-- Name: records_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.records_id_seq', 1, false);


--
-- Name: relation_databases_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.relation_databases_id_seq', 1, false);


--
-- Name: routes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.routes_id_seq', 12, true);


--
-- Name: user_project_settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.user_project_settings_id_seq', 11, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 1, true);


--
-- Name: view_item_group_members_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.view_item_group_members_id_seq', 1, false);


--
-- Name: view_item_groups_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.view_item_groups_id_seq', 1, false);


--
-- Name: view_item_models_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.view_item_models_id_seq', 1, false);


--
-- Name: view_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.view_items_id_seq', 82, true);


--
-- Name: views_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.views_id_seq', 16, true);


--
-- Name: admins admins_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admins
    ADD CONSTRAINT admins_email_key UNIQUE (email);


--
-- Name: admins admins_login_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admins
    ADD CONSTRAINT admins_login_name_key UNIQUE (login_name);


--
-- Name: admins admins_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admins
    ADD CONSTRAINT admins_pkey PRIMARY KEY (id);


--
-- Name: api_actions api_actions_name_api_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.api_actions
    ADD CONSTRAINT api_actions_name_api_id_key UNIQUE (name, api_id);


--
-- Name: api_actions api_actions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.api_actions
    ADD CONSTRAINT api_actions_pkey PRIMARY KEY (id);


--
-- Name: api_groups api_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.api_groups
    ADD CONSTRAINT api_groups_pkey PRIMARY KEY (id);


--
-- Name: api_params api_params_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.api_params
    ADD CONSTRAINT api_params_pkey PRIMARY KEY (id);


--
-- Name: apis apis_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.apis
    ADD CONSTRAINT apis_pkey PRIMARY KEY (id);


--
-- Name: attributes attributes_name_model_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attributes
    ADD CONSTRAINT attributes_name_model_id_key UNIQUE (name, model_id);


--
-- Name: attributes attributes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attributes
    ADD CONSTRAINT attributes_pkey PRIMARY KEY (id);


--
-- Name: databases databases_name_hostname_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.databases
    ADD CONSTRAINT databases_name_hostname_key UNIQUE (name, hostname);


--
-- Name: databases databases_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.databases
    ADD CONSTRAINT databases_pkey PRIMARY KEY (id);


--
-- Name: langs langs_lang_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.langs
    ADD CONSTRAINT langs_lang_key UNIQUE (lang);


--
-- Name: langs langs_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.langs
    ADD CONSTRAINT langs_name_key UNIQUE (name);


--
-- Name: langs langs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.langs
    ADD CONSTRAINT langs_pkey PRIMARY KEY (id);


--
-- Name: localize_strings localize_strings_name_project_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.localize_strings
    ADD CONSTRAINT localize_strings_name_project_id_key UNIQUE (project_id, name);


--
-- Name: localize_strings localize_strings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.localize_strings
    ADD CONSTRAINT localize_strings_pkey PRIMARY KEY (id);


--
-- Name: menus menus_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.menus
    ADD CONSTRAINT menus_pkey PRIMARY KEY (id);


--
-- Name: models models_name_project_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.models
    ADD CONSTRAINT models_name_project_id_key UNIQUE (name, project_id);


--
-- Name: models models_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.models
    ADD CONSTRAINT models_pkey PRIMARY KEY (id);


--
-- Name: page_filters page_filters_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.page_filters
    ADD CONSTRAINT page_filters_pkey PRIMARY KEY (id);


--
-- Name: page_models page_models_model_id_page_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.page_models
    ADD CONSTRAINT page_models_model_id_page_id_key UNIQUE (model_id, page_id);


--
-- Name: page_models page_models_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.page_models
    ADD CONSTRAINT page_models_pkey PRIMARY KEY (id);


--
-- Name: pages pages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pages
    ADD CONSTRAINT pages_pkey PRIMARY KEY (id);


--
-- Name: projects projects_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.projects
    ADD CONSTRAINT projects_pkey PRIMARY KEY (id);


--
-- Name: public_localize_strings public_localize_strings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.public_localize_strings
    ADD CONSTRAINT public_localize_strings_pkey PRIMARY KEY (id);


--
-- Name: record_items record_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.record_items
    ADD CONSTRAINT record_items_pkey PRIMARY KEY (id);


--
-- Name: records records_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.records
    ADD CONSTRAINT records_pkey PRIMARY KEY (id);


--
-- Name: relation_databases relation_databases_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.relation_databases
    ADD CONSTRAINT relation_databases_pkey PRIMARY KEY (id);


--
-- Name: routes routes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.routes
    ADD CONSTRAINT routes_pkey PRIMARY KEY (id);


--
-- Name: user_project_settings user_project_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_project_settings
    ADD CONSTRAINT user_project_settings_pkey PRIMARY KEY (id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: view_item_group_members view_item_group_members_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_item_group_members
    ADD CONSTRAINT view_item_group_members_pkey PRIMARY KEY (id);


--
-- Name: view_item_group_members view_item_group_members_view_item_group_id_view_item_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_item_group_members
    ADD CONSTRAINT view_item_group_members_view_item_group_id_view_item_id_key UNIQUE (view_item_group_id, view_item_id);


--
-- Name: view_item_groups view_item_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_item_groups
    ADD CONSTRAINT view_item_groups_pkey PRIMARY KEY (id);


--
-- Name: view_item_models view_item_models_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_item_models
    ADD CONSTRAINT view_item_models_pkey PRIMARY KEY (id);


--
-- Name: view_items view_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_items
    ADD CONSTRAINT view_items_pkey PRIMARY KEY (id);


--
-- Name: views views_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.views
    ADD CONSTRAINT views_pkey PRIMARY KEY (id);


--
-- Name: api_actions api_actions_api_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.api_actions
    ADD CONSTRAINT api_actions_api_id_fkey FOREIGN KEY (api_id) REFERENCES public.apis(id);


--
-- Name: apis apis_api_group_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.apis
    ADD CONSTRAINT apis_api_group_id_fkey FOREIGN KEY (api_group_id) REFERENCES public.api_groups(id);


--
-- Name: apis apis_project_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.apis
    ADD CONSTRAINT apis_project_id_fkey FOREIGN KEY (project_id) REFERENCES public.projects(id);


--
-- Name: attributes attributes_model_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attributes
    ADD CONSTRAINT attributes_model_id_fkey FOREIGN KEY (model_id) REFERENCES public.models(id) ON DELETE CASCADE;


--
-- Name: localize_strings localize_strings_project_id_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.localize_strings
    ADD CONSTRAINT localize_strings_project_id_id_fkey FOREIGN KEY (project_id) REFERENCES public.projects(id);


--
-- Name: models models_project_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.models
    ADD CONSTRAINT models_project_id_fkey FOREIGN KEY (project_id) REFERENCES public.projects(id) ON DELETE CASCADE;


--
-- Name: page_filters page_filters_attribute_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.page_filters
    ADD CONSTRAINT page_filters_attribute_id_fkey FOREIGN KEY (attribute_id) REFERENCES public.attributes(id);


--
-- Name: page_filters page_filters_page_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.page_filters
    ADD CONSTRAINT page_filters_page_id_fkey FOREIGN KEY (page_id) REFERENCES public.pages(id);


--
-- Name: page_models page_models_model_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.page_models
    ADD CONSTRAINT page_models_model_id_fkey FOREIGN KEY (model_id) REFERENCES public.models(id);


--
-- Name: page_models page_models_page_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.page_models
    ADD CONSTRAINT page_models_page_id_fkey FOREIGN KEY (page_id) REFERENCES public.pages(id);


--
-- Name: pages pages_model_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pages
    ADD CONSTRAINT pages_model_id_fkey FOREIGN KEY (model_id) REFERENCES public.models(id);


--
-- Name: pages pages_parent_page_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pages
    ADD CONSTRAINT pages_parent_page_id_fkey FOREIGN KEY (parent_page_id) REFERENCES public.pages(id);


--
-- Name: pages pages_project_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pages
    ADD CONSTRAINT pages_project_id_fkey FOREIGN KEY (project_id) REFERENCES public.projects(id);


--
-- Name: pages pages_where_model_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pages
    ADD CONSTRAINT pages_where_model_id_fkey FOREIGN KEY (where_model_id) REFERENCES public.models(id);


--
-- Name: record_items record_items_record_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.record_items
    ADD CONSTRAINT record_items_record_id_fkey FOREIGN KEY (record_id) REFERENCES public.records(id) ON DELETE CASCADE;


--
-- Name: records records_project_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.records
    ADD CONSTRAINT records_project_id_fkey FOREIGN KEY (project_id) REFERENCES public.projects(id);


--
-- Name: relation_databases relation_databases_old_database_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.relation_databases
    ADD CONSTRAINT relation_databases_old_database_id_fkey FOREIGN KEY (old_database_id) REFERENCES public.databases(id);


--
-- Name: relation_databases relation_databases_project_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.relation_databases
    ADD CONSTRAINT relation_databases_project_id_fkey FOREIGN KEY (project_id) REFERENCES public.projects(id);


--
-- Name: routes routes_page_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.routes
    ADD CONSTRAINT routes_page_id_fkey FOREIGN KEY (page_id) REFERENCES public.pages(id) ON DELETE CASCADE;


--
-- Name: view_item_group_members view_item_group_members_view_item_group_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_item_group_members
    ADD CONSTRAINT view_item_group_members_view_item_group_id_fkey FOREIGN KEY (view_item_group_id) REFERENCES public.view_item_groups(id);


--
-- Name: view_item_group_members view_item_group_members_view_item_id_fkey1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_item_group_members
    ADD CONSTRAINT view_item_group_members_view_item_id_fkey1 FOREIGN KEY (view_item_id) REFERENCES public.view_items(id);


--
-- Name: view_item_groups view_item_groups_view_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_item_groups
    ADD CONSTRAINT view_item_groups_view_id_fkey FOREIGN KEY (view_id) REFERENCES public.views(id);


--
-- Name: view_items view_items_attribute_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_items
    ADD CONSTRAINT view_items_attribute_id_fkey FOREIGN KEY (attribute_id) REFERENCES public.attributes(id) ON DELETE CASCADE;


--
-- Name: view_items view_items_link_param_id_attribute_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_items
    ADD CONSTRAINT view_items_link_param_id_attribute_id_fkey FOREIGN KEY (link_param_id_attribute_id) REFERENCES public.attributes(id) ON DELETE CASCADE;


--
-- Name: view_items view_items_localize_string_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_items
    ADD CONSTRAINT view_items_localize_string_id_fkey FOREIGN KEY (localize_string_id) REFERENCES public.localize_strings(id) ON DELETE CASCADE;


--
-- Name: view_items view_items_page_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_items
    ADD CONSTRAINT view_items_page_id_fkey FOREIGN KEY (page_id) REFERENCES public.pages(id) ON DELETE CASCADE;


--
-- Name: view_items view_items_view_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_items
    ADD CONSTRAINT view_items_view_id_fkey FOREIGN KEY (view_id) REFERENCES public.views(id) ON DELETE CASCADE;


--
-- Name: view_items view_items_where_attribute_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_items
    ADD CONSTRAINT view_items_where_attribute_id_fkey FOREIGN KEY (where_attribute_id) REFERENCES public.attributes(id) ON DELETE CASCADE;


--
-- Name: view_items view_items_where_model_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.view_items
    ADD CONSTRAINT view_items_where_model_id_fkey FOREIGN KEY (where_model_id) REFERENCES public.models(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

