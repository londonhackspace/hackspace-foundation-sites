begin;

create table flourish_tables (
  tablename name
);

copy flourish_tables (tablename) from stdin;
aliases
cards
interests
interests_categories
learnings
lhspayments_payment
locations
meeting_attendees
password_resets
perms
project_states
projects
projects_logs
proxy_votes
subscriptions
transactions
userperms
users
users_aliases
users_interests
users_learnings
users_profiles
\.

commit;
