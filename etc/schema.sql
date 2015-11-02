BEGIN;

CREATE TABLE "users" (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    subscribed BOOLEAN NOT NULL DEFAULT false,
    bankhash TEXT,
    creationdate TEXT,
    address TEXT,
    hackney BOOLEAN NOT NULL DEFAULT false,
    subscription_period INTEGER NOT NULL DEFAULT 0,
    nickname VARCHAR(255) UNIQUE,
    irc_nick VARCHAR(255) UNIQUE,
    gladosfile VARCHAR(255),
    terminated BOOLEAN NOT NULL DEFAULT false,
    admin BOOLEAN NOT NULL DEFAULT false,
    has_profile BOOLEAN NOT NULL DEFAULT false,
    disabled_profile BOOLEAN NOT NULL DEFAULT false,
    doorbot_timestamp TIMESTAMP WITHOUT TIME ZONE,
    emergency_name VARCHAR(255),
    emergency_phone VARCHAR(40),
    ldapuser VARCHAR(32) UNIQUE,
    ldapnthash VARCHAR(32),
    ldapsshahash VARCHAR(38),
    ldapshell VARCHAR(32),
    ldapemail VARCHAR(255)
);

CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    fit_id TEXT NOT NULL UNIQUE,
    timestamp TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    user_id INTEGER NOT NULL REFERENCES users(id),
    amount NUMERIC(6, 2) NOT NULL
);

create index transactions_timestamp on transactions (timestamp);
create index transactions_user_id on transactions (user_id);

CREATE TABLE password_resets (
    key TEXT PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id),
    expires TIMESTAMP WITHOUT TIME ZONE NOT NULL
);

CREATE TABLE subscriptions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id),
    transaction_id INTEGER NOT NULL REFERENCES transactions,
    start_date TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    end_date TIMESTAMP WITHOUT TIME ZONE NOT NULL
);

CREATE TABLE cards (
    uid VARCHAR(255) PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id),
    added_date TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    active BOOLEAN NOT NULL DEFAULT true
);

CREATE TABLE perms (
    id SERIAL PRIMARY KEY,
    perm_name VARCHAR(255) NOT NULL
);

CREATE TABLE userperms (
    perm_id INTEGER NOT NULL REFERENCES perms(id),
    user_id INTEGER NOT NULL REFERENCES users(id)
);

CREATE TABLE locations (
    id SERIAL PRIMARY KEY, 
    name VARCHAR(255) NOT NULL
);
INSERT INTO locations (name) VALUES ('Ground floor');
INSERT INTO locations (name) VALUES ('Basement');
INSERT INTO locations (name) VALUES ('Yard');

CREATE TABLE project_states (
    id SERIAL PRIMARY KEY, 
    name VARCHAR(255) NOT NULL
);
INSERT INTO project_states (name) VALUES ('Pending Approval');
INSERT INTO project_states (name) VALUES ('Approved');
INSERT INTO project_states (name) VALUES ('Unapproved');
INSERT INTO project_states (name) VALUES ('Extended');
INSERT INTO project_states (name) VALUES ('Passed Deadline');
INSERT INTO project_states (name) VALUES ('Removed');
INSERT INTO project_states (name) VALUES ('Archived');

CREATE TABLE projects (
    id SERIAL PRIMARY KEY, 
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE, 
    name VARCHAR(255) NOT NULL,
    description VARCHAR(500) NOT NULL,
    state_id INTEGER NOT NULL REFERENCES project_states(id) ON DELETE CASCADE, 
    location_id INTEGER NOT NULL REFERENCES locations(id) ON DELETE CASCADE, 
    location VARCHAR(255),
        updated_date TIMESTAMP WITHOUT TIME ZONE NOT NULL,
        from_date TIMESTAMP WITHOUT TIME ZONE NOT NULL,
        to_date TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE projects_logs (
    id SERIAL PRIMARY KEY, 
        timestamp INTEGER NOT NULL,
    project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id),
    details VARCHAR(255) NOT NULL
);

CREATE TABLE users_profiles (
    user_id INTEGER PRIMARY KEY NOT NULL REFERENCES users(id) ON DELETE CASCADE, 
    allow_email BOOLEAN NOT NULL DEFAULT false,
    allow_doorbot BOOLEAN NOT NULL DEFAULT false, 
    photo VARCHAR(255), 
    website VARCHAR(255), 
    description VARCHAR(500), 
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE learnings (
    learning_id SERIAL PRIMARY KEY, 
    name VARCHAR(255) NOT NULL, 
    description VARCHAR(255) NOT NULL,
    url VARCHAR(255)
);

CREATE TABLE users_learnings (
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE, 
    learning_id INTEGER NOT NULL REFERENCES learnings(learning_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, learning_id)
);

INSERT INTO learnings (name,description,url) VALUES ('Laser cutting','Laser cutter trained','https://wiki.london.hackspace.org.uk/view/Lasercutter_Training');
INSERT INTO learnings (name,description,url) VALUES ('Vinyl cutting','Vinyl cutter trained','https://wiki.london.hackspace.org.uk/view/Equipment/VinylCutter');
INSERT INTO learnings (name,description,url) VALUES ('3D printing','3D printer trained','https://wiki.london.hackspace.org.uk/view/3dprinter_training');

CREATE TABLE aliases (
    id VARCHAR(255) PRIMARY KEY NOT NULL,
    type INTEGER NOT NULL DEFAULT 2
);

CREATE TABLE users_aliases (
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,  
    alias_id VARCHAR(255) NOT NULL REFERENCES aliases(id) ON DELETE CASCADE, 
    username VARCHAR(255) NOT NULL,
    PRIMARY KEY (user_id, alias_id)
);

INSERT INTO aliases (id,type) VALUES ('IRC',1);
INSERT INTO aliases (id,type) VALUES ('Hackspace Wiki',1);
INSERT INTO aliases (id,type) VALUES ('Twitter',2);
INSERT INTO aliases (id,type) VALUES ('Facebook',2);
INSERT INTO aliases (id,type) VALUES ('Google+',2);
INSERT INTO aliases (id,type) VALUES ('LinkedIn',2);
INSERT INTO aliases (id,type) VALUES ('GitHub',2);
INSERT INTO aliases (id,type) VALUES ('YouTube',2);
INSERT INTO aliases (id,type) VALUES ('Flickr',2);
INSERT INTO aliases (id,type) VALUES ('Callsign',2);
INSERT INTO aliases (id,type) VALUES ('XMPP/Jabber',2);
INSERT INTO aliases (id,type) VALUES ('RSS',2);
INSERT INTO aliases (id,type) VALUES ('Minecraft',2);
INSERT INTO aliases (id,type) VALUES ('Ello',2);

CREATE TABLE interests_categories (
    id VARCHAR(255) PRIMARY KEY NOT NULL
);

CREATE TABLE interests (
    interest_id SERIAL PRIMARY KEY, 
    category VARCHAR(255) NOT NULL REFERENCES interests_categories(id) ON DELETE CASCADE,
    suggested BOOLEAN NOT NULL DEFAULT false,
    name VARCHAR(255) NOT NULL, 
    url VARCHAR(255)
);

CREATE TABLE users_interests (
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE, 
    interest_id INTEGER NOT NULL REFERENCES interests(interest_id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, interest_id)
);

INSERT INTO interests_categories (id) VALUES ('Computing');
INSERT INTO interests_categories (id) VALUES ('Fabrication');
INSERT INTO interests_categories (id) VALUES ('Crafts');
INSERT INTO interests_categories (id) VALUES ('Special interests');
INSERT INTO interests_categories (id) VALUES ('Other');
INSERT INTO interests (category,suggested,name,url) VALUES ('Computing',true,'Robotics','https://wiki.london.hackspace.org.uk/view/Robotics');
INSERT INTO interests (category,suggested,name,url) VALUES ('Computing',true,'Arduino','https://wiki.london.hackspace.org.uk/view/Not_Just_Arduino');
INSERT INTO interests (category,suggested,name,url) VALUES ('Computing',true,'Raspberry Pi','https://wiki.london.hackspace.org.uk/view/Not_Just_Arduino');
INSERT INTO interests (category,suggested,name,url) VALUES ('Computing',true,'Soldering','https://wiki.london.hackspace.org.uk/view/Electronics_Area');
INSERT INTO interests (category,suggested,name,url) VALUES ('Computing',true,'Hardware','https://wiki.london.hackspace.org.uk/view/Not_Just_Arduino');
INSERT INTO interests (category,suggested,name,url) VALUES ('Computing',true,'Software','https://wiki.london.hackspace.org.uk/w/index.php?title=Special%3ASearch&search=software');
INSERT INTO interests (category,suggested,name,url) VALUES ('Computing',true,'Programming','https://wiki.london.hackspace.org.uk/w/index.php?title=Special%3ASearch&search=programming&go=Go');
INSERT INTO interests (category,suggested,name,url) VALUES ('Fabrication',true,'3D printing','https://wiki.london.hackspace.org.uk/view/Equipment/Lulzbot_a0_101');
INSERT INTO interests (category,suggested,name,url) VALUES ('Fabrication',true,'Laser cutting','https://wiki.london.hackspace.org.uk/view/Laser_Cutter');
INSERT INTO interests (category,suggested,name,url) VALUES ('Fabrication',true,'CNC routing','https://wiki.london.hackspace.org.uk/view/Pledge:_CNC_Mill');
INSERT INTO interests (category,suggested,name,url) VALUES ('Fabrication',true,'Woodworking','https://wiki.london.hackspace.org.uk/view/Dusty_Wood_Shop');
INSERT INTO interests (category,suggested,name,url) VALUES ('Fabrication',true,'Metalworking','https://wiki.london.hackspace.org.uk/view/Dirty_Metal_Shop');
INSERT INTO interests (category,suggested,name,url) VALUES ('Fabrication',true,'Welding','https://wiki.london.hackspace.org.uk/view/Welding_Training');
INSERT INTO interests (category,suggested,name,url) VALUES ('Crafts',true,'Sewing','https://wiki.london.hackspace.org.uk/view/Sewing_Machines');
INSERT INTO interests (category,suggested,name,url) VALUES ('Crafts',true,'Knitting','https://wiki.london.hackspace.org.uk/view/Equipment/Knitting_Machine');
INSERT INTO interests (category,suggested,name,url) VALUES ('Crafts',true,'Painting','https://wiki.london.hackspace.org.uk/view/Hackspace_Art');
INSERT INTO interests (category,suggested,name,url) VALUES ('Crafts',true,'Sculpting','https://wiki.london.hackspace.org.uk/view/Hackspace_Art');
INSERT INTO interests (category,suggested,name,url) VALUES ('Crafts',true,'Vinyl cutting','https://wiki.london.hackspace.org.uk/view/Vinyl_cutter');
INSERT INTO interests (category,suggested,name,url) VALUES ('Special interests',true,'Amateur radio','https://wiki.london.hackspace.org.uk/view/Amateur_Radio');
INSERT INTO interests (category,suggested,name,url) VALUES ('Special interests',true,'Lock picking','https://wiki.london.hackspace.org.uk/view/Project:Lockpicking');
INSERT INTO interests (category,suggested,name,url) VALUES ('Special interests',true,'Music hacking','https://wiki.london.hackspace.org.uk/view/Music_Hack_Space');
INSERT INTO interests (category,suggested,name,url) VALUES ('Special interests',true,'Photography','https://wiki.london.hackspace.org.uk/view/Project:Darkroom');
INSERT INTO interests (category,suggested,name,url) VALUES ('Special interests',true,'Biohacking','https://wiki.london.hackspace.org.uk/view/Biohacking');
INSERT INTO interests (category,suggested,name,url) VALUES ('Special interests',true,'LAN gaming','https://wiki.london.hackspace.org.uk/view/London_FRAGspace');
INSERT INTO interests (category,suggested,name,url) VALUES ('Special interests',true,'PL(A)YWOOD','https://wiki.london.hackspace.org.uk/view/Playwood');
INSERT INTO interests (category,suggested,name,url) VALUES ('Special interests',true,'Brewing','https://wiki.london.hackspace.org.uk/view/Brewing');
INSERT INTO interests (category,suggested,name,url) VALUES ('Special interests',true,'Gardening','https://wiki.london.hackspace.org.uk/view/Garden');
INSERT INTO interests (category,suggested,name,url) VALUES ('Special interests',true,'Cycling','https://wiki.london.hackspace.org.uk/w/index.php?title=Special%3ASearch&search=cycling&go=Go');
INSERT INTO interests (category,suggested,name,url) VALUES ('Special interests',true,'Aerospace','https://wiki.london.hackspace.org.uk/view/LondonAerospace');

COMMIT;
