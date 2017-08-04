-- Restores multi-column primary keys for Flourish
-- Django doesn't understand multi-column primary keys yet.
-- This should only need to be run after initial migration.

begin;

alter table users_learnings drop column if exists id;
alter table users_learnings add primary key (user_id, learning_id);

alter table users_aliases drop column if exists id;
alter table users_aliases add primary key (user_id, alias_id);

alter table users_interests drop column if exists id;
alter table users_interests add primary key (user_id, interest_id);

commit;
