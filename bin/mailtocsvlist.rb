#!/usr/bin/env ruby

require 'rubygems'
require 'pg'
require 'net/smtp'

#JUSTINCASE
end

f = File.open(".pgpass")
pgpass = f.gets
pgpass = pgpass.chomp
f.close

db = PG.connect(dbname: 'hackspace', user: 'hackspace', password: pgpass)

db.exec("SELECT * FROM users WHERE id IN (xxxx,xxxx)") do |result|
result.each do |user|
 email = "To: #{user['full_name']} <#{user['email']}>
From: London Hackspace Trustees<trustees@london.hackspace.org.uk>
Subject: blah blah blah

Dear #{user['full_name'].gsub(/^(Mr|Mrs|Miss|Ms)\.?\s+/, '').split(' ')[0]},

(This is being sent on behalf of LHS trustees to all members in the xxxx list.)
 
blah blah blah

Regards
The London Hackspace Trustees


"
  Net::SMTP.start('localhost') do |smtp|
    puts user['email']
    #puts email
    smtp.send_message email, 'trustees@london.hackspace.org.uk', user['email']
  end
end
end
