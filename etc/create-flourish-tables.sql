create table flourish_tables (
  tablename name
);
insert into flourish_tables select tablename from pg_tables where tableowner = 'hackspace' order by tablename;

