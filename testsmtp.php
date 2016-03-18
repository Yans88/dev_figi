<?php 
ini_set('display_errors', 1);

include 'common.php';

$from = 'admin@hci.edu.sg';
$to = 'safarin@chesterfield.sg';
$sub = 'test email';
$msg = 'sending message/email from hwa chong smtp server';
$cc = 'elbas@chesterfield.sg';

echo "from: $from<br>to: $to<br>cc: $cc<br>subject: $sub<br>message: $msg<br>";
smtp_SendEmail($from, $to, $sub, $msg, $cc);

?>
