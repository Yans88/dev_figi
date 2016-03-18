<style>
.error{
	text-align:center;
}
</style>

<?php 
if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;

if ($_id > 0) {
  $data = get_faq_by_id($_id);
  //print_r($data);
  if (count($data) > 0) {
        // delete stock
        $query = "DELETE FROM faq_figi WHERE id_faq = $_id";
		
		mysql_query($query);
        // delete item
		
        
		$_msg = "FAQ was deleted!";        
        
  } else
	$_msg = "FAQ is not found!";
} else 
	$_msg = "FAQ is not specified!";
	  

if ($_msg != null)
	echo '<br/><br/><br/><div class="error">' . $_msg . '</div>';
?>
<br/><br/>
<div class="center">
<a href="./?mod=faq&act=list"> Back to FAQ List</a> 
</div>