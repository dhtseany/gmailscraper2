<?php
set_time_limit ( 60 );

// Email login credentials
$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'user@domain.org'; 			// e.g somebody@gmail.com
$password = 'password';

// Email search parameters
//$SearchString = 'ALL';	
$SearchString = 'UNSEEN UNFLAGGED';	

//$SearchString = 'FROM "do_not_reply@sensopia.com"';							// Filters the searched email. i.e. " 'UNSEEN FROM "do_not_reply@sensopia.com"' "
// $max_emails = 10000;								// Searching 'ALL' can be expensive. Limit the number of emails to check for attachments.

?>
