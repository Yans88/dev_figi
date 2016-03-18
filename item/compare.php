<?php
if (!defined('FIGIPASS')) exit;
require 'item/comparison_util.php';

if ($_act == null) $_act = 'list';
$_path = 'item/compare_' . $_act . '.php';
  
if (!file_exists($_path)) 
	$_path = 'item/compare_list.php';


echo '<div>';
//if ($i_can_create)
{
	echo '<a class="button" href="./?mod=item&sub=compare&act=import"> Import ... </a> ';
	echo '<a class="button" href="./?mod=item&sub=compare&act=comparing"> Comparing ... </a> ';
}
echo '</div>';

include($_path);
