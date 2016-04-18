#!/usr/bin/env ruby
# encoding: UTF-8

require 'rubygems'
require '../ruby-lib/ofx-parser.rb'
require 'pg'
require 'erubis'
require 'mail'
require 'time'

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

def send_lapse_warning_email(email, full_name, last_payment)
  vars = {'name' => firstname(full_name),
          'date' => last_payment.strftime('%Y-%m-%d')}
  template = Erubis::Eruby.new(File.read('../emails/lapse_warning.erb'))
  send_email(email, "Your London Hackspace membership will lapse in 7 days", template.result(vars))
end

def send_unsubscribe_email(email, full_name, last_payment)
  vars = {'name' => firstname(full_name),
          'date' => last_payment.strftime('%Y-%m-%d')}
  template = Erubis::Eruby.new(File.read('../emails/lapse.erb'))
  send_email(email, "Your London Hackspace membership has lapsed", template.result(vars))
end

def send_subscribe_email(email, full_name)
  vars = {'name' => firstname(full_name)}
  template = Erubis::Eruby.new(File.read('../emails/subscribe.erb'))
  send_email(email, "Your London Hackspace membership is now active", template.result(vars))
end

ofx = OfxParser::OfxParser.parse(open(ARGV[0]))

db = PG.connect(dbname: 'hackspace', user: 'hackspace')
ofx.bank_account.statement.transactions.each do |transaction|
    if transaction.fit_id.to_i < 200000000000000
      # Barclays now returns non_unique fit_ids in a low range for uncleared transactions.
      # As we rely on fit_ids being unique, we have to ignore these.
      next
    end
    c = db.exec_params("SELECT count(*) FROM transactions WHERE fit_id = $1", [transaction.fit_id])[0]['count']
    if c.to_i > 0
      next
    end

    match = transaction.payee.upcase.match(/H[S5] ?([O0-9]{4,})/)
    if !match
      next
    end

    reference = match[1].gsub(/O/, '0')

    res = db.exec_params("SELECT * FROM users WHERE id = $1", [reference.to_i])
    if res.ntuples == 0
      puts "Payment for invalid user ID #{reference.to_i}!"
      next
    end
    user = res[0]

    if user['terminated'] == "t"
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
      db.exec_params("INSERT INTO transactions (fit_id, timestamp, user_id, amount) VALUES ($1, $2, $3, $4)",
                      [transaction.fit_id, transaction.date.iso8601(), user['id'], transaction.amount])
      db.exec_params("UPDATE users SET subscribed = true WHERE id = $1", [user['id']])

      if user['subscribed'] == 'f'
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

db.exec("SELECT users.*, (SELECT max(timestamp) FROM transactions WHERE user_id = users.id) AS lastsubscription
            FROM users
            WHERE users.subscribed = true
            AND (SELECT max(timestamp) FROM transactions WHERE user_id = users.id) < now() - interval '1 month' - interval '7 days'
            AND (SELECT max(timestamp) FROM transactions WHERE user_id = users.id) > now() - interval '1 month' - interval '8 days'"
            ) do |result|

    result.each do |user|
      puts "Warning #{user['full_name']} of membership soon to lapse"
      send_lapse_warning_email(user['email'], user['full_name'], Time.parse(user['lastsubscription']))
    end
end

db.exec("SELECT users.*, (SELECT max(timestamp) FROM transactions WHERE user_id = users.id) AS lastsubscription
            FROM users
            WHERE users.subscribed = true
            AND (SELECT max(timestamp) FROM transactions WHERE user_id = users.id) < now() - interval '1 month' - interval '14 days'"
            ) do |result|

    result.each do |user|
      puts "Unsubscribing #{user['full_name']}."
      db.exec_params("UPDATE users SET subscribed = false WHERE id = $1", [user['id']])
      p user['lastsubscription']
      send_unsubscribe_email(user['email'], user['full_name'], Time.parse(user['lastsubscription']))
      # check for ldap infos, delete if they exist
      if user['ldapuser']
        puts "deleting LDAP account: #{user['ldapuser']}"
          system("/var/www/hackspace-foundation-sites/bin/ldap-delete.sh", user['ldapuser'])
      end
    end
end
