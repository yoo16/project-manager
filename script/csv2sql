#!/usr/bin/env ruby
BASE = File.dirname(__FILE__) + '/../'
require 'csv'

class CsvConverter
  attr_accessor :table, :csv_dir, :output_dir

  def initialize table
    @table = table
    @csv_dir = "#{BASE}db/records/"
    @output_dir = "#{BASE}tmp/"
  end

  def csv_file
    file = "#{@csv_dir}#{@table}.csv"
    raise "#{file} is not exist" unless File.exist? file
    file
  end

  def shift_columns csv_reader = nil
    table_columns = {}
    if csv_reader.nil?
      csv_reader = CSV.foreach csv_file
      csv_columns = csv_reader.shift
      csv_reader.close
    else
      csv_columns = csv_reader.shift
    end
    csv_columns.size.times do |n|
      csv_column = csv_columns[n]
      table_columns[n] = csv_column.intern unless csv_column.nil? || csv_column[0,1] == '-'
    end
    table_columns
  end

  def puts_each file = "#{output_dir}#{@table}.sql"
    csv = CSV.foreach csv_file
    columns = shift_columns csv
    open file, 'w' do |file|
      csv.each do |line|
        record = {}
        columns.each do |n, column|
          record[column] = ((line[n].nil? || line[n] == '') ? 'null' : "'#{line[n]}'")
        end
        sql = yield record
        file.puts sql unless sql.nil?
      end
    end
    csv.close
  end

  def create_insert_sql
    puts_each do |record|
      "INSERT INTO #{@table} (#{record.keys * ','}) VALUES (#{record.values * ','});"
    end
  end

  def create_update_sql pkey = :id
    raise "'#{pkey}' column not found" unless shift_columns.value? pkey
    puts_each do |record|
      key_values = []
      record.each { |key, value| key_values << "#{key}=#{value}" unless key == pkey }
      "UPDATE #{@table} SET #{key_values * ','} WHERE #{pkey} = #{record[pkey]};"
    end
  end
end

begin
  table = $*[0]

  if /^(\w+)(\.csv)?$/ =~ table
    table = $1 unless $2.nil?
  else
    raise 'invalid argument'
  end

  cnv = CsvConverter.new table

  if $*[1].nil?
    cnv.create_insert_sql
  else
    cnv.create_update_sql $*[1].intern
  end
rescue
  puts $!
end
