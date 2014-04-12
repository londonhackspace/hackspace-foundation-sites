CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    subscribed BOOLEAN NOT NULL DEFAULT 0,
    address TEXT,
    bankhash TEXT,
    subscription_period INTEGER NOT NULL DEFAULT 1,
    hackney BOOLEAN NOT NULL DEFAULT 0,
    nickname VARCHAR(255) UNIQUE,
    irc_nick VARCHAR(255) UNIQUE,
    gladosfile VARCHAR(255),
    terminated BOOLEAN NOT NULL DEFAULT 0,
    admin BOOLEAN NOT NULL DEFAULT 0
);

CREATE TABLE transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fit_id TEXT NOT NULL UNIQUE,
    timestamp DATETIME NOT NULL,
    user_id INTEGER NOT NULL REFERENCES users(id),
    amount TEXT NOT NULL
);

CREATE TABLE password_resets (
    key TEXT PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id),
    expires DATETIME NOT NULL
);

CREATE TABLE subscriptions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL REFERENCES users(id),
    transaction_id INTEGER NOT NULL REFERENCES transactions,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL
);

CREATE TABLE cards (
    uid VARCHAR(255) PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id),
    added_date DATETIME NOT NULL,
    active BOOLEAN NOT NULL DEFAULT 1
);

CREATE TABLE perms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    perm_name VARCHAR(255) NOT NULL
);

CREATE TABLE userperms (
    perm_id INTEGER NOT NULL REFERENCES perms(id),
    user_id INTEGER NOT NULL REFERENCES users(id)
);
