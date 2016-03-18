<?php

include '../util.php';
include '../common.php';

$id_category = !empty($_POST['id_category']) ? $_POST['id_category'] : 0;

$thelist = '';
$cats = array();
$query = "SELECT id_accessory, accessory_name FROM accessories WHERE id_category='$id_category' ORDER by order_no ASC ";
$rs = mysql_query($query);
if ($rs)
	while($rec = mysql_fetch_row($rs))
	$cats[]	= "$rec[0]~$rec[1]";
if(count($cats)>0)
	$thelist = implode('|', $cats);
echo $thelist;
