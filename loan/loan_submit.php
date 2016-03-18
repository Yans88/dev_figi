<?php
if (!defined('FIGIPASS')) return;
if (!$i_can_create) {
    include 'unauthorized.php';
    return;
}

$my_nric = NRIC;
$draft = check_draft($my_nric);
if(!empty($draft)){
	$id_loan = $draft;
	redirect("./?mod=loan&sub=loan&act=draft&id=$id_loan");
}


$_msg = null;

$this_time = time();
$this_day = date('N');
$next_48h = strtotime('+48 hours 5 minutes');
switch ($this_day ) {
case 4:
case 5:
case 6: $next_48h  = strtotime('+2 days', $next_48h); break;
case 7: $next_48h  = strtotime('+1 days', $next_48h); break;
}

$next_48h_day = date('N', $next_48h);

$next_two_day_str = date('j-M-Y H:i', $next_48h);
$day_until = strtotime('+1 day', $next_48h);
$day_until_str = date('j-M-Y H:i', $day_until);

$loan_type = 'HARDWARE';

if (isset($_POST['submitcode']) && $_POST['submitcode']  == 'submit') {
    $userid = USERID;
    if ($_POST['type'] == 'SERVICE'){
        $start_date = convert_date($_POST['service_date'], 'Y-m-d H:i:s');
        $end_date = $start_date;
        $loan_type = 'SERVICE';
    } else {
        $start_date = convert_date($_POST['start_date'], 'Y-m-d H:i:s');
        $end_date = convert_date($_POST['end_date'], 'Y-m-d H:i:s');
    }
    $quantity = (!empty($_POST['quantity'])) ? $_POST['quantity'] : 0;
    $without_approval = (REQUIRE_LOAN_APPROVAL) ? 1 : 0;
    $status = (REQUIRE_LOAN_APPROVAL) ? 'PENDING' : 'APPROVED' ;
    $query = "INSERT INTO loan_request(requester, id_category, start_loan, end_loan, 
            quantity, remark, request_date, status, without_approval) 
            VALUES ($userid, $_POST[id_category], '$start_date', '$end_date',
            $quantity, '$_POST[remark]', now(), '$status', $without_approval)"; 

    mysql_query($query);
   // echo mysql_error().$query;
    if (mysql_affected_rows() > 0) {
        $submitted = true;
        $_id = mysql_insert_id();
              
        // sending email notification 
        send_submit_request_notification($_id);
    
  } else
    $submitted = false;

}
?>
<script>
function changetab(me, name){
  var id = 'tab_' + name;
  var tab = document.getElementById(id);
  var divs = document.getElementById('tabset').getElementsByTagName('div');
  for (i=0; i<divs.length; i++){
    if (divs[i].className == 'tabset_content')
      divs[i].style.display = 'none';
  }
 
  tab.style.display = 'block';
  var ass = document.getElementById('buttonbox1').getElementsByTagName('a');
  for (i=0; i<ass.length; i++){
      ass[i].className = '';
  }

  me.className = 'active';
}

var min_date = <?php echo $next_48h ?>;

function more_than_48(dtstr){
  var months = new Array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
  var result = false;
  var dtntm = dtstr.split(' ');
  var adt = dtntm[0].split('-');
  var atm = dtntm[1].split(':');
  
  var dttm = new Date(adt[2], months.indexOf(adt[1]), adt[0], atm[0], atm[1]);
  var now = new Date();
  var h48 = 48 * 60 * 60 * 1000;
  
  var text  = "Your date loan/service is less than 48-hours from date of request. \n";
      text += "              Please contact HOD IT directly.                      \n";                        
      text += "                        Thank You.                                  ";

  //if (dttm.getTime()-now.getTime() > h48)
  if ((dttm.getTime()/1000)>min_date)
    result = true;
  else
    show_message('lt48')
    return result
}

function submit_request(frm){
  var check_date
  var do_submit = false;
  var qty
  if (frm.type.value == 'SERVICE'){
    check_date = frm.service_date.value
    
  } else  {
    check_date = frm.start_date.value
    qty = parseInt(frm.quantity.value)
    if (isNaN(qty) || (qty < 1)) {      		
      alert('Please fill in correct quantity to complete the request!');
      return;
    } else do_submit = true;
  }
  if (frm.remark.value == '') {
    alert('Please fill in the remark to complete the request!');
    return;
  } else do_submit = true;
  do_submit = (more_than_48(check_date));  
    
  if (do_submit){
    frm.submitcode.value = 'submit'
    frm.submit()
   }
}

function show_message(id){
	var div = document.getElementById(id)
	var sw = document.body.clientWidth
	var sh = document.body.clientHeight
	if (div) {
		div.style.display = '';
		div.style.left = ((sw - div.offsetWidth) / 2) + 'px';
		div.style.top  = ((sh - div.offsetHeight) / 2) +30+ 'px';
	}
}

function hide_message(id){
	var div = document.getElementById(id)	
	if (div) {
		div.style.display = 'none';
	}
}
</script>

<style>
#tabset {
    width: 700px; 
}
.buttonbox { 
  list-style: none;
  text-align: left; 
  font-size: 18px; 
  font-weight: bold;
  border: 0 white solid;
  padding-top: 2px;
  padding-bottom : 2px;
  padding-left: 0;
  }
.buttonbox li {
  display: inline;
  }
ul.buttonbox > li > a {
  background-color: #637b63; 
  padding: 2px 15px 3px 15px;
  margin: 0 -4px 0 0;
  font-size : 12pt;
  text-decoration: none;
  }
.buttonbox  a:hover {
  background-color: #516151; 
  }
.buttonbox  a:active {
  background-color: #fff; 
  color: #000;
  }
.buttonbox  a.active {
  background-color: #fff; 
  color: #000;
  }

.tabset_content { 
  width: 700px; 
  border: 0 blue solid;
  background-color: #fff;
  margin-top: -18px;
}
#tab_service {
  display: none;
  }
.notify {
	border: 2px maroon solid;
	width : 400px;
	font-size: 18pt;
	font-weight: bold;
	color: navy;
	text-align: center;
	vertical-align: middle;
	background-color: #fff;
	position: absolute;
	padding: 10px;
}
</style>

<br/>
<h2 style="margin-top:-10px">FIGI Loan, Service and Facility Portal</h2>
<div id="tabset">
  <ul class="buttonbox" id="buttonbox1">
    <li><a href="#" id="loan" onclick="changetab(this,'loan')"  >Loan</a></li>
    <li><a href="#" id="service" onclick="changetab(this,'service')" >Service</a></li>
    <li><a href="#" id="facility" onclick="changetab(this,'facility')" >Facility</a></li>
  </ul>
  <div id="tab_loan" class="tabset_content">
     &nbsp; <br/>
     <form method="post">
     <input type=hidden name=type value="EQUIPMENT">
     <input type=hidden name=submitcode value="">
     <table width="98%" class="itemlist" cellpadding=4 cellspacing=1 style="border: 1px solid #103821">
      <tr>
        <td width=130 align="left">Category</td>
        <td align="left"><?php echo build_category_combo('EQUIPMENT')?></td>
      </tr>
      <tr class="alt">
        <td align="left">Period</td>
        <td align="left">
          <input type="text" size=16 id="start_date" name="start_date" value="<?php echo $next_two_day_str?>">
		  <button id="button_start_date"><img src="images/cal.jpg" alt="[calendar icon]"/></button>
		  <script>
			$('#button_start_date').click(
			  function(e) {
				$('#start_date').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y %H:%i"}).focus();
				e.preventDefault();
			  } );
		  </script>          
		  &nbsp;to&nbsp;
          <input type="text" size=16 id="end_date" name="end_date" value="<?php echo $day_until_str?>">
		  <button id="button_end_date"><img src="images/cal.jpg" alt="[calendar icon]"/></button>
		  <script>
			$('#button_end_date').click(
			  function(e) {
				$('#end_date').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y %H:%i"}).focus();
				e.preventDefault();
			  } );
		  </script>
  </td>
      </tr>
      <tr>
        <td align="left">Quantity</td>
        <td align="left"><input type="text" size=6 name="quantity" value=1></td>
      </tr>
      <tr class="alt">
        <td align="left">Remarks / <br> Purpose of Use / <br> Special Requirements</td>
        <td align="left"><textarea rows=7 cols=63 name="remark"></textarea></td>
      </tr>
      <tr>
        <td colspan=2 align="right"><input type="button" value="  Submit Request  " onclick="submit_request(this.form)"></td>
      </tr>
     </table>
     </form>
     &nbsp; <br/>
  </div>
  <div id="tab_service" class="tabset_content">
    &nbsp; <br/>
     <form method="post">
     <input type=hidden name=type value="SERVICE">
     <input type=hidden name=submitcode value="">
     <table width="98%" class="itemlist" cellpadding=4 cellspacing=1 style="border: 1px solid #103821">
      <tr>
        <td align="left" width=130>Category</td>
        <td align="left"><?php echo build_category_combo("SERVICE")?></td>
      </tr>
      <tr class="alt">
        <td align="left">Date</td>
        <td align="left">
          <input type="text" size=16 id="service_date" name="service_date" value="<?php echo $next_two_day_str?>" >
		  <button id="button_service_date"><img src="images/cal.jpg" alt="[calendar icon]"/></button>
		  <script>
			$('#button_service_date').click(
			  function(e) {
				$('#service_date').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y %H:%i"}).focus();
				e.preventDefault();
			  } );
		  </script>
        </td>
      </tr>
      </tr>
      <tr>
        <td align="left">Remarks / <br> Purpose of Use / <br> Special Requirements</td>
        <td align="left"><textarea rows=9 cols=63 name="remark"></textarea></td>
      </tr>
      <tr class="alt">
        <td colspan=2 align="right"><input type="button" value="  Submit Request  " onclick="submit_request(this.form)"></td>
      </tr>
     </table>
     </form>
     &nbsp; <br/>
  </div>
  <div id="tab_facility" class="tabset_content">
    &nbsp; <br/>
     <form method="post">
     <input type=hidden name=type value="SERVICE">
     <input type=hidden name=submitcode value="">
     <table width="98%" class="itemlist" cellpadding=4 cellspacing=1 style="border: 1px solid #103821">
      <tr>
        <td align="left" width=130>Category</td>
        <td align="left"><?php echo build_category_combo("SERVICE")?></td>
      </tr>
      <tr class="alt">
        <td align="left">Date</td>
        <td align="left">
          <input type="text" size=16 id="service_date" name="service_date" value="<?php echo $next_two_day_str?>" >
		  <button id="button_service_date"><img src="images/cal.jpg" alt="[calendar icon]"/></button>
		  <script>
			$('#button_service_date').click(
			  function(e) {
				$('#service_date').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y %H:%i"}).focus();
				e.preventDefault();
			  } );
		  </script>
        </td>
      </tr>
      </tr>
      <tr>
        <td align="left">Remarks / <br> Purpose of Use / <br> Special Requirements</td>
        <td align="left"><textarea rows=9 cols=63 name="remark"></textarea></td>
      </tr>
      <tr class="alt">
        <td colspan=2 align="right"><input type="button" value="  Submit Request  " onclick="submit_request(this.form)"></td>
      </tr>
     </table>
     </form>
     &nbsp; <br/>
  </div>

  <div class="note"><?php echo get_text('loan_submit_note')?>  </div>
</div>

<br/>
<div class="notify" id="lt48" style="display: none;">
Your date loan/service is less than 48-hours from date of request. Please contact HOD IT directly.
Thank You.
  <br/><br/><input type="button" value="   Ok  " onclick="hide_message('lt48')">
</div>
<div class="notify" id="failed" style="display: none;">
Your request failed to proceed. Please contact HOD IT directly.
Thank You.
  <br/><br/><input type="button" value="   Ok  " onclick="hide_message('failed')">
</div>
<div class="notify" id="success" style="display: none;">
Your request has been successfully submitted.<br/>
Please check your mailbox regularly for the latest status of your loan Request.<br/>
Thank You.
  <br/><br/><input type="button" value="   Ok  " onclick="hide_message('success')">
</div>

<script>
//update_category_list('loan');
  if ('<?php echo $loan_type?>' == 'SERVICE')
	changetab(document.getElementById('service'),'service')
  else
	changetab(document.getElementById('loan'),'loan')
  AnyTime.picker( "service_date", { format: "%e-%b-%Y %H:%i", firstDOW: 1 } );
<?php 
    if (isset($submitted)) {
        if ($submitted) 
            echo "\nshow_message('success');\n";
        else
            echo "\nshow_message('failed');\n";
    }
?>
</script>
