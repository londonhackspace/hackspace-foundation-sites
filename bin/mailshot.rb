#!/usr/bin/env ruby

require 'rubygems'
require 'pg'
require 'net/smtp'

#JUSTINCASE
#end

f = File.open(".pgpass")
pgpass = f.gets
pgpass = pgpass.chomp
f.close

db = PG.connect(dbname: 'hackspace', user: 'hackspace', password: pgpass)

db.exec("SELECT *, (SELECT amount FROM lhspayments_payment WHERE user_id = users.id ORDER BY timestamp DESC LIMIT 1) AS amount FROM users WHERE subscribed = true") do |result|
result.each do |user|
 email = "To: #{user['full_name']} <#{user['email']}>
From: London Hackspace Trustees<trustees@london.hackspace.org.uk>
Subject: Blah

Dear #{user['full_name'].gsub(/^(Mr|Mrs|Miss|Ms)\.?\s+/, '').split(' ')[0]},

Text text text text Text text text text Text text text text
Text text text text Text text text text Text text text text
Text text text text Text text text text Text text text text

Regards
The London Hackspace Trustees

"
  Net::SMTP.start('turing.hackspace.org.uk') do |smtp|
    puts user['email']
    #puts email
    smtp.send_message email, 'trustees@london.hackspace.org.uk', user['email']
  end
end
end
