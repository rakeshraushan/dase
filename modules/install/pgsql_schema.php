<?php

$query =<<<EOF

CREATE TABLE {$table_prefix}admin_search_table (
    id serial NOT NULL,
    item_id integer,
    collection_id integer,
    value_text text,
    status_id integer,
    updated character varying(50)
);

CREATE TABLE {$table_prefix}attribute (
    id serial NOT NULL,
    ascii_id character varying(200),
    collection_id integer,
    attribute_name character varying(200),
    usage_notes character varying(2000),
    sort_order integer DEFAULT 999,
    in_basic_search boolean DEFAULT true,
    is_on_list_display boolean DEFAULT true,
    is_public boolean DEFAULT true,
    mapped_admin_att_id integer DEFAULT 0,
    updated character varying(50),
    html_input_type character varying(50) DEFAULT 'text'::character varying
);

CREATE TABLE {$table_prefix}attribute_category (
    id serial NOT NULL,
    attribute_id integer,
    category_id integer
);

CREATE TABLE {$table_prefix}attribute_item_type (
    id serial NOT NULL,
    item_type_id integer NOT NULL,
    attribute_id integer NOT NULL,
    cardinality character varying(20) DEFAULT '0:m'::character varying
);

CREATE TABLE {$table_prefix}category (
    id serial NOT NULL,
    term character varying(200),
    scheme character varying(200),
    label character varying(200)
);

CREATE TABLE {$table_prefix}collection (
    id serial NOT NULL,
    ascii_id character varying(200),
    collection_name character varying(200),
    description character varying(2000),
    is_public boolean,
    created character varying(50),
    updated character varying(50),
    visibility character varying(50),
    item_count integer DEFAULT 0
);

CREATE TABLE {$table_prefix}collection_category (
    id serial NOT NULL,
    collection_id integer,
    category_id integer
);

CREATE TABLE {$table_prefix}collection_manager (
    id serial NOT NULL,
    collection_ascii_id character varying(200),
    dase_user_eid character varying(20),
    auth_level character varying(20),
    expiration character varying(50),
    created character varying(50)
);

CREATE TABLE {$table_prefix}comment (
    id serial NOT NULL,
    text text,
    "type" character varying(10),
    item_id integer,
    p_collection_ascii_id character varying(100),
    p_serial_number character varying(100),
    updated character varying(100),
    updated_by_eid character varying(100)
);

CREATE TABLE {$table_prefix}content (
    id serial NOT NULL,
    text text,
    "type" character varying(10),
    item_id integer,
    p_collection_ascii_id character varying(100),
    p_serial_number character varying(100),
    updated character varying(100),
    updated_by_eid character varying(100)
);

CREATE TABLE {$table_prefix}dase_user (
    id serial NOT NULL,
    eid character varying(255),
    name character varying(200),
    has_access_exception boolean DEFAULT false,
    cb character varying(200),
    last_cb_access character varying(50),
    display character varying(20),
    max_items integer,
    last_item character varying(2000),
    last_action character varying(2000),
    current_search_cache_id integer,
    current_collections character varying(2000),
    backtrack character varying(2000),
    service_key_md5 character varying(200),
    template_composite character varying(2000)
);

CREATE TABLE {$table_prefix}defined_value (
    id serial NOT NULL,
    attribute_id integer,
    value_text character varying(200)
);

CREATE TABLE {$table_prefix}item (
    id serial NOT NULL,
    serial_number character varying(200),
    collection_id integer,
    item_type_id integer DEFAULT 0,
    created character varying(50) DEFAULT 0,
    updated character varying(50) DEFAULT 0,
    status character varying(50),
    created_by_eid character varying(50)
);

CREATE TABLE {$table_prefix}input_template (
    id serial NOT NULL,
    collection_manager_id integer,
    attribute_id integer
);

CREATE TABLE {$table_prefix}item_link (
    id serial NOT NULL,
    href character varying(2000),
    rel character varying(100),
    "type" character varying(50),
    title character varying(100),
    length integer,
    item_unique character varying(100)
);

CREATE TABLE {$table_prefix}item_category (
    id serial NOT NULL,
    item_id integer,
    category_id integer
);

CREATE TABLE {$table_prefix}item_link_category (
    id serial NOT NULL,
    item_link_id integer,
    category_id integer
);

CREATE TABLE {$table_prefix}item_type (
    id serial NOT NULL,
    collection_id integer DEFAULT 0 NOT NULL,
    name character varying(200),
    ascii_id character varying(200) NOT NULL,
    description character varying(2000)
);

CREATE TABLE {$table_prefix}media_file (
    id serial NOT NULL,
    item_id integer,
    filename character varying(2000),
    height integer,
    width integer,
    mime_type character varying(200),
    size character varying(20),
    p_serial_number character varying(200) DEFAULT 0,
    p_collection_ascii_id character varying(200) DEFAULT 0,
    file_size integer,
    updated character varying(50),
    md5 character varying(200)
);

CREATE TABLE {$table_prefix}tag_item (
    id serial NOT NULL,
    tag_id integer,
    item_id integer,
    annotation character varying(2000),
    sort_order integer,
    size character varying(200),
    p_serial_number character varying(20) DEFAULT 0,
    p_collection_ascii_id character varying(200) DEFAULT 0,
    updated character varying(50)
);


CREATE TABLE {$table_prefix}search_cache (
    id serial NOT NULL,
    query character varying(2000),
    dase_user_id integer,
    attribute_id integer,
    collection_id_string character varying(2000),
    refine character varying(2000),
    item_id_string text,
    exact_search integer,
    is_stale boolean DEFAULT false,
    sort_by integer,
    cb_id integer,
    search_md5 character varying(40),
    "timestamp" character varying(50)
);

CREATE TABLE {$table_prefix}search_table (
    id serial NOT NULL,
    item_id integer NOT NULL,
    collection_id integer NOT NULL,
    value_text text,
    status_id integer,
    updated character varying(50)
);

CREATE TABLE {$table_prefix}subscription (
    id serial NOT NULL,
    dase_user_id integer,
    tag_id integer
);

CREATE TABLE {$table_prefix}tag (
    id serial NOT NULL,
    name character varying(200),
    description character varying(200),
    dase_user_id integer,
    is_public boolean DEFAULT false,
    background character varying(20) DEFAULT 'white'::character varying,
    admin_collection_id integer,
    ascii_id character varying(200),
    created character varying(50),
    updated character varying(50),
    "type" character varying(50) DEFAULT 'set'::character varying,
    eid character varying(50),
    visibility character varying(50),
    item_count integer DEFAULT 0
);

CREATE TABLE {$table_prefix}tag_category (
    id serial NOT NULL,
    tag_id integer,
    category_id integer
);

CREATE TABLE {$table_prefix}tag_item_category (
    id serial NOT NULL,
    tag_item_id integer,
    category_id integer
);

CREATE TABLE {$table_prefix}value (
    id serial NOT NULL,
    item_id integer,
    attribute_id integer,
    value_text text
);

CREATE TABLE {$table_prefix}value_revision_history (
    id serial NOT NULL,
    dase_user_eid character varying(20),
    deleted_text text,
    added_text text,
    item_serial_number character varying(200),
    attribute_name character varying(200),
    collection_ascii_id character varying(200),
    "timestamp" character varying(50)
);

CREATE SEQUENCE {$table_prefix}attribute_category_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}collection_category_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}item_category_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}item_link_category_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}tag_category_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}tag_item_category_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}admin_search_table_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}attribute_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}attribute_item_type_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}collection_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}collection_manager_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}content_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}comment_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}dase_user_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}defined_value_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}item_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}input_template_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}item_link_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}item_type_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}media_file_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}tag_item_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}search_cache_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}search_table_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}subscription_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}tag_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}value_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE SEQUENCE {$table_prefix}value_revision_history_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

EOF;

/*
$name = 'forms';
$type = 'pgsql';
$host = '';
$user = '';
$pass = '';
$dsn = $type.':host='.$host.';dbname='.$name;
try {
	$pdo = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
	print $e->getMessage();
}
 */
//$pdo->exec($query);
//print_r($pdo->errorInfo());
