--
-- PostgreSQL database dump
--

\restrict 933jf9FT43Com404qWuNdmG4D11jZmPI1nRYiLlgJsnlWeqL09bvSgJ7OTaGDxD

-- Dumped from database version 14.22 (Debian 14.22-1.pgdg13+1)
-- Dumped by pg_dump version 14.22 (Debian 14.22-1.pgdg13+1)

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

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: channel_members; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.channel_members (
    id integer NOT NULL,
    channel_id integer NOT NULL,
    user_id integer NOT NULL,
    role character varying(20) DEFAULT 'MEMBER'::character varying NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: channel_members_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.channel_members_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: channel_members_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.channel_members_id_seq OWNED BY public.channel_members.id;


--
-- Name: channel_references; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.channel_references (
    id integer NOT NULL,
    channel_id integer NOT NULL,
    reference_id integer NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: channel_references_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.channel_references_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: channel_references_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.channel_references_id_seq OWNED BY public.channel_references.id;


--
-- Name: channels; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.channels (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    created_by integer,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: channels_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.channels_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: channels_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.channels_id_seq OWNED BY public.channels.id;


--
-- Name: note_reference_added; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.note_reference_added (
    id integer NOT NULL,
    note_id integer NOT NULL,
    reference_node_id integer NOT NULL,
    user_id integer NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: note_reference_added_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.note_reference_added_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: note_reference_added_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.note_reference_added_id_seq OWNED BY public.note_reference_added.id;


--
-- Name: notes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notes (
    id integer NOT NULL,
    user_id integer NOT NULL,
    content text NOT NULL,
    reference_node_id integer,
    visibility character varying(20) DEFAULT 'PRIVATE'::character varying NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: notes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.notes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: notes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.notes_id_seq OWNED BY public.notes.id;


--
-- Name: reference_nodes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.reference_nodes (
    id integer NOT NULL,
    type character varying(50) NOT NULL,
    content text,
    label character varying(255),
    reference_id integer NOT NULL,
    parent_node_id integer,
    "position" integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: reference_nodes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.reference_nodes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: reference_nodes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.reference_nodes_id_seq OWNED BY public.reference_nodes.id;


--
-- Name: references; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public."references" (
    id integer NOT NULL,
    type character varying(50) NOT NULL,
    title character varying(255) NOT NULL,
    abbreviation character varying(50),
    author character varying(255),
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: references_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.references_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: references_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.references_id_seq OWNED BY public."references".id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    password character varying(255),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: channel_members id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.channel_members ALTER COLUMN id SET DEFAULT nextval('public.channel_members_id_seq'::regclass);


--
-- Name: channel_references id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.channel_references ALTER COLUMN id SET DEFAULT nextval('public.channel_references_id_seq'::regclass);


--
-- Name: channels id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.channels ALTER COLUMN id SET DEFAULT nextval('public.channels_id_seq'::regclass);


--
-- Name: note_reference_added id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.note_reference_added ALTER COLUMN id SET DEFAULT nextval('public.note_reference_added_id_seq'::regclass);


--
-- Name: notes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notes ALTER COLUMN id SET DEFAULT nextval('public.notes_id_seq'::regclass);


--
-- Name: reference_nodes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reference_nodes ALTER COLUMN id SET DEFAULT nextval('public.reference_nodes_id_seq'::regclass);


--
-- Name: references id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public."references" ALTER COLUMN id SET DEFAULT nextval('public.references_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: channel_members channel_members_channel_id_user_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.channel_members
    ADD CONSTRAINT channel_members_channel_id_user_id_key UNIQUE (channel_id, user_id);


--
-- Name: channel_members channel_members_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.channel_members
    ADD CONSTRAINT channel_members_pkey PRIMARY KEY (id);


--
-- Name: channel_references channel_references_channel_id_reference_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.channel_references
    ADD CONSTRAINT channel_references_channel_id_reference_id_key UNIQUE (channel_id, reference_id);


--
-- Name: channel_references channel_references_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.channel_references
    ADD CONSTRAINT channel_references_pkey PRIMARY KEY (id);


--
-- Name: channels channels_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.channels
    ADD CONSTRAINT channels_pkey PRIMARY KEY (id);


--
-- Name: note_reference_added note_reference_added_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.note_reference_added
    ADD CONSTRAINT note_reference_added_pkey PRIMARY KEY (id);


--
-- Name: notes notes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notes
    ADD CONSTRAINT notes_pkey PRIMARY KEY (id);


--
-- Name: reference_nodes reference_nodes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reference_nodes
    ADD CONSTRAINT reference_nodes_pkey PRIMARY KEY (id);


--
-- Name: reference_nodes reference_nodes_reference_id_parent_node_id_position_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reference_nodes
    ADD CONSTRAINT reference_nodes_reference_id_parent_node_id_position_key UNIQUE (reference_id, parent_node_id, "position");


--
-- Name: references references_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public."references"
    ADD CONSTRAINT references_pkey PRIMARY KEY (id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: channel_members channel_members_channel_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.channel_members
    ADD CONSTRAINT channel_members_channel_id_fkey FOREIGN KEY (channel_id) REFERENCES public.channels(id) ON DELETE CASCADE;


--
-- Name: channel_members channel_members_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.channel_members
    ADD CONSTRAINT channel_members_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: channel_references channel_references_channel_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.channel_references
    ADD CONSTRAINT channel_references_channel_id_fkey FOREIGN KEY (channel_id) REFERENCES public.channels(id) ON DELETE CASCADE;


--
-- Name: channel_references channel_references_reference_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.channel_references
    ADD CONSTRAINT channel_references_reference_id_fkey FOREIGN KEY (reference_id) REFERENCES public."references"(id) ON DELETE CASCADE;


--
-- Name: channels channels_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.channels
    ADD CONSTRAINT channels_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: note_reference_added note_reference_added_note_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.note_reference_added
    ADD CONSTRAINT note_reference_added_note_id_fkey FOREIGN KEY (note_id) REFERENCES public.notes(id) ON DELETE CASCADE;


--
-- Name: note_reference_added note_reference_added_reference_node_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.note_reference_added
    ADD CONSTRAINT note_reference_added_reference_node_id_fkey FOREIGN KEY (reference_node_id) REFERENCES public.reference_nodes(id) ON DELETE CASCADE;


--
-- Name: note_reference_added note_reference_added_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.note_reference_added
    ADD CONSTRAINT note_reference_added_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: notes notes_reference_node_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notes
    ADD CONSTRAINT notes_reference_node_id_fkey FOREIGN KEY (reference_node_id) REFERENCES public.reference_nodes(id) ON DELETE SET NULL;


--
-- Name: notes notes_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notes
    ADD CONSTRAINT notes_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: reference_nodes reference_nodes_parent_node_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reference_nodes
    ADD CONSTRAINT reference_nodes_parent_node_id_fkey FOREIGN KEY (parent_node_id) REFERENCES public.reference_nodes(id) ON DELETE CASCADE;


--
-- Name: reference_nodes reference_nodes_reference_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.reference_nodes
    ADD CONSTRAINT reference_nodes_reference_id_fkey FOREIGN KEY (reference_id) REFERENCES public."references"(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict 933jf9FT43Com404qWuNdmG4D11jZmPI1nRYiLlgJsnlWeqL09bvSgJ7OTaGDxD

