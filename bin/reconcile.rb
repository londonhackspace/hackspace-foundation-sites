#!/usr/bin/env ruby

require 'rubygems'
require 'ofx-parser'
require 'sqlite3'

ofx = OfxParser::OfxParser.parse(open(ARGV[0]))

db = SQLite3::Database.new("../var/database.db")
db.results_as_hash = true

ofx.bank_account.statement.transactions.each do |transaction|
    c = db.get_first_value("SELECT count(*) FROM transactions WHERE fit_id = ?", transaction.fit_id)
    if c.to_i > 0:
      next
    end

    match = transaction.payee.match(/HS([0-9]{5,})/)
    if !match:
      next
    end

    user = db.get_first_row("SELECT * FROM users WHERE id = ?", match[1].to_i)
    if !user:
      puts "Payment for invalid user ID #{match[1].to_i}! Bug?"
      next
    end
    
    db.transaction do |db|
      db.execute("INSERT INTO transactions (fit_id, timestamp, user_id, amount) VALUES (?, ?, ?, ?)",
                      transaction.fit_id, transaction.date, user['id'], transaction.amount)
      db.execute("UPDATE users SET subscribed = 1 WHERE id = ?", user['id'])
    end
    puts "User #{user['full_name']} now subscribed."
end

db.execute("SELECT * FROM users") do |row|
  
end
