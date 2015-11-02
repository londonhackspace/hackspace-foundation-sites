-- update cards set added_date = '2011-01-01' where uid = '04300E82462380';


CREATE EXTENSION sqlite_fdw;
CREATE SERVER sqlite_server foreign data wrapper sqlite_fdw options (database '/Users/russ/hackspace/hackspace-foundation-sites/var/database.db');

BEGIN;


CREATE SCHEMA sqlite;

CREATE FOREIGN TABLE sqlite.transactions (
    id INTEGER,
    fit_id TEXT,
    timestamp TIMESTAMP WITHOUT TIME ZONE,
    user_id INTEGER,
    amount NUMERIC(6, 2)
)
SERVER sqlite_server OPTIONS(table 'transactions');

CREATE FOREIGN TABLE sqlite.password_resets (
    key TEXT,
    user_id INTEGER,
    expires TIMESTAMP WITHOUT TIME ZONE
)
SERVER sqlite_server OPTIONS(table 'password_resets');

CREATE FOREIGN TABLE sqlite.cards(uid text,
                        user_id integer,
                        added_date TIMESTAMP WITHOUT TIME ZONE,
                        active bool)
SERVER sqlite_server OPTIONS(table 'cards');


CREATE FOREIGN TABLE sqlite.perms (
    id INTEGER,
    perm_name TEXT
)
SERVER sqlite_server OPTIONS(table 'perms');


CREATE FOREIGN TABLE sqlite.userperms (
    perm_id INTEGER,
    user_id INTEGER
)
SERVER sqlite_server OPTIONS(table 'userperms');

CREATE FOREIGN TABLE sqlite.aliases (
  id TEXT,
  type INTEGER
) SERVER sqlite_server OPTIONS(table 'aliases');

CREATE FOREIGN TABLE sqlite.users_profiles (
  user_id INTEGER,
  allow_email BOOLEAN,
  allow_doorbot BOOLEAN,
  photo VARCHAR(255),
  website VARCHAR(255),
  description VARCHAR(500)
) SERVER sqlite_server OPTIONS(table 'users_profiles');


CREATE FOREIGN TABLE sqlite.learnings (
  learning_id INTEGER,
  name VARCHAR(255),
  description VARCHAR(255),
  url VARCHAR(255)
) SERVER sqlite_server OPTIONS(table 'learnings');


CREATE FOREIGN TABLE sqlite.users_learnings (
  user_id INTEGER,
  learning_id INTEGER
) SERVER sqlite_server OPTIONS(table 'users_learnings');

CREATE FOREIGN TABLE sqlite.users_aliases (
  user_id INTEGER,
  alias_id VARCHAR(255),
  username VARCHAR(255)
) SERVER sqlite_server OPTIONS(table 'users_aliases');

CREATE FOREIGN TABLE sqlite.interests_categories (
  id VARCHAR(255)
) SERVER sqlite_server OPTIONS(table 'interests_categories');

CREATE FOREIGN TABLE sqlite.interests (
  interest_id INTEGER,
  category VARCHAR(255),
  suggested BOOLEAN,
  name VARCHAR(255),
  url VARCHAR(255)
) SERVER sqlite_server OPTIONS(table 'interests');

CREATE FOREIGN TABLE sqlite.users_interests (
  user_id INTEGER,
  interest_id INTEGER
) SERVER sqlite_server OPTIONS(table 'users_interests');

CREATE FOREIGN TABLE sqlite."users" (
    id INTEGER,
    email VARCHAR(255) ,
    password VARCHAR(255),
    full_name VARCHAR(255),
    subscribed BOOLEAN,
    bankhash TEXT,
    creationdate TEXT,
    address TEXT,
    hackney BOOLEAN,
    subscription_period INTEGER  DEFAULT 1,
    nickname VARCHAR(255),
    irc_nick VARCHAR(255),
    gladosfile VARCHAR(255),
    terminated BOOLEAN,
    admin BOOLEAN,
    has_profile BOOLEAN,
    disabled_profile BOOLEAN,
    doorbot_timestamp TIMESTAMP WITHOUT TIME ZONE,
    emergency_name VARCHAR(255),
    emergency_phone VARCHAR(40),
    ldapuser VARCHAR(32),
    ldapnthash VARCHAR(32),
    ldapsshahash VARCHAR(38),
    ldapshell VARCHAR(32)
) SERVER sqlite_server OPTIONS(table 'users');

CREATE FOREIGN TABLE sqlite.projects_logs (
        id INTEGER,
        timestamp INTEGER,
        project_id INTEGER,
        user_id INTEGER,
        details VARCHAR(255)
) SERVER sqlite_server OPTIONS(table 'projects_logs');

CREATE FOREIGN TABLE sqlite.locations (
        id INTEGER,
        name VARCHAR(255)
) SERVER sqlite_server OPTIONS(table 'locations');

CREATE FOREIGN TABLE sqlite.project_states (
        id INTEGER,
        name VARCHAR(255)
) SERVER sqlite_server OPTIONS(table 'project_states');

CREATE FOREIGN TABLE sqlite.projects (
        id INTEGER,
        user_id INTEGER,
        name VARCHAR(255),
        description VARCHAR(500),
        state_id INTEGER,
        location_id INTEGER,
        location VARCHAR(255),
        updated_date TIMESTAMP WITHOUT TIME ZONE,
        from_date TIMESTAMP WITHOUT TIME ZONE,
        to_date TIMESTAMP WITHOUT TIME ZONE
) SERVER sqlite_server OPTIONS(table 'projects');


ANALYZE;

SET CONSTRAINTS ALL DEFERRED;

TRUNCATE aliases CASCADE;
TRUNCATE learnings CASCADE;
TRUNCATE locations CASCADE;
TRUNCATE project_states CASCADE;
TRUNCATE interests_categories CASCADE;
TRUNCATE interests CASCADE;

INSERT INTO aliases (id,type) VALUES ('0',0);
INSERT INTO aliases (id,type) VALUES ('1',0);

INSERT INTO "users" SELECT * from sqlite."users";
INSERT INTO transactions SELECT * from sqlite.transactions;
INSERT INTO password_resets SELECT * from sqlite.password_resets;
INSERT INTO cards SELECT * from sqlite.cards;
INSERT INTO perms SELECT * from sqlite.perms;
INSERT INTO userperms SELECT * from sqlite.userperms;
INSERT INTO aliases SELECT * from sqlite.aliases;
INSERT INTO users_profiles SELECT * from sqlite.users_profiles;
INSERT INTO learnings SELECT * from sqlite.learnings;
INSERT INTO users_learnings SELECT * from sqlite.users_learnings;
INSERT INTO users_aliases SELECT * from sqlite.users_aliases;
INSERT INTO interests_categories SELECT * from sqlite.interests_categories;
INSERT INTO interests SELECT * from sqlite.interests;
INSERT INTO users_interests SELECT * from sqlite.users_interests;
INSERT INTO locations SELECT * from sqlite.locations;
INSERT INTO project_states SELECT * from sqlite.project_states;
INSERT INTO projects SELECT * from sqlite.projects;
INSERT INTO projects_logs SELECT * from sqlite.projects_logs;

DROP SCHEMA sqlite CASCADE;
DROP SERVER sqlite_server;

ANALYZE;

SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));
SELECT setval('transactions_id_seq', (SELECT MAX(id) FROM transactions));
SELECT setval('subscriptions_id_seq', (SELECT MAX(id) FROM subscriptions));
SELECT setval('perms_id_seq', (SELECT MAX(id) FROM perms));
SELECT setval('locations_id_seq', (SELECT MAX(id) FROM locations));
SELECT setval('project_states_id_seq', (SELECT MAX(id) FROM project_states));
SELECT setval('projects_id_seq', (SELECT MAX(id) FROM projects));
SELECT setval('projects_logs_id_seq', (SELECT MAX(id) FROM projects_logs));
SELECT setval('learnings_id_seq', (SELECT MAX(id) FROM learnings));
SELECT setval('interests_id_seq', (SELECT MAX(id) FROM interests));

COMMIT;
