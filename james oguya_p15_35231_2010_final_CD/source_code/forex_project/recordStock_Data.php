<?php
include_once 'db_connect.php';
/*
 * read from db
 * copy to tempData
 * update the status of transfered to old 
 */ 


###function to read from db
function fetchData(){
	$sql="select tempID, company, hiData, loData, turnOver,status from tempData where status='new'";
	$result=mysql_query($sql);
	
	if(!$result){
		$dbError="SQL Error: ".mysql_error()."SQL CODE: ".$sql;
		flog($sql, $_SERVER["SCRIPT_FILENAME"]);
		return FALSE;
	}else{
		$logs="Just fetched ".mysql_num_rows($result)." stocks for transfer!";
		stockLogs($logs, $_SERVER["SCRIPT_FILENAME"]);
		return $result;
	}
}
 

###func to copy the data from tempData tbl to liveData tbl
function copyData($result){
	$sql=array();
	$tempID=array();
	while($rows=mysql_fetch_array($result)){
		#$rows[''] insert into liveData(companyID,hi,low,turnOver) values();
		$alias=$rows['company'];
		$hi=$rows['hiData'];
		$lo=$rows['loData'];
		$turnOver=$rows['turnOver'];
		$tempID=$rows['tempID'];
		
		if(fetchcID($alias)){
			$companyID = fetchcID($alias);
		}

		if(isset($companyID)){
			$sql[]="insert into liveData(companyID,hi,low,turnOver) values($companyID,$hi,$lo,'$turnOver')";
		}
	}
	
	$logs="About to perform bulk insert to liveData... ".count($sql)." records!";
	stockLogs($logs, $_SERVER["SCRIPT_FILENAME"]);
	
	//copy the data to liveData
	for($x=0;$x<(count($sql));$x++){
		$result=mysql_query($sql[$x]);
		if(!$result){
			$dbErr="SQL ERROR: ".mysql_error()." SQL CODE: ".$sql[$x];
			flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		}else{
			$logs="Sucessful Copy: ".$sql[$x];
			stockLogs($logs, $_SERVER["SCRIPT_FILENAME"]);
		}
	}
}


###func. to fetch companyID
function fetchcID($alias){
	$alias=strtoupper($alias);
	
	$sql="select companyID,sectorID,companyName, Alias from companies where Alias like '%$alias%' limit 1";
	$result = mysql_query($sql);
	#echo "fetchID: ".$sql."\n";
	if(!$result){
		$dbError="SQL ERROR: ".mysql_error()."SQL CODE: ".$sql;
		flog($dbError, $_SERVER["SCRIPT_FILENAME"]);
		return FALSE;
	}else{
		$logs="Just fetched companyID for Alias: ".$alias;
		stockLogs($logs, $_SERVER["SCRIPT_FILENAME"]);
		while($rows=mysql_fetch_array($result)){
			$companyID=$rows['companyID'];
		}
		if(isset($companyID)){
			return $companyID;
		}else{
			return FALSE;
		}
	}
}

###func to update the status of copied data
function updateData($tempID){
	$status = "old";
	$logs="About to update ".count($tempID)." requests!";
	stockLogs($logs, $_SERVER["SCRIPT_FILENAME"]);
	
	for($x=0;$x<(count($tempID));$x++){
		$sql="update tempData set status ='old' where tempID=$tempID[$x]";
		$result=mysql_query($sql);
		if(!$result){
			$dbErr="DB ERROR: ".mysql_error()."SQL CODE: ".$sql;
			flog($dbErr, $_SERVER["SCRIPT_FILENAME"]);
		}else{
			$logs="Successful Update: ".$sql;
			stockLogs($logs, $_SERVER["SCRIPT_FILENAME"]);
		}
	}
}
?>


<?php
/*
 * Steps:- fetchData
 * 	-copyData
 * 	-update data
 */

$tempID=array();

if(fetchData()){
	$result=fetchData();
	
	$result2=$result;
	while ($rows=mysql_fetch_array($result2)){
		$tempID[]=$rows['tempID'];
	}
	copyData(fetchData());
	#var_dump($tempID);
	updateData($tempID);
}
?>