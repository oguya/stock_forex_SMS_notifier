<?php
function sendData($url){
	$timeout=10;
	
	$ch=curl_init($url);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	$page=curl_exec($ch);
	curl_close($ch);
	return $page;
}

?>

<!--http://127.0.0.1/sms/incoming.php?sender=$originator&receiver=$recipient&msgdata=$messagedata&recvtime=$receivedtime&msgid=$messageid-->

<?php

$msidsn = mysql_real_escape_string($_REQUEST['sender']);
$destAddr = mysql_real_escape_string($_REQUEST['receiver']);
$messageID = mysql_real_escape_string($_REQUEST['msgid']);
$timeIN = mysql_real_escape_string($_REQUEST['recvtime']);
$messageType = mysql_real_escape_string($_REQUEST['messagetype']);
$messageContent = mysql_real_escape_string($_REQUEST['msgdata']);
$operator = mysql_real_escape_string($_REQUEST['operator']);
$callbackID = mysql_real_escape_string($_REQUEST['callbackID']);

$url="http://127.0.0.1/forex_project/incomingRequests.php?sender=".urlencode($msidsn)."&receiver=".urlencode($destAddr)."&msgdata=".urlencode($messageContent)."&recvtime=".urlencode($timeIN)."&msgid=".urlencode($messageID)."&operator=".urlencode($operator);

$page=sendData($url);
echo "2e70e2147e5694606eeb6d99a2001e88 ".md5($page)."\n";
if(strlen($page)>0){
	$command="bash /home/james/workspace/forex_project/prjkt_cron.sh";
	exec($command);
	$web="viewText.php?sender=".urlencode($msidsn);
	header("Refresh:3;url=$web");
	exit;
}
?>