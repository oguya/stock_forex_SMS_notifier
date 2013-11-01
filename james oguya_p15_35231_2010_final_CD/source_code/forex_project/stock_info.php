<?php
include_once 'db_connect.php';

/*
 * fetch from db
 * validate keywords/alias
 * send stock price
 * update requests.
 * 
 */

##fetch requests from db
 function fetchStock(){
 	$keyword="STOCK";
 	$sql="select requestID,MSISDN,messageContent from incomingRequests where messageContent like '%$keyword%' and processed = 5";
 	$result = mysql_query($sql);
 	if(!$result){
 		$dbErr="SQL ERROR: ".mysql_error()."SQL CODE: ".$sql;
 		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
 		return FALSE;
 	}else{
 		$logs="Just fetched ".mysql_num_rows($result)." sms for processing.";
 		stockInfo($logs, $_SERVER["SCRIPT_FILENAME"]);
 		return $result;
 	}
 }
 
 ##validate company name
 function validateCompany($alias){
 	$alias=strtoupper($alias);
 	$sql="select companyID,sectorID,companyName,Alias from companies where Alias  like '%$alias%' limit 1";
 	$result=mysql_query($sql);
 	if(!$result){
 		$dbErr="SQL ERROR: ".mysql_error()."SQL CODE: ".$sql;
 		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
 		return FALSE;
 	}else{
 		if(mysql_num_rows($result) > 0){
	 		$logs="Successful Validation: ".$sql;
	 		stockInfo($logs, $_SERVER["SCRIPT_FILENAME"]);
	 		return $result;
 		}else{
	 		$logs="Failed Validation: ".$sql;
	 		stockInfo($logs, $_SERVER["SCRIPT_FILENAME"]);
	 		return FALSE;
 		}
 	}
}
 
 ##send prices
 function sendPrices($destAddr,$alias){
 	$alias=strtoupper($alias);
 	$sql="select liveData.companyID, hi,low,turnOver,Alias,companyName,liveData.dateCreated from liveData inner join companies on companies.companyID=liveData.companyID where Alias='$alias' and substr(timediff(time(now()),time(liveData.dateCreated)),1,6) < '00:45' and date(liveData.dateCreated)=curdate() limit 1";
 	$result=mysql_query($sql);
 	if(!$result){
 		$dbErr="SQL ERROR: ".mysql_error()." SQL CODE: ".$sql;
 		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
 	}else{
 		while($row=mysql_fetch_array($result)){
 			$hi=$row['hi'];
 			$low=$row['low'];
 			$turnOver=$row['turnOver'];
 			$company=$row['companyName'];
 			$timestamp=$row['dateCreated'];
 			
 			$logs="fetched stock prices from liveData. ".$sql;
 			stockInfo($logs, $_SERVER['SCRIPT_FILENAME']);
 			
 			$response="Company: ".$company." Hi: ".$hi." Low: ".$low." TurnOver: ".$turnOver." Timestamp: ".$timestamp;
 			
 			$sql_send="insert into outbound(destAddr,sourceAddr,messageType,messageContent,status) values('$destAddr',default,default,'$response',default)";
 			$result2=mysql_query($sql_send);
 			if(!$result2){
 				$dbErr="SQL ERROR: ".mysql_error()." SQL CODE: ".$sql;
 				flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
 			}else{
 				$logs="Successfully inserted message to outbound. ".$sql_send;
 				stockInfo($logs, $_SERVER['SCRIPT_FILENAME']);
 			}
 		}
 	}
 }
 
 ###func. to update requests
 function updateText($requestID){
	$processed = 32;
	$logs ="About to update ".count($requestID)." requests.";
	infoLogs($logs, $_SERVER["SCRIPT_FILENAME"]);
	
	if( count($requestID) == 0 ){
		$logs ="No requests to be updated!";
		stockInfo($logs, $_SERVER["SCRIPT_FILENAME"]);
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
			stockInfo($logs, $_SERVER["SCRIPT_FILENAME"]);
		}
	}
}

?>

<?php
/*
 * fetch from db
 * validate keywords/alias
 * send stock price
 * update requests.
 * 
 */

$req=fetchStock();
$requestID=array();
while($row=mysql_fetch_array($req)){
	$sms=$row['messageContent'];
	$destAddr=$row['MSISDN'];
	$requestID[] = $row['requestID'];
	
	$parts=explode(' ',$sms);
	$company=$parts[1];
	
	//validate company
	if($result=validateCompany($company)){
		while($row=mysql_fetch_array($result)){
			$alias=$row['Alias'];
		}
		//send the rates to user
		sendPrices($destAddr, $alias);
	}else{
		exit();
	}
}

//update processed sms
updateText($requestID);
?>
