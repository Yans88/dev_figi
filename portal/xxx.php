<?php
include_once './item/item_util.php';
include_once './loan/loan_util.php';


$_id_user = isset($_POST['id_user']) ? $_POST['id_user'] : 0;
$_input = isset($_POST['input']) ? $_POST['input'] : null;
$_nric = isset($_POST['nric']) ? $_POST['nric'] : null;
$_items = isset($_POST['items']) ? $_POST['items'] : null;
$_del_id = isset($_POST['del_id']) ? $_POST['del_id'] : 0;
$items = json_decode($_items);

$user = array();
if(isset($_nric)){
	$user = get_user_by_nric($_nric);
	if(!empty($user)){
		$_id_user = $user['id_user'];
	}
}

////////////////////////////////////////
$quantities = array();
$hidden_fields = null;
if ($_input != null){ // scan item
    $item = get_item_by_serial($_input);
	
    if (!empty($item['id_item'])){
		$items[] = $item['id_item'];
    }
}

$new_item = array();
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
	
	
	$scanned_list  = '<table class="item_list"  width="800" style="color: #000; ">';
		$scanned_list .= '<tr><th width=30>No</th><th width=120>Serial No.</th>
							  <th>Model No</th><th>Category Name</th><th>Department Name</th><th width=20></th></tr>';
	$no = 1;
	foreach($items as $key => $row){
		$query = get_item($row[0]);
			
			
			$dellink = '<a class="button delete" href="javascript:void(0)" onclick="del_this(' . ($key+1) . ')">x</a>';
			$scanned_list .= '<tr><td align="center">' . ($no++) . '.</td><td>' .
				$query['serial_no'] . '</td><td>' . $query['model_no'] . '</td><td>'.$query['category_name'].'</td><td>'.$query['department_name'].'</td><td>' . $dellink . '</td></tr>';
		
	}
	$scanned_list .= '</table><br/>';
	
}

?>
<style>
#start_date, #end_date { 
	background-image:url("images/cal.jpg");
	background-position:right center; background-repeat:no-repeat;
	border:1px solid #5FC030;color:#000;font-weight:normal
}
.item_list  td{
	border-left: black 1px solid;
}
.item_list td:first-child{
	border-left: none;
}
.item_list th{
	border-bottom: black 1px solid;
}
</style>

<div id="tab_loan" class="tabset_content">
    &nbsp;
     <div class="leftcol" style="width: 260px; text-align: left; padding-left: 5px" ><h2 style="color: #000; display: inline">Loan Request Form</h2></div>
     <div class="submenu" style="float: right">
        <a href="./?mod=portal&portal=loan">Loan Request Form</a> | 
        <a href="./?mod=portal&portal=return">Loan Return Form</a> | 
        <a href="./?mod=portal&sub=history&portal=loan">Loan Request History</a>
     </div>
     <div class="clear"></div>
		 <form method="post" id="form_loan" action="<?php //echo $_SERVER['SCRIPT_NAME']?>">
		 <input type="hidden" id="items" name="items" value='<?php if(!empty($items))echo json_encode($items)?>'>
		 <input type="hidden" id="nric" name="nric" value="<?php echo $_nric?>">
		 <input type="hidden" id="del_id" name="del_id" value="0"/>
			<?php if(isset($_nric) && isset($_id_user)&& !empty($_id_user)){
			echo '<h2 style="color: #000;">Welcome '.$user['full_name'].'</h2>';
			if (count($items) == 0)
				echo '<span style="color: #000; ">Scan an item: </span>';
			else
				echo '<span style="color: #000; ">Scan another item: </span>';
		?>
			<br/>    
			<br/>    
			<input type="text" id="input" name="input" class="inputbox" autocomplete="off" onkeyup="check_entry()">
			<br/>
			<br/>
			<br/>
		<?php
			echo $scanned_list;
			if (count($items)>0){
				echo '<br/>&nbsp;<br/><p style="color: #000">or click<a href="javascript:manage_this()" class="button manage">Done</a> to proceed</p>';

			}
		}
		else
		{
			?>
			<h4 style="color: #000; ">Scan Your NRIC</h4>
			<br/>    
			<br/>    
			<input type="text" id="input" name="nric" class="inputbox" autocomplete="off" onkeyup="check_entry()">
		<?php
		}
		
	?>
		</form>
     <!--<form method="post" id="form_loan" action="<?php echo $_SERVER['SCRIPT_NAME']?>">
     <input type=hidden name=portal value="LOAN">
     <input type=hidden name=submitcode value="">
     <table width="98%" class="itemlist" cellpadding=4 cellspacing=1 style="border: 1px solid #103821; padding: 2px 2px 2px 2px">
      <tr class="alt">
        <td width=130 align="left">Department</td>
        <td align="left">
		<select name="id_department" id="dept_loan" onchange="department_change('loan')">
		<?php echo build_option($department_list, $_department)?>
		</select>
	</td>
      </tr>
      <tr>
        <td width=130 align="left">Category</td>
        <td align="left">
		<select name="id_category" id="cat_loan" >
		<?php 
			//echo build_option(get_category_list('EQUIPMENT', $first_dkey), $_category)
			?>
		</select>
        </td>
      </tr>
      <tr class="alt">

        <td align="left">Period</td>
        <td align="left">
          <input type="text" size=20 id="start_date" name="start_date" value="<?php echo $next_two_day_str?>">
		  &nbsp;to&nbsp;
          <input type="text" size=20 id="end_date" name="end_date" value="<?php echo $day_until_str?>" >
		  <script type="text/javascript">
                var lt = new Date(<?php echo $lead_time*1000;?>);
                var dFormat = "%e-%b-%Y %H:%i";
                $('#start_date').AnyTime_noPicker().AnyTime_picker({format: dFormat, earliest: lt});
                $('#start_date').change(function (e){
                    try {
                        var oneDay = 24*60*60*1000;
                        var dConv = new AnyTime.Converter({format:dFormat});
                        var fromDay = dConv.parse($(this).val()).getTime();
                        var dayLater = new Date(fromDay+oneDay);
                        //dayLater.setHours(23,59,59,999);
                        $("#end_date").
                          AnyTime_noPicker().
                          removeAttr("disabled").
                          val(dConv.format(dayLater)).
                          AnyTime_picker({ earliest: dayLater, format: dFormat});
                        } catch(e){ $("#date_finish").val("").attr("disabled","disabled"); 
                    }     
                });
				//$('#end_date').AnyTime_picker({format: "%e-%b-%Y %H:%i"});
		</script>
		</td>
      </tr>
      <tr>
        <td align="left">Quantity</td>
        <td align="left"><input type="text" size=6 name="quantity" value=1></td>
      </tr>
      <tr class="alt">
        <td align="left">Purpose</td>
        <td align="left">
            <input type="text" size=55 name="purpose" id="purpose_loan" onKeyUp="suggest(this, this.value);" autocomplete="off" >
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
      <tr class="alt">
        <td align="left">Long Term Usage</td>
        <td align="left">
            <input type="radio" name="longterm" value=0 checked>No &nbsp;
            <input type="radio" name="longterm" value=1 >Yes
        </td>
      </tr>
      <tr>
        <td colspan=2 align="right"><button id="submit_loan" type="button" onclick="submit_request(this.form)">Submit Request</button></td>
      </tr>
     </table>
     </form>
	 -->
     <br/>
     <div class="note"><?php echo $messages['loan_request_note']?>  </div>
</div>
<div id='msgok' class='dialog ui-helper-hidden'>
    <div class="alertbox" style="text-align: center">
        <?php echo $messages['loan_request_success'];?>
    </div>
</div>
<div id="msgerr" class='dialog ui-helper-hidden'>
    <div class="alertbox" id="message" style="text-align: center">
        <?php echo $messages['loan_request_fail'];?> 
    </div>
</div>
<script type="text/javascript">
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

</script>