<?php
require_once 'db_connect.php';
#include_once 'global_functions.php';


##return a list of major forex currencies
function majorForex(){
	$forex = array('KES','USD','EURO','AUD','ZAR','GBP','JPY','NZD','CAD','CHF');
	return $forex;
}

##return a list of urls for fetching the forex rates
function rssURLS(){
	$url=array(
	'KES' =>  'http://themoneyconverter.com/rss-feed/KES/rss.xml',
	'USD' =>  'http://themoneyconverter.com/rss-feed/USD/rss.xml',
	'EURO' => 'http://themoneyconverter.com/rss-feed/EUR/rss.xml',
	'AUD' =>  'http://themoneyconverter.com/rss-feed/AUD/rss.xml',
	'ZAR' =>  'http://themoneyconverter.com/rss-feed/ZAR/rss.xml',
	'GBP' =>  'http://themoneyconverter.com/rss-feed/GBP/rss.xml',
	'JPY' =>  'http://themoneyconverter.com/rss-feed/JPY/rss.xml',
	'NZD' =>  'http://themoneyconverter.com/rss-feed/NZD/rss.xml',	
	'CAD' =>  'http://themoneyconverter.com/rss-feed/CAD/rss.xml',
	'CHF' =>  'http://themoneyconverter.com/rss-feed/CHF/rss.xml');
	
	return $url;
}

##create rss file for storage
function touchRSS($currency){
	$rss_file = $currency.".xml";
	if( file_exists($rss_file) ){
		echo $rss_file." already exists! Deleting it...\n";
		$_SESSION['appLogs'] .= $rss_file." already exists! Deleting it...\n";
		unlink($rss_file);
	}
	if( touch($rss_file) ){
		echo $rss_file." successfully created!\n";
		$_SESSION['appLogs'] .= $rss_file." successfully created!\n";
		return $rss_file;
	}
	else{
		$_SESSION['appLogs'] .= "Cant create rss file: ".$rss_file."\n";
		die("Cant create rss file: ".$rss_file."\n");
		return FALSE;
	}
}

###download the file
function dlRSS($rss_file,$url){
	if( file_exists($rss_file) ){
		echo $rss_file." exists...proceed!\n";
		$_SESSION['appLogs'] .= $rss_file." exists...proceed!\n";
		
		$cmd = "wget ".$url. " --output-document ".$rss_file;
		shell_exec($cmd);
		
		#check if file has grown
		if( filesize($rss_file)>0 ){
			echo "successful download!\n";
			$_SESSION['appLogs'] .= "successful download!\n";
			return TRUE;
		}
		else{
			echo $rss_file." file might be downloaded but saved under a different name!\n";
			$_SESSION['appLogs'] .= $rss_file." file might be downloaded but saved under a different name!\n";
			return FALSE;
		}
	}
}

##parse the file..include the parsers
function includeParsers($rss_file,$currency){
	$filename = $rss_file;
	$_SESSION['currency'] = $currency;
	require 'rss_parsers.php';
}

###rename the parsed files
function Renamer($rss_file){
	$timezone=date_default_timezone_get();
	date_default_timezone_set($timezone);
	$timestamp = time();
	
	$newname = $rss_file."_".date("Y-m-d_G:i:s",$timestamp);
	if( rename($rss_file, $newname) ){
		echo $rss_file."successfully renamed to ".$newname."\n";
		$_SESSION['appLogs'] .= $rss_file."successfully renamed to ".$newname."\n";
		return $newname;
	}
	else{
		echo $rss_file." FAILED to be renamed to ".$newname."\n";
		$_SESSION['appLogs'] .= $rss_file." FAILED to be renamed to ".$newname."\n";
		return FALSE;
	}
}

####move the parsed...renamed file to archive###
function Archive($rss_file){
	$storage="archives/".$rss_file;
	if( move_uploaded_file($rss_file,$storage) ){
		echo $rss_file." successfully moved to ".$storage."\n";
		$_SESSION['appLogs'] .= $rss_file." successfully moved to ".$storage."\n";
		return TRUE;
	}
	else{
		echo $rss_file." failed to move to ".$storage."\n";
		$_SESSION['appLogs'] .= $rss_file." failed to move to ".$storage."\n";
		return FALSE;
	}
}

#####copy the parsed..renamed file to archive dir.#####
function copyRSS($rss_file){
	$storage="/home/james/projo-test/archives/".$rss_file;
	if (copy($rss_file,$storage) ){
		unlink($rss_file);	
		echo $rss_file." successfuly copied to ".$storage."\n";
		$_SESSION['appLogs'] .= $rss_file." successfuly copied to ".$storage."\n";
		return TRUE;
	}
	else{
		echo $rss_file." failed copied to ".$storage."\n";
		$_SESSION['appLogs'] .= $rss_file." failed copied to ".$storage."\n";
		return FALSE;
	}
}
?>

<?php
/*
	STEPS
--create a loop...do:
	--touch the file
	--dl the rss file & save it to touch_d file
	--call the parsers
	--rename the file
	--move the renamed files to archived dir
*/

##init. logs
$logTime = date("Y-m-d G:i:s",time());
$startLog = $logTime."\nAbout to fetch,parse & record  1 forex bucket!\n\n";
logEvents($startLog, $_SERVER['PHP_SELF']);

$currency = array();
$currency = majorForex();
$url = rssURLS();

###loop according to no of currencies...
for($x=0; $x<count($currency); $x++){
	echo "Initiating Forex fetch app!!\n";
	
	$_SESSION['appLogs'] = "Initiating Forex fetch app!!\n";
	
	echo "Currency: ".$currency[$x]."\n";
	echo "URL: ".$url[$currency[$x]]."\n";
	
	$_SESSION['appLogs'] .= "Currency: ".$currency[$x]."\n";
	$_SESSION['appLogs'] .= "URL: ".$url[$currency[$x]]."\n";
	
	####touch the rss file###
	$rss_file = touchRSS($currency[$x]);
	$rss_url = $url[$currency[$x]];
	
	##dl the rss file
	if (!dlRSS($rss_file, $rss_url) ){
		echo "Failed to download ".$rss_file." file!\n";
		$_SESSION['appLogs'] .= "Failed to download ".$rss_file." file!\n";
	}
	
	###call parsers
	includeParsers($rss_file, $currency[$x]);
	
	
	###renamed the parsed file#######
	$rss_file = Renamer($rss_file);
	if( Archive($rss_file) ){
		echo "successful archive!\n";
		$_SESSION['appLogs'] .= "successful archive!\n";
	}
	else{
		echo "Failed Archive process...using copy function!\n";
		$_SESSION['appLogs'] .= "Failed Archive process...using copy function!\n";
		
		if( copyRSS($rss_file) ){
			echo "Successful copy archive process.\n";
			$_SESSION['appLogs'] .= "Successful copy archive process.\n";
		}
		else {
			echo "Failed copy archive process.\n";
			$_SESSION['appLogs'] .= "Failed copy archive process.\n";
		}
	}
	
	###log to file####
	
	$appLogs = $_SESSION['appLogs'];
	logEvents($appLogs, $_SERVER["PHP_SELF"]);
	
}

##close. logs
$logTime = date("Y-m-d G:i:s",time());
$endLog = "Just completed fetching,parsing & recording 1 forex bucket!\n\n";
logEvents($endLog, $_SERVER["PHP_SELF"]);

?>