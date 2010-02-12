#!/usr/bin/env ruby

require 'rubygems'
require 'sqlite3'
require 'net/smtp'

exit # Safety mechanism

db = SQLite3::Database.new("../var/database.db")
db.results_as_hash = true

db.execute("SELECT *, (SELECT amount FROM transactions WHERE user_id = users.id ORDER BY timestamp DESC LIMIT 1) AS amount
              FROM users WHERE subscribed = 1 AND cast(amount AS integer) < 20") do |user|
  email = "To: #{user['full_name']} <#{user['email']}>
From: London Hackspace <contact@hackspace.org.uk>
Subject: Your London Hackspace Membership

Hi #{user['full_name'].gsub(/^(Mr|Mrs|Miss|Ms)\.?\s+/, '').split(' ')[0]},

We're contacting you because you're a member of the London Hackspace, but you're paying less than the recommended membership rate. Our records show that the last payment you made was £#{user['amount']}, but the recommended minimum rate is currently £20 per month.

We totally understand if you don't have the money, and it won't affect your membership if you keep paying your current rate. However, we're still very much in need of money: the space is entirely funded by membership and donations from people like you.

The London Hackspace project turned one year old this month, and hopefully you've dropped into to our space in Barnsbury and seen the potential we have for a hacker space here in London. We're well aware that the current space is small —  not to mention cold at this time of year —  so we've made it our main goal to have a bigger, better (warmer!) space by next winter.

The problem is that property in London is viciously expensive, and we really need good financial support from our members in order to make the economics work. We need enough money to put down a deposit on a new venue, as well as a chunk of rent in reserve. A small increase in your monthly payments will make a big difference helping us find a great new place sooner.

So if you have the cash, please consider bumping your standing order up by a tenner or two a month.

Thanks,

 Jonty & Russ
 contact@hackspace.org.uk
"
  Net::SMTP.start('localhost') do |smtp|
        smtp.send_message email, 'contact@hackspace.org.uk', user['email']
  end
end
