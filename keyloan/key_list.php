<?php
if (!defined('FIGIPASS')) exit;

if (!empty($_SESSION['DC_ITEM_ORDER_STATUS']))
    $order_status = unserialize($_SESSION['DC_ITEM_ORDER_STATUS']);
else
    $order_status = array('serial_no' => 'asc',                 
                          'status' =>  'asc');

$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_orderby = isset($_GET['ordby']) ? $_GET['ordby'] : 'serial_no';
$_changeorder = isset($_GET['chgord']) ? true : false;
$_searchby = !empty($_GET['searchby']) ? $_GET['searchby'] : null;
$_searchtext = !empty($_GET['searchtext']) ? $_GET['searchtext'] : null;

$_limit = RECORD_PER_PAGE;
$_start = 0;



$total_item = count_key_item($_searchby, $_searchtext, USERDEPT);

$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) 
    $_page = 1;
if ($_page > 0)
	$_start = ($_page-1) * $_limit;

$sort_order = $order_status[$_orderby];
if ($_changeorder)
    $sort_order = ($order_status[$_orderby] == 'asc') ? 'desc' : 'asc';
$order_status[$_orderby] = $sort_order;
$buffer = ob_get_contents();
ob_clean();
$_SESSION['DC_ITEM_ORDER_STATUS'] = serialize($order_status);
echo $buffer;
$row_class = ' class="sort_'.$sort_order.'"';
$order_link = './?mod=keyloan&sub=key&act=list&chgord=1&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page='.$_page.'&ordby=';

?>
<br/>
<div style="width:980px;" >
<div align="left" valign="middle" class="leftlink" >
<?php

echo '</div>';

if ($total_item > 0){
?>
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
		$.post("keyloan/suggest_item.php", {queryString: ""+inputString+"", inputId: ""+me.id+"", searchBy: ""+$('#searchby').val()+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
				var pos =  $('#searchtext').offset();
                var w =  $('#searchtext').width();                                              
				$('#suggestions').css('position', 'absolute');
				$('#suggestions').offset({top:pos.bottom, left:pos.left});
                $('#suggestions').width(w);
			}
		});
	}
}

</script>
<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px}
</style>

<form method="get">
<input type="hidden" name="mod" value="keyloan">
<input type="hidden" name="sub" value="key">
<input type="hidden" name="act" value="list">
<input type="hidden" name="ordby" value="<?php echo $_orderby?>">
<div class="searchbox" >
    Search by
    <select name="searchby" id="searchby">
   
   
    <option value="serial_no" <?php if ($_searchby == 'serial_no') echo 'selected'?>>Serial No</option>
    <option value="status" <?php if ($_searchby == 'status') echo 'selected'?>>Status</option>
    <!--<option value="department_name">Department</option>-->
    </select>
    <input type="text" id="searchtext" name="searchtext" class="searchinput" size=20 value="<?php echo $_searchtext?>" 
    onKeyUp="suggest(this, this.value);" onBlur="fill('searchtext', this.value);" autocomplete=off>
    <input type="image" src="images/loupe.png" class="searchsubmit" width=12 height=12>
    <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
</div>
<?php } ?>
</div>
</form>
<?php
    if ($total_item > 0) {
?>
<div class="clear"></div>
<table id="itemlist" cellpadding=2 cellspacing=1 class="itemlist" style="width:90%;" >
<tr height=30>
  <th width=30>No</th>  
  <th width=100 align="left" <?php echo ($_orderby == 'serial_no') ? $row_class : null ?> >
	<a href="<?php echo $order_link ?>serial_no">Serial No.</a></th>
 
  <th width=60>Status</th>
<?php
if (USERDEPT == 0) {
?>
  <th width=100 <?php echo ($_orderby == 'department_name') ? $row_class : null ?>>
    <a href="<?php echo $order_link ?>department_name">Department</a></th>
<?php } ?>  
<th width=60>Issued to</th>
<th width=60>Date/Time Loan</th>
<th width=60>Contact</th>
<th width=60>Description</th>
  <th width=50>Action</th>
</tr>

<?php
$dept = defined('USERDEPT') ? USERDEPT : 0;
$rs = get_key_item($_orderby, $sort_order, $_start, $_limit, $_searchby, $_searchtext, $dept);
$counter = $_start+1;
$user = get_user_info();

$issued_to = null;
while ($rec = mysql_fetch_array($rs))
{
	$edit_link = null;
	
	$_query = "SELECT kl.* FROM key_loan kl
				  LEFT JOIN key_loan_item kli ON kli.id_loan = kl.id_loan
				  LEFT JOIN key_item ki ON ki.id_item = kli.id_item
				  where kli.id_item = $rec[id_item] and ki.status = 'On Loan'";
	$_rs = mysql_query($_query); 
	if($_rs){
		$_loan = mysql_fetch_assoc($_rs);		
		$date = date_create($_loan['loan_start']);
		$loan_start = date_format($date,"d-M-Y h:i");	
		$issued_to = $_loan['id_user'];
		
	}	
	
	
	if(!empty($issued_to)){		
		$issued_name = $user[$issued_to]['full_name'];		
		$contact = $user[$issued_to]['contact'];		
	}else{
		$contact = '-';
		$issued_name = '-';
		$loan_start = '-';
		$align = 'align=center';
	}
	
	if (!SUPERADMIN && !$i_can_update && !$i_can_delete)
		$edit_link = <<<EDIT
<a href="./?mod=keyloan&act=edit&id=$rec[id_item]" title="edit"><img class="icon" src="images/edit.png" alt="edit"></a>
<a href="./?mod=keyloan&act=del&id=$rec[id_item]" 
       onclick="return confirm('Are you sure delete &quot;$rec[serial_no]&quot;?')" title="delete"><img class="icon" src="images/delete.png" alt="delete"></a>
EDIT;
	
	$dept_name = (USERDEPT > 0) ? null : "	<td>$rec[department_name]</td>";
	$_class = ($counter % 2 == 0) ? 'class="alt"':'class="normal"';
	
	echo <<<DATA
	<tr $_class>
	<td align="center">$counter.</td>
   
	
	<td>$rec[serial_no]</td>
   
    <td align="center">$rec[status]</td>
    $dept_name
	<td $align>$issued_name</td>
	<td $align>$loan_start</td>
	<td $align>$contact</td>	
	<td>$rec[description]</td>
	<td align="center" nowrap>
	<a href="./?mod=keyloan&act=view&id=$rec[id_item]" title="view"><img class="icon" src="images/loupe.png" alt="view" ></a>
	$edit_link
	</td>
	</tr>
DATA;
  $counter++;
}

echo '<tr ><td colspan=8 class="pagination">';
echo make_paging($_page, $total_page, './?mod=keyloan&sub=key&act=list&searchby='.$_searchby.'&searchtext='.$_searchtext.'&page=');
echo  '</td></tr></table><br/>';

} else { //total_item <= 0 
    echo '<p class="error" style="margin-top: 10px">Data is not available!.</p>';
}
?>
<script>
    $('#searchtext').focus();
</script>