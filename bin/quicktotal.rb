#!/usr/bin/env ruby

# Just print a total of payments for a particular transaction NAME (perplexingly called a 'payee').

require 'rubygems'
require '../ruby-lib/ofx-parser.rb'

ofx = OfxParser::OfxParser.parse(open(ARGV[0]))

trans_name = ARGV[1].upcase
total = 0

ofx.bank_account.statement.transactions.each do |transaction|

    match = transaction.payee.match(trans_name)
    if match:
		pounds, pence = transaction.amount.split('.')

		total += pounds.to_i * 100
		total += pence.to_i
    end
end

puts "%d.%02d" % [total / 100, total % 100]

