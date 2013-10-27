#!/usr/bin/env ruby

require 'rubygems'
require '../ruby-lib/ofx-parser.rb'
require 'sqlite3'
require 'erubis'

def send_unsubscribe_email(email, name)
  template = File.read('../emails/lapse.erb')
  p template
end

send_unsubscribe_email('russ@garrett.co.uk', 'Russ Garrett')

exit

ofx = OfxParser::OfxParser.parse(open(ARGV[0]))

db = SQLite3::Database.new("../var/database.db")
db.results_as_hash = true

ofx.bank_account.statement.transactions.each do |transaction|
    c = db.get_first_value("SELECT count(*) FROM transactions WHERE fit_id = ?", transaction.fit_id)
    if c.to_i > 0
      next
    end

    match = transaction.payee.upcase.match(/H[S5] ?([O0-9]{4,})/)
    if !match
      next
    end

    reference = match[1].gsub(/O/, '0')

    user = db.get_first_row("SELECT * FROM users WHERE id = ?", reference.to_i)
    if !user
      puts "Payment for invalid user ID #{reference.to_i}! Bug?"
      next
    end

    if user['terminated'] == 1
      puts "User #{user['full_name']} is paying but their membership is terminated."
      next
    end

    if user['address'] == ''
      puts "User #{user['full_name']} has no address, not subscribing."
      next
    end

    if transaction.amount.to_i < 5
      puts "User #{user['full_name']} is paying less than £5 (£#{transaction.amount}), not subscribing."
      next
    end

    db.transaction do |db|
      db.execute("INSERT INTO transactions (fit_id, timestamp, user_id, amount) VALUES (?, ?, ?, ?)",
                      transaction.fit_id, transaction.date, user['id'], transaction.amount)
      db.execute("UPDATE users SET subscribed = 1 WHERE id = ?", user['id'])
    end
    puts "User #{user['full_name']} now subscribed."
end

db.execute("SELECT users.*, (SELECT max(timestamp) FROM transactions WHERE user_id = users.id) AS lastsubscription 
        FROM users WHERE users.subscribed = 1 AND lastsubscription < date('now', '-1 month', '-14 days')") do |user|
    puts "Unsubscribing #{user['full_name']}."
    db.execute("UPDATE users SET subscribed = 0 WHERE id = ?", user['id'])
end
