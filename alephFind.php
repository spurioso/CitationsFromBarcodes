<?php
//$barcodes = array ("31430054272036","31430054447695","31430055485397","31430055203543","31430030940326", "31430049752605", "31430052399393", "31430052399393", "31430056673173", "31430042466138","31430006677696","31430007712559","31430006129102","31430053093094","31430039849874","31430054128535"); //barcodes for testing
//$barcode = $barcodes[6];

require "alephXobjects.php";

$query = $_REQUEST["query"]; //get barcode from alephBarcodeForm.html form
$index = $_REQUEST["index"];
$style = $_REQUEST["style"];

$item = new AlephX($query, $index);

$oclcNumber = $item->getOCLCnum();
$docNumber = $item->getAlephNum();
$isbn = $item->getISBNjustOne();
$alephURL = $item->getAlephURL();
$barcode = $item->getBarcode();

$permalink = "https://umaryland.on.worldcat.org/oclc/".$oclcNumber."?databaseList=638"; //build a permalink pointing to WCL out of the OCLC number
$worldCatKey = getenv('HTTP_WORLDCAT_BASIC_KEY');


$citationStyles = array("apa", "chicago", "harvard", "mla", "turabian", "all");
//$style = $citationStyles[4];
$worldcatCitationsURL = "http://www.worldcat.org/webservices/catalog/content/citations/".$oclcNumber."?cformat=".$style."&wskey=".$worldCatKey;

$citation = file_get_contents($worldcatCitationsURL);

$itemDataURL = "http://catalog.umd.edu/X?op=item_data&doc_number=".$docNumber."&base=CP";

$alephItemData = file_get_contents($itemDataURL);
$alephItemDataXML = new SimpleXMLElement($alephItemData);

$collegeParkLocations = array(); //set up an array to hold location info
foreach ($alephItemDataXML->item as $item) { //step through each item in the results list
    if (preg_match("/^UMCP/", $item->{'sub-library'})) { //if the item is held by College Park...
    $location = array(); //...save College Park sublibrary, collection, and call number for each CP item
        $location["sublibrary"] = $sublibrary = $item->{'sub-library'};
        $location["collection"] = $collection = $item->collection;  
        $location["callNumber"] = preg_replace('/\$\$h/', "", $item->{'call-no-1'});
$location["callNumber"] = preg_replace('/\$\$i/', " ", $location["callNumber"]);
array_push($collegeParkLocations, $location);
    }//end if
} //end foreach
echo "<strong>Aleph number: </strong>".$docNumber."<br />";
echo "<strong>OCLC Number: </strong>".$oclcNumber."<br />";
echo "<strong>ISBN: </strong>".$isbn."<br />";
echo "<strong>Barcode: </strong>".$barcode."<br />";
echo "<a href=\"".$alephURL."\" target=\"_blank\">".$alephURL."</a><br /><br />";

//print out the citation, stripping all HTML tags but <em> and <i>, necessary for CSS formatting on AlephBarcodeForm.html
echo strip_tags($citation, "<em><i>");
echo "</br>";

//print out the WCL permalink
echo "<a href=\"".$permalink."\" target=\"_blank\">".$permalink."</a><br />"; 
echo "</br>";

foreach ($collegeParkLocations as $location) {  //print out holding info for each College Park item
echo $location["sublibrary"]."<br />";

if ($location["collection"] == "RDREF") {
	echo "Paged Collections Room, Ask at Circulation Desk";
	} elseif ($location["collection"] == "REF") {
		echo "Reference";	
	} elseif ($location["collection"] == "STACK") {
		echo "Stacks";
	} elseif ($location["collection"] == "FOLIO") {
		echo "Folio";
	} elseif ($location["collection"] == "MINI") {
		echo "Miniature Scores";
	} else { 
		echo $location["collection"];		
} // end if
echo "</br>";
//echo $location["collection"]."<br />";
echo $location["callNumber"]."<br /><br />";
} //end foreach
?>
