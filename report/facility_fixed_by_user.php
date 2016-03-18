<style>
.total{
	height:25px;
	valign:bottom;
}
</style>
<?php
if (!defined('FIGIPASS')) exit;

$msg = null;
$res = null;
$_username = !empty($_POST['username']) ? $_POST['username'] : null;
$_view = isset($_POST['view']) ? true : false;
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$total_page = 0;
$total_sum =0;
if (!$_view) {
	$_view = isset($_GET['view']) ? $_GET['view'] : false;
	if (!$_username)
		$_username = !empty($_GET['username']) ? $_GET['username'] : null;
}
$_limit = RECORD_PER_PAGE;
$_start = 0;
$_export = (isset($_POST['act']) && $_POST['act'] == 'export');

if ($_view){
	$query  = "SELECT id_student FROM students WHERE full_name = '$_username' ";
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs) > 0) {
		$rec = mysql_fetch_array($rs);
		$studentid = $rec['id_student'];		
		$query = "SELECT count(st.id_student) FROM students_trans_detail st WHERE st.id_student ='$studentid'";					
		$res = mysql_query($query);
		$rec = mysql_fetch_row($res);
	
		$total_item = $rec[0];
		$total_page = ceil($total_item/$_limit);
		if ($_page > $total_page) 
			$_page = 1;
		if ($_page > 0)
			$_start = ($_page-1) * $_limit;		
		$query = "select std.id_student, l.id_location, l.location_name, count(st.id_location) as ttal from location l, 
				  students_trans st, students_trans_detail std where l.id_location = st.id_location and std.id_student = ".$studentid;
		if (!$_export)
			$query .= " LIMIT $_start, $_limit";
		$res = mysql_query($query);
	
	} else {
		$msg = 'Fullname "' . $_username . '" is not found!';
	}
}
	
if ($_export){
	$crlf = "\r\n";	
	$content = '"Name of Student : "'.$_username .$crlf;
	$content .= '"Facility/room","Total"'.$crlf;
	if (mysql_num_rows($res)>0){		
		while ($rec=mysql_fetch_array($res)){			
			$content .= "$rec[location_name],$rec[ttal]$crlf";
		}		
	}
	ob_clean();
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=facility_fixed_user_by_$_username.csv");
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
	var key = 'report';
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("facility/suggest_students.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", key: ""+key+""}, function(data){
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

<h2>Facility fixed by user</h2>
<style>
#suggestions { margin-top: 1px; width: 250px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px}
</style>
<form method="post" autocomplete="off">
<input type="hidden" name="act" value="">
<div >
    Search Name of Student <input type="text" id="username" name="username" size=20 value="$_username"  
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
<table class="report" width="800" cellpadding=2 cellspacing=1>
<tr>
	<td ><h3>Fullname : $_username<h3></td>
	<td align="right"><a class="button" href="#" onclick="export_this()">Export</a></td>
</tr>
<tr><td colspan=2>
<table width="100%" class='userlist' cellpadding=2 cellspacing=1>
  <tr>
	<th>No</th>
    <th>Facility/room</th>
	<th>Total</th>
	
  </tr>
TABLE;
		$row = 0;
		if (mysql_num_rows($res)>0){
		  while ($rec=mysql_fetch_array($res)){		
			$total_sum += $rec['ttal'];		  
			$row++;
			$class = ($row % 2 == 0) ? ' class="alt"' : ' class="normal"';			
			echo '<tr '.$class .'>
				<td>'.$row++.'</td>    
				<td>'.$rec['location_name'].'</td>
				<td>'.$rec['ttal'].'</td>				
			   </tr>';
			}
			echo'<tr '.$class .'>
			<td colspan=2 class="total">Total</td><td >'.$total_sum.'</td>
			</tr>';
			echo '<tr ><td colspan=7 class="pagination">';			
			echo make_paging($_page, $total_page, './?mod=report&sub=facility&act=view&term=fixed&by=user&view=1&username='.$_username.'&page=');
			echo  '</td></tr>';			
		} else
		  echo '<tr class="normal"><td colspan=7  align=center>Data is not available!</td></tr>';
		  
		echo '</table>';
	} else
		echo 'User not found'. $msg;
	echo '</td></tr></table>';
}
?>
