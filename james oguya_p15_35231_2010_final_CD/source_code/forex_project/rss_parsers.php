<?php
include_once 'db_connect.php';
#include_once 'global_functions.php';
require_once 'rss_functions.php'
?>

<?php

//open the downloaded file
if( !($fp=fopen($filename,'r')) ){
	$errLog="Cant open $filename to be parsed.";
	echo "Cant open $filename!\n";
	logParsersError($errLog,$_SERVER["PHP_SELF"]); //log error to file
}

#initialize the parsers
$parser=xml_parser_create();

#element handler
xml_set_element_handler($parser, "start", "stop");

#data handler
xml_set_character_data_handler($parser,"char");

#read data from xml file
while( $data =fread($fp,4096) ){
	
	
	xml_parse($parser, $data, feof($fp)) or
	(sprintf("XML Error: %s at line %d ",xml_error_string(xml_get_error_code($parser)),xml_get_current_line_number($parser)));
	
	$parserErr = "XML Error: ".xml_error_string(xml_get_error_code($parser))."  at line ".xml_get_current_line_number($parser);
	logParsers($parserErr, $_SERVER["PHP_SELF"]);
}

//free the xml parser
xml_parser_free($parser);
?>

<?php
##process the parsed data

$bulk_data=array();
$bulk_data=$_SESSION['print'];
$stripped=array();
$market=array();

for ($x=0; $x<count($bulk_data)-1; $x++){
	if( preg_match("/GMT/i", $bulk_data[$x]) ){
		$date[] = $bulk_data[$x];
	}
	
	$currency = $_SESSION['currency'];
	$long = longCurrency($currency);
	$search_params = "/1 ".$long." =/i";
	
	if( preg_match($search_params, $bulk_data[$x]) ){
		echo $bulk_data[$x]."\n";
		$_SESSION['rssLogs'] = $bulk_data[$x]."\n";
		$stripped[] = $bulk_data[$x];
	}

	
	##getting market info...eg ZAR/CAD
	$market_search = "/".$currency."$/";
	if( $currency == "EURO" )
		$market_search = "/"."EUR"."$/";
	
	if( (preg_match($market_search, $bulk_data[$x])) && (!eregi("http://", $bulk_data[$x]))  ){
		echo "Market: ".$bulk_data[$x]."\n";
		$_SESSION['rssLogs'] .= "Market: ".$bulk_data[$x]."\n";
		$market[] = $bulk_data[$x];
	}
}

#print currency
for ($x=0; $x<count($stripped)-1; $x++){
	echo "Rate: ". $stripped[$x]."\n";
	echo "Last Update: ".$date[$x]."\n";
	$_SESSION['rssLogs'] .= "Rate: ". $stripped[$x]."\n"."Last Update: ".$date[$x]."\n";
}

#insert Rates to db
insertRates($stripped, $date, $currency, $market);

##log unprocessed xml data to db
insertXMLData($stripped, $date);

####log events to file
$content = $_SESSION['rssLogs'];
logEvents($content, $_SERVER["PHP_SELF"]);

?>