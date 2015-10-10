#!/usr/bin/env ruby
# encoding: UTF-8

require 'rubygems'
require '../ruby-lib/ofx-parser.rb'
require 'sqlite3'
require 'erubis'
require 'mail'

def firstname(full_name)
  full_name.gsub(/^(Mr|Mrs|Miss|Ms)\.?\s+/, '').split(' ')[0]
end

def send_email(address, subject, text)
  mail = Mail.new do
    from "London Hackspace <trustees@london.hackspace.org.uk>"
    to address
    subject subject
    body text
  end
  mail.delivery_method :sendmail
  mail.deliver
end

def send_imminent_unsubscribe_email(email, full_name, last_payment)
  vars = {'name' => firstname(full_name),
          'date' => last_payment.strftime('%Y-%m-%d')}
  email_send_helper('../emails/imminent-lapse.erb', vars, email, "Your London Hackspace membership is about to lapse")
end

def send_unsubscribe_email(email, full_name, last_payment)
  vars = {'name' => firstname(full_name),
          'date' => last_payment.strftime('%Y-%m-%d')}
  email_send_helper('../emails/lapse.erb', vars, email, "Your London Hackspace membership has lapsed")
end

def send_subscribe_email(email, full_name)
  vars = {'name' => firstname(full_name)}
  email_send_helper('../emails/subscribe.erb', vars, email, "Your London Hackspace membership is now active")
end

def email_send_helper(filename, vars, email, subject)
  template = Erubis::Eruby.new(File.read(filename))
  send_email(email, subject, template.result(vars))
end

ofx = OfxParser::OfxParser.parse(open(ARGV[0]))

db = SQLite3::Database.new("../var/database.db")
db.results_as_hash = true
db.busy_timeout = 10000

ofx.bank_account.statement.transactions.each do |transaction|
    if transaction.fit_id.to_i < 200000000000000
      # Barclays now returns non_unique fit_ids in a low range for uncleared transactions.
      # As we rely on fit_ids being unique, we have to ignore these.
      next
    end
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
      puts "Payment for invalid user ID #{reference.to_i}!"
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
      #puts "User #{user['full_name']} is paying less than £5 (£#{transaction.amount}), not subscribing."
      next
    end

    db.transaction do |db|
      db.execute("INSERT INTO transactions (fit_id, timestamp, user_id, amount) VALUES (?, ?, ?, ?)",
                      transaction.fit_id, transaction.date.iso8601(), user['id'], transaction.amount)
      db.execute("UPDATE users SET subscribed = 1 WHERE id = ?", user['id'])

      if user['subscribed'] == 0
        # User is a new subscriber
        puts "User #{user['full_name']} now subscribed."
        send_subscribe_email(user['email'], user['full_name'])
        # check for ldap infos, enable if they exist.
        if user['ldapuser'] and user['ldapnthash'] and user['ldapsshahash'] and user['ldapshell'] and user    ['ldapemail']
          puts "enabling LDAP account: #{user['ldapuser']} (with uid #{user['id'] + 100000})"
          system("/var/www/hackspace-foundation-sites/bin/ldap-add.sh", user['ldapuser'], (user['id'] + 100000).to_s, user['ldapnthash'], user['ldapsshahash'], user['ldapshell'], user['ldapemail'])
        end
      end
    end
end

# Email people who have been unsubscribed.
db.execute("SELECT users.*, (SELECT max(timestamp) FROM transactions WHERE user_id = users.id) AS lastsubscription
        FROM users WHERE users.subscribed = 1 AND lastsubscription < date('now', '-1 month', '-14 days')") do |user|

    puts "Unsubscribing #{user['full_name']}."
    db.execute("UPDATE users SET subscribed = 0 WHERE id = ?", user['id'])
    send_unsubscribe_email(user['email'], user['full_name'], Time.iso8601(user['lastsubscription']))
    # check for ldap infos, delete if they exist
    if user['ldapuser']
    	puts "deleting LDAP account: #{user['ldapuser']}"
      	system("/var/www/hackspace-foundation-sites/bin/ldap-delete.sh", user['ldapuser'])
    end
end

# Email people who are about to be unsubscribed.
db.execute("SELECT users.*, (SELECT max(timestamp) FROM transactions WHERE user_id = users.id) AS lastsubscription
            FROM users
            WHERE users.subscribed = 1
              AND lastsubscription < date('now', '-1 month', '-10 days')
              AND (lapsing_membership_reminder_timestamp IS NULL
                OR lapsing_membership_reminder_timestamp < date('now', '-1 month'))") do |user|

    puts "Warning #{user['full_name']} about imminent subscription lapse."
    db.execute("UPDATE users SET lapsing_membership_reminder_timestamp = date('now') WHERE id = ?", user['id'])
    send_imminent_unsubscribe_email(user['email'], user['full_name'], Time.iso8601(user['lastsubscription']))
end

