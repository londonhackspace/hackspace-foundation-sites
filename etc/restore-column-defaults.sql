-- Restores defaults on columns for Flourish
-- Django migrations can force defaults to be removed.
-- This should be safe to run at any time to clean up.

begin;

alter table "users"
    alter subscribed set default false,
    alter hackney set default false,
    alter subscription_period set default 0,
    alter terminated set default false,
    alter admin set default false,
    alter has_profile set default false,
    alter disabled_profile set default false
;

alter table cards
    alter active set default true
;

alter table users_profiles
    alter allow_email set default false,
    alter allow_doorbot set default false
;

alter table aliases
    alter type set default 2
;

alter table interests
    alter suggested set default false
;

commit;
