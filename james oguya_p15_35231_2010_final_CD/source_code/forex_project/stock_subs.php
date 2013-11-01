<?php
 include_once 'db_connect.php';

 /*
  * steps:-
  * fetch from db..keyword:-start stock 
  * add user to stock_subs tbl
  * 
  */
 
 ##fetch texts from db
 function fetchSMS(){

	$sql="select requestID,MSISDN,messageContent,processed from incomingRequests where processed = 5";
	$result=mysql_query($sql);
	if(!$result){
		$dbErr="SQL Error: ".mysql_error()."SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$subLogs = "Fetched 1 sms bucket of approx ".mysql_num_rows($result)." items.";
		stockSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
	}
	return $result;
}

##func to subscribe new user
function Newsubscriptions($command, $subscriber, $alias){
	$command=strtoupper($command);
	if ($command == "SUBSCRIBE"){
		$sql="insert into stock_subs(channelID, MSISDN, dateSubscribed,active,company) values(1,'$subscriber',now(),default,'$alias')";
		$result=mysql_query($sql);
		if(!$result){
			$dbErr="SQL ERROR: ".mysql_error()." SQL CODE: ".$sql;
			flog($dbErr, $_SERVER['SCRIPT_FILENAME']);
			return FALSE;
		}else{
			$subLogs="Successful subscription: ".$sql;
			stockSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
			return TRUE;
		}
	}elseif ($command == "UNSUBSCRIBE"){
		$sql="update stock_subs set active =0,dateUnsubscribed=now() where MSISDN ='$subscriber' and company='$alias'";
		$result=mysql_query($sql);
		if(!$result){
			$dbErr="SQL ERROR: ".mysql_error()." SQL CODE: ".$sql;
			flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
			return FALSE;
		}else{
			$subLogs="Successful unsubscription: SQL:".$sql;
			stockSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
			return TRUE;
		}
	}
}


##func to check multiple subscriptions
function duplicateSubscriptions($subscriber, $alias){
	$alias=strtoupper($alias);
	
	$sql="select count(*) as duplicates from stock_subs where MSISDN ='$subscriber' and active =1 and company='$alias'";
	$result=mysql_query($sql);
	if(!$result){
		$dbErr="SQL ERROR: ".mysql_error()." SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$subLogs="Checking for Duplicate Subscriptions.".$sql;
		stockSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
		$rows=mysql_fetch_array($result);
		if($rows['duplicates'] >= 1){
			$subLogs="$subscriber has already subscribed  to stock alerts!";
			stockSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
			
			return FALSE;
		}else{
			$subLogs="$subscriber has not yet subscribed to stock alerts!";
			stockSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
			return TRUE;
		}
	}
}

###func for subscription status
function subscriptionStatus($destAddr, $keyword, $state){
	if($state == "SUBSCRIBE"){
		$response="You have successfully subscribed to $keyword stock Alerts";
		$sql="insert into outbound(destAddr,sourceAddr,messageType,messageContent,status) values('$destAddr',default,default,'$response','send')";
		$result=mysql_query($sql);
		if(!$result){
			$dbErr="SQL ERROR: ".mysql_error()." SQL CODE: ".$sql;
			flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		}else{
			$subLogs="Successfully responded to $destAddr about stock subscriptions.";
			stockSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
		}
	}elseif ($state == "UNSUBSCRIBE"){
		$response="You have successfully unsubscribed from $keyword stock Alerts";
		$sql="insert into outbound(destAddr,sourceAddr,messageType,messageContent,status) values('$destAddr',default,default,'$response','send')";
		$result=mysql_query($sql);
		if(!$result){
			$dbErr="SQL ERROR: ".mysql_error()." SQL CODE: ".$sql;
			flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		}else{
			$subLogs="Successfully responded to $destAddr about stock unsubscription.";
			stockSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
		}
	}else {
		$subLogs="Invalid state: ".$state. " cant proceed!";
		stockSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
	}
}

###func to respond to user if he/she did a duplicate subscription
function doubleSubs($destAddr,$alias){
	$response="You have already subscribed to $alias stock Alerts";
	$sql="insert into outbound(destAddr,sourceAddr,messageType,messageContent,status) values('$destAddr',default,default,'$response','send')";
	$result=mysql_query($sql);
	if(!$result){
		$dbErr="SQL ERROR: ".mysql_error()." SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$subLogs="Successfully alerted $destAddr about duplicate subscriptions. SQL CODE: $sql";
		stockSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
	}
}

###verify company name
function validateCompany($alias){
 	$alias=strtoupper($alias);
 	$sql="select companyID,sectorID,companyName,Alias from companies where Alias ='$alias' limit 1";
 	$result=mysql_query($sql);
 	if(!$result){
 		$dbErr="SQL ERROR: ".mysql_error()."SQL CODE: ".$sql;
 		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
 		return FALSE;
 	}else{
 		if(mysql_num_rows($result) > 0){
	 		$logs="Successful Validation: ".$sql;
	 		stockSubs($logs, $_SERVER["SCRIPT_FILENAME"]);
	 		return $result;
 		}else{
	 		$logs="Failed Validation: ".$sql;
 			stockSubs($logs, $_SERVER["SCRIPT_FILENAME"]);
	 		return FALSE;
 		}
 	}
}

###return a list of companies
function returnCompanies($destAddr){
	$sql="select companyName,Alias from companies";
	$result=mysql_query($sql);
	if(!$result){
		$dbErr="SQL ERROR: ".mysql_error()." SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$subLogs="Just fetched a list of all companies. SQL: ".$sql;
		stockSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
		$message='';
		while($rows=mysql_fetch_array($result)){
			$companyName=$rows['companyName'];
			$alias=$rows['Alias'];

			$message.=$companyName." ".$alias."\n ";
		}
		$subLogs="fetched a list of all companies. SQL: ".$message;
		stockSubs($subLogs, $_SERVER['SCRIPT_FILENAME']);
		#return $message;
		$sql="insert into outbound(destAddr,sourceAddr,messageType,messageContent,status) values('$destAddr',default,default,'$message','send')";
		$result=mysql_query($sql);
		if(!$result){
			$dbErr="SQL ERROR: ".mysql_error()." SQL CODE: ".$sql;
			flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		}else{
			$subLogs="Just responded with a list of all companies. SQL: ".$sql;
			stockSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
		}
	}
}

##inform user about invalid stock prices
function notifyUser($destAddr,$alias){
	$response="Currently we dont provide stock prices  for the company $alias.To subscribe for stock alerts for any company please send STOCK START <company> to to 0714044696.for example: send STOCK START SCOM to get alerts on on Safaricom shares.For more info please visit www.nse.co.ke";
	$sql="insert into outbound(destAddr,sourceAddr,messageType,messageContent,status) values('$destAddr',default,default,'$response','send')";
	$result=mysql_query($sql);
	if(!$result){
		$dbErr="SQL ERROR: ".mysql_error()." SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$subLogs="Successfully responded to $destAddr about $alias stocks subscriptions. SQL: ".$response;
		stockSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
	}
}

##update the status of fetched texts to
function updateSMS($requestID){
	$processed = 32;
	$subLogs="About to update ". count($processed) ." requests";
	stockSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
	
	for ($x=0;$x<(count($requestID)); $x++){
		$sql_update = "update incomingRequests set processed = $processed where requestID = $requestID[$x] limit 1";
		$result = mysql_query($sql_update);
		//log db errors
		if(!$result){
			$dbErr = "SQL Error: ".mysql_error()."SQL CODE: ".$sql_update;
			flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		}else{
			$subLogs = "Just finished processing a new request. SQL CODE: ".$sql_update;
			stockSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
		}
		
	}
	
}

?>

<?php
 
 /*
  * steps:-
  * fetch from db..keyword:-start stock 
  * add user to stock_subs tbl
  * 
  */

$result=fetchSMS();
$requestID=array();
while($row=mysql_fetch_array($result)){
	$text=$row['messageContent'];
	$requestID[]=$row['requestID'];
	$sourceAddr=$row['MSISDN'];
	
	$parts=explode(' ', $text);
	if( preg_match('/STOCK/i', $parts[0]) ){
		//subscribe user to alerts
		if( preg_match('/START/i', $parts[1]) ){
			//validate company name
			if( validateCompany($parts[2]) ){
				//check double subs.
				if( duplicateSubscriptions($sourceAddr, $parts[2]) ){
					//add user to db
					$command="SUBSCRIBE";
					Newsubscriptions($command, $sourceAddr, $parts[2]);
					//inform user about subs.
					$state="SUBSCRIBE";
					subscriptionStatus($sourceAddr, $parts[2], $state);
				}else{
					$subLogs="Duplicate Subscriptions: User: ".$sourceAddr;
					stockSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
					doubleSubs($sourceAddr, $parts[2]);
				}				
			}else{
				//invalid company name
				notifyUser($sourceAddr, $parts[2]);
			}

		}elseif ( preg_match('/STOP/i', $parts[1]) ){
			//unsubscribe user from alerts
			$command="UNSUBSCRIBE";
			Newsubscriptions($command, $sourceAddr, $parts[2]);
			//inform user about unsubs.
			$state="UNSUBSCRIBE";
			subscriptionStatus($sourceAddr, $parts[1], $state);
		}elseif ( preg_match("/ALL/i", strtoupper($parts[1])) ) {
			returnCompanies($sourceAddr);
		}
	}else{
		exit();
	}
}

##update processed texts
updateSMS($requestID);

?>