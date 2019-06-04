<?php
# Great Lakes Council scraper - ePathway
require_once 'vendor/autoload.php';
require_once 'vendor/openaustralia/scraperwiki/scraperwiki.php';

use PGuardiario\PGBrowser;
use Sunra\PhpSimple\HtmlDomParser;

date_default_timezone_set('Australia/Sydney');


$term_url = "https://services.greatlakes.nsw.gov.au/ePathway/Production/Web/GeneralEnquiry/EnquiryLists.aspx?ModuleCode=LAP";

# Agreed Terms
$browser = new PGBrowser();
$page = $browser->get($term_url);
$form = $page->form();
$form->set('mDataGrid:Column0:Property', 'ctl00$MainBodyContent$mDataList$ctl03$mDataGrid$ctl02$ctl00');
$form->set('ctl00$MainBodyContent$mContinueButton', 'Next');
$page = $form->submit();

# Click Search will show all DAs
$form = $page->form();
$form->set('ctl00$MainBodyContent$mGeneralEnquirySearchControl$mEnquiryListsDropDownList', '55');
$form->set('ctl00$MainBodyContent$mGeneralEnquirySearchControl$mTabControl$ctl04$mStreetNumberTextBox', '');
$form->set('ctl00$MainBodyContent$mGeneralEnquirySearchControl$mTabControl$ctl04$mStreetNameTextBox', '');
$form->set('ctl00$MainBodyContent$mGeneralEnquirySearchControl$mTabControl$ctl04$mStreetTypeDropDown', '(any)');
$form->set('ctl00$MainBodyContent$mGeneralEnquirySearchControl$mTabControl$ctl04$mSuburbTextBox', '');
$form->set('ctl00$MainBodyContent$mGeneralEnquirySearchControl$mSearchButton', 'Search');
$page = $form->submit();

$dom = HtmlDomParser::str_get_html($page->html);
$pages = $dom->find("span[id=ctl00_MainBodyContent_mPagingControl_pageNumberLabel]", 0);
$pages = explode(" ", $pages->innertext);
$pages = $pages[3];
if ($pages > 10) {
    $pages = 10;
} else {
    $pages = 1;
}

for ($i=1; $i<=$pages; $i++) {
    echo "Scraping page $i of $pages\n";
    $page = $browser->get("https://services.greatlakes.nsw.gov.au/ePathway/Production/Web/GeneralEnquiry/EnquirySummaryView.aspx?PageNumber=$i");
    $dom = HtmlDomParser::str_get_html($page->html);
    $dataset  = $dom->find("tr[class=ContentPanel], tr[class=AlternateContentPanel]");

    # The usual, look for the data set and if needed, save it
    foreach ($dataset as $record) {
        # Slow way to transform the date but it works
        $date_received = explode(' ', (trim($record->find('span',0)->plaintext)), 2);
        $date_received = explode('/', $date_received[0]);
        $date_received = "$date_received[2]-$date_received[1]-$date_received[0]";
        $date_received = date('Y-m-d', strtotime($date_received));

        $address   = preg_replace('/\s+/', ' ', trim(html_entity_decode($record->find('span', 1)->plaintext)));
        $address   = explode(",", $address, 2);
        $address   = trim($address[1]);

        # Put all information in an array
        $application = array (
            'council_reference' => trim(html_entity_decode($record->find('a',0)->plaintext)),
            'address'           => $address,
            'description'       => preg_replace('/\s+/', ' ', trim(html_entity_decode($record->find('span', 4)->plaintext))),
            'info_url'          => $term_url,
            'date_scraped'      => date('Y-m-d'),
            'date_received'     => $date_received
        );

        print ("Saving record " . $application['council_reference'] . " - " . $application['address']. "\n");
//             print_r ($application);
        scraperwiki::save(['council_reference'], $application);
    }

}


?>
