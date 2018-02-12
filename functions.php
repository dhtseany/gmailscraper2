<?php

function importTosupportmailbox($csvFile,$sqlTable,$db)
{
	$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
    if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'],$csvMimes)){
        // if(is_uploaded_file($_FILES['file']['tmp_name'])){
        //     //open uploaded csv file with read only mode
        //     $csvFile = fopen($_FILES['file']['tmp_name'], 'r');
            
            //skip first line
            //fgetcsv($csvFile);
            
            //parse data from csv file line by line
            $procCount = 0;
            $dupCount = 0;
            $insertCount = 0;
            $failCount = 0;
            while(($line = fgetcsv($csvFile)) !== FALSE){
                // $_SESSION['mysqlErrors'] 
                //Fetch Message_Id from CSV
                $message_Id = get_string_between($line[3], '<', '>');
                //Fetch initial, full email address from CSV data
                $formattedAddress = get_string_between($line[1], '<', '>');
                //Fetch the fullName
                $fullName = $line[1];
                $fullName = explode("<", $fullName);
                $fullName = trim($fullName[0]);
                // echo "<b>fullName: </b>". $fullName ."<br />";
                //Fetch the subject
                $subject = $line[0];
                //Parse just the username from $emailAddress
                $account = explode("@",$formattedAddress);
                array_pop($account);
                $account = implode("@",$account);
                //Parse just the domain from $emailAddress
                $domain = strstr($formattedAddress, '@');
                $domain = ltrim($domain, '@');
                //Fetch base Timestap
                $datestamp = $line[2];
                //Convert $datestamp to UnixTime
                $unixtime = strtotime($datestamp);
                
                // Begin processing the individual message
                $checkQuery = "SELECT * FROM " . $sqlTable . " WHERE message_id = '" . $message_Id . "' LIMIT 1";
                
                if ($checkResult = $db->query($checkQuery) or die(mysqli_error($db)))  {
                    if ($checkResult->num_rows === 0) {
                        // No results. Let's insert it then

                        $subject = $db->real_escape_string($subject);
                        $fullName = $db->real_escape_string($fullName);
                        $insertQuery = "INSERT INTO ". $sqlTable ." (message_id, formattedAddress, fullName, subject, account, domain, datestamp, unixtime) VALUES ('". $message_Id ."', '". $formattedAddress ."', '". $fullName ."', '". $subject ."', '". $account ."', '". $domain ."', '". $datestamp ."', '". $unixtime ."');";
                        $insertResult = $db->query($insertQuery) or die(mysqli_error($db));
                        // echo "Query was ". $insertQuery;;
                        if ($insertResult) {
                            $insertCount ++;
                        } 
                        else {
                            $mysqlErrors[] = $insertQuery;
                            // echo "Failing Query was: ". $insertQuery ."<br />";
                            // echo "<hr />";
                            $failCount ++;
                        }            
                    } 
                    else {
                        $dupCount ++;
                    }                    
                }
                $procCount ++;
            }
            
            //close opened csv file
            fclose($csvFile);
            $_SESSION['mysqlErrors'] = $mysqlErrors;

            $qstring = '?status=succ&procCount='. $procCount .'&insertCount='. $insertCount .'&failCount='. $failCount .'&dupCount='. $dupCount;
        }else{
            $qstring = '?status=err&procCount='. $procCount .'&insertCount='. $insertCount .'&failCount='. $failCount .'&dupCount='. $dupCount;
        }
		header("Location: csv_import.php".$qstring);
}

function importTophoneSystem($csvFile,$sqlTable,$db)
{
	$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
    if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'],$csvMimes)){
            
            //skip first line
            fgetcsv($csvFile);
            
            //parse data from csv file line by line
            $procCount = 0;
            $dupCount = 0;
            $insertCount = 0;
            $failCount = 0;
            while(($line = fgetcsv($csvFile)) !== FALSE){              
				$datestamp = $line[0];
				$unixtime = strtotime($datestamp);
				$callDuration = $line[1];
				$callDirection = $line[2];
				$callFromNumber = $line[3]; // This line will still needed parsed to only the first X characters
				// $callFromNumber = explode('"',$callFromNumber);
				// array_pop($callFromNumber);
				$callFromNumber = substr($callFromNumber, 0, 14);
				$callTo = $line[4];
				$answeringExtension = $line[6];
                
                // Begin processing the individual lines
                $checkQuery = "SELECT * FROM " . $sqlTable . " WHERE unixtime = '" . $unixtime . "' LIMIT 1";
                
                if ($checkResult = $db->query($checkQuery) or die(mysqli_error($db)))  {
                    if ($checkResult->num_rows === 0) {
                        // No results. Let's insert it then

						// First we sanitize the data
						$callFromNumber = $db->real_escape_string($callFromNumber);
						$callTo = $db->real_escape_string($callTo);
						$datestamp = $db->real_escape_string($datestamp);
						$unixtime = $db->real_escape_string($unixtime);
						$callDuration = $db->real_escape_string($callDuration);
						$callDirection = $db->real_escape_string($callDirection);
						$answeringExtension = $db->real_escape_string($answeringExtension);

						// Then we actually insert it.
                        $insertQuery = "INSERT INTO ". $sqlTable ." (callFromNumber, callTo, datestamp, unixtime, callDuration, callDirection, answeringExtension) VALUES ('". $callFromNumber ."', '". $callTo ."', '". $datestamp ."', '". $unixtime ."', '". $callDuration ."', '". $callDirection ."', '". $answeringExtension ."');";
                        $insertResult = $db->query($insertQuery) or die(mysqli_error($db));
                        // echo "Query was ". $insertQuery;;
                        if ($insertResult) {
                            $insertCount ++;
                        } 
                        else {
                            $mysqlErrors[] = $insertQuery;
                            // echo "Failing Query was: ". $insertQuery ."<br />";
                            // echo "<hr />";
                            $failCount ++;
                        }            
                    } 
                    else {
                        $dupCount ++;
                    }                    
                }
                $procCount ++;
            }
            
            //close opened csv file
            fclose($csvFile);
            $_SESSION['mysqlErrors'] = $mysqlErrors;

            $qstring = '?status=succ&procCount='. $procCount .'&insertCount='. $insertCount .'&failCount='. $failCount .'&dupCount='. $dupCount;
        }else{
            $qstring = '?status=err&procCount='. $procCount .'&insertCount='. $insertCount .'&failCount='. $failCount .'&dupCount='. $dupCount;
        }
		header("Location: csv_import.php".$qstring);
}

function singleSpace($s)
{
	return preg_replace ( '/\s+/', ' ', $s );
}

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

?>
