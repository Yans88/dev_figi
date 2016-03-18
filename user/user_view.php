<?php
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;

if ($_id > 0) {
	$rec = get_user($_id);
	$status = ($rec['user_active'] == 1) ? 'active' : '<span style="color:red">inactive</span>';
} else 
	return;
$_caption = 'View User Info';


?>  
	
<br/>
<table width="400" class="userlist" cellpadding=3 cellspacing=1>	
	<tr><th colspan=2><h2 style="color: #000"><?php echo $_caption ?></h2></th></tr>
	<tr class="normal">
    <td align="left" width="130">Full Name</td>
    <td align="left" ><?php echo $rec['full_name']?></td>
	</tr>
	<tr class="alt">
    <td align="left">NRIC</td>
    <td align="left"><?php echo $rec['nric']?></td>
	</tr>
	<tr class="normal">
    <td align="left">Contact No.</td>
    <td align="left"><?php echo $rec['contact_no']?></td>
	</tr>
	<tr class="alt">
    <td align="left">Username</td>
    <td align="left"><?php echo $rec['user_name']?></td>
	</tr>
	<tr class="normal">
    <td align="left">Email</td>
    <td align="left"><?php echo $rec['user_email']?></td>
	</tr>
	<tr class="alt">
    <td align="left">Password</td>
    <td align="left">**********</td>
	</tr>
	<tr class="normal">	
    <td align="left">Main Department</td>
    <td align="left"><?php echo $rec['department_name']?></td>
	</tr>
	<tr class="alt">	
    <td align="left">Other Department</td>
    <td align="left">
	<?php
	
	$x = check_department($rec['id_department'], $rec['id_group']);
	$q = mysql_query($x);
	while($data = mysql_fetch_array($q)){
		echo $data['department_name']."<br />";
	}
	?>
	</td>
	</tr>
	<tr class="normal">	
    <td align="left">Group</td>
    <td align="left"><?php echo $rec['group_name']?></td>
	</tr>
	<tr class="alt">
    <td align="left">Status</td>
    <td align="left"><?php echo $status?></td>
	</tr>
	<tr class="normal"><th colspan=2><br/>
    <button type="button" name="cancel" onclick="javascript:location='./?mod=user&act=list'">Back to User List</button>
    <button type="button" onclick="javascript:location='./?mod=user&act=log&id=<?php echo $_id?>'">View User's Log</button> 
    <br/><br/>
    <button type="button" onclick="javascript:location='./?mod=user&act=loan&id=<?php echo $_id?>'">View User's Loan History</button>
    <br/><br/>
   </th></tr>
</table>
