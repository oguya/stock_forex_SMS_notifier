<?php

###logging to file
include 'global_functions.php';

###connects to db
$host="localhost";
$username="root";
$passwd="";
$db="sentinel";

$connect=mysql_pconnect($host,$username,$passwd);

if(!$connect){
	$logs="DB error: ".mysql_error()."\n";
	flog($logs,$_SERVER["SCRIPT_FILENAME"]); //log to file
	die("DB error: ".mysql_error()."\n");
}

if(!mysql_select_db($db)){
	$logs="DB error: ".mysql_error()."\n";
	flog($logs,$_SERVER["SCRIPT_FILENAME"]); //log to file
	die("DB error: ".mysql_error()."\n");
}
?>