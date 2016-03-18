<?php
require 'common.php';
$crlf = "\r\n";

mysql_query('start transaction');
echo 'start: ' .mysql_error().$crlf;
mysql_query("insert into configuration value('test','test','test')");
echo 'insert: ' .mysql_affected_rows().$crlf;
$rs = mysql_query("select * from configuration where section='test'");
echo 'select 1:'.mysql_numrows().$crlf;

mysql_query('commit');
echo 'roll: ' .mysql_error().$crlf;
$rs = mysql_query("select * from configuration where section='test'");
echo 'select 2:'.mysql_numrows().$crlf;
?>