<?php
include_once 'db_connect.php';

/*
 * generates forex alerts for each of the registered currencies
 *  
 */
 
###return a list of currencies for alerts
//func..fetch currencyname,currencyid from db ret. currencyname
function currencyAlerts(){
	$currencies = array();
	$sql="select serviceName,services.currencyID,currencies.fullName,currencies.abbreviation from services inner join currencies on currencies.currencyID=services.currencyID";
	$result=mysql_query($sql);
	if(!$result){
		$dbErr="SQL Error: ".mysql_error()." SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$alertsLogs = "Successfully fetched registered currency alerts: ".$sql;
		logAlerts($alertsLogs, $_SERVER["SCRIPT_FILENAME"]);
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
		$alertsLogs = "Successfully fetched market rates for $currency: ".$sql_alerts;
		logAlerts($alertsLogs, $_SERVER["SCRIPT_FILENAME"]);
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
		$alertsLogs = "Successfully created a new alert template: ".$sql_newAlerts;
		logAlerts($alertsLogs, $_SERVER["SCRIPT_FILENAME"]);
	}
}


##send the generated alerts..move the template*subs. to outbound
##func:- stake unsent alert from alerts tbl and insert it into outbound
function sendAlerts(){
	#$sql="select  alertID,alerts.channelID,msisdn,messageContent,messageStatus from alerts inner join subscriptions on subscriptions.channelID = alerts.channelID and messageStatus= 'send'";
	$sql="select alertID,msisdn,active,sourceAddr,messageContent,dateCreated from subscriptions inner join alerts on  alerts.channelID = subscriptions.channelID where date(dateCreated) = curdate() and active=1 and messageStatus = 'send' order by dateCreated asc";
	$result = mysql_query($sql);
	//logging
	if(!$result){
		$dbErr = "SQL Error: ".mysql_error()."SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}
	$destAddr = array();
	$messageContent = array();
	$alertID = array();
	
	while($rows = mysql_fetch_array($result)){
		$destAddr[] = $rows['msisdn'];
		$messageContent[] = $rows['messageContent'];
		$alertID[] = $rows['alertID'];
	}
	
	#insert into outbound
	$alertsLogs="\nAbout to perform bulk insert into outbound... ".count($alertID)." Records";
	logAlerts($alertsLogs, $_SERVER["SCRIPT_FILENAME"]);
	
	for($x=0;$x<(count($alertID)); $x++){
		$sql_send="insert into outbound(destAddr,sourceAddr,messageType,messageContent,status) values('$destAddr[$x]',default,default,'$messageContent[$x]','send')";
		$result = mysql_query($sql_send);
		if(!$result){
			$dbErr = "SQL Error: ".mysql_error(). "SQL CODE: ".$sql_send;
			flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		}else{
			$alertsLogs = "Successfully inserted alerts template for sending: ".$sql_send;
			logAlerts($alertsLogs, $_SERVER["SCRIPT_FILENAME"]);
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
			$alertsLogs = "Successfully updated alerts template for sending: ".$sql_update;
			logAlerts($alertsLogs, $_SERVER["SCRIPT_FILENAME"]);
		}
	}
}

?>

<?php
#select alertID,alerts.channelID,sourceAddr,subscriptions.msisdn,messageContent,messageStatus,dateCreated from alerts inner join subscriptions on subscriptions.channelID = alerts.channelID where date(dateCreated) = curdate() ;
##generate currency alerts
$currency = currencyAlerts();

for($x=0;$x<count($currency); $x++){

	genAlerts($currency);
	
	sendAlerts();
}

?>