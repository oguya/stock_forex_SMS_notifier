<?php

function fetchText($msisdn){
	$connect=mysql_connect("localhost","root","");
	mysql_select_db("sentinel");
	$sql="select outboundID, destAddr, sourceAddr, messageContent,dateCreated from outbound where date(dateCreated )=curdate() and destAddr like '%$msisdn' order by 1 desc limit 1";
	$result=mysql_query($sql);
	if(!$result){
		echo "No text processed yet...<br>";
		exit;
	}else
		return $result;
}

$msisdn=$_REQUEST['sender'];
if(isset($msisdn)){
	#fetch latest text from db
	$result=fetchText($msisdn);
	while($row=mysql_fetch_array($result)){
		$destAddr=$row['destAddr'];
		$sourceAddr=$row['sourceAddr'];
		$messageContent=$row['messageContent'];
		$dateCreated=$row['dateCreated'];
	}
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width; initial-scale=1.0 user-scale=no" />
		<title>simulator::<?php echo $_REQUEST['sender'];?></title>
		<!--<link rel="stylesheet" href="style.css" />-->
		<style type="text/css">
			body{
				margin-left: 190px;
				margin-right: 250px;
				margin-top: 50px;
				background-image: url('utube-background.png');
				-webkit-border-radius: 10px;
				-moz-border-radius: 10px;
				border: 1px solid;
				padding-left: 20px;
			}
			#main{
				
			}
			label{
				width: 150px;
				float: left;
				padding-top: 10px;
			}
			#phone input[type="text"]{
				border-color: #96A6C5;
				border-width: 1px;
				height:30px;
				font-size: 15px;
				text-align: left;
			}
			textarea{
				border-color:#96A6C5;
				border-width: 1px;
				text-align: left;
			}
			button{
				height: 30px;
				font-family: monospace;
				width: 100px;
			}
			#buttons{
				margin-left: 100px;
			}
			h2{
				margin-left: 100px;
			}
		</style>
	</head>
	<body>
		<div id="main">
			<h2><em>SMS Simulator</em></h2>
			<div id="phone">
					<label>Sender Number:</label>
					<input type="text" placeholder="phone number" maxlength="13" title="System Phone Number" readonly value="<?php echo $sourceAddr;?>"/><br /><br />
					<label>Receiver Number:</label>
					<input type="text" placeholder="phone number" maxlength="13" title="Sender Phone Number" readonly value="<?php echo $destAddr;?>"/><br /><br />
					<label>Message: </label>
					<textarea rows="10" cols="30" readonly><?php echo $messageContent; ?></textarea><br /><br />
					<div id="buttons">
						<button type="submit" onclick="window.location='index.php'">New SMS</button>
						<button type="reset" style="margin-left: 50px;" onclick="history.go(-2);">Back</button>
					</div>
			</div>
			<br /><br />
		</div>
	</body>
</html>
