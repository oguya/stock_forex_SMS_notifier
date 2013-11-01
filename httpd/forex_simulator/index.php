<?php
date_default_timezone_set('Africa/Nairobi');
$time=date('Y-m-d_G:i:s',time());

?>
<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width; initial-scale=1.0 user-scale=no" />
		<title>Web simulator</title>
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
		<!--http://127.0.0.1/sms/incoming.php?sender=$originator&receiver=$recipient&msgdata=$messagedata&recvtime=$receivedtime&msgid=$messageid-->
		<div id="main">
			<h2><em>SMS Simulator</em></h2>
			<div id="phone">
				<form action="curl_ops.php" method="get">
					<label>Phone Number:</label>
					<input type="text" class="inputtext" required placeholder="phone number" maxlength="13" title="Enter phone number" name="sender" /><br /><br />
					<label>Message: </label>
					
					<textarea rows="10" cols="30" name="msgdata"></textarea><br /><br />
					<!-- hidden attribs to be sent to request processor -->
					<input type="hidden" name="receiver" value="+254714044696" />
					<input type="hidden" name="msgid" value="0d40a78c-20b9-471f-bbee-ac1e1557c126" />
					<input type="hidden" name="operator" value="Simulator" />
					<input type="hidden" name="recvtime" value="<?php echo $time;?>" />
					<div id="buttons">
						<button type="submit">Send SMS</button>
						<button type="reset" style="margin-left: 50px;">Cancel</button>
					</div>
				</form>
			</div>
			<br /><br />
		</div>
	</body>
</html>
