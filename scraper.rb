require "epathway_scraper"

EpathwayScraper.scrape_and_save(
  "https://services.greatlakes.nsw.gov.au/ePathway/Production",
  list_type: :all, max_pages: 10
)
