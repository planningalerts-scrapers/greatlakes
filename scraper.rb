require "epathway_scraper"

EpathwayScraper.scrape(
  "https://services.greatlakes.nsw.gov.au/ePathway/Production",
  list_type: :all, max_pages: 10
) do |record|
  # Remove the first bit of the address as it just contains lot information
  record["address"] = record["address"].split(", ")[1..-1].join(", ")
  # Squeeze whitespace
  record["address"] = record["address"].squeeze(" ")
  # Remove returns in description and squeeze whitespace
  record["description"] = record["description"].gsub("\n", " ").squeeze(" ").strip
  EpathwayScraper.save(record)
end
