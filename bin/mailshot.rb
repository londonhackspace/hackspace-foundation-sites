#!/usr/bin/env ruby

require 'rubygems'
require 'pg'
require 'net/smtp'

#JUSTINCASE
# end

f = File.open(".pgpass")
pgpass = f.gets
pgpass = pgpass.chomp
f.close

db = PG.connect(dbname: 'hackspace', user: 'hackspace', password: pgpass)

db.exec("SELECT *, (SELECT amount FROM transactions WHERE user_id = users.id ORDER BY timestamp DESC LIMIT 1) AS amount FROM users WHERE subscribed = true") do |result|
result.each do |user|
  email = "To: #{user['full_name']} <#{user['email']}>
From: London Hackspace Trustees<trustees@london.hackspace.org.uk>
Subject: Trustee Election 2018

Dear #{user['full_name'].gsub(/^(Mr|Mrs|Miss|Ms)\.?\s+/, '').split(' ')[0]},

Hi All,

We are now accepting nominations for the 2018 London Hackspace Trustee elections. 
Please read this message fully.

Trustees are elected by the whole membership using a ranked secret ballot.
You can read more about the duties of trustees here:
 
 http://wiki.london.hackspace.org.uk/view/Trustees

And there is an additional statement from the current Trustees here:

<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

As the membership has grown in recent years its directorship have increasingly found that there is more to do than just ‘sit ceremonially’ on the board. 
It is indeed an actual job rather than a position that looks good on your CV. 
So, while it is not our intention to try to steer the electorate/candidates in a particular direction, we feel we should make it clear that there is stuff to do and ‘can-do/hands-on’ are most welcome. 
It’s not a lot of work when fairly distributed. When not, it is a strain on the incumbents. The current board are all in agreement that should such a situation arise in the future we will waste no time in moving to rectify it by whatever measures our Constitution allows. Better, however, to avoid it in the first place.

The Trustee guidelines were specifically re-written last year to try to avoid exactly this kind of situation - thus:

Note that the previous advice regarding the time commitment (3 hours per month) was woefully out of date:- The Trustees of late are quite busy, communicate on a daily basis and the expectation is that all should take an active role in proceedings, to be available at short notice to discuss and deal with issues, and to help with the fair distribution of the workload.

 Please take some time to read and digest.

Examples of trustee work include (but are not limited to)
Financial decision making
Managing Social media bits
Commercial leases and contracts
Managing delivery drivers
Ordering consumables and tools
Dealing with interpersonal drama

To avoid any possible confusion, the position we call ‘Trustee’ is in fact ‘Director’ of London Hackspace Ltd in full compliance with UK Company Law. 

>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
 
We'd like to see as many members as possible applying to be a trustee.
Applications from women and minorities are especially encouraged.
 
One existing Trustee is required to retire by rotation under Article 25(b).  

Philip Roy will be retiring as per Articles 25(b) & 25(c), and will be standing for re-election as per Article 25(d). 
There will be 3 trustee positions available in this year's election, increasing the size of the board by 2  seats to 5.

If you would like to put your name forward, please do the following:
 
 1) Email elections@london.hackspace.org.uk from your membership email address, with your full name. With your application, please supply a brief (no more than 250 word) statement about your suitability to be a trustee and a photo so that members can recognise you.
 2) Ask another member to second you. They should email elections@london.hackspace.org.uk from their membership email address with your name, stating that they're seconding you for election.
 
Nominations close at midday on Friday November 30th. The election will start later that day and will last for two weeks.
 
Regards,

The London Hackspace Trustees
 



"
  Net::SMTP.start('localhost') do |smtp|
    puts user['email']
    #puts email
    smtp.send_message email, 'trustees@london.hackspace.org.uk', user['email']
  end
end
end
