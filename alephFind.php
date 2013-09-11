<?php

// old version using settings.ini. Commenting out to test for sure whether it works without this file.
/* $SETTINGS = 'settings.ini';

function readSettingsFile($file='settings.ini') {
    return parse_ini_file($file);

}

$values = readSettingsFile(); */



$barcodes = array ("31430054272036","31430054447695","31430055485397","31430055203543","31430030940326", "31430049752605", "31430052399393", "31430052399393", "31430056673173", "31430042466138","31430006677696","31430007712559","31430006129102","31430053093094","31430039849874","31430054128535"); //barcodes for testing
//$barcode = $barcodes[6];
$barcode = $_REQUEST["barcode"]; //get barcode from alephBarcodeForm.html form
$style = $_REQUEST["style"];

$op = "find";
$code = "bar";
$base = "CP";
$findURL = "http://catalog.umd.edu/X?request=".$barcode."&op=".$op."&code=".$code."&base=".$base; //url for Aleph x-services find request. Returns set number if successful    
$findResults = file_get_contents($findURL); // get results of find request
$alephFindXML = new SimpleXMLElement($findResults); //and turn the results into an XML object
    if ($alephFindXML->set_number) {        //check for set number
        $alephSetNumber = $alephFindXML->set_number; //if there is a set number, store it in a variable
        //echo "Set Number: ".$alephSetNumber."<br />";        
    } elseif ($alephFindXML->error) {        //error is returned if barcode not found
        echo "Barcode not found <br />";
        break;        
    } else {
        echo "Something went horribly wrong <br />"; //handles other unforseen errors        
        break;                
    }      //end if
        

 
$presentURL = "http://catalog.umd.edu/X?set_no=".$alephSetNumber."&set_entry=1&op=present"; //present url gets the actual MARC XML file for the results set
//echo "Present URL: ".$presentURL."<br />";
$presentResults = file_get_contents($presentURL);
$alephPresentXML = new SimpleXMLElement($presentResults);
$docNumber = $alephPresentXML->record->doc_number;
$alephURL = "http://catalog.umd.edu/docno=".$docNumber;
//echo "Doc Number: ".$docNumber."<br />";
//echo "Marc Fields: <br />";
$oclcPattern = "/^oc[A-z][0-9]{6,9}$/"; //regular expression pattern for matching oclc numbers (other data can be stored in 035 MARC fields)
foreach ($alephPresentXML->record->metadata->oai_marc->varfield as $varfield) { //go through each MARC variable field in the result
    if ($varfield->attributes()->id == "035") { //Find the 035 fields. see http://www.electrictoolbox.com/php-simplexml-element-attributes/ for accessing element attributes
        if (preg_match($oclcPattern, $varfield->subfield)) { //look for 035 fields that store OCLC numbers
            $oclcNumber = preg_replace("/^oc[A-z]/", "", $varfield->subfield); //remove the "ocn" or "ocm" prefix and store the oclc number as a variable
            //echo "OCLC Number: ".$oclcNumber."<br />";                    
        } // end if       
    } // end if
    if ($varfield->attributes()->id == "020") {
    $isbn = $varfield->subfield;
    } // end if
} // end foreach
$permalink = "http://umaryland.worldcat.org/oclc/".$oclcNumber; //build a permalink pointing to WCL out of the OCLC number

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
echo "<a href=\"".$permalink."\" target=\"_blank\">".$permalink."</a><br />"; //print out the WCL permalink
echo $citation; //print out the citation

foreach ($collegeParkLocations as $location) {  //print out holding info for each College Park item
echo $location["sublibrary"]."<br />";
echo $location["collection"]."<br />";
echo $location["callNumber"]."<br /><br />";
} //end foreach
?>
