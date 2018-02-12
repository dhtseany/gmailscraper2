<html>
<head>
    <title>Gmail Import Utility</title>
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>

<body>
<?php
// $rustart = getrusage();

require('functions.php');
require('settings.php');
require('dbconfig.php');


// if(!empty($_GET['status'])){
//     switch($_GET['status']){
//         case 'succ':
//             $procCount = $_GET['procCount'];
//             $insertCount = $_GET['insertCount'];
//             $failCount = $_GET['failCount'];
//             $dupCount = $_GET['dupCount'];
//             $statusMsgClass = 'alert-success';
//             $statusMsg = $procCount .' records processed, '. $insertCount .' records inserted, '. $failCount .' records failed, '. $dupCount .' duplicate records ignored.';
//             break;
//         case 'err':
//             $procCount = $_GET['procCount'];
//             $insertCount = $_GET['insertCount'];
//             $failCount = $_GET['failCount'];
//             $dupCount = $_GET['dupCount'];
//             $statusMsgClass = 'alert-danger';
//             $statusMsg = 'Undefined errors occured, however '. $procCount .' records processed, '. $insertCount .' records inserted, '. $failCount .' records failed, '. $dupCount .' duplicate records ignored.';
//             break;
//         default:
//             $statusMsgClass = '';
//             $statusMsg = '';
//     }
// }
echo "<div class='container'>";
// if(!empty($statusMsg)){
// 	echo "<div class='alert ". $statusMsgClass ."'>'". $statusMsg ."'</div>";
// }
echo "<div class='panel panel-default'>
	<div class='panel-heading'>
		Gmail IMAP Scaper Tool
		<a href=\"javascript:void(0);\" onclick=\"$('#importFrm').slideToggle();\">Collapse</a>
	</div>
	<div class='panel-body'>";

if(isset($_POST['importSubmit'])){
	// $logfile = 'log.txt';
	date_default_timezone_set('America/New_York');
	$date = date('m/d/Y h:i:s a', time());
	$logfile = fopen("log.txt", "a");
	$sqlDatabase = $_POST['databaseName'];
	$sqlTable = $_POST['tableName'];
	$max_emails = $_POST['emailLimit'];
	$db = new mysqli($sqlHost, $sqlUser, $sqlPwd, $sqlDatabase);
	$fillLine = "====================================================================";
	fwrite($logfile, "\n". $fillLine);
	$startMessage = "Scrape started at: ". $date;
	fwrite($logfile, "\n". $startMessage);
	if(mysqli_connect_errno()){
		echo "Error: Could not connect to database.";
		exit;
	}
	$procCount = 0;
	$insertCount = 0;
	$failCount = 0;
	$dupCount = 0;
	# Attempt connection to Gmail.
	$inbox = imap_open ( $hostname, $username, $password ) or die ( 'Cannot connect to Gmail: ' . imap_last_error () );
	if (!$inbox) {
		die ('Unable to connect via IMAP.');
	}	
	$emails = imap_search ( $inbox, $SearchString, SE_UID );
	// $emails = imap_search ( $inbox, $SearchString, FT_UID );
	if (!$emails) {
		die ('No messages were pulled.');
	}
	echo "<p><b>Logged into account: </b>" . $username . "<br />";
	echo "<b>Searching through email:</b>" . $SearchString . "</p>";

	if ($emails) {
		//rsort ( $emails );
		// $procCount = 0;
		echo "<p><b>Connected to MySQL successfully!</b><br />";
		echo "////////////////////////////////////////</p>";
		$messages = array();
		foreach($emails as $n) {
			// var_dump($emails);
			// $structure = imap_fetchstructure($inbox, $n);
			// var_dump($structure);
			$hinfo = imap_headerinfo($inbox, $n);
			if (isset($hinfo->subject)) {
				$subject = $hinfo->subject;
			}
			$fromData = $hinfo->from;
			$account = $fromData[0]->mailbox;
			$domain = $fromData[0]->host;
			if (isset($fromData[0]->personal)) {
				$fullName = $fromData[0]->personal;
			}
			$formattedAddress = $account . "@" . $domain;
			$datestamp = $hinfo->date;
			$unixtime = $hinfo->udate;
			$rawMessageID = $hinfo->message_id;
			$parsedMessageID = get_string_between($rawMessageID, '<', '>');
			$message = array($parsedMessageID, $formattedAddress, $fullName, $subject, $account, $domain, $datestamp, $unixtime);
			// echo "<hr /><br />";
			// echo "<p>";
			// echo "<b>Subject: </b>" . $subject . "<br />";
			// echo "<b>Account: </b>". $account . "<br />";
			// echo "<b>Domain: </b>". $domain . "<br />";
			// echo "<b>Full Name: </b>". $fullName . "<br />";
			// echo "<b>Formatted Address: </b> " . $formattedAddress . "<br />";
			// echo "<b>Timestamp: </b>" . $datestamp . "<br />";
			// echo "<b>Unix Time: </b>" . $unixtime . "<br />";
			// echo "<b>messageID: </b>" .$parsedMessageID . "<br />";
			// echo "</p>";
			// echo "<b>Result: </b><br />";
			// Does this message already exist in the database?
			
			$checkQuery = "SELECT * FROM " . $sqlTable . " WHERE message_id = '" . $parsedMessageID . "' LIMIT 1";
			// echo "Query is: " . $checkQuery;		
			if ($checkResult = $db->query($checkQuery)) {	
				// $insertCount = 0;
				// $failCount = 0;
				// $dupCount = 0;
				if ($checkResult->num_rows === 0) {
					// No results. Let's insert it then
					$subject = $db->real_escape_string($subject);
					$fullName = $db->real_escape_string($fullName);
					$insertQuery = "INSERT INTO ". $sqlTable ." (message_id, formattedAddress, fullName, subject, account, domain, datestamp, unixtime) VALUES ('". $parsedMessageID ."', '". $formattedAddress ."', '". $fullName ."', '". $subject ."', '". $account ."', '". $domain ."', '". $datestamp ."', '". $unixtime ."');";
					$insertResult = $db->query($insertQuery);
					if ($insertResult){
						// echo "<br />" . $db->affected_rows . " entry inserted into database.";
						$lineResult = "SUCCESS: " . $insertQuery;
						//file_put_contents($logfile, $lineResult);
						fwrite($logfile, "\n". $lineResult);
						$insertCount ++;
					} 
					else {						
						$lineResult = "FAIL: " . $insertQuery;
						// file_put_contents($logfile, $lineResult);
						fwrite($logfile, "\n". $lineResult);
						$failCount ++;
						// echo "<br />0 entry inserted into database (may be caused by duplicate or another error.).<br />";
						// echo "Error was: " . $db->error . "<br />";
						// echo "Query was: ". $insertQuery . "<br />";
					}
				} 
				else {
					$lineResult = "DUPLICATE: " . $checkQuery;
					// file_put_contents($logfile, $lineResult);
					fwrite($logfile, "\n". $lineResult);
					$dupCount ++;
					// echo "An entry was already found. Ignored.";
					// printf("%d Rows founds.\n", $db->affected_rows);
				}
			}
			imap_setflag_full($inbox,$n, "SEEN FLAGGED");
			// imap_setflag_full($inbox,$n, "\\Seen");
			// echo "<hr />";
			// $messages[] = $message; // I honestly can't remember why I was collecting all the processed message in an array. It probably breaks something. Lol.
			$procCount ++;
			if ($procCount ++ >= $max_emails)
			break;
		}
		echo "<p>Total emails processed: " . $procCount . "<br />";
		echo "Total emails inserted: " . $insertCount . "<br />";
		echo "Total duplicates occured: " . $dupCount . "<br />";
		echo "Total errors occured: " . $failCount . "</p>";
		// echo '<pre>'; print_r($messages); echo '</pre>';
	}

	/* close the connection */
	imap_close ( $inbox );
	// function rutime($ru, $rus, $index) {
	// 	return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
	// 	-  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
	// }

	// $ru = getrusage();
	// echo "This process used " . rutime($ru, $rustart, "utime") .
	// 	" ms for its computations <br/ >";
	// echo "It spent " . rutime($ru, $rustart, "stime") .
	// 	" ms in system calls<br />";

	// echo "<br />Stript has reached the end.<br />";
	// echo "Done.</p>";
	
	
	echo "<h1>Scraping complete. N count was ". $n .".</h1><br/>";
	echo "<a href='importStart.php'><h3>Start Over. </h3></a>
	</div>
    </div>
	</div>
	</body>
	</html>";
	// $qstring = '?status=succ&procCount='. $procCount .'&insertCount='. $insertCount .'&failCount='. $failCount .'&dupCount='. $dupCount;
	fclose($logfile);
} else {
	echo "
				<form action='importStart.php' method='post' enctype='multipart/form-data' id='importFrm'>
					<label>Would you like to begin scraping the inbox?</label><br />
					<i>Note: This will take quite some time. Just leave it running forever.</i></br>
					<label>Choose Database to Import to:</label>
                    <select name='databaseName'>
                        <option value='testdump1'>testdump1</option>
                        <option value='primaryData'>primaryData</option>
                    </select>
                <br />
                <label>Choose Table to Import to:</label>
                    <select name='tableName'>
                        <option value='supportmailbox'>supportmailbox</option>
                    </select>
				<br />
				<label>Number of emails to process:</label>
				<select name='emailLimit'>
					<option value='1'>1</option>
					<option value='10'>10</option>
					<option value='100'>100</option>
					<option value='1000'>1000</option>
					<option value='5000'>5000</option>
					<option value='10000'>10,000</option>
					<option value='20000'>20,000</option>
					<option value='50000'>50,000</option>
					<option value='60000'>60,000</option>
					<option value='75000'>75,000</option>
					<option value='80000'>80,000</option>
					<option value='90000'>90,000</option>
					<option value='100000'>100,000</option>
				</select>
			<br />
					<input type='submit' class='btn btn-primary' name='importSubmit' value='Begin'>
				</form>
			</div>
		</div>
	</div>
	</body>
	</html>";
}
?>