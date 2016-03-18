<?php
if (!defined('FIGIPASS')) exit;

$msg = null;
$res = null;
$_username = !empty($_POST['username']) ? $_POST['username'] : null;
$_view = isset($_POST['view']) ? true : false;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$total_page = 0;
if (!$_view) {
	$_view = isset($_GET['view']) ? $_GET['view'] : false;
	if (!$_username)
		$_username = !empty($_GET['username']) ? $_GET['username'] : null;
}
$_limit = RECORD_PER_PAGE;
$_start = 0;
$_export = (isset($_POST['act']) && $_POST['act'] == 'export');

if ($_view){
	$query  = "SELECT id_user FROM user
				WHERE full_name = '$_username' ";
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs) > 0) {
		$rec = mysql_fetch_array($rs);
		$userid = $rec['id_user'];
		
		$query = "SELECT count(lr.id_loan) FROM loan_request lr 
					LEFT JOIN category c ON c.id_category = lr.id_category 
					WHERE lr.requester ='$userid' AND category_type = 'EQUIPMENT' ";
		$res = mysql_query($query);
		$rec = mysql_fetch_row($res);
		$total_item = $rec[0];
		$total_page = ceil($total_item/$_limit);
		if ($_page > $total_page) 
			$_page = 1;
		if ($_page > 0)
			$_start = ($_page-1) * $_limit;
		$dtf = ($_export) ? '%d-%b-%Y %H:%i:%s' : '%d-%b-%Y %H:%i';
		$query = "SELECT lr.id_loan, c.category_name, date_format(lr.start_loan, '$dtf') as start_loan, status,  
				  date_format(lr.end_loan, '$dtf') as end_loan, date_format(lr.request_date, '$dtf') as request_date 
				  FROM loan_request lr 
				  LEFT JOIN category c ON c.id_category = lr.id_category 
				  WHERE lr.requester ='$userid'  AND category_type = 'EQUIPMENT' 
				  ORDER BY id_loan DESC ";
		if (!$_export)
			$query .= " LIMIT $_start, $_limit";
		$res = mysql_query($query);
	
	} else {
		$msg = 'Username "' . $_username . '" is not found!';
	}
}
	
if ($_export){
	$crlf = "\r\n";	
	$content = '"Transaction No",Category,"Request Date","Date Loaned","Date to be Returned",Status'.$crlf;
	if (mysql_num_rows($res)>0){
		while ($rec=mysql_fetch_array($res)){
			$content .= "LN$rec[id_loan],$rec[category_name],$rec[request_date],$rec[start_loan],$rec[end_loan],$rec[status]$crlf";
		}
	}
	ob_clean();
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=loan_tracking_for_$_username.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Content-length: " . strlen($content));
	echo $content;
	ob_end_flush();
	exit;
}


echo <<<HEAD
<script>
function export_this(){
	var frm  = document.forms[0]
	frm.act.value='export';
	frm.view.click();
	frm.act.value='';
}
function fill(id, thisValue, uid) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
    var frm = document.forms[0];
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("user/user_suggest.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
                var pos = $('#username').offset();  
                var w = $('#username').width();
                $('#suggestions').css('position', 'absolute');               
                $('#suggestions').offset({top:pos.bottom, left:pos.left});
                $('#suggestions').width(w);
			}
		});
	}
}
</script>

<h2>User's Loan History</h2>
<style>
#suggestions { margin-top: 1px; width: 250px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px}
</style>
<form method="post" >
<input type="hidden" name="act" value="">
<div class="center" >
    Search username <input type="text" id="username" name="username" size=20 value="$_username"  
    onKeyUp="suggest(this, this.value);" onBlur="fill('username', this.value);" > <button name="view">View</button>
    <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
</div>
</form>
HEAD;
if ($_view) {
	if ($res) {
		echo <<<TABLE
<table class="report middle" width="99%" cellpadding=2 cellspacing=1>
<tr>
	<td ><h3>Username: $_username<h3></td>
	<td align="right"><a href="#" onclick="export_this()" class="button">Export</a></td>
</tr>
<tr><td colspan=2>
<table width="100%" class='userlist' cellpadding=2 cellspacing=1>
  <tr>
    <th>Transaction No</th>
    <th>Category</th>   
    <th>Request Date</th>
    <th>Date Loaned</th>
    <th>Date to be Returned</th>
    <th>Status</th>    
    <th>View</th>
  </tr>
TABLE;
		$row = 0;
		if (mysql_num_rows($res)>0){
		  while ($rec=mysql_fetch_array($res)){
			//$serials = get_serials($rec['id_loan']);
			$link = null;
			$act = null;
			switch($rec['status']){
			case LOANED: $act = 'view_issue'; break;
			case RETURNED: $act = 'view_return'; break;
			case COMPLETED: $act = 'view_complete'; break;
			default: $act = 'view'; break;
			}
			if ($act)
				$link = '<a href="./?mod=loan&sub=loan&act='.$act.'&id='.$rec['id_loan'].'">view</a>';
			$row++;
			$class = ($row % 2 == 0) ? ' class="alt"' : ' class="normal"';
			echo '<tr '.$class .'>
				<td>LN'.$rec['id_loan'].'</td>    
				<td>'.$rec['category_name'].'</td>
				<td>'.$rec['request_date'].'</td>
				<td>'.$rec['start_loan'].'</td>
				<td>'.$rec['end_loan'].'</td>
				<td>'.$rec['status'].'</td>
				<td>'.$link.'</td>
			   </tr>';
			}
			echo '<tr ><td colspan=7 class="pagination">';
			echo make_paging($_page, $total_page, './?mod=report&sub=loan&act=view&term=tracking&by=user&view=1&username='.$_username.'&page=');
			echo  '</td></tr>';			
		} else
		  echo '<tr class="normal"><td colspan=7  align=center>Data is not available!</td></tr>';
		  
		echo '</table>';
		echo '</td></tr></table>';
	} else
		echo '<p class="center error">'.$msg.'</p>';
}
?>
