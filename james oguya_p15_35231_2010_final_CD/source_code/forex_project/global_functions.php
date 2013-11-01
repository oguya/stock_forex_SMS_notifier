<?php
date_default_timezone_set('Africa/Nairobi');

####log db errors to file####
function flog($content,$source_file){
	$timezone=date_default_timezone_get();
	date_default_timezone_set($timezone);
	$filename="/home/james/projo-test/logs/db_error.log";
	
	$fp=fopen($filename,"a") or die("Cant open $filename for logging!\n");
	$timer=date("Y-m-d G:i:s",time());
	
	$startlog = $timer ." - Logging Started.\n";
	fwrite($fp,$startlog);
	
	$stringlog = $timer ." - ".$source_file." - ". $content."\n";
	fwrite($fp,$stringlog);
	
	$endlog = $timer ." - Logging stopped.\n";
	fwrite($fp,$endlog);
	
	fclose($fp);
}

###log parsers errors & events to file###
function logParsers($content,$source_file){
	$timezone=date_default_timezone_get();
	date_default_timezone_set($timezone);
	$filename="/home/james/projo-test/logs/parsers_errors.log";
	
	$fp=fopen($filename,"a") or die("Cant open $filename for logging!\n");
	$timer=date("Y-m-d G:i:s",time());
	
	$stringlog = $timer ." - ".$source_file." - ". $content."\n";
	fwrite($fp, $stringlog);
	
	fclose($fp);
}

####log general events to log file ####
function logEvents($content,$source_file){
	$timezone=date_default_timezone_get();
	date_default_timezone_set($timezone);
	$filename="/home/james/projo-test/logs/forex_app.log";
	
	$fp = fopen($filename,"a") or die("Cant open $filename for logging!\n");
	$timer=date("Y-m-d G:i:s",time());
	
	$appLog=$timer." - ".$source_file." - ".$content."\n";
	fwrite($fp, $appLog);
	
	fclose($fp);
}

####log incomingReqs. events to log file ####
function incomingReqLogs($content,$source_file){
	$timezone=date_default_timezone_get();
	date_default_timezone_set($timezone);
	$filename="/home/james/projo-test/logs/incomingRequests.log";
	
	$fp = fopen($filename,"a") or die("Cant open $filename for logging!\n");
	$timer=date("Y-m-d G:i:s",time());
	
	$appLog=$timer." - ".$source_file." - ".$content."\n";
	fwrite($fp, $appLog);
	
	fclose($fp);
}

####log general events to log file ####
function logSubs($content,$source_file){
	$timezone=date_default_timezone_get();
	date_default_timezone_set($timezone);
	$filename="/home/james/projo-test/logs/subscriptions.log";
	
	$fp = fopen($filename,"a") or die("Cant open $filename for logging!\n");
	$timer=date("Y-m-d G:i:s",time());
	
	$appLog=$timer." - ".$source_file." - ".$content."\n";
	fwrite($fp, $appLog);
	
	fclose($fp);
}

####log alerts events to log file ####
function logAlerts($content,$source_file){
	$timezone=date_default_timezone_get();
	date_default_timezone_set($timezone);
	$filename="/home/james/projo-test/logs/alerts.log";
	
	$fp = fopen($filename,"a") or die("Cant open $filename for logging!\n");
	$timer=date("Y-m-d G:i:s",time());
	
	$appLog=$timer." - ".$source_file." - ".$content."\n";
	fwrite($fp, $appLog);
	
	fclose($fp);
}

####log forex info events to log file ####
function infoLogs($content,$source_file){
	$timezone=date_default_timezone_get();
	date_default_timezone_set($timezone);
	$filename="/home/james/projo-test/logs/forex_info.log";
	
	$fp = fopen($filename,"a") or die("Cant open $filename for logging!\n");
	$timer=date("Y-m-d G:i:s",time());
	
	$appLog=$timer." - ".$source_file." - ".$content."\n";
	fwrite($fp, $appLog);
	
	fclose($fp);
}

####log stock ops events to log file ####
function stockLogs($content,$source_file){
	$timezone=date_default_timezone_get();
	date_default_timezone_set($timezone);
	$filename="/home/james/projo-test/logs/stocks.log";
	
	$fp = fopen($filename,"a") or die("Cant open $filename for logging!\n");
	$timer=date("Y-m-d G:i:s",time());
	
	$appLog=$timer." - ".$source_file." - ".$content."\n";
	fwrite($fp, $appLog);
	
	fclose($fp);
}

###log stock info events to log file
function stockInfo($content,$source_file){
	$timezone=date_default_timezone_get();
	date_default_timezone_set($timezone);
	$filename="/home/james/projo-test/logs/stocks_info.log";
	
	$fp = fopen($filename,"a") or die("Cant open $filename for logging!\n");
	$timer=date("Y-m-d G:i:s",time());
	
	$appLog=$timer." - ".$source_file." - ".$content."\n";
	fwrite($fp, $appLog);
	
	fclose($fp);
}

###log stock info events to log file
function stockSubs($content,$source_file){
	$timezone=date_default_timezone_get();
	date_default_timezone_set($timezone);
	$filename="/home/james/projo-test/logs/stocks_subs.log";
	
	$fp = fopen($filename,"a") or die("Cant open $filename for logging!\n");
	$timer=date("Y-m-d G:i:s",time());
	
	$appLog=$timer." - ".$source_file." - ".$content."\n";
	fwrite($fp, $appLog);
	
	fclose($fp);
}


###log stock alerts
function stockAlerts($content,$source_file){
	$timezone=date_default_timezone_get();
	date_default_timezone_set($timezone);
	$filename="/home/james/projo-test/logs/stocks_alerts.log";
	
	$fp = fopen($filename,"a") or die("Cant open $filename for logging!\n");
	$timer=date("Y-m-d G:i:s",time());
	
	$appLog=$timer." - ".$source_file." - ".$content."\n";
	fwrite($fp, $appLog);
	
	fclose($fp);
}

?>