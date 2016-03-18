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

$quantities = array();
$hidden_fields = null;
if ($_input != null){ // scan item
    $item = get_expendable_item_by_code($_input);
    if (!empty($item['id_item'])){
      
		$same = 0;
		foreach($items as $key=>$row){
			if($item['id_item'] == $row[0]){
				$items[$key][1] = $items[$key][1]+1;
				$same +=1;
			}
			
		}
		
		if($same == 0){
			$items[] = array($item['id_item'],1);
		}
		
    }
}

$new_item = array();
if (is_array($items))
	foreach($items as $row){
		if($_del_id == $row[0]){
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
		$quantities[$row[0]] = $row[1];
	
	}

    if ($_confirm){
		//echo print_r($items);
		// keep item-out trx
		$id_username = get_user_id_by_fullname($_full_name);
		
		$query = "INSERT INTO expendable_loan_request (requester, start_loan, end_loan,remark,purpose) VALUES ('$id_username', '$start_date', '$end_date','$_POST[remark]','$_POST[purpose]')";
		mysql_query($query);
		// echo mysql_error();
		if (mysql_affected_rows() > 0){
            $id_loan = mysql_insert_id();
			// keep items
	 		foreach ($items as $stuff ){
				$query = "INSERT INTO expendable_loan_item (id_loan,id_item, quantity)
							VALUES ($id_loan,$stuff[0], $stuff[1])";
				mysql_query($query);
                             
             
            }
		
			ob_clean();
            header('Location: ./?mod=expendable&sub=loan&act=issue&id=' . $id_loan);
            ob_end_flush();
        }   
        $_input = '';
        $_nric = '';
        $_id_user = '';
        $_items = '';
        $_full_name = '';
        $items = array();
    }

	
    // get item's info
	$query  = "SELECT ei.*, department_name, category_name 
                FROM expendable_item ei 
                LEFT JOIN category cat ON cat.id_category = ei.id_category 
                LEFT JOIN department dept ON dept.id_department = cat.id_department   
                WHERE id_item IN (" . implode(',', $items_list) . ")";
    $rs = mysql_query($query);
	
    // echo $query;
    $no = 1;
    if ($rs && mysql_num_rows($rs)>0){
        $scanned_list  = '<table class="consumable_item_list" cellpadding=3 cellspacing=3 width="800">';
        $scanned_list .= '<tr><th width=30>No</th><th width=120>Part No.</th>
                          <th >Name</th><th width=100>Quantity</th><th width=20></th></tr>';
        while ($rec = mysql_fetch_assoc($rs)){
            $dellink = '<a class="button delete" href="javascript:void(0)" onclick="del_this(' . $rec['id_item'] . ')">x</a>';
            $scanned_list .= '<tr><td align="center">' . ($no++) . '.</td><td>' .
                             $rec['item_code'] . '</td><td>' . $rec['item_name'] . '</td><td align="center" id="row-'.$rec['id_item'].'" onclick="insert_quantity(' . $rec['id_item'] . ')"><input data-id="' . $rec['id_item'] . '" style="width: 60px;display: none;" type="number" value="' . 
                             $quantities[$rec['id_item']] . '" id="qty-' . $rec['id_item'] . '" max="'.get_expendable_stock($rec['id_item']).'"><p id="q-'.$rec['id_item'].'">' . 
                             $quantities[$rec['id_item']] . '</p></td><td>' . $dellink . '</td></tr>';
        }                
        $scanned_list .= '</table><br/>';
    }
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
    else if ($_manage){
?>

<table class="itemlist" cellpadding=5 cellspacing=0>
    <tr><th colspan=2>Fill form to completion</th></tr>
    <tr><td colspan=2 align="right"><br/> &nbsp;</td></tr>
    <tr class="alt"><td>User</td><td>
        <input type="text" id="user_name" name="user_name" size=24 
         onKeyUp="suggest(this, this.value);" onBlur="fill('user_name', this.value);" autocomplete="off">
        <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;"> 
            <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
        </div>
    </td></tr>
	<tr class="alt">
        <td align="left">Period</td>
        <td align="left">
			<input type="text" size=22 id="start_date" name="start_date" value="<?php echo $today_str?>">
			&nbsp;to&nbsp;
			<input type="text" size=22 id="end_date" name="end_date" value="<?php echo $day_until_str?>" >
			<script>
				$('#start_date').AnyTime_picker({format: "%e-%b-%Y %H:%i "});
				$('#end_date').AnyTime_picker({format: "%e-%b-%Y %H:%i"});
			</script>
		</td>
	</tr>
	<tr class="alt">
        <td align="left">Purpose</td>
        <td align="left">
            <input type="text" size=55 name="purpose" id="purpose_loan" onKeyUp="suggest_purpose(this, this.value);" autocomplete="off" >
                <div class="suggestionsBox" id="suggestions_loan" style="display: none; z-index: 500;"> 
                <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                <div class="suggestionList" id="suggestionsList_loan"> &nbsp; </div>
            </div>
        </td>
      </tr>
      <tr>
        <td align="left">Remarks / <br> Special Requirements</td>
        <td align="left"><textarea rows=3 cols=70 name="remark"></textarea></td>
      </tr>
    

    <tr><td colspan=2 align="right"><br/><input type="image" name="submit" id="submit" onclick="return confirm_this()" src="images/submit.png" /><br/>&nbsp;</td></tr>
</table>

<?php
    } // manage    
    else {
        if (count($items) == 0)
            echo 'Scan an item: ';
        else
            echo 'Scan another item: ';
?>
    <br/>    
    <br/>    
    <input type="text" id="input" name="input" class="inputbox" autocomplete="off" onkeyup="check_entry()">
<?php
    if (count($items)>0){
        echo '<br/>&nbsp;<br/>or click <a href="javascript:manage_this()" class="button manage">Manage</a> to proceed';

    } // item   > 0
    }
?>
</form>
</div>

<script type="text/javascript">

var serial_length = <?php echo EXPENDABLE_LENGTH ?>;
var need_signature = '<?php echo $need_signature ?>';
var list = $('#items').val();
var items = $.parseJSON(list);
function insert_quantity(id){
	$('#qty-'+id).show();
	$('#q-'+id).hide();
}

$('input[id|="qty"]').blur(function(){
	update_quantity($(this).data('id'),$(this).val(),$(this).attr('max'));
	// alert($(this).val());
});
function update_quantity(id,newqty,max)
{
	newqty = parseInt(newqty);
	max = parseInt(max);
   
	var new_items = [];
	
	for(i = 0; i< items.length;i++){
		if(items[i][0] == id){
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

function del_this(id)
{
    $('#del_id').val(id);
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


function manage_this()
{
    if (confirm("Are you sure confirm this loan?")){
        $('#manage').val(1);
        $('form').submit();
    }
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
    if (v.length >= serial_length)
		$('form').submit();    
    
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
