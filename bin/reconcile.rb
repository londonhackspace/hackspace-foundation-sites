#!/usr/bin/env ruby

require 'rubygems'
require 'ofx-parser'
require 'sqlite3'

ofx = OfxParser::OfxParser.parse(open(ARGV[0]))

db = SQLite3::Database.new("../var/database.db")

p ofx
