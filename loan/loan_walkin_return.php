<?php 
$_category = (!empty($_POST['id_category'])) ? $_POST['id_category'] : -1;
$_id_user = isset($_POST['id_user']) ? $_POST['id_user'] : 0;
$_input = isset($_POST['input']) ? $_POST['input'] : null;
$_nric = isset($_POST['nric']) ? $_POST['nric'] : null;
$_items = isset($_POST['items']) ? $_POST['items'] : null;
$_del_id = isset($_POST['del_id']) ? $_POST['del_id'] : 0;
$_manage = !empty($_POST['manage']) ? ($_POST['manage'] == 1) : false;
$items = json_decode($_items);
$dept = USERDEPT;
$msg = null;

$category_list = !empty($_POST['category_list']) ? json_decode($_POST['category_list']) : array();

include_once './item/item_util.php';
include_once './loan/loan_util.php';

$user = array();
if(isset($_nric)){
	$user = get_user_by_nric($_nric);
	if(!empty($user)){
		$_id_user = $user['id_user'];
	}
}

////////////////////////////////////////
// $quantities = array();
// $hidden_fields = null;
// if ($_input != null){ // scan item
    // $item = get_item_by_serial($_input);
	
    // if (!empty($item['id_item'])){
		// $items[] = $item['id_item'];
    // }
	
	// if(empty($category_list)){
		// $category_list[] = array((int)$item['id_category'], 1);
	// }
	// else{
		
		// $cat_key = 0;
		
		// foreach($category_list as $key => $row){
			// if($row[0]==(int)$item['id_category']){
				// $cat_key = $key;
				// $category_list[$cat_key][1] += 1;
				
			// }
			// else{
				// $category_list[] = array((int)$item['id_category'], 1);
			// }
		// }
	// }
// }
// $cat_list = array();
// $total_quantity = 0;
// $cat_list_name = array();
// foreach($category_list as $key =>$row){
	
	// if(isset($cat_list[$row[0]])||$row[0] == null){
		// continue;
	// }
	// else{
		// $cat_list[$row[0]] = $row[1];
		// $cat_list_name[] = get_category_name($row[0]);
	// }
	// $total_quantity +=$row[1];
	
// }

// $new_item = array();
// foreach($items as $key=>$row){
	// if(($_del_id - 1) == $key){
		
		// continue;
	// }
	// else{
		// $new_item[] = $row;
	// }
// }

// $items = $new_item;

// $items_list = array();
// foreach($items as $row){
	// $items_list[] = $row[0];
// }

// $scanned_list = '<div id="itemspace"></div>';

// if (count($items) > 0){
	
    
	
	
	// $scanned_list  = '<table class="consumable_item_list"  width="800" style="color: #000; ">';
		// $scanned_list .= '<tr><th width=30>No</th><th width=120>Serial No.</th>
							  // <th>Model No</th><th>Category Name</th><th>Department Name</th><th width=20></th></tr>';
	// $no = 1;
	// foreach($items as $key => $row){
		// $query = get_item($row[0]);
			
			
			// $dellink = (!$_manage) ? '<a class="button delete" href="javascript:void(0)" onclick="del_this(' . ($key+1) . ')">x</a>' : '';
			// $scanned_list .= '<tr><td align="center">' . ($no++) . '.</td><td>' .
				// $query['serial_no'] . '</td><td>' . $query['model_no'] . '</td><td>'.$query['category_name'].'</td><td>'.$query['department_name'].'</td><td>' . $dellink . '</td></tr>';
		
	// }
	// $scanned_list .= '</table><br/>';
	
// }

// if (!empty($_POST['submitcode'])){
    // $start_date = convert_date($_POST['start_date'], 'Y-m-d H:i:s');
    // $end_date = convert_date($_POST['end_date'], 'Y-m-d H:i:s');
    // $quantity = (isset($total_quantity)) ? $total_quantity : 0;
    // $fullname = $_POST['requester'];
    // $long_term = $_POST['longterm'];
    // $userid = get_user_id_by_fullname($fullname);
	
	// if(count($cat_list)>1){
	// $id_cat =  0;
	// }else{
	// $cl = $cat_list;
	// reset($cl);
	// $id_cat = key($cl);
	// }
	
    // if ($userid > 0){
        // $query = "INSERT INTO loan_request(requester, id_category, start_loan, end_loan, 
                    // quantity, remark, request_date, status, without_approval, long_term) 
                    // VALUES ($userid, $id_cat, '$start_date', '$end_date',
                    // $quantity, '$_POST[remark]', now(), 'PENDING', 1, '$long_term')"; 

        // mysql_query($query);
        // if (mysql_affected_rows() > 0) {
            // $submitted = true;
            // $id = mysql_insert_id(); 
				// foreach($cat_list as $key => $row){
				// $query = "INSERT INTO loan_request_category(id_loan, id_category) 
                    // VALUES ($id, $key)"; 
				
				// mysql_query($query);
				// }
				// foreach($items as $row){
					// $query = "INSERT INTO loan_item(id_loan, id_item) VALUES ($id, $row)"; 
					// mysql_query($query);
				// }
            // ob_clean();
            // header('Location: ./?mod=loan&sub=loan&act=issue&id=' . $id);
            // ob_end_flush();
            // exit;
            
        // }
    // } else
        // $msg = "Please put in correct user's full name";
// }

// $today  = time();
// $today_str = date('j-M-Y H:i', $today);
// $day_until = strtotime('+1 day', $today);
// $day_until_str = date('j-M-Y H:i', $day_until);

?>

<style type="text/css">
  #start_date { background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
  #end_date { background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
	#form_loan {
		color: #fff;
	}
	.consumable_item_list td {
	color: #fff;
}
</style>
<div >
     &nbsp; <br/>
	 <form method="post" id="form_loan">
	<div>
        <a href="./?mod=loan&sub=loan&act=walkin">Loan Request</a> | 
        <a href="./?mod=loan&sub=loan&act=walkin_return">Loan Return</a>
     </div>
     <h2>Walk-in Return</h2>
	  <input type="hidden" id="items" name="items" value='<?php if(!empty($items))echo json_encode($items)?>'/>
		 <input type="hidden" id="nric" name="nric" value="<?php echo $_nric?>"/>
		 <input type="hidden" id="category_list" name="category_list" value="<?php echo json_encode($category_list)?>"/>
		 <input type="hidden" id="cat_list" name="cat_list" value="<?php echo serialize($cat_list)?>"/>
		 <input type="hidden" id="del_id" name="del_id" value="0"/>
		 <input type="hidden" id="manage" name="manage" value="0">
		 <?php if(isset($_nric) && isset($_id_user)&& !empty($_id_user)){
			echo '<h2>Welcome '.$user['full_name'].'</h2>';
			$loan_list = get_loan_request($user['id_user']);
			if(count($loan_list)>0){
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
            echo ' <a href="./?mod=loan&sub=loan&act=return&id='.$rec['id_loan'].'" title="return" ><img class="icon" src="images/undo.png" alt="return"></a> ';
        }
	echo " </td></tr>";
			}
	?>
	</table>
	<?php 
		}
		else{
			echo "<p style='color:yellow'>You don't have any loaned item now</p>";
		}
		}
		else
		{
			?>
			<h4>Scan Your NRIC</h4>
			<br/>    
			<br/>    
			<input type="text" id="input" name="nric" class="inputbox" autocomplete="off" onkeyup="check_entry()">
		<?php
		}
		
	?>
     <!--<form method="post" id="form_loan">
     -->
     </form>
     &nbsp; <br/>
     <!--
     <div class="note"><?php echo @$messages['walkin_request_note']?>  </div>
     -->
  </div>
<script>
var nric_length = <?php echo NRIC_LENGTH?>;
var serial_length = <?php echo SERIAL_LENGTH?>;
$('.inputbox').focus();
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


function del_this(row)
{
    $('#del_id').val(row);
    $('form').submit();
}

function manage_this()
{
    if (confirm("Are you sure confirm this loan?")){
        $('#manage').val(1);
        $('form').submit();
    }
}
function submit_request(frm)
{
    if (confirm("Are you sure make this request?")){
		
        frm.submitcode.value = 'submit';    
        frm.submit();
   }
}

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
                var pos =  $('#requester').offset();                       
                var w = $('#requester').width();
                var h = $('#requester').height();                                              
                $('#suggestions').css('position', 'absolute');
                $('#suggestions').offset({left:pos.left, top:pos.top + h + 5});
                $('#suggestions').width(w);
            }
        });
    }
}
<?php
if ($msg != null)
    echo 'alert("'.$msg.'");';
?>
</script>
<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px;}
</style>
