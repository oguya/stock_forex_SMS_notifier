<?php

include_once 'db_connect.php';

/*
 * process general forex info requests eg req KES info
 */

##func. to fetch texts from db
function fetchtext(){
	$keyword='INFO';
	$sql="select requestID,MSISDN,messageContent from incomingRequests where messageContent like '%$keyword%' and processed = 5";
	$result = mysql_query($sql);

	if(!$result){
		$dbErr = "SQL Error: ".mysql_error()." SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$logs = "Just fetched ".mysql_num_rows($result)." SMS for processing.";
		infoLogs($logs, $_SERVER["SCRIPT_FILENAME"]);
		return $result;
	}
}


##func. to check if currency exists
function validateCurrency($currency){
	$currency = strtoupper($currency);
	$sql = "select currencyID,fullName,abbreviation from currencies where abbreviation like '%$currency%' limit 1";
	$result = mysql_query($sql);
	if(!$result){
		$dbErr = "SQL Error: ".mysql_error()." SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		
		$logs="Validation Error: $currency doesn't exist! SQL CODE:".$sql;
		infoLogs($logs, $_SERVER["SCRIPT_FILENAME"]);
		
		return FALSE;
	}
	else{
		while($rows = mysql_fetch_array($result)){
			$keyword = $rows['abbreviation'];
		}
		
		$logs = "Just validated $keyword currency. SQL CODE: $sql";
		infoLogs($logs, $_SERVER["SCRIPT_FILENAME"]);

		return $keyword;
	}
}


##func. to fetch latest rates for that currency
function fetchRates($currency){
	$sql="select currencyID,market,rate,dateCreated from Rates where market in ('$currency/USD','$currency/AUD','$currency/CAD','$currency/CHF','$currency/EUR','$currency/GBP','$currency/JPY','$currency/NZD','$currency/ZAR') and date(dateCreated)=curdate() and substr(timediff(time(now()),time(dateCreated)),1,2) < '02' order by dateCreated desc";
	$result = mysql_query($sql);
	
	if(!$result){
		$dbErr = "SQL Error: ".mysql_error()." SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		return FALSE;
	}
	else{
		$logs = "Successfully fetched market rates for $currency. SQL CODE:".$sql;
		infoLogs($logs, $_SERVER["SCRIPT_FILENAME"]);
		return $result;
		
		$rates = '';
		while($rows = mysql_fetch_array($result)){
			$rates .= $rows['market']."=".$rows['rate']." ";
		}
		if($rates == ''){
			return FALSE;
		}
		else{
			return $rates;
		}
	}
}

##function to send the rates to user
function sendRates($destAddr,$rates){
	$sql="insert into outbound(destAddr,sourceAddr,messageType,messageContent,status) values('$destAddr',default,default,'$rates',default)";
	$result = mysql_query($sql);
	if(!$result){
		$dbErr = "SQL Error: ".mysql_error()." SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		return FALSE;
	}
	else{
		$logs="Just sent rates to $destAddr. SQL CODE: ".$sql;
		infoLogs($logs, $_SERVER["SCRIPT_FILENAME"]);
		return TRUE;
	}
}

##func. to inform user about wrong currency rate
function invalidCurrency($destAddr,$currency){
	$message="Currently we dont provide forex rates for the currency $currency.To view rates for other currencies please send INFO <currency> to 0714044696.for example: send INFO KES to view the latest forex rates for KES.For more info please visit www.cbk.go.ke";
	$sql="insert into outbound(destAddr,sourceAddr,messageType,messageContent,status) values('$destAddr',default,default,'$message',default)";
	$result = mysql_query($sql);
	if(!$result){
		$dbErr ="DB Error: ".mysql_error()."SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}
	else{
		$logs="Just informed $destAddr about the invalid currency. SQL CODE: ".$sql;
		infoLogs($logs, $_SERVER["SCRIPT_FILENAME"]);
	}
}

##func. to update the processed info requests
function updateText($requestID){
	$processed = 32;
	$logs ="About to update ".count($requestID)." requests.";
	infoLogs($logs, $_SERVER["SCRIPT_FILENAME"]);
	
	if( count($requestID) == 0 ){
		$logs ="No requests to be updated!";
		infoLogs($logs, $_SERVER["SCRIPT_FILENAME"]);
	}
	
	for ($x=0;$x<(count($requestID)); $x++){
		$sql_update = "update incomingRequests set processed = $processed where requestID = $requestID[$x] limit 1";
		$result = mysql_query($sql_update);
		//log db errors
		if(!$result){
			$dbErr = "SQL Error: ".mysql_error()."SQL CODE: ".$sql_update;
			flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		}else{
			$logs = "Just finished processing a new request. SQL CODE: ".$sql_update;
			infoLogs($logs, $_SERVER["SCRIPT_FILENAME"]);
		}
		
	}
	
}
?>

<?php

$texts=fetchtext();
$requestID = array();
while($rows=mysql_fetch_array($texts)){
	$sms = $rows['messageContent'];
	$destAddr=$rows['MSISDN'];
	$requestID[] = $rows['requestID'];
	
	if(preg_match('/INFO/i', $sms)){
		//explode the text into parts
		$parts = explode(' ', $sms);
		$currency = $parts[1];

		//validate currency
		if( $keyword = validateCurrency($currency) ){
			//fetch the rates
			$rates = fetchRates($currency);
			
			//blank rates..dont send blank text
			if($rates == ''){
				$logs = "RATES ERROR: Cannot send a blank SMS! Please update forex rates info.!";
				infoLogs($logs, $_SERVER["SCRIPT_FILENAME"]);
				exit();
			}
			
			//send the rates...insert into outbound
			$test="";
			while($rows=mysql_fetch_array($rates)){
				$test.=$rows['market']."=".$rows['rate']." ";
			}
			sendRates($destAddr, $test);
		}
		else{
			//invalid or no rates for that currency
			invalidCurrency($destAddr, $currency);
		}
	}
} 

//update the processed txt
updateText($requestID);

?>