<?php

$query =<<<EOF

--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS 'Standard public schema';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = true;

--
-- Name: admin_search_table; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE admin_search_table (
    id serial NOT NULL,
    item_id integer,
    collection_id integer,
    value_text text,
    status_id integer,
    updated character varying(50)
);


ALTER TABLE public.admin_search_table OWNER TO postgres;

--
-- Name: admin_search_table_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE admin_search_table_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.admin_search_table_seq OWNER TO postgres;

--
-- Name: application_monitor_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE application_monitor_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.application_monitor_seq OWNER TO postgres;

--
-- Name: attribute; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE attribute (
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


ALTER TABLE public.attribute OWNER TO postgres;

--
-- Name: COLUMN attribute.html_input_type; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN attribute.html_input_type IS 'text,textarea,radio,checkbox,select,listbox,no_edit,text_with_menu';


--
-- Name: attribute_category_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE attribute_category_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.attribute_category_seq OWNER TO postgres;

--
-- Name: attribute_item_type; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE attribute_item_type (
    id serial NOT NULL,
    item_type_id integer NOT NULL,
    attribute_id integer NOT NULL,
    cardinality character varying(20) DEFAULT '0:m'::character varying
);


ALTER TABLE public.attribute_item_type OWNER TO postgres;

--
-- Name: attribute_item_type_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE attribute_item_type_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.attribute_item_type_seq OWNER TO postgres;

--
-- Name: attribute_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE attribute_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.attribute_seq OWNER TO postgres;

--
-- Name: category_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE category_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.category_seq OWNER TO postgres;

--
-- Name: collection; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE collection (
    id serial NOT NULL,
    ascii_id character varying(200),
    collection_name character varying(200),
    description character varying(2000),
    is_public boolean,
    created character varying(50),
    updated character varying(50),
    visibility character varying(50)
);


ALTER TABLE public.collection OWNER TO postgres;

--
-- Name: COLUMN collection.visibility; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN collection.visibility IS 'manager,user (meaning any valid user), public';


--
-- Name: collection_manager; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE collection_manager (
    id serial NOT NULL,
    collection_ascii_id character varying(200),
    dase_user_eid character varying(20),
    auth_level character varying(20),
    expiration character varying(50),
    created character varying(50)
);


ALTER TABLE public.collection_manager OWNER TO postgres;

--
-- Name: collection_manager_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE collection_manager_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.collection_manager_seq OWNER TO postgres;

--
-- Name: collection_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE collection_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.collection_seq OWNER TO postgres;

SET default_with_oids = false;

--
-- Name: content; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE content (
    id serial NOT NULL,
    text text,
    "type" character varying(10),
    item_id integer,
    p_collection_ascii_id character varying(100),
    p_serial_number character varying(100),
    updated character varying(100),
    updated_by_eid character varying(100)
);


ALTER TABLE public.content OWNER TO postgres;

--
-- Name: content_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE content_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.content_seq OWNER TO postgres;

SET default_with_oids = true;

--
-- Name: dase_user; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE dase_user (
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
    template_composite character varying(2000)
);


ALTER TABLE public.dase_user OWNER TO postgres;

--
-- Name: dase_user_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE dase_user_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.dase_user_seq OWNER TO postgres;

--
-- Name: defined_value; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE defined_value (
    id serial NOT NULL,
    attribute_id integer,
    value_text character varying(200)
);


ALTER TABLE public.defined_value OWNER TO postgres;

--
-- Name: defined_value_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE defined_value_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.defined_value_seq OWNER TO postgres;

--
-- Name: item; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE item (
    id serial NOT NULL,
    serial_number character varying(200),
    collection_id integer,
    item_type_id integer DEFAULT 0,
    created character varying(50) DEFAULT 0,
    updated character varying(50) DEFAULT 0,
    status character varying(50),
    created_by_eid character varying(50)
);


ALTER TABLE public.item OWNER TO postgres;

--
-- Name: dupe ser nums; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW "dupe ser nums" AS
    SELECT item.serial_number, item.collection_id, count(*) AS count FROM item GROUP BY item.serial_number, item.collection_id ORDER BY count(*) DESC LIMIT 10;


ALTER TABLE public."dupe ser nums" OWNER TO postgres;

--
-- Name: html_input_type_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE html_input_type_seq
    START WITH 9
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.html_input_type_seq OWNER TO postgres;

--
-- Name: input_template; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE input_template (
    id serial NOT NULL,
    collection_manager_id integer,
    attribute_id integer
);


ALTER TABLE public.input_template OWNER TO postgres;

--
-- Name: input_template_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE input_template_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.input_template_seq OWNER TO postgres;

--
-- Name: item_content_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE item_content_seq
    START WITH 16
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.item_content_seq OWNER TO postgres;

--
-- Name: item_link_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE item_link_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.item_link_seq OWNER TO postgres;

SET default_with_oids = false;

--
-- Name: item_link; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE item_link (
    id serial NOT NULL,
    href character varying(2000),
    rel character varying(100),
    "type" character varying(50),
    title character varying(100),
    length integer,
    item_unique character varying(100)
);


ALTER TABLE public.item_link OWNER TO postgres;

--
-- Name: item_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE item_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.item_seq OWNER TO postgres;

--
-- Name: item_status_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE item_status_seq
    START WITH 4
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.item_status_seq OWNER TO postgres;

SET default_with_oids = true;

--
-- Name: item_type; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE item_type (
    id serial NOT NULL,
    collection_id integer DEFAULT 0 NOT NULL,
    name character varying(200),
    ascii_id character varying(200) NOT NULL,
    description character varying(2000)
);


ALTER TABLE public.item_type OWNER TO postgres;

--
-- Name: item_type_relation_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE item_type_relation_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.item_type_relation_seq OWNER TO postgres;

--
-- Name: item_type_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE item_type_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.item_type_seq OWNER TO postgres;

--
-- Name: media_attribute_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE media_attribute_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.media_attribute_seq OWNER TO postgres;

--
-- Name: media_file; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE media_file (
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


ALTER TABLE public.media_file OWNER TO postgres;

--
-- Name: media_file_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE media_file_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.media_file_seq OWNER TO postgres;

--
-- Name: media_value_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE media_value_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.media_value_seq OWNER TO postgres;

--
-- Name: message_queue_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE message_queue_seq
    START WITH 12
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.message_queue_seq OWNER TO postgres;

--
-- Name: tag_item; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE tag_item (
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


ALTER TABLE public.tag_item OWNER TO postgres;

--
-- Name: schema_element_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE schema_element_seq
    START WITH 10
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.schema_element_seq OWNER TO postgres;

--
-- Name: schema_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE schema_seq
    START WITH 5
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.schema_seq OWNER TO postgres;

--
-- Name: search_cache; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE search_cache (
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


ALTER TABLE public.search_cache OWNER TO postgres;

--
-- Name: search_cache_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE search_cache_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.search_cache_seq OWNER TO postgres;

--
-- Name: search_table; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE search_table (
    id serial NOT NULL,
    item_id integer NOT NULL,
    collection_id integer NOT NULL,
    value_text text,
    status_id integer,
    updated character varying(50)
);


ALTER TABLE public.search_table OWNER TO postgres;

--
-- Name: search_table_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE search_table_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.search_table_seq OWNER TO postgres;

--
-- Name: subscription; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE subscription (
    id serial NOT NULL,
    dase_user_id integer,
    tag_id integer
);


ALTER TABLE public.subscription OWNER TO postgres;

--
-- Name: subscription_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE subscription_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.subscription_seq OWNER TO postgres;

--
-- Name: tag; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE tag (
    id serial NOT NULL,
    name character varying(200),
    description character varying(200),
    dase_user_id integer,
    is_public boolean DEFAULT false,
    background character varying(20) DEFAULT 'white'::character varying,
    admin_collection_id integer,
    ascii_id character varying(200),
    created character varying(50),
    "type" character varying(50) DEFAULT 'set'::character varying,
    eid character varying(50),
    visibility character varying(50)
);


ALTER TABLE public.tag OWNER TO postgres;

--
-- Name: COLUMN tag."type"; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN tag."type" IS 'set,slideshow,cart, or admin';


--
-- Name: COLUMN tag.visibility; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN tag.visibility IS 'owner,user (meaning any valid user), or public';


--
-- Name: tag items by collection; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW "tag items by collection" AS
    SELECT collection.collection_name, item.collection_id, count(*) AS count FROM collection, item, tag_item WHERE ((item.id = tag_item.item_id) AND (collection.id = item.collection_id)) GROUP BY collection.collection_name, item.collection_id;


ALTER TABLE public."tag items by collection" OWNER TO postgres;

--
-- Name: tag_item_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE tag_item_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.tag_item_seq OWNER TO postgres;

--
-- Name: tag_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE tag_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.tag_seq OWNER TO postgres;

--
-- Name: tag_type_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE tag_type_seq
    START WITH 6
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.tag_type_seq OWNER TO postgres;

--
-- Name: upload_filetype_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE upload_filetype_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.upload_filetype_seq OWNER TO postgres;

--
-- Name: upload_status_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE upload_status_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.upload_status_seq OWNER TO postgres;

--
-- Name: user_collection_data; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW user_collection_data AS
    SELECT tag_item.id, item.serial_number, collection.ascii_id FROM tag_item, item, collection WHERE ((tag_item.item_id = item.id) AND (item.collection_id = collection.id));


ALTER TABLE public.user_collection_data OWNER TO postgres;

--
-- Name: user_history_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE user_history_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.user_history_seq OWNER TO postgres;

--
-- Name: util_cache_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE util_cache_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.util_cache_seq OWNER TO postgres;

--
-- Name: value; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE value (
    id serial NOT NULL,
    item_id integer,
    attribute_id integer,
    value_text text
);


ALTER TABLE public.value OWNER TO postgres;

--
-- Name: value_revision_history; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE value_revision_history (
    id serial NOT NULL,
    dase_user_eid character varying(20),
    deleted_text text,
    added_text text,
    item_serial_number character varying(200),
    attribute_name character varying(200),
    collection_ascii_id character varying(200),
    "timestamp" character varying(50)
);


ALTER TABLE public.value_revision_history OWNER TO postgres;

--
-- Name: value_revision_history_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE value_revision_history_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.value_revision_history_seq OWNER TO postgres;

--
-- Name: value_search_table_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE value_search_table_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.value_search_table_seq OWNER TO postgres;

--
-- Name: value_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE value_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.value_seq OWNER TO postgres;

--
-- Name: web_service_user_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE web_service_user_seq
    START WITH 3
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.web_service_user_seq OWNER TO postgres;

--
-- Name: whose collections are messed up; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW "whose collections are messed up" AS
    SELECT dase_user.name, tag.name AS tag_name FROM dase_user, tag WHERE ((dase_user.id = tag.dase_user_id) AND (tag.id IN (SELECT tag_item.tag_id FROM (tag_item LEFT JOIN item ON ((tag_item.item_id = item.id))) WHERE (item.id IS NULL) GROUP BY tag_item.tag_id)));


ALTER TABLE public."whose collections are messed up" OWNER TO postgres;

--
-- Name: admin_search_table_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY admin_search_table
    ADD CONSTRAINT admin_search_table_pkey PRIMARY KEY (id);


--
-- Name: att_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY attribute
    ADD CONSTRAINT att_pk PRIMARY KEY (id);


--
-- Name: attribute_item_type_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY attribute_item_type
    ADD CONSTRAINT attribute_item_type_pkey PRIMARY KEY (id);


--
-- Name: coll_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY collection
    ADD CONSTRAINT coll_pk PRIMARY KEY (id);


--
-- Name: collection_manager_collection_ascii_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY collection_manager
    ADD CONSTRAINT collection_manager_collection_ascii_id_key UNIQUE (collection_ascii_id, dase_user_eid);


--
-- Name: collection_manager_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY collection_manager
    ADD CONSTRAINT collection_manager_pkey PRIMARY KEY (id);


--
-- Name: content_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY content
    ADD CONSTRAINT content_pkey PRIMARY KEY (id);


--
-- Name: dase_user_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY dase_user
    ADD CONSTRAINT dase_user_pkey PRIMARY KEY (id);


--
-- Name: def_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY defined_value
    ADD CONSTRAINT def_pk PRIMARY KEY (id);


--
-- Name: input_template_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY input_template
    ADD CONSTRAINT input_template_pkey PRIMARY KEY (id);


--
-- Name: item_link_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY item_link
    ADD CONSTRAINT item_link_pkey PRIMARY KEY (id);


--
-- Name: item_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY item
    ADD CONSTRAINT item_pk PRIMARY KEY (id);


--
-- Name: item_serial_number_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY item
    ADD CONSTRAINT item_serial_number_key UNIQUE (serial_number, collection_id);


--
-- Name: item_type_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY item_type
    ADD CONSTRAINT item_type_pkey PRIMARY KEY (id);


--
-- Name: med_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY media_file
    ADD CONSTRAINT med_pk PRIMARY KEY (id);


--
-- Name: search_cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY search_cache
    ADD CONSTRAINT search_cache_pkey PRIMARY KEY (id);


--
-- Name: search_table_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY search_table
    ADD CONSTRAINT search_table_pkey PRIMARY KEY (id);


--
-- Name: subscription_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY subscription
    ADD CONSTRAINT subscription_pkey PRIMARY KEY (id);


--
-- Name: tag_item_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY tag_item
    ADD CONSTRAINT tag_item_pkey PRIMARY KEY (id);


--
-- Name: tag_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY tag
    ADD CONSTRAINT tag_pkey PRIMARY KEY (id);


--
-- Name: val_pk; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY value
    ADD CONSTRAINT val_pk PRIMARY KEY (id);


--
-- Name: value_revision_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY value_revision_history
    ADD CONSTRAINT value_revision_history_pkey PRIMARY KEY (id);


--
-- Name: aid_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX aid_idx ON attribute USING btree (ascii_id);


--
-- Name: coll_id_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX coll_id_idx ON item USING btree (collection_id);


--
-- Name: collid_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX collid_idx ON collection USING btree (id);


--
-- Name: id_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX id_idx ON media_file USING btree (id);


--
-- Name: in_basic_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX in_basic_idx ON attribute USING btree (in_basic_search);


--
-- Name: it_att_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX it_att_idx ON value USING btree (attribute_id, item_id);


--
-- Name: item_id_size_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX item_id_size_idx ON media_file USING btree (item_id, size);


--
-- Name: itid_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX itid_idx ON item USING btree (id);


--
-- Name: search_table_coll_id_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX search_table_coll_id_idx ON search_table USING btree (collection_id);


--
-- Name: sernum_coll_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX sernum_coll_idx ON media_file USING btree (p_serial_number, p_collection_ascii_id);


--
-- Name: st_item_id_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX st_item_id_idx ON search_table USING btree (item_id);


--
-- Name: t_and_i_id_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX t_and_i_id_idx ON tag_item USING btree (tag_id, item_id);


--
-- Name: thumb_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX thumb_idx ON attribute USING btree (is_on_list_display);


--
-- Name: ti_id_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX ti_id_idx ON tag_item USING btree (id);


--
-- Name: ti_tag_id_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX ti_tag_id_idx ON tag_item USING btree (tag_id);


--
-- Name: value_text_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX value_text_idx ON value USING btree (value_text);


--
-- Name: vatt_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX vatt_idx ON value USING btree (attribute_id);


--
-- Name: vitid_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX vitid_idx ON value USING btree (item_id);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- Name: admin_search_table; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE admin_search_table FROM PUBLIC;
REVOKE ALL ON TABLE admin_search_table FROM postgres;
GRANT ALL ON TABLE admin_search_table TO postgres;
GRANT ALL ON TABLE admin_search_table TO dase_prod;


--
-- Name: admin_search_table_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE admin_search_table_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE admin_search_table_id_seq FROM postgres;
GRANT ALL ON TABLE admin_search_table_id_seq TO postgres;
GRANT ALL ON TABLE admin_search_table_id_seq TO dase_prod;


--
-- Name: admin_search_table_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE admin_search_table_seq FROM PUBLIC;
REVOKE ALL ON TABLE admin_search_table_seq FROM postgres;
GRANT ALL ON TABLE admin_search_table_seq TO postgres;
GRANT ALL ON TABLE admin_search_table_seq TO dase_prod;


--
-- Name: application_monitor_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE application_monitor_seq FROM PUBLIC;
REVOKE ALL ON TABLE application_monitor_seq FROM postgres;
GRANT ALL ON TABLE application_monitor_seq TO postgres;
GRANT ALL ON TABLE application_monitor_seq TO dase_prod;


--
-- Name: attribute; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE attribute FROM PUBLIC;
REVOKE ALL ON TABLE attribute FROM postgres;
GRANT ALL ON TABLE attribute TO postgres;
GRANT ALL ON TABLE attribute TO dase_prod;


--
-- Name: attribute_category_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE attribute_category_seq FROM PUBLIC;
REVOKE ALL ON TABLE attribute_category_seq FROM postgres;
GRANT ALL ON TABLE attribute_category_seq TO postgres;
GRANT ALL ON TABLE attribute_category_seq TO dase_prod;


--
-- Name: attribute_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE attribute_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE attribute_id_seq FROM postgres;
GRANT ALL ON TABLE attribute_id_seq TO postgres;
GRANT ALL ON TABLE attribute_id_seq TO dase_prod;


--
-- Name: attribute_item_type; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE attribute_item_type FROM PUBLIC;
REVOKE ALL ON TABLE attribute_item_type FROM postgres;
GRANT ALL ON TABLE attribute_item_type TO postgres;
GRANT ALL ON TABLE attribute_item_type TO dase_prod;


--
-- Name: attribute_item_type_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE attribute_item_type_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE attribute_item_type_id_seq FROM postgres;
GRANT ALL ON TABLE attribute_item_type_id_seq TO postgres;
GRANT ALL ON TABLE attribute_item_type_id_seq TO dase_prod;


--
-- Name: attribute_item_type_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE attribute_item_type_seq FROM PUBLIC;
REVOKE ALL ON TABLE attribute_item_type_seq FROM postgres;
GRANT ALL ON TABLE attribute_item_type_seq TO postgres;
GRANT ALL ON TABLE attribute_item_type_seq TO dase_prod;


--
-- Name: attribute_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE attribute_seq FROM PUBLIC;
REVOKE ALL ON TABLE attribute_seq FROM postgres;
GRANT ALL ON TABLE attribute_seq TO postgres;
GRANT ALL ON TABLE attribute_seq TO dase_prod;


--
-- Name: category_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE category_seq FROM PUBLIC;
REVOKE ALL ON TABLE category_seq FROM postgres;
GRANT ALL ON TABLE category_seq TO postgres;
GRANT ALL ON TABLE category_seq TO dase_prod;


--
-- Name: collection; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE collection FROM PUBLIC;
REVOKE ALL ON TABLE collection FROM postgres;
GRANT ALL ON TABLE collection TO postgres;
GRANT ALL ON TABLE collection TO dase_prod;


--
-- Name: collection_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE collection_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE collection_id_seq FROM postgres;
GRANT ALL ON TABLE collection_id_seq TO postgres;
GRANT ALL ON TABLE collection_id_seq TO dase_prod;


--
-- Name: collection_manager; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE collection_manager FROM PUBLIC;
REVOKE ALL ON TABLE collection_manager FROM postgres;
GRANT ALL ON TABLE collection_manager TO postgres;
GRANT ALL ON TABLE collection_manager TO dase_prod;


--
-- Name: collection_manager_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE collection_manager_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE collection_manager_id_seq FROM postgres;
GRANT ALL ON TABLE collection_manager_id_seq TO postgres;
GRANT ALL ON TABLE collection_manager_id_seq TO dase_prod;


--
-- Name: collection_manager_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE collection_manager_seq FROM PUBLIC;
REVOKE ALL ON TABLE collection_manager_seq FROM postgres;
GRANT ALL ON TABLE collection_manager_seq TO postgres;
GRANT ALL ON TABLE collection_manager_seq TO dase_prod;


--
-- Name: collection_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE collection_seq FROM PUBLIC;
REVOKE ALL ON TABLE collection_seq FROM postgres;
GRANT ALL ON TABLE collection_seq TO postgres;
GRANT ALL ON TABLE collection_seq TO dase_prod;


--
-- Name: content; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE content FROM PUBLIC;
REVOKE ALL ON TABLE content FROM postgres;
GRANT ALL ON TABLE content TO postgres;
GRANT ALL ON TABLE content TO dase_prod;


--
-- Name: content_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE content_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE content_id_seq FROM postgres;
GRANT ALL ON TABLE content_id_seq TO postgres;
GRANT ALL ON TABLE content_id_seq TO dase_prod;


--
-- Name: content_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE content_seq FROM PUBLIC;
REVOKE ALL ON TABLE content_seq FROM postgres;
GRANT ALL ON TABLE content_seq TO postgres;
GRANT ALL ON TABLE content_seq TO dase_prod;


--
-- Name: dase_user; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE dase_user FROM PUBLIC;
REVOKE ALL ON TABLE dase_user FROM postgres;
GRANT ALL ON TABLE dase_user TO postgres;
GRANT ALL ON TABLE dase_user TO dase_prod;


--
-- Name: dase_user_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE dase_user_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE dase_user_id_seq FROM postgres;
GRANT ALL ON TABLE dase_user_id_seq TO postgres;
GRANT ALL ON TABLE dase_user_id_seq TO dase_prod;


--
-- Name: dase_user_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE dase_user_seq FROM PUBLIC;
REVOKE ALL ON TABLE dase_user_seq FROM postgres;
GRANT ALL ON TABLE dase_user_seq TO postgres;
GRANT ALL ON TABLE dase_user_seq TO dase_prod;


--
-- Name: defined_value; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE defined_value FROM PUBLIC;
REVOKE ALL ON TABLE defined_value FROM postgres;
GRANT ALL ON TABLE defined_value TO postgres;
GRANT ALL ON TABLE defined_value TO dase_prod;


--
-- Name: defined_value_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE defined_value_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE defined_value_id_seq FROM postgres;
GRANT ALL ON TABLE defined_value_id_seq TO postgres;
GRANT ALL ON TABLE defined_value_id_seq TO dase_prod;


--
-- Name: defined_value_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE defined_value_seq FROM PUBLIC;
REVOKE ALL ON TABLE defined_value_seq FROM postgres;
GRANT ALL ON TABLE defined_value_seq TO postgres;
GRANT ALL ON TABLE defined_value_seq TO dase_prod;


--
-- Name: item; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE item FROM PUBLIC;
REVOKE ALL ON TABLE item FROM postgres;
GRANT ALL ON TABLE item TO postgres;
GRANT ALL ON TABLE item TO dase_prod;


--
-- Name: html_input_type_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE html_input_type_seq FROM PUBLIC;
REVOKE ALL ON TABLE html_input_type_seq FROM postgres;
GRANT ALL ON TABLE html_input_type_seq TO postgres;
GRANT ALL ON TABLE html_input_type_seq TO dase_prod;


--
-- Name: input_template; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE input_template FROM PUBLIC;
REVOKE ALL ON TABLE input_template FROM postgres;
GRANT ALL ON TABLE input_template TO postgres;
GRANT ALL ON TABLE input_template TO dase_prod;


--
-- Name: input_template_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE input_template_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE input_template_id_seq FROM postgres;
GRANT ALL ON TABLE input_template_id_seq TO postgres;
GRANT ALL ON TABLE input_template_id_seq TO dase_prod;


--
-- Name: input_template_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE input_template_seq FROM PUBLIC;
REVOKE ALL ON TABLE input_template_seq FROM postgres;
GRANT ALL ON TABLE input_template_seq TO postgres;
GRANT ALL ON TABLE input_template_seq TO dase_prod;


--
-- Name: item_content_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE item_content_seq FROM PUBLIC;
REVOKE ALL ON TABLE item_content_seq FROM postgres;
GRANT ALL ON TABLE item_content_seq TO postgres;
GRANT ALL ON TABLE item_content_seq TO dase_prod;


--
-- Name: item_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE item_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE item_id_seq FROM postgres;
GRANT ALL ON TABLE item_id_seq TO postgres;
GRANT ALL ON TABLE item_id_seq TO dase_prod;


--
-- Name: item_link_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE item_link_seq FROM PUBLIC;
REVOKE ALL ON TABLE item_link_seq FROM postgres;
GRANT ALL ON TABLE item_link_seq TO postgres;
GRANT ALL ON TABLE item_link_seq TO dase_prod;


--
-- Name: item_link; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE item_link FROM PUBLIC;
REVOKE ALL ON TABLE item_link FROM postgres;
GRANT ALL ON TABLE item_link TO postgres;
GRANT ALL ON TABLE item_link TO dase_prod;


--
-- Name: item_link_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE item_link_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE item_link_id_seq FROM postgres;
GRANT ALL ON TABLE item_link_id_seq TO postgres;
GRANT ALL ON TABLE item_link_id_seq TO dase_prod;


--
-- Name: item_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE item_seq FROM PUBLIC;
REVOKE ALL ON TABLE item_seq FROM postgres;
GRANT ALL ON TABLE item_seq TO postgres;
GRANT ALL ON TABLE item_seq TO dase_prod;


--
-- Name: item_status_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE item_status_seq FROM PUBLIC;
REVOKE ALL ON TABLE item_status_seq FROM postgres;
GRANT ALL ON TABLE item_status_seq TO postgres;
GRANT ALL ON TABLE item_status_seq TO dase_prod;


--
-- Name: item_type; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE item_type FROM PUBLIC;
REVOKE ALL ON TABLE item_type FROM postgres;
GRANT ALL ON TABLE item_type TO postgres;
GRANT ALL ON TABLE item_type TO dase_prod;


--
-- Name: item_type_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE item_type_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE item_type_id_seq FROM postgres;
GRANT ALL ON TABLE item_type_id_seq TO postgres;
GRANT ALL ON TABLE item_type_id_seq TO dase_prod;


--
-- Name: item_type_relation_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE item_type_relation_seq FROM PUBLIC;
REVOKE ALL ON TABLE item_type_relation_seq FROM postgres;
GRANT ALL ON TABLE item_type_relation_seq TO postgres;
GRANT ALL ON TABLE item_type_relation_seq TO dase_prod;


--
-- Name: item_type_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE item_type_seq FROM PUBLIC;
REVOKE ALL ON TABLE item_type_seq FROM postgres;
GRANT ALL ON TABLE item_type_seq TO postgres;
GRANT ALL ON TABLE item_type_seq TO dase_prod;


--
-- Name: media_attribute_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE media_attribute_seq FROM PUBLIC;
REVOKE ALL ON TABLE media_attribute_seq FROM postgres;
GRANT ALL ON TABLE media_attribute_seq TO postgres;
GRANT ALL ON TABLE media_attribute_seq TO dase_prod;


--
-- Name: media_file; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE media_file FROM PUBLIC;
REVOKE ALL ON TABLE media_file FROM postgres;
GRANT ALL ON TABLE media_file TO postgres;
GRANT ALL ON TABLE media_file TO dase_prod;


--
-- Name: media_file_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE media_file_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE media_file_id_seq FROM postgres;
GRANT ALL ON TABLE media_file_id_seq TO postgres;
GRANT ALL ON TABLE media_file_id_seq TO dase_prod;


--
-- Name: media_file_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE media_file_seq FROM PUBLIC;
REVOKE ALL ON TABLE media_file_seq FROM postgres;
GRANT ALL ON TABLE media_file_seq TO postgres;
GRANT ALL ON TABLE media_file_seq TO dase_prod;


--
-- Name: media_value_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE media_value_seq FROM PUBLIC;
REVOKE ALL ON TABLE media_value_seq FROM postgres;
GRANT ALL ON TABLE media_value_seq TO postgres;
GRANT ALL ON TABLE media_value_seq TO dase_prod;


--
-- Name: message_queue_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE message_queue_seq FROM PUBLIC;
REVOKE ALL ON TABLE message_queue_seq FROM postgres;
GRANT ALL ON TABLE message_queue_seq TO postgres;
GRANT ALL ON TABLE message_queue_seq TO dase_prod;


--
-- Name: tag_item; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE tag_item FROM PUBLIC;
REVOKE ALL ON TABLE tag_item FROM postgres;
GRANT ALL ON TABLE tag_item TO postgres;
GRANT ALL ON TABLE tag_item TO dase_prod;


--
-- Name: schema_element_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE schema_element_seq FROM PUBLIC;
REVOKE ALL ON TABLE schema_element_seq FROM postgres;
GRANT ALL ON TABLE schema_element_seq TO postgres;
GRANT ALL ON TABLE schema_element_seq TO dase_prod;


--
-- Name: schema_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE schema_seq FROM PUBLIC;
REVOKE ALL ON TABLE schema_seq FROM postgres;
GRANT ALL ON TABLE schema_seq TO postgres;
GRANT ALL ON TABLE schema_seq TO dase_prod;


--
-- Name: search_cache; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE search_cache FROM PUBLIC;
REVOKE ALL ON TABLE search_cache FROM postgres;
GRANT ALL ON TABLE search_cache TO postgres;
GRANT ALL ON TABLE search_cache TO dase_prod;


--
-- Name: search_cache_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE search_cache_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE search_cache_id_seq FROM postgres;
GRANT ALL ON TABLE search_cache_id_seq TO postgres;
GRANT ALL ON TABLE search_cache_id_seq TO dase_prod;


--
-- Name: search_cache_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE search_cache_seq FROM PUBLIC;
REVOKE ALL ON TABLE search_cache_seq FROM postgres;
GRANT ALL ON TABLE search_cache_seq TO postgres;
GRANT ALL ON TABLE search_cache_seq TO dase_prod;


--
-- Name: search_table; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE search_table FROM PUBLIC;
REVOKE ALL ON TABLE search_table FROM postgres;
GRANT ALL ON TABLE search_table TO postgres;
GRANT ALL ON TABLE search_table TO dase_prod;


--
-- Name: search_table_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE search_table_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE search_table_id_seq FROM postgres;
GRANT ALL ON TABLE search_table_id_seq TO postgres;
GRANT ALL ON TABLE search_table_id_seq TO dase_prod;


--
-- Name: search_table_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE search_table_seq FROM PUBLIC;
REVOKE ALL ON TABLE search_table_seq FROM postgres;
GRANT ALL ON TABLE search_table_seq TO postgres;
GRANT ALL ON TABLE search_table_seq TO dase_prod;


--
-- Name: subscription; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE subscription FROM PUBLIC;
REVOKE ALL ON TABLE subscription FROM postgres;
GRANT ALL ON TABLE subscription TO postgres;
GRANT ALL ON TABLE subscription TO dase_prod;


--
-- Name: subscription_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE subscription_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE subscription_id_seq FROM postgres;
GRANT ALL ON TABLE subscription_id_seq TO postgres;
GRANT ALL ON TABLE subscription_id_seq TO dase_prod;


--
-- Name: subscription_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE subscription_seq FROM PUBLIC;
REVOKE ALL ON TABLE subscription_seq FROM postgres;
GRANT ALL ON TABLE subscription_seq TO postgres;
GRANT ALL ON TABLE subscription_seq TO dase_prod;


--
-- Name: tag; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE tag FROM PUBLIC;
REVOKE ALL ON TABLE tag FROM postgres;
GRANT ALL ON TABLE tag TO postgres;
GRANT ALL ON TABLE tag TO dase_prod;


--
-- Name: tag_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE tag_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE tag_id_seq FROM postgres;
GRANT ALL ON TABLE tag_id_seq TO postgres;
GRANT ALL ON TABLE tag_id_seq TO dase_prod;


--
-- Name: tag_item_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE tag_item_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE tag_item_id_seq FROM postgres;
GRANT ALL ON TABLE tag_item_id_seq TO postgres;
GRANT ALL ON TABLE tag_item_id_seq TO dase_prod;


--
-- Name: tag_item_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE tag_item_seq FROM PUBLIC;
REVOKE ALL ON TABLE tag_item_seq FROM postgres;
GRANT ALL ON TABLE tag_item_seq TO postgres;
GRANT ALL ON TABLE tag_item_seq TO dase_prod;


--
-- Name: tag_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE tag_seq FROM PUBLIC;
REVOKE ALL ON TABLE tag_seq FROM postgres;
GRANT ALL ON TABLE tag_seq TO postgres;
GRANT ALL ON TABLE tag_seq TO dase_prod;


--
-- Name: tag_type_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE tag_type_seq FROM PUBLIC;
REVOKE ALL ON TABLE tag_type_seq FROM postgres;
GRANT ALL ON TABLE tag_type_seq TO postgres;
GRANT ALL ON TABLE tag_type_seq TO dase_prod;


--
-- Name: upload_filetype_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE upload_filetype_seq FROM PUBLIC;
REVOKE ALL ON TABLE upload_filetype_seq FROM postgres;
GRANT ALL ON TABLE upload_filetype_seq TO postgres;
GRANT ALL ON TABLE upload_filetype_seq TO dase_prod;


--
-- Name: upload_status_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE upload_status_seq FROM PUBLIC;
REVOKE ALL ON TABLE upload_status_seq FROM postgres;
GRANT ALL ON TABLE upload_status_seq TO postgres;
GRANT ALL ON TABLE upload_status_seq TO dase_prod;


--
-- Name: user_history_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE user_history_seq FROM PUBLIC;
REVOKE ALL ON TABLE user_history_seq FROM postgres;
GRANT ALL ON TABLE user_history_seq TO postgres;
GRANT ALL ON TABLE user_history_seq TO dase_prod;

--
-- Name: value; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE value FROM PUBLIC;
REVOKE ALL ON TABLE value FROM postgres;
GRANT ALL ON TABLE value TO postgres;
GRANT ALL ON TABLE value TO dase_prod;


--
-- Name: value_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE value_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE value_id_seq FROM postgres;
GRANT ALL ON TABLE value_id_seq TO postgres;
GRANT ALL ON TABLE value_id_seq TO dase_prod;


--
-- Name: value_revision_history; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE value_revision_history FROM PUBLIC;
REVOKE ALL ON TABLE value_revision_history FROM postgres;
GRANT ALL ON TABLE value_revision_history TO postgres;
GRANT ALL ON TABLE value_revision_history TO dase_prod;


--
-- Name: value_revision_history_id_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE value_revision_history_id_seq FROM PUBLIC;
REVOKE ALL ON TABLE value_revision_history_id_seq FROM postgres;
GRANT ALL ON TABLE value_revision_history_id_seq TO postgres;
GRANT ALL ON TABLE value_revision_history_id_seq TO dase_prod;


--
-- Name: value_revision_history_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE value_revision_history_seq FROM PUBLIC;
REVOKE ALL ON TABLE value_revision_history_seq FROM postgres;
GRANT ALL ON TABLE value_revision_history_seq TO postgres;
GRANT ALL ON TABLE value_revision_history_seq TO dase_prod;


--
-- Name: value_search_table_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE value_search_table_seq FROM PUBLIC;
REVOKE ALL ON TABLE value_search_table_seq FROM postgres;
GRANT ALL ON TABLE value_search_table_seq TO postgres;
GRANT ALL ON TABLE value_search_table_seq TO dase_prod;


--
-- Name: value_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE value_seq FROM PUBLIC;
REVOKE ALL ON TABLE value_seq FROM postgres;
GRANT ALL ON TABLE value_seq TO postgres;
GRANT ALL ON TABLE value_seq TO dase_prod;

--
-- PostgreSQL database dump complete
--

EOF;
