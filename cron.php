<?php
header("Content-Type: text/plain; charset=utf-8'");
// Lets start all the support services
require_once('./start.php');
$message = "";
if(date('d') == 23) $message .= $bokn->send_calendar(date('m',mktime()+240*3600),date('Y',mktime()+240*3600));
$message .= $bokn->send_reminders(31);
$message .= $bokn->send_reminders(21);
$message .= $bokn->send_reminders(7);
echo $message;
?>