<?php
#include 'db_connect.php';
#include_once 'global_functions.php';

##contains functions used by the parsers

#function to use at the start of an element
function start($parser,$element_name,$element_attrs){
	switch($element_name){
		case "channel": echo "CHANNEL DATA!";
		break;
		case "title": echo "Currency Pair: ";
		break;
		case "link": echo "URL: ";
		break;
		case "pubDate": echo "Date: ";
		break;
		case "description": echo "Description: ";
		break;
		case "category": echo "Region: ";
	}
}

#function to use at the end of an element
function stop($parser,$element_name){
	echo "\n";
}

#function to use when finding charac data...CDATA
function char($parser,$data){
	$_SESSION['print'][]=$data;
}

?>

<?php

##convert the currency from shortform to long form.
function longCurrency($currency){
	switch ($currency){
		case "KES": $long="Kenyan Shilling";
		break;
		case "USD": $long="US Dollar";
		break;
		case "EURO": $long="Euro";
		break;
		case "AUD": $long="Australian Dollar";
		break;	
		case "ZAR": $long="South African Rand";
		break;
		case "GBP": $long="British Pound Sterling";
		break;
		case "JPY": $long="Japanese Yen";
		break;
		case "NZD": $long="New Zealand Dollar";
		break;
		case "CAD": $long="Canadian Dollar";
		break;
		case "CHF": $long="Swiss Franc";
	}
	return $long;
}

##log the parsed data to db
function insertRates($rate,$timestamp,$currency,$market){
	
	for($x=0; $x<count($rate); $x++){
		$parts = explode('=' , $rate[$x]);
		$exploded = $parts[1];
		$temp = explode(' ',$exploded);
		$float_rate = $temp[1];
		
		###applicable for EURO only..bcoz in db we have EUR..not EURO
		if($currency == 'EURO'){
			$currency = "EUR";
		}
		
		#log the rates to db....fetch id from db & use it for insertion
		$sql_get_id="select currencyID,abbreviation from currencies where abbreviation like '%$currency%' limit 1";
		
		$result = mysql_query($sql_get_id);
		if(!$result){
			$dbErr="SQL error: ".mysql_error()." SQL CODE: ".$sql_get_id;
			flog($dbErr, $_SERVER["PHP_SELF"]); //log to file
			logEvents($dbErr, $_SERVER["PHP_SELF"]);
			die("SQL error: ".mysql_error());
		}
		
		$appLogs = "successfully fetched currencyID for $currency\nSQL CODE: ".$sql_get_id."\n";
		
		while($rows=mysql_fetch_array($result)){
			$currencyID = $rows['currencyID'];
		}
		
		$sql_ins_rates="insert into Rates(currencyID,market,rate,timestamp) values($currencyID,'$market[$x]',$float_rate,'$timestamp[$x]')";
		if( !(mysql_query($sql_ins_rates)) ){
			$dbErr = "SQL error: ".mysql_error()." SQL CODE: ". $sql_ins_rates ." Current FOREX: ".$currency;
			flog($dbErr, $_SERVER["PHP_SELF"]);
		}
		
		$appLogs .= "successfully inserted Rates to db!";
		logEvents($appLogs, $_SERVER["PHP_SELF"]);
	}
}

#log unprocessed xml data 2 db
function insertXMLData($rate,$timestamp){
	for($x=0; $x<count($rate)-1; $x++){
		$sql="insert into xmlData(rate,timestamp) values('$rate[$x]','$timestamp[$x]')";
		$result=mysql_query($sql);
		
		if(!$result){
			$dbErr="SQL error: ".mysql_error()."SQL CODE: ".$sql;
			flog($dbErr, $_SERVER["PHP_SELF"]);
		}
		else{
			$appLogs = "successfully inserted rates to db!";
			logEvents($appLogs, $_SERVER["PHP_SELF"]);
		}
	}
}

?>