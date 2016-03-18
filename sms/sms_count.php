<?php
$_limit = RECORD_PER_PAGE;
$_start = 0;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$month = isset($_POST['month']) ? $_POST['month'] : 0;
$select = null;
$sms_count = get_sms_count($month);
$month_names = array(
				'1' => 'January', 
				'2' => 'February', 
				'3' => 'March',
				'4' => 'April', 
				'5' => 'May', 
				'6' => 'June', 
				'7' => 'July',
				'8' => 'August', 
				'9' => 'September', 
				'10' => 'October', 
				'11' => 'November', 
				'12' => 'December'
			);
			
	echo '<br/>';
	echo '<form method="post" id="sms_list">';
	echo '<div align="right" valign="middle" style="width:400px; margin-left:330px; color:#fff;"> Month : &nbsp;';
	echo '<select name="month" id="month" style="float:right">
	<option value="0">--Select month--</option>';
	foreach($month_names as $key=>$mn){
		echo '<option value="'.$key.'">'.$mn.'</option>';	}
	echo '</select>';
if(!empty($sms_count)){
	
	
	echo '<table width="400" class="userlist" cellpadding=2 cellspacing=1>';
	echo '<tr>
		<th>No</th>
		<th> Module Name</th>
		<th width=100> SMS Counter</th>
	</tr>';
	$no = 1;
	foreach($sms_count as $sl){
		$class = ($no % 2 == 0) ? 'class="alt"' : 'class="normal"';
		echo '<tr '.$class.'>';
		echo '<td align="center" width=30>'.$no.'</td>';
		echo '<td> '.ucwords($sl['module']).'</td>';
		echo '<td align="center"> '.$sl['cnt'].'</td>';
		echo '</tr>';
		$no++;
	}
	echo '</table>';
}else{
	echo '<br/><br/><h3>Data not available</h3>';
	echo '</div></form>';
}
echo '<br/>';
echo '<div style="text-align:center;width:200;vertical-align:middle;">
<a href="./?mod=sms" align="">Back to SMS List</a>
</div>';
?>
	
<script>
var month = '<?php echo $month;?>';
$(document).ready(function(){
	if(month.length > 0){
		$("#month").val(month);
	}
});

$("#month").change(function(){
	$('#sms_list').submit();
});
</script>
