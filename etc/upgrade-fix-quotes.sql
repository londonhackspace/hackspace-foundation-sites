update users_aliases  set username    = replace(replace(username,    '&#34;', '"'), '&#39;', '''') where username    like '%&#%';
update users_profiles set website     = replace(replace(website,     '&#34;', '"'), '&#39;', '''') where website     like '%&#%';
update users_profiles set description = replace(replace(description, '&#34;', '"'), '&#39;', '''') where description like '%&#%';
update projects       set name        = replace(replace(name,        '&#34;', '"'), '&#39;', '''') where name        like '%&#%';
update projects       set description = replace(replace(description, '&#34;', '"'), '&#39;', '''') where description like '%&#%';
update projects       set location    = replace(replace(location,    '&#34;', '"'), '&#39;', '''') where location    like '%&#%';
update projects_logs  set details     = replace(replace(details,     '&#34;', '"'), '&#39;', '''') where details     like '%&#%';

