<?php
include_once 'db_connect.php';

/*
 * functionality:-
 * 		-read from incomingReq(db) for new texts
 * 		-explode the string for keyword & command ..start KES / stop KES
 * 		-subscribe or unsubscribe user according to command in the text
 * 
 */

###fetch new text from db in buckets of approx 10 sms
function fetchSMS(){

	$sql="select requestID,MSISDN,messageContent,processed from incomingRequests where processed = 5";
	$result=mysql_query($sql);
	if(!$result){
		$dbErr="SQL Error: ".mysql_error()."SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$subLogs = "Fetched 1 sms bucket of approx ".mysql_num_rows($result)." items.";
		logSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
		
	}
	return $result;
}

##update the status of fetched texts to
function updateSMS($requestID){
	$processed = 32;
	$subLogs="About to update ". count($processed) ." requests";
	logSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
	
	for ($x=0;$x<(count($requestID)); $x++){
		$sql_update = "update incomingRequests set processed = $processed where requestID = $requestID[$x] limit 1";
		$result = mysql_query($sql_update);
		//log db errors
		if(!$result){
			$dbErr = "SQL Error: ".mysql_error()."SQL CODE: ".$sql_update;
			flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		}else{
			$subLogs = "Just finished processing a new request. SQL CODE: ".$sql_update;
			logSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
		}
		
	}
	
}

##acknowledge users of invalid sms
function invalidKeyword($destAddr,$keyword){
	$textErr="invalid Keyword ".$keyword."\r\n";
	$sql="insert into outbound(destAddr,sourceAddr,messageType,messageContent,status ) values('$destAddr',default,default,'$textErr','send')";
	
	$result = mysql_query($sql);
	if(!$result){
		$dbErr="SQL Error: ".mysql_error()."SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$subsLogs = "About to inform ".$destAddr ." about sending invalid keyword ".$keyword;
		logSubs($subsLogs, $_SERVER["SCRIPT_FILENAME"]);
	}
}

##acknowledge user about new subscription/ unsubscription..
function subscriptionStatus($destAddr,$keyword,$state){
	if($state == 'unsubscribe'){
		$response="You have successfully unsubscribed from $keyword alerts";
		$sql_unsub="insert into outbound(destAddr,sourceAddr,messageType,messageContent,status) values('$destAddr',default,default,'$response','send')";
		$result = mysql_query($sql_unsub);
		//log error
		if(!$result){
			$dbErr="SQL Error: ".mysql_error()."SQL CODE: ".$sql_unsub;
			flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		}else{
			$subsLogs = "Successfully responded to ".$destAddr ." about $keyword unsubscription";
			logSubs($subsLogs, $_SERVER["SCRIPT_FILENAME"]);
		}
	}elseif ($state == 'subscribe'){
		$response = "You have successfully subscribed to $keyword alerts";
		$sql_sub = "insert into outbound(destAddr,sourceAddr,messageType,messageContent,status) values('$destAddr',default,default,'$response','send')";
		$result = mysql_query($sql_sub);
		//log error
		if(!$result){
			$dbErr="SQL Error: ".mysql_error()."SQL CODE: ".$sql_sub;
			flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		}else{
			$subsLogs = "Successfully responded to ".$destAddr." about $keyword subscription";
			logSubs($subsLogs, $_SERVER["SCRIPT_FILENAME"]);
		}
	}else{
		$subsLogs = "Invalid state: ".$state. " cant proceed!";
		logSubs($subsLogs, $_SERVER["SCRIPT_FILENAME"]);		
	}
}

###check whether user had already subscribed
function checkSubscriptions($subscriber,$keyword){
	$keyword = strtoupper($keyword);
	$sqlCheck="select count(*) as duplicates from subscriptions inner join infoChannels on infoChannels.channelID = subscriptions.channelID and keyword like '%$keyword%'and msisdn = '$subscriber'";
	$results=mysql_query($sqlCheck);
	
	$subLogs="About to check whether $subscriber has already subscribed to $keyword ";
	$subLogs .= "SQL CODE: ".$sqlCheck;
	logSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
	
	if(!$results){
		
		$dbErr="SQL Error: ".mysql_error()." SQL CODE: ".$sqlCheck;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		while($rows=mysql_fetch_array($results)){
			$duplicates=$rows['duplicates'];
			if($duplicates >= 1){
				duplicateSubscriptions($subscriber,$keyword);
				$subLogs="Duplicate Error: $subscriber has already subscribed to $keyword.";
				#echo "DUP:n ".$duplicates."\n";
				logSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
				return FALSE;
			}else{
				return TRUE;
			}
		}
	}
}

##alert user incase of duplicate subscriptions
function duplicateSubscriptions($subscriber,$keyword){
	$response = "You have already subscribed to $keyword alerts";
	$sql_dup = "insert into outbound(destAddr,sourceAddr,messageType,messageContent,status)	values('$subscriber',default,default,'$response','send')";
	$results=mysql_query($sql_dup);
	if(!$results){
		$dbErr="SQL Error: ".mysql_error()." SQL CODE: ".$sql_dup;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$subLogs="Successfully alerted $subscriber about duplicate subscriptions. SQL CODE: $sql_dup";
		logSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
	}
}

####subscribe new users..store in db
###func. :- 
function Newsubscriptions($command,$subscriber,$keyword){
	$keyword=strtoupper($keyword);
	$sqlKW = "select channelID,channelName,keyword from infoChannels where keyword like '%$keyword%' limit 1";
	$resultKW = mysql_query($sqlKW);
	#log errors
	
	if(!$resultKW){
		##Notify user...probably wrong keyword
		invalidKeyword($subscriber, $keyword);
		$dbErr="SQL Error: ".mysql_error()."SQL CODE: ".$sqlKW;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}
	while($rows=mysql_fetch_array($resultKW)){
		$channelID = $rows['channelID'];
	}
	
	$sql_newSub = "insert into subscriptions(channelID,msisdn,dateSubscribed) values($channelID,'$subscriber',now())";
	$result_newSub = mysql_query($sql_newSub);
	#log errors
	if(!$result_newSub){
		$dbErr="SQL Error: ".mysql_error()."SQL CODE: ".$sqlKW;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}
	else{
		$subsLogs = "Successfully subscribed ".$subscriber ." to $keyword alerts";
		logSubs($subsLogs, $_SERVER["SCRIPT_FILENAME"]);
		$state= 'subscribe';
		subscriptionStatus($subscriber, $keyword, $state);
	}
}

#####unsubscribe users...just disable their active status
function delSubs($command,$subscriber,$keyword){
	$sql_del="update subscriptions set active = 0 where msisdn like '%$subscriber%' and dateUnsubscribed=now() limit 1";
	$result = mysql_query($sql_del);
	#logging
	if(!$result){
		$dbErr="SQL Error: ".mysql_error()."SQL CODE: ".$sqlKW;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$subsLogs = "Successfully unsubscribed ".$subscriber ." to $keyword alerts";
		logSubs($subsLogs, $_SERVER["SCRIPT_FILENAME"]);
	}
	
} 

###return a list of currencies for alerts
//func..fetch currencyname,currencyid from db ret. currencyname
function currencyAlerts(){
	$currencies = array();
	$sql="select serviceName,services.currencyID,currencies.fullName,currencies.abbreviation from services inner join currencies on currencies.currencyID=services.currencyID";
	$result=mysql_query($sql);
	if(!$result){
		$dbErr="SQL Error: ".mysql_error()."SQL CODE: ".$sql_alerts;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$subsLogs = "Successfully fetched registered currency alerts: ".$sql;
		logSubs($subsLogs, $_SERVER["SCRIPT_FILENAME"]);
		while($rows=mysql_fetch_array($result)){
			$currencies = $rows['abbreviation'];
		}
	}
	return $currencies;
}


#####alerts.. sending daily sms alerts
##func..compose template txt 2send..
/*
 * major currencies:- USD,EURO,GBP,ZAR,JPY,NZD,INR,KES
 */

function genAlerts($currency){
	$currency = strtoupper($currency);
	## for KES
	$sql_alerts = "select currencyID,market,rate,dateCreated from Rates where market in ('$currency/USD','$currency/AUD','$currency/CAD','$currency/CHF','$currency/EUR','$currency/GBP','$currency/JPY','$currency/NZD','$currency/ZAR') and date(dateCreated)=curdate() and substr(timediff(time(now()),time(dateCreated)),1,2) < '02' order by dateCreated desc";
	$result = mysql_query($sql_alerts);
	
	#logging
	if(!$result){
		$dbErr="SQL Error: ".mysql_error()."SQL CODE: ".$sql_alerts;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$subsLogs = "Successfully fetched market rates for $currency: ".$sql_alerts;
		logSubs($subsLogs, $_SERVER["SCRIPT_FILENAME"]);
	}

	$alerts = '';
	###insert template to alerts tbl
	while ($rows = mysql_fetch_array($result)){
		$alerts .= $rows['market'] . "=". $rows['rate']." ";
	}
	$channelID=1;
	
	//empty alerts
	if( $alerts=='' ){
		return FALSE;
	}
	
	$sql_newAlerts = "insert into alerts(channelID,sourceAddr,messageContent,messageStatus) values($channelID,default,'$alerts','send')";
	$resultAlerts = mysql_query($sql_newAlerts);
	if(!$resultAlerts){
		$dbErr = "SQL Error: ".mysql_error()."SQL CODE: ".$sql_alerts;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$subsLogs = "Successfully created a new alert template: ".$sql_newAlerts;
		logSubs($subsLogs, $_SERVER["SCRIPT_FILENAME"]);
	}
}

##send the generated alerts..move the template*subs. to outbound
##func:- stake unsent alert from alerts tbl and insert it into outbound
function sendAlerts(){
	$sql="select  alertID,alerts.channelID,msisdn,messageContent,messageStatus from alerts inner join subscriptions on subscriptions.channelID = alerts.channelID and messageStatus= 'send'";
	$result = mysql_query($sql);
	//logging
	if(!$result){
		$dbErr = "SQL Error: ".mysql_error()."SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}
	while($rows = mysql_fetch_array($result)){
		$destAddr = $rows['msisdn'];
		$messageContent = $rows['messageContent'];
		$alertID = $rows['alertID'];
	}
	
	#insert into outbound
	for($x=0;$x<(count($alertID)); $x++){
		$sql_send="insert into outbound(destAddr,sourceAddr,messageType,messageContent,status) values('$destAddr[$x]',default,default,'$messageContent[$x]','send')";
		$result = mysql_query($sql_send);
		if(!$result){
			$dbErr = "SQL Error: ".mysql_error(). "SQL CODE: ".$sql_send;
			flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		}else{
			$subsLogs = "Successfully inserted alerts template for sending: ".$sql_newAlerts;
			logSubs($subsLogs, $_SERVER["SCRIPT_FILENAME"]);
		}
	}
	
	##update sent alerts in alerts tbl
	for($x=0; $x<(count($alertID)); $x++){
		$sql_update = "update alerts set messageStatus = 'sent' where alertID = '$alertID[$x]'";
		$result = mysql_query($sql_update);
		if(!$result){
			$dbErr = "SQL Error: ".mysql_error(). "SQL CODE: ".$sql_send;
			flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		}else{
			$subsLogs = "Successfully updated alerts template for sending: ".$sql_update;
			logSubs($subsLogs, $_SERVER["SCRIPT_FILENAME"]);
		}
	}
}



?>

<?php
/*
 * steps:- subscription
 * 		-explode string to parts..command+keyword
 * 		- if command is subscribe
 * 			-sub.then inform user
 * 		- if command is unsubs
 * 			-unsub then inform user	
 */ 

$texts = fetchSMS();
$requestID = array();
while($rows = mysql_fetch_array($texts)){
	$sms = $rows['messageContent'];
	$sourceAddr = $rows['MSISDN'];
	if(preg_match('/START/i', $sms)){
		//explode sms into parts
		$parts = explode(' ', $sms);
		$keyword = $parts[1]; 
		$command = 'START';
		$requestID = $rows['requestID'];
		if( checkSubscriptions($sourceAddr, $keyword) ){
			#subscribe new user to $keyword
			Newsubscriptions($command, $sourceAddr, $keyword);
			
		}else{
			$subLogs="Duplicate subscription. USER:$sourceAddr KEYWORD: $keyword ";
			logSubs($subLogs, $_SERVER["SCRIPT_FILENAME"]);
		}
		#subscription keyword
		
	}elseif( preg_match('/STOP/i', $sms) ){
		//explode sms into parts
		$parts = explode(' ', $sms);
		$keyword = $parts[1];
		$command = 'STOP';
		$requestID[] = $rows['requestID'];
		#unsubscription keyword
		delSubs($command, $sourceAddr, $keyword);
		subscriptionStatus($sourceAddr,$keyword,"unsubscribe");
	}
	//echo "Requests: ".print_r($requestID)."\n";
}

##update processed requests

updateSMS($requestID);

##generate currency alerts
/*
$currency = currencyAlerts();
for($x=0;$x<count($currency); $x++){
	if(genAlerts($currency) == FALSE)
		break;
}
*/
?>
