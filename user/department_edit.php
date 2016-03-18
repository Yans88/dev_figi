<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;

if (isset($_POST['save'])) {
	// check if duplicate name
	$query  = "SELECT count(*) 
				FROM department 
				WHERE (department_name = '$_POST[department_name]') and (id_department != $_id)";
	$rs = mysql_query($query);
	$rec = mysql_fetch_row($rs);
	if ($rec[0] == 0) {
        $admin_id = get_user_id($_POST['admin_name']);
        $hod_id = get_user_id($_POST['hod_name']);
		$query = "REPLACE INTO department (id_department, department_name, id_admin, id_hod, department_code) 
				  VALUES ($_id, '$_POST[department_name]', $admin_id, $hod_id, '')";
		$rs = mysql_query($query);
        //echo $query.mysql_error();
		if (mysql_affected_rows()>0){
			if ($_id == 0){
				$_id = mysql_insert_id();
				user_log(LOG_CREATE, 'Create department '. $_POST['department_name']. '(ID:'. $_id.')');
			} else
				user_log(LOG_UPDATE, 'Update department '. $_POST['department_name']. '(ID:'. $_id.')');
		}
		$_msg = "Department's name updated!";
	} else 
		$_msg = "Error : duplicated department's name!";
} else if (isset($_POST['delete'])) {
	$_id = isset($_POST['id']) ? $_POST['id'] : 0;
	ob_clean();
	header('Location: ./?mod=user&sub=department&act=del&id=' . $_id);
	ob_flush();
	ob_end_flush();
	exit;
}		
	
if ($_id > 0) {
  $query  = "SELECT * FROM department WHERE id_department = $_id";
  $rs = mysql_query($query);
  $data_item = mysql_fetch_array($rs);
  $users = get_user_list(false, true);
  $data_item['admin_name'] = !empty($users[$data_item['id_admin']]) ? $users[$data_item['id_admin']] : '';
  $data_item['hod_name'] = !empty($users[$data_item['id_hod']]) ? $users[$data_item['id_hod']] : '';
  
} else {

  $data_item['id_department'] = '0';
  $data_item['department_name'] = '';
  $data_item['admin_name'] = '';
  $data_item['hod_name'] = '';
}
   

// logging
/*
$last_time = date("Y-m-d g:i:s");
$viewlog="INSERT INTO log_table VALUES(null,'".$_id."','".$data_item['asset_no']."','','','".$last_time."','','".USERNAME."','".$last_status."')";
mysql_query($viewlog);
*/

?>

<form method="POST">

<table width=300 class="itemlist" cellpadding=2 cellspacing=1>
<tr><th colspan=2>Edit Department</th></tr>
<!--
<tr>
  <td width=120>Department ID </td>
  <td><?php echo $data_item['id_department']?></td>
</tr>
-->
<tr class="alt">
  <td>Department Name </td>
  <td><input type="text" id="department_name" name="department_name" value="<?php echo $data_item['department_name']?>"></td>
</tr>
<tr class="alt">
  <td>Admin Name </td>
  <td>
    <input type="text" id="admin_name" name="admin_name" value="<?php echo $data_item['admin_name']?>" 
    onKeyUp="suggest(this, this.value);" onBlur="fill('admin_name', this.value);" >
    <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
    
    </td>
</tr>
<tr class="alt">
  <td>HoD Name </td>
  <td>
    <input type="text" id="hod_name" name="hod_name" value="<?php echo $data_item['hod_name']?>"
    onKeyUp="suggest1(this, this.value);" onBlur="fill1('hod_name', this.value);" >
    <div class="suggestionsBox" id="suggestions1" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList1"> &nbsp; </div>
    </div>
    </td>
</tr>
</table>
<br/>
<input type="hidden" name="id" value="<?php echo $_id?>" > 
<button type="submit" name="save" id="save" >Save</button> 
<button type="button" name="cancel" onclick="location.href='./?mod=user&sub=department'">Cancel</button> 
<?php
if ($_id > 0) {
echo <<<TEXT
<button type="submit" name="delete" 
	onclick="return confirm('Are you sure you want to delete $data_item[department_name]?')">Delete</button> 
TEXT;
}
?>
</form>
<script>

$('#save').click(function(){
  var frm = document.forms[0]
  if ($('#department_name').val()==''){
    alert('Specify department name!');
    return false;
  }
});


function fill(id, thisValue) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
    var frm = document.forms[0];
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("user/user_suggest_by_group.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", group: "<?php echo GRPADM?>"}, function(data){
			if(data.length >0) {
                $('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			}
		});
	}
}
function fill1(id, thisValue) {
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestions1').fadeOut();", 100);
}

function suggest1(me, inputString){
    var frm = document.forms[0];
	if(inputString.length == 0) {
		$('#suggestions1').fadeOut();
	} else {
		$.post("user/user_suggest_by_group.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", group: "<?php echo GRPHOD?>"}, function(data){
			if(data.length >0) {
                $('#suggestions1').fadeIn();
				$('#suggestionsList1').html(data);
			}
		});
	}
}

</script>
<style>
#suggestions { margin-top: -3px; width: 160px;}
#suggestionsList ul{ margin-top: 1px;}
#suggestions1 { margin-top: -3px; width: 160px; }
#suggestionsList1 ul{ margin-top: 1px;}
</style>
<br/>
<?php
if ($_msg != null)
	echo '<script>alert("' . $_msg . '"); location.href = "./?mod=user&sub=department&act=list";</script>';
?>
