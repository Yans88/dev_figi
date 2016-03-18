<?php

define('USE_ITEM', true);

$need_signature = defined('CONSUMABLE_NEED_SIGNATURE') ? CONSUMABLE_NEED_SIGNATURE : false;
$_input = isset($_POST['input']) ? $_POST['input'] : null;
$_nric = isset($_POST['nric']) ? $_POST['nric'] : null;
$_id_user = isset($_POST['id_user']) ? $_POST['id_user'] : 0;
$_del_id = isset($_POST['del_id']) ? $_POST['del_id'] : 0;
$_full_name = isset($_POST['full_name']) ? $_POST['full_name'] : null;
$_items = isset($_POST['items']) ? $_POST['items'] : null;
$_confirm = !empty($_POST['confirm']) ? ($_POST['confirm'] == 1) : false;
$_manage = !empty($_POST['manage']) ? ($_POST['manage'] == 1) : false;
$_signature = isset($_POST['user_signature']) ? $_POST['user_signature'] : null;
$start_date = !empty($_POST['start_date']) ? convert_date($_POST['start_date']) :  date('Y-m-d H:i:s');
$end_date = !empty($_POST['end_date']) ? convert_date($_POST['end_date']) :  date('Y-m-d H:i:s');
$items = json_decode($_items);

$user = array();
if(isset($_nric)){
$user = get_user_by_nric($_nric);
if(!empty($user)){
	$_id_user = $user['id_user'];
}
}
$quantities = array();
$hidden_fields = null;
if ($_input != null){ // scan item
    $item = get_expendable_item_out_by_code($_input,$user['id_user']);
    if (!empty($item['id_item'])){
        
		$items[] = array($item['id_item'],1,$item['id_loan']);
    }
}
$new_item = array();
if (is_array($items))
	foreach($items as $key=>$row){
		if(($_del_id - 1) == $key){
			
			continue;
		}
		else{
			$new_item[] = $row;
			}
	}

$items = $new_item;


$items_list = array();
foreach($items as $row){
	$items_list[] = $row[0];
}

$scanned_list = '<div id="itemspace"></div>';
if (count($items) > 0){
	foreach($items as $row){
		$quantities[] = $row[1];
	
	}
	
    if ($_confirm){
		
		// keep item-out trx
		// $id_username = get_user_id_by_fullname($_full_name);
		
		// $query = "INSERT INTO expendable_loan_request (requester, start_loan, end_loan,remark,purpose) VALUES ('$id_username', '$start_date', '$end_date','$_POST[remark]','$_POST[purpose]')";
		// mysql_query($query);
		
		// if (mysql_affected_rows() > 0){
            // $id_loan = mysql_insert_id();
			// keep items
	 		// foreach ($items as $stuff ){
				// $query = "INSERT INTO expendable_loan_item (id_loan,id_item, quantity)
							// VALUES ($id_loan,$stuff[0], $stuff[1])";
				// mysql_query($query);
                             
             
            // }
			// echo print_r($_POST);
			// ob_clean();
            // header('Location: ./?mod=expendable&sub=loan&act=return');
            // ob_end_flush();
        // }   
        // $_input = '';
        // $_nric = '';
        // $_id_user = '';
        // $_items = '';
        // $_full_name = '';
        // $items = array();
    }
	
	
	$scanned_list  = '<table class="consumable_item_list" cellpadding=3 cellspacing=3 width="800">';
			$scanned_list .= '<tr><th width=30>No</th><th width=120>Item Code</th>
							  <th >Name</th><th width=100>Quantity</th><th>Status</th><th width=20></th></tr>';
	$no = 1;
	foreach($items as $key => $row){
		
		$query  = "SELECT ei.*,elio.*, department_name, category_name ,sum(quantity) as quantity
                FROM expendable_loan_item_out elio 
				LEFT JOIN expendable_item ei ON ei.id_item = elio.id_item
                LEFT JOIN category cat ON cat.id_category = ei.id_category 
                LEFT JOIN department dept ON dept.id_department = cat.id_department   
                WHERE elio.id_item = $row[0] group by elio.id_item";
		
		$rs = mysql_query($query);
		if ($rs && mysql_num_rows($rs)>0){
			$rec = mysql_fetch_assoc($rs);
			
			$select_op = '<select name="st-item['.$key.']"><option value="AVAILABLE">AVAILABLE</option><option value="FAULTY">FAULTY</option><option value="LOST">LOST</option><option value="STORAGE">STORAGE</option></select>';
			$dellink = '<a class="button delete" href="javascript:void(0)" onclick="del_this(' . ($key+1) . ')">x</a>';
			$scanned_list .= '<tr><td align="center">' . ($no++) . '.</td><td>' .
				 $rec['item_code'] . '</td><td>' . $rec['item_name'] . '</td><td align="center" id="row-'.$rec['id_item'].'" ><input data-row="'.$key.'" data-id="' . $rec['id_item'] . '" style="width: 60px;" type="number" value="' . 
				 $quantities[$key] . '" class="qty-' . $rec['id_item'] . '" name="qty['.$rec['id_item'].']['.$rec['quantity'].']['.$rec['id_loan'].']" max="'.$rec['quantity'].'"></td><td>'.$select_op.'</td><td>' . $dellink . '</td></tr>';
			// }                
			
		}
	}
	$scanned_list .= '</table><br/>';
	
}

$today  = time();
$today_str = date('j-M-Y H:i', $today);
$day_until = strtotime('+1 day', $today);
$day_until_str = date('j-M-Y H:i', $day_until);
?>
<br/>
<br/>
<style type="text/css">
  #start_date { background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
  #end_date { background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
</style>
<div id="form">
<form method="post" id="consumableform">
<input type="hidden" id="user_signature" name="user_signature" value="">
<input type="hidden" id="del_id" name="del_id" value="0">
<input type="hidden" id="manage" name="manage" value="0">
<input type="hidden" id="confirm" name="confirm" value="0">
<input type="hidden" id="nric" name="nric" value="<?php echo $_nric?>">
<input type="hidden" id="items" name="items" value='<?php if(!empty($items))echo json_encode($items)?>'>
<input type="hidden" id="id_user" name="id_user" value="<?php echo $_id_user?>">
<input type="hidden" id="full_name" name="full_name" value="<?php echo $_full_name?>">
<?php
    echo $scanned_list;
	
    if ($_confirm){
        echo '<div id="cmdlabel">Item usage recorded! Click <a href="./?mod=consumable&act=use">here</a> to make new record.</div>';
    } 
    
    else {
		
		if(isset($_nric) && isset($_id_user)&& !empty($_id_user)){
			echo '<h2>Welcome '.$user['full_name'].'</h2>';
			$loan_list = get_loan_request($user['id_user']);
			?>
			<h4>Loaned Item List</h4>
			<table cellpadding=2 cellspacing=1 class="loan_table item-list" >
			<tr height=30 valign="top">
			  <th width=35>Id Loan</th><th>Date of Request</th>
			  <th>Requestor</th><th>Loan Start Date</th>
			  <th>Loan End Date</th>
			  <th width=25>Quantity</th><th>Purpose</th><th width=60>Action</th>
			</tr>
		<?php
			// echo print_r($loan_list);
			foreach($loan_list as $rec){
				echo <<<DATA
    <tr $_class valign='top'>
    <td align="center">$transaction_prefix$rec[id_loan]</td>
    <td align="center">$rec[loan_date]</td>
    <td>$rec[requester]</td>
    <td align="center">$rec[start_loan]</td>
    <td align="center">$rec[end_loan]</td>
    <td align="center">$rec[quantity]</td>
    <td>$rec[purpose]</td>
    <td align="center">
    <a href="./?mod=loan&sub=loan&act=view&id=$rec[id_loan]" title="view"><img class="icon" src="images/view.png" alt="view"></a>
DATA;
	if ($i_can_update) {
            echo ' <a href="./?mod=expendable&sub=loan&act=return&id='.$rec['id_loan'].'" title="return" ><img class="icon" src="images/undo.png" alt="return"></a> ';
        }
	echo " </td></tr>";
			}
	?>
	</table>
	<?php
		}
		else{
			?>
			Scan Your NRIC
			<br/>    
			<br/>    
			<input type="text" id="input" name="nric" class="inputbox" autocomplete="off" onkeyup="check_entry()">
		<?php
		}
	}
?>
</form>
</div>

<script type="text/javascript">
var isbn_length = <?php echo ISBN_LENGTH?>;
var nric_length = <?php echo NRIC_LENGTH?>;
var serial_length = <?php echo SERIAL_LENGTH?>;
var need_signature = '<?php echo $need_signature ?>';
var list = $('#items').val();
var items = $.parseJSON(list);


$('input[class|="qty"]').blur(function(){
	update_quantity($(this).data('id'),$(this).val(),$(this).attr('max'),$(this).data('row'));
	// alert($(this).val());
});
function update_quantity(id,newqty,max,row)
{
	newqty = parseInt(newqty);
	max = parseInt(max);
   
	var new_items = [];
	
	for(i = 0; i< items.length;i++){
		if(i == row){
			new_items[i] = [String(id),newqty];
		}
		else{
			new_items[i] = [items[i][0],items[i][1]]
		}
	}
	
	var jsons = JSON.stringify(new_items);
	  $('#items').val(jsons);
    
    if (isNaN(newqty)){
        alert('Please enter correct number of quantity');
    }
	else if(parseInt(newqty)>parseInt(max)){
		alert('You cannot fill more than '+max);
	}
	else{
		  $('form').submit();
	}
}

function del_this(row)
{
    $('#del_id').val(row);
    $('form').submit();
}

function cancel_this()
{
    if (confirm("Are you sure cancel this loan?")){
        new_loan();
    }
}

function fill(id, thisValue) {
	$('#'+id).val(thisValue);
	$('#full_name').val(thisValue);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
		$.post("user/user_suggest.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			}
		});
	}
}

function fill_loc(id, thisValue) 
{
	$('#'+id).val(thisValue);
	setTimeout("$('#suggestionsLoc').fadeOut();", 100);
	
}


function return_this()
{
    //if (confirm("Are you sure confirm this loan?")){
        $('#confirm').val(1);
        $('form').submit();
    //}
}

function confirm_this()
{
    if ($('#user_name').val() == ''){
        alert('Please fill in name of the user!');
        return false;
    }
    // if ((need_signature == 1) && isCanvasEmpty){
        // alert('Please sign-in for user!');
        // return false;
    // }
    if (confirm("Are you sure confirm this request?")){
        $('#confirm').val(1);
        // var cvs = document.getElementById('imageView');
        // $('#user_signature').val(cvs.toDataURL("image/png"));
        $('form').submit();
        return true;
    }
    
    return false;
}

function check_entry()
{
    var v = $('#input').val();
    if ($('#nric').val() == ''){
        if (v.length >= nric_length)
            $('form').submit();
    } else {
        if (v.length >= serial_length)
            $('form').submit();    
    }
}

function fill_purpose(id, thisValue, onclick) {
    $('#'+id).val(thisValue);
    var suggest_for = id.substring(8);
    setTimeout("$('#suggestions_"+suggest_for+"').fadeOut();", 100);
}

function suggest_purpose(me, inputString){
    var dept, url, suggest_for;
	if(inputString.length == 0) {
		$('.suggestions').fadeOut();
	} else {
        suggest_for = me.id.substring(8);
        switch (suggest_for){
        case 'service': url = "./service/suggest_purpose.php"; dept = $('#dept_service option:selected').val(); break;
        case 'loan': url = "./loan/suggest_purpose.php"; dept = $('#dept_loan option:selected').val(); break;
        case 'facility': url = "./facility/suggest_purpose.php"; dept = $('#dept_facility option:selected').val();
        }
        
		$.post(url, {queryString: ""+inputString+"", inputId: ""+me.id+"", deptId: ""+dept+""}, function(data){
			if(data.length >0) {
				$('#suggestions_'+suggest_for).fadeIn();
				$('#suggestionsList_'+suggest_for).html(data);
			}else
                $('#suggestions_'+suggest_for).fadeOut();
		});
	}
}
$('.inputbox').focus();

</script>
