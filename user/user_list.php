<?php
if (!defined('FIGIPASS')) exit;


$page_quickUM = get_page_privileges(USERGROUP, get_pages_id_by_name('User Management'));
$i_can_view_quickUM = (isset($page_quickUM[CAN_VIEW]) && ($page_quickUM[CAN_VIEW] == 1)); 
if(!$i_can_view_quickUM){
	echo '<div class="center">';
	include 'unauthorized.php';
	echo '</div>';
	exit;
}


$group = defined('USERGROUP') ? USERGROUP : null;
$dept = defined('USERDEPT') ? USERDEPT : null;
//echo "Group: ".$group." - Depart ". $dept;

if (!SUPERADMIN && !GRPASSETOWNER) {
    include 'unauthorized.php';
    return;
}
if (!empty($_SESSION['ORDER_STATUS']))
    $order_status = unserialize($_SESSION['ORDER_STATUS']);
else
    $order_status = array('full_name' => 'asc', 
                          'user_name' => 'asc', 
                          'user_email' => 'asc', 
                          'nric' =>  'asc', 
                          'group_name' =>  'asc', 
                          'department_name' => 'asc' );

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'full_name';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_searchtext = !empty($_GET['searchtext']) ? $_GET['searchtext'] : null;

$_limit = RECORD_PER_PAGE;
$_start = 0;

$total_item = get_user_count($_searchtext);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) 
    $_page = 1;
if ($_page > 0)
	$_start = ($_page-1) * $_limit;

$sort_order = $order_status[$_orderby];
if ($_changeorder)
	$sort_order = ($sort_order == 'asc') ? 'desc' : 'asc';
$order_status[$_orderby] = $sort_order;
$buffer = ob_get_contents();
ob_clean();
$_SESSION['ORDER_STATUS'] = serialize($order_status);
echo $buffer;

if($group == 16){
	//ASSET OWNER CANOT CREATE NEW, EXPORT ALL USER, AND IMPORT USER
	echo "<br />";
} else {

?>
<br/>

<div align="left" valign="middle" class="leftlink">
	<a class="button" href="./?mod=user&act=edit"><img width=16 height=16 border=0 src="images/add.png"> Create New User</a>
	<a class="button" href="./?mod=user&act=export"><img width=16 height=16 border=0 src="images/download.png"> Export All User</a>
	<a class="button" href="./?mod=user&act=import"><img width=16 height=16 border=0 src="images/upload.png"> Import User(s)</a>
</div>

<?php  } ?>
<script>
function fill(id, thisValue) {
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
				var pos =  $('#suggestions').offset();                       
				$('#suggestions').css('position', 'absolute');
				$('#suggestions').offset({top:pos.top, left:pos.left});
			}
		});
	}
}

</script>
<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px;}
</style>
<form method="get">
<input type="hidden" name="mod" value="user">
<input type="hidden" name="act" value="list">
<input type="hidden" name="ordby" value="<?php echo $_orderby?>">
<div class="searchbox" >
    <input type="text" id="searchtext" name="searchtext" class="searchinput" size=30 value="<?php echo $_searchtext?>" 
    onKeyUp="suggest(this, this.value);" onBlur="fill('searchtext', this.value);" >
    <input type="image" src="images/loupe.png" class="searchsubmit" width=12 height=12>
    <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
</div>
</form>
<?php
$row_class = ' class="sort_'.$sort_order.'"';
$data = get_user_data($_orderby, $sort_order, $_start, $_limit, $_searchtext, $group, $dept);
$order_link = './?mod=user&sub=user&act=list&chgord=1&searchtext='.$_searchtext.'&page='.$_page.'&ordby=';

?>
<table width="100%" cellpadding=2 cellspacing=1 class="userlist" >
<tr height="20">
	<th width="140px" <?php echo ($_orderby == 'full_name') ? $row_class : null ?> >
            <a href="<?php echo $order_link ?>full_name">Full Name</a></th>
	<th width="80px" <?php echo ($_orderby == 'user_name') ? $row_class : null ?>>
            <a href="<?php echo $order_link ?>user_name">User Name</a></th>
	<th <?php echo ($_orderby == 'user_email') ? $row_class : null ?>>
            <a href="<?php echo $order_link ?>user_email">Email</a></th>
	<th width=100 <?php echo ($_orderby == 'nric') ? $row_class : null ?>>
            <a href="<?php echo $order_link ?>nric">NRIC</a></th>
	<th width="40px">Status</th>
	<th width="100px" <?php echo ($_orderby == 'group_name') ? $row_class : null ?>>
            <a href="<?php echo $order_link ?>group_name">Group</a></th>
	<th width="100px" <?php echo ($_orderby == 'department_name') ? $row_class : null ?>>
            <a href="<?php echo $order_link ?>department_name">Department</a></th>
	<th width="100px" <?php echo ($_orderby == 'department_name') ? $row_class : null ?>>
			Other Department</th>
	<th width=60>Action</th>
</tr>
<?php

	
$counter = 0;
if ($total_item>0) {
    foreach($data as $rec){
        $class = ($counter % 2 == 0) ? 'class="alt"' : 'class="normal"';
        $status = ($rec['user_active'] == 1) ? 'active' : '<span style="color: red">inactive</span>';
        $admin_link = '';
        if ($i_can_update)
            $admin_link .= '<a href="./?mod=user&sub=user&act=edit&id=' . $rec['id_user'] . '" title="edit"><img class="icon" src="images/edit.png" alt="edit"></a> ';
        if ($i_can_delete && ($rec['id_user'] != USERID))
			if($group !=16)
            $admin_link .= '<a href="./?mod=user&sub=user&&act=del&id=' . $rec['id_user'] . '"
                            onclick="return confirm(\'Are you sure want to delete this user?\')" title="delete"><img class="icon" src="images/delete.png" alt="delete"></</a>';
		$decrypted_user_email = $rec['user_email'];
		$decrypted_user_name  = $rec['user_name'];
		$decrypted_contact_no = $rec['contact_no'];
        $email = (!empty($rec['user_email'])) ? '<a href="mailto:'.$rec['user_email'].'">'.$decrypted_user_email.'</a>' : null;
		
        
        echo '
        <tr '.$class.' >
            <td>'.$rec['full_name'].'</td>
            <td>'.$decrypted_user_name.'</td>
            <td>'.$decrypted_user_email.'</td>
            <td>'.$rec['nric'].'</td>
            <td>'.$status.'</td>
            <td>'.$rec['group_name'].'</td>
            <td>'.$rec['department_name'].'</td>
			<td>';

			if($rec['id_group'] == GRPASSETOWNER || $rec['id_group'] == GRPADM ){
				$x = check_department($rec['id_department'], $rec['id_group']);
				
				$q = mysql_query($x);
				while($data = mysql_fetch_array($q)){
					if($data['department_name'] == $rec['department_name']){} else{
						echo $data['department_name'].",";
					}
				}
			} else {
				echo "";
			}

			echo'</td>
            <td align="center">
              <a href="?mod=user&sub=user&act=view&id='.$rec['id_user'].'" title="view"><img class="icon" src="images/loupe.png" alt="view" ></a>
              '.$admin_link.'
            </td>
        </tr>

';

        $counter++;
    }
    echo '<tr class="alt"><td colspan=9 class="pagination">';
    echo make_paging($_page, $total_page, './?mod=user&sub=user&act=list&page=');
    echo  '</td></tr>';
} else {
    echo '<tr ><td colspan=9 align="center">Data is not available!</td></tr>';

}
echo '</table><br/>';
?>