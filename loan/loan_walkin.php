<?php 
$my_nric = NRIC;
$_category = (!empty($_POST['id_category'])) ? $_POST['id_category'] : -1;
$_id_user = isset($_POST['id_user']) ? $_POST['id_user'] : 0;
$_input = isset($_POST['input']) ? $_POST['input'] : null;
$_nric = isset($_POST['nric']) ? $_POST['nric'] : null;
$_items = isset($_POST['items']) ? $_POST['items'] : '';
$_manage = !empty($_POST['manage']) ? ($_POST['manage'] == 1) : false;
$items = json_decode($_items);
$dept = USERDEPT;
$msg = null;

$category_list = !empty($_POST['category_list']) ? json_decode($_POST['category_list']) : array();

include_once './item/item_util.php';
include_once './loan/loan_util.php';

$draft = check_draft($my_nric);
if(!empty($draft)){
	$id_loan = $draft;
	redirect("./?mod=loan&sub=loan&act=draft&id=$id_loan");
}


function get_item_by_asset_serial($no, $dept=0)
{
	
	$result = array();
    $query = "SELECT *, date_format(date_of_purchase, '%d-%b-%Y') date_of_purchase, location_name  
                FROM item i 
                LEFT JOIN category c ON c.id_category = i.id_category 
                LEFT JOIN brand b ON b.id_brand = i.id_brand 
                LEFT JOIN vendor v ON v.id_vendor = i.id_vendor 
                LEFT JOIN location l ON l.id_location = i.id_location 
                WHERE i.id_department=$dept AND (asset_no = '$no' OR serial_no = '$no')";
    $rs = mysql_query($query);
    //echo $query;
    if ($rs && mysql_num_rows($rs)>0)
        $result = mysql_fetch_assoc($rs);

    return $result;
	
}
$name = '';
$user = array();
if(!empty($_nric)){
	$user = get_user_by_nric($_nric);
	if(!empty($user)){
		$_id_user = $user['id_user'];
		$name = $user['full_name'];
	}
}

////////////////////////////////////////
//$items= array();

$hidden_fields = null;
if ($_input != null){ // scan item
    $item = get_item_by_asset_serial($_input, $dept);
	
    if (!empty($item['id_item'])){
		$items[] = $item['id_item'];
    } else $item_is_not_found = true;
	
	if(empty($category_list)){
		$category_list[] = array((int)$item['id_category'], 1);
	}
	else{
		
		$cat_key = 0;
		
		foreach($category_list as $key => $row){
			if($row[0]==(int)$item['id_category']){
				$cat_key = $key;
				$category_list[$cat_key][1] += 1;
				
			}
			else{
				$category_list[] = array((int)$item['id_category'], 1);
			}
		}
	}
}
$cat_list = array();
$total_quantity = 0;
$cat_list_name = array();
foreach($category_list as $key =>$row){
	
	if(isset($cat_list[$row[0]])||$row[0] == null){
		continue;
	}
	else{
		$cat_list[$row[0]] = $row[1];
		$cat_list_name[] = get_category_name($row[0]);
	}
	$total_quantity +=$row[1];
	
}

$new_item = array();
foreach($items as $key=>$row){
    $new_item[] = $row;
}

$items = $new_item;

$items_list = array();
foreach($items as $row){
	$items_list[] = $row[0];
}

$scanned_list = '<div id="itemspace"></div>';

if (count($items) > 0){
	$scanned_list  = '<table class="consumable_item_list"  width="100%" style="color: #000; ">';
		$scanned_list .= '<tr><th width=30>No</th><th width=120>Serial No.</th><th width=120>Asset No.</th>
							  <th>Model No</th><th>Category Name</th><th>Department Name</th><th width=20></th></tr>';
	$no = 1;
	//print_r($items);
	foreach($items as $key){
		$query2 = get_item($key); 
		$dellink = (!$_manage) ? '<a class="delete" href="javascript:void(0)" onclick="del_this(' . ($key+1) . ')">X</a>' : '';
		$scanned_list .= '<tr><td align="center">' . ($no++) . '.</td><td>'.$query2['serial_no'] . '</td><td>'.$query2['asset_no'] . '</td><td>' . $query2['model_no'] . '</td><td>'.$query2['category_name'].'</td><td>'.$query2['department_name'].'</td><td>' . $dellink . '</td></tr>';
		
	}
	$scanned_list .= '</table><br/>';
	
}
//error_log(serialize($_POST));
if (!empty($_POST['submitcode'])){
	
    $start_date = convert_date($_POST['start_date'], 'Y-m-d H:i:s');
    $end_date = convert_date($_POST['end_date'], 'Y-m-d H:i:s');
    $quantity = (!empty($total_quantity)) ? $total_quantity : 0;
	//$quantity = (isset($_POST['quantity'])) ? $_POST['quantity'] : 0;
	if(isset($_POST['quantity'])){
		$quantity = $_POST['quantity'];
	}
    $fullname = $_POST['requester'];
    $long_term = $_POST['longterm'];
    $userid = get_user_id_by_fullname($fullname);
	
	if(!isset($_POST['use_nric'])){
		if(count($cat_list)>1){
			$id_cat =  0;
		}
		else
		{
			$cl = $cat_list;
			reset($cl);
			$id_cat = key($cl);
		}
	}
	else
	{
		$id_cat = $_POST['id_category'];
	}
    if ($userid > 0){
	$purpose = mysql_real_escape_string($_POST['purpose']);
	$remark = mysql_real_escape_string($_POST['remark']);
        $query = "INSERT INTO loan_request(requester, id_category, start_loan, end_loan, 
                    quantity, purpose, remark, request_date, status, without_approval, long_term, id_department) 
                    VALUES ($userid, $id_cat, '$start_date', '$end_date',
                    $quantity, '$purpose', '$remark', now(), 'PENDING', 1, '$long_term', '$dept')"; 

        mysql_query($query);
		//error_log( mysql_error().$query);
        if (mysql_affected_rows() > 0) {
			
            $submitted = true;
            $id = mysql_insert_id(); 
				foreach($cat_list as $key => $row){
				$query = "INSERT INTO loan_request_category(id_loan, id_category) 
                    VALUES ($id, $key)"; 
				
				mysql_query($query);
				}
				foreach($items as $row){
					$query = "INSERT INTO loan_item(id_loan, id_item) VALUES ($id, $row)"; 
					mysql_query($query);
				}
            ob_clean();
            header('Location: ./?mod=loan&sub=loan&act=issue&id=' . $id);
            ob_end_flush();
            exit;
            
        }
    } else
        $msg = "Please put in correct user's full name";
}

$today  = time();
$today_str = date('j-M-Y H:i', $today);
$day_until = strtotime('+1 day', $today);
$day_until_str = date('j-M-Y H:i', $day_until);

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
<div class="center" >
	 
    <h2>Walk-in Request</h2>
	<!--
	<div>
        <a href="./?mod=loan&sub=loan&act=walkin">Loan Request</a> | 
        <a href="./?mod=loan&sub=loan&act=walkin_return">Loan Return</a>
     </div>
	 -->
     <?php
     	if(isset($_POST['nonric'])){
     ?>
     <div style="width:80%" class="middle">
     <form method="post" id="form_loan">
     <input type=hidden name=portal value="LOAN">
     <input type=hidden name=submitcode value="">
     <input type=hidden name=use_nric value="1">
     <input type="hidden" id="id_user" name="id_user" value="<?php echo $_id_user?>">
     <table class="itemlist middle" cellpadding=4 cellspacing=1 style="border: 1px solid #103821; width: 630px">
      <tr>
        <td width=130 align="left">Category</td>
        <td align="left">
		<select name="id_category" id="cat_loan" >
		<?php 
			echo build_option(get_category_list('EQUIPMENT', $dept), $_category)
			?>
		</select>
        </td>
      </tr>
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
      <tr>
        <td align="left">Requester</td>
        <td align="left"><input type="text" name="requester" value="<?php echo $name ?>" id="requester" autocomplete="off" size=30 
    onKeyUp="suggest(this, this.value);" onBlur="fill('requester', this.value);" >
   <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div></td>
      </tr>
      <tr class="alt">
        <td align="left">Quantity</td>
        <td align="left"><input type="text" size=6 name="quantity" value=1></td>
      </tr>
      <tr class="">
        <td align="left">Purpose of Use</td>
        <td align="left"><input type="text" name="purpose" style="width: 470px"></td>
      </tr>
      <tr class="alt">
        <td align="left">Remarks / <br> Special Requirements</td>
        <td align="left"><textarea rows=5 cols=65 name="remark">Walk-in request!
        </textarea></td>
      </tr>
      <tr >
        <td align="left">Long Term Usage</td>
        <td align="left">
            <input type="radio" name="longterm" value=0 onchange="change_term(this)" checked>No &nbsp;
            <input type="radio" name="longterm" value=1 onchange="change_term(this)">Yes
        </td>
      </tr>
      <tr class="alt">
        <td colspan=2 align="right"><button id="submit_loan" type="button" onclick="submit_request(this.form)">Continue to Loan-Out Issue</button></td>
      </tr>
     </table>
     </form>
     </div>
     <?php
     	}
		else{
     ?>
     <form method="post" id="form_loan">
	 <input type="hidden" name="requester" value="<?php echo $name ?>" >
	  <input type="hidden" id="items" name="items" value='<?php if(!empty($items))echo json_encode($items)?>'/>
		 <input type="hidden" id="nric" name="nric" value="<?php echo $_nric?>"/>
		 <input type="hidden" id="category_list" name="category_list" value="<?php echo json_encode($category_list)?>"/>
		 <input type="hidden" id="cat_list" name="cat_list" value="<?php echo serialize($cat_list)?>"/>
		 <input type="hidden" id="id_user" name="id_user" value="<?php echo $_id_user?>"/>
		 <input type="hidden" id="manage" name="manage" value="0">
		 <?php if($_manage){
			echo $scanned_list;
		 ?>
			<input type="hidden" name="portal" value="LOAN">
			<input type="hidden" name="submitcode" value="">
			 <table class="itemlist" cellpadding=4 cellspacing=1 style="border: 1px solid #103821; width: 630px">
			  <tr>
				<td width=130 align="left">Category</td>
				<td align="left" style="word-break: break-word">
				<?php 
				/*
				<!--<select name="id_category" id="cat_loan" >
					echo build_option(get_category_list('EQUIPMENT', $dept), $_category)
				</select>-->
					*/
					?>

				<?php echo implode(', ',$cat_list_name)?>
				</td>
			  </tr>
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
			 <tr class="">
				<td align="left">Quantity</td>
				<td align="left"><?php echo $total_quantity;?></td>
			  </tr>
			  <tr class="alt">
				<td align="left">Requester</td>
				<td align="left"><?php echo $name ?></td>
			  </tr>
			   <tr class="">
				<td align="left">Purpose of Use</td>
				<td align="left"><input type="text" value="" name="purpose" style="width: 470px"/>
				</td>
			  </tr>
			  <tr class="alt">
				<td align="left">Remarks / <br>Special Requirements</td>
				<td align="left"><textarea rows=4 cols=65 name="remark">Walk-in request!
				</textarea></td>
			  </tr>
			  <tr >
				<td align="left">Long Term Usage</td>
				<td align="left">
					<input type="radio" name="longterm" value=0 onchange="change_term(this)" checked>No &nbsp;
					<input type="radio" name="longterm" value=1 onchange="change_term(this)">Yes
				</td>
			  </tr>
			  <tr class="alt">
				<td colspan=2 align="right"><button id="submit_loan" type="button" onclick="submit_request(this.form)">Continue to Loan-Out Issue</button></td>
			  </tr>
			 </table>
				
		 <?php } else if(!empty($_id_user) && $_id_user>0){
			echo '<h2>Welcome '.$user['full_name'].'</h2>';
			if (count($items) == 0)
				echo '<span>Scan an item: </span>';
			else
				echo '<span>Scan another item: </span>';
		?>
			<br/>    
			<br/>    
			<input type="text" id="input" name="input" class="inputbox" autocomplete="off" onkeyup="check_entry()"/>
			<br/>
			<br/>
			<br/>
		<?php
			echo $scanned_list;
			if (count($items)>0){
				echo '<br/>&nbsp;<br/><p>or click<a onclick="manage_this()" class="button manage">Done</a> to proceed</p>';

			}
		}
		else
		{
			?>
			<h4>Scan Your NRIC</h4>
			<br/>    
			<br/>    
			<input type="text" id="input" name="nric" class="inputbox" autocomplete="off" onkeyup="check_entry()">
			<br/>
			<br/>
			<p>For Non-NRIC user click <input type="button" name="no-nric" value="HERE" onclick="no_nric_go()"></p>
		<?php
		}
		
	?>
     
     </form>
     <?php 
     	}
     ?>
     &nbsp; <br/>
     
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

function no_nric_go()
{
    $('form').append('<input type="hidden" name="nonric" value=1>');   
    $('form').submit(); 
}

function manage_this()
{
    //if (confirm("Are you sure confirm this loan?"))
	{
        $('#manage').val(1);
        $('form').submit();
    }
}
function submit_request(frm)
{
    var requester = $('#requester').val();
    if ((requester=='')){ // || id_user==undefined || id_user<=0
        alert('You must set requester that suggested by the system!');
        return false;
    }
	var purpose = $('input[name=purpose]').val();
    if (purpose.length==0){
        alert('Please enter purpose of the loan. It is mandatory!');
        return false;
	}
    //if (confirm("Are you sure make this request?"))
	{
		
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
               // alert(data)
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
if (isset($item_is_not_found)) $msg = 'Item scaned is not available!';
if ($msg != null)
    echo 'alert("'.$msg.'");';
?>
</script>
<style>
#suggestions { margin-top: 1px; }
#suggestionsList ul{ margin-top: 1px; margin-bottom: 1px;}
</style>
