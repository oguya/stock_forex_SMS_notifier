<?php
include_once 'db_connect.php';
/*
 * generate alerts
 * insert alerts to stock_alerts tbl
 * send alerts...insert to outbound. 
 */

##func to gen. the alerts
function generateAlerts(){
	$sql="select hi,low,turnOver,companyName,Alias, liveData.dateCreated from liveData inner join companies on companies.companyID=liveData.companyID where date(liveData.dateCreated)=curdate() and 
	 Alias in ('EVRD','EQTY','EGAD','EABL','DTK','COOP','CMC','SCOM','CITY','CFCI','CFC','CARB','CABL','C&G','BRIT','BOC','BERG','BBK','BAUM','BAT','BAMB','ARM','ACCS') order by ldataID desc limit 22";
	$result=mysql_query($sql);
	if(!$result){
		$dbErr=$dbErr = "SQL Error: ".mysql_error(). "SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$alertsLogs="Successfully fetched stock data: ".$sql;
		stockAlerts($alertsLogs, $_SERVER["SCRIPT_FILENAME"]);
		return $result;
	}
}

###func to send the alerts
function sendAlerts($result){
	$alert=NULL;
	while($row=mysql_fetch_array($result)){
		$hi=$row['hi'];
		$lo=$row['low'];
		$turnOver=$row['turnOver'];
		$companyName=$row['companyName'];
		$alias=$row['Alias'];
		
		$alert .= " $alias HI: ".$hi." LO: ".$lo." TurnOver: ".$turnOver;
	}

	#if(isset($alert)){
		$sql="insert into stock_alerts(channelID,sourceAddr,messageContent,messageStatus) values(1,default,'$alert',default)";
		$result=mysql_query($sql);
		if(!$result){
			$dbErr="SQL ERROR: ".mysql_error()." SQL CODE: ".$sql;
			flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		}else{
			$alertLogs="Successfully created a new alert template: ".$sql;
			stockAlerts($alertLogs, $_SERVER["SCRIPT_FILENAME"]);
		}
		
		//send texts to subscribers
		$sql="select channelID,MSISDN,active from stock_subs where active =1";
		$result=mysql_query($sql);
		if(!$result){
			$dbErr="SQL ERROR: ".mysql_error()." SQL CODE: ".$sql;
			flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		}else{
			while($row=mysql_fetch_array($result)){
				$destAddr=$row['MSISDN'];
				
				$sql_send="insert into outbound(destAddr,sourceAddr,messageType,messageContent,status) values('$destAddr',default,default,'$alert','send')";
				$result_send=mysql_query($sql_send);
				
				if(!$result_send){
					$dbErr="SQL ERROR: ".mysql_error()." SQL CODE: ".$sql;
					flog($alertLogs, $_SERVER["SCRIPT_FILENAME"]);
				}else{
					$alertLogs="Successfully created a new message to be sent. ".$sql_send;
					stockAlerts($alertLogs, $_SERVER["SCRIPT_FILENAME"]);
				}
			}
		}
	#}
}

?>

<?php
#$result=generateAlerts() ;
#sendAlerts($result);
?>

<?php 
################################### NEW STOCK GENERATE ALERTS V2 ###########################

##fetch rates from db
function getPrices($company){
	$company=strtoupper($company);
	$sql="select hi,low,turnOver,companyName,Alias, liveData.dateCreated from liveData inner join companies on companies.companyID=liveData.companyID where date(liveData.dateCreated)=curdate() and   Alias='$company' order by dateCreated desc limit 1";
	$result=mysql_query($sql);
	if(!$result){
		$dbErr=$dbErr = "SQL Error: ".mysql_error(). "SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$alertsLogs="Successfully fetched stock data: ".$sql;
		stockAlerts($alertsLogs, $_SERVER["SCRIPT_FILENAME"]);
		return $result;
	}
}


##fetch all subscribers
function getSubscribers(){
	$sql="select channelID, MSISDN , company, active from stock_subs where active=1";
	$result=mysql_query($sql);
	if(!$result){
		$dbErr=$dbErr = "SQL Error: ".mysql_error(). "SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$alertsLogs="Successfully fetched stock data: ".$sql;
		stockAlerts($alertsLogs, $_SERVER["SCRIPT_FILENAME"]);
		return $result;
	}
}


##send the alerts to user..insert to outbound
function sendStockPrices($destAddr,$message){
	$sql="insert into outbound(destAddr,sourceAddr,messageType,messageContent,status) values('$destAddr',default,default,'$message','send')";
	$result=mysql_query($sql);
	if(!$result){
		$dbErr=$dbErr = "SQL Error: ".mysql_error(). "SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}else{
		$alertsLogs="Successfully fetched stock data: ".$sql;
		stockAlerts($alertsLogs, $_SERVER["SCRIPT_FILENAME"]);
		return $result;
	}
}


?>

<?php
/*
 * algorithm
 * 	fetch subscribers
 * 	get prices for the company that subscriber requested
 * 	send the message
 */ 

$subscribers=getSubscribers();
while($rows=mysql_fetch_array($subscribers)){
	$destAddr=$rows['MSISDN'];
	$company=$rows['company'];
	
	//get stock prices
	$stockPrices=getPrices($company);
	while( $rowsPrices=mysql_fetch_array($stockPrices) ){
		$companyName=$rowsPrices['companyName'];
		$hi=$rowsPrices['hi'];
		$lo=$rowsPrices['low'];
		$turnover=$rowsPrices['turnOver'];
		$timestamp=$rowsPrices['dateCreated'];
		
		$message="Company: ".$companyName." HI: ".$hi." LOW: ".$lo." TURNOVER: ".$turnover." TIMESTAMP: ".$timestamp;
		
		//send stock prices
		sendStockPrices($destAddr, $message);
	}
}
?>
















