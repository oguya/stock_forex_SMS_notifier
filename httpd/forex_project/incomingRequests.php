<?php 
include_once 'db_connect.php';

/*
 * Purpose:- log2db the incoming sms,
 * Functionality:
 * 		-parse the text and drop fake sms.
 * 		-get the route for the sms processor
 *		-log the text 2 db
 * 		-call the necessary scripts to process it ie routes
 * 		-
 */

####log the incoming sms to db
function logSMS(){
	$msidsn = mysql_real_escape_string($_REQUEST['sender']);
	$destAddr = mysql_real_escape_string($_REQUEST['receiver']);
	$messageID = mysql_real_escape_string($_REQUEST['msgid']);
	$timeIN = mysql_real_escape_string($_REQUEST['recvtime']);
	$messageType = mysql_real_escape_string($_REQUEST['messagetype']);
	$messageContent = mysql_real_escape_string($_REQUEST['msgdata']);
	$operator = mysql_real_escape_string($_REQUEST['operator']);
	$callbackID = mysql_real_escape_string($_REQUEST['callbackID']);
	
	$timeIN = date("Y-m-d H:i:s",strtotime($timeIN));
	$processed = 5;
	$routeID = 1;
	if($operator == ''){
		$operator = 'default';
	}
	
	#http://127.0.0.1/sms/incoming.php?sender=$originator&receiver=$recipient&msgdata=$messagedata&recvtime=$receivedtime&msgid=$messageid
	/**sender=$originator
	receiver=$recipient
	&msgdata=$messagedata
	&recvtime=$receivedtime
	&msgid=$messageid
	**/
	$sql = " insert into 
	incomingRequests(MSISDN,destAddr,messageID,messageType,messageContent,processed,operator,routeID,receivedTime) 
	values('$msidsn','$destAddr','$messageID',default,'$messageContent',$processed,'$operator',$routeID,'$timeIN')";
	
	$results = mysql_query($sql);
	
	if(!$results){
		$dbErr = "SQL Error: ".mysql_error()."SQL CODE: ".$sql;
		flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
	}
	else{
		$newSMSlogs = "successfully inserted new sms to db ".$sql;
		incomingReqLogs($newSMSlogs, $_SERVER["SCRIPT_FILENAME"]);
	}
}

###acknowledge user about received text...http autoresponse
function acknowledge($operator,$sourceAddr,$destAddr,$messageContent){
	#{MESSAGE TYPE}{OPERATOR}{SOURCEADDR}{DESTADDR}{MESSAGECONTENT}
	#{SMS:TEXT}{Vodafone}{+447778888888}{+447779999910}{Hello to you too}
	$response="{SMS:TEXT}"."{".$operator."}{".$sourceAddr."}{".$destAddr."}{".$messageContent."}";
	echo $response;
	incomingReqLogs($response,$_SERVER["SCRIPT_FILENAME"]);
}

?>

<?php

if(isset($_REQUEST['sender']) && isset($_REQUEST['recvtime']) && isset($_REQUEST['msgdata'])){
	
	$newSMSlogs = "About to process sms from ".$_REQUEST['sender'];
	incomingReqLogs($newSMSlogs, $_SERVER["SCRIPT_FILENAME"]);
	
	#log sms to db
	logSMS();
	$destAddr = mysql_real_escape_string($_REQUEST['sender']);
	$sourceAddr = mysql_real_escape_string($_REQUEST['receiver']);
	$messageContent = "Thank you for sending....";
	$operator = mysql_real_escape_string($_REQUEST['operator']);

	##acknowledge user about the received the sms
	acknowledge($operator, $sourceAddr, $destAddr, $messageContent);
	
	##start processing the text...
	
	###call dl
	$command="bash /var/www/html/project/prjkt_cron.sh";
	$output=shell_exec($command);
	die("Output is: ".$output);
}

?>
























