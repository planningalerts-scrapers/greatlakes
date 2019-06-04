require 'scraperwiki'
require 'yaml'

File.delete("./data.sqlite") if File.exist?("./data.sqlite")

puts "Running original scraper..."
system("php scraper.php")

results_original = ScraperWiki.select("* from data order by council_reference")

ScraperWiki.close_sqlite

File.open("results_original.yml", "w") do |f|
  f.write(results_original.to_yaml)
end

File.delete("./data.sqlite") if File.exist?("./data.sqlite")

puts "Running ruby scraper..."
system("bundle exec ruby scraper.rb")

results_ruby = ScraperWiki.select("* from data order by council_reference")

File.open("results_ruby.yml", "w") do |f|
  f.write(results_ruby.to_yaml)
end

if results_ruby == results_original
  puts "Succeeded"
else
  raise "Failed"
end
