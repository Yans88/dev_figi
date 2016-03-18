<?php
include_once './item/item_util.php';
include_once './loan/loan_util.php';

$config = $configuration['loan'];

$_msg = null;
if (!empty($_POST)){
  $ok= loan_save_request();
  ob_clean();
  echo ($ok) ? 'OK' : 'ERR';
  ob_end_flush();
  exit;
}
/*
$rs = mysql_query('SELECT NOW()');
$row = mysql_fetch_row($rs);
echo $row[0].date('Y-m-d H:i:s');
*/
$my_nric = NRIC;
function loan_save_request(){
	
    $userid = USERID;
    $start_date = convert_date($_POST['start_date'], 'Y-m-d H:i:s');
    $end_date = convert_date($_POST['end_date'], 'Y-m-d H:i:s');
    $quantity = (!empty($_POST['quantity'])) ? $_POST['quantity'] : 0;
    $without_approval = (REQUIRE_LOAN_APPROVAL) ? 0 : 1;
    $remark = mysql_real_escape_string($_POST['remark']);
    $purpose = mysql_real_escape_string($_POST['purpose']);
    $status = 'PENDING'  ;
    //$status = (REQUIRE_LOAN_APPROVAL) ? 'PENDING' : 'APPROVED' ;
    $query = "INSERT INTO loan_request(requester, id_category, start_loan, end_loan, 
				quantity, remark, purpose, request_date, status, without_approval, id_department) 
				VALUES ($userid, $_POST[id_category], '$start_date', '$end_date',
				$quantity, '$remark', '$purpose',  now(), '$status', $without_approval, '$_POST[id_department]')"; 

    $submitted = false;
	mysql_query($query);
    //echo mysql_error().$query;
    if (mysql_affected_rows() > 0) {
        $submitted = true;
        $_id = mysql_insert_id();              
        // sending email notification 
        send_submit_request_notification($_id);
        
    }
	return $submitted;
}

$draft = check_draft($my_nric);
if(!empty($draft)){
	$id_loan = $draft;
	redirect("./?mod=loan&sub=loan&act=draft&id=$id_loan");
}



$lead_time = (ENABLE_REQUEST_LEADTIME) ? get_lead_time($config['request_leadtime']) : time();
$next_two_day_str = date('j-M-Y H:i', $lead_time);
$day_until = strtotime('+1 day', $lead_time);
$day_until_str = $next_two_day_str;//date('j-M-Y H:i', $day_until);
$_department = (!empty($_POST['id_department'])) ? $_POST['id_department'] : 0;
$_category = (!empty($_POST['id_category'])) ? $_POST['id_category'] : -1;
$department_list = array('0' => '-- select a department --') + get_department_list();

$dkeys = array_keys($department_list);
$first_dkey = !empty($dkeys[0]) ? $dkeys[0] : 0;
$messages['loan_request_note'] = get_text('loan_submit_note');
?>
<link rel="stylesheet" type="text/css" href="./style/default/anytimec.css" />
<script type="text/javascript" src="./js/anytimec.js"></script>

<style>
#start_date, #end_date { 
	background-image:url("images/cal.jpg");
	background-position:right center; background-repeat:no-repeat;
	border:1px solid #5FC030;color:#000;font-weight:normal
}

</style>

<div style="margin-bottom: 30px"></div>
<div id="tab_loan" class="tabset_content">
    &nbsp;
     <div class="leftcol" style="width: 260px; text-align: left; padding-left: 5px" ><h2 style="color: #000; display: inline">Loan Request Form</h2></div>
     <div class="submenu" style="float: right">
        <a href="./?mod=portal&portal=loan">Loan Request Form</a> | 
        <a href="./?mod=portal&sub=history&portal=loan">Loan Request History</a>
     </div>
     <div class="clear"></div>
     <form method="post" id="form_loan" action="<?php echo $_SERVER['SCRIPT_NAME']?>">
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
          <input type="text" size=24 id="start_date" name="start_date" value="<?php echo $next_two_day_str?>">
		  &nbsp;to&nbsp;
          <input type="text" size=24 id="end_date" name="end_date" value="<?php echo $day_until_str?>" >
		  <script type="text/javascript">
                var lt = new Date(<?php echo $lead_time*1000;?>);
                var dFormat = "%e-%b-%Y %H:%i";
                $('#start_date').AnyTime_noPicker().AnyTime_picker({format: dFormat, earliest: lt});
                $('#start_date').change(function (e){
                    try {
                        var oneDay = 0;//24*60*60*1000;
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


var months = new Array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
var min_date = <?php echo $lead_time ?>;
var lt48 = "<?php echo preg_replace('/[\r\n]/', "\\\r\n", $messages['loan_request_at_least']) ?>";
var check_leadtime = <?php echo (ENABLE_REQUEST_LEADTIME) ? 1 : 0?>;


function department_change(sect, cat){
    var d = $('#dept_'+sect)[0];
    var did = d.options[d.selectedIndex].value;
    $('#submit_'+sect).attr("disabled","disabled");
    $.post("./item/get_category_by_department.php", {queryString: ""+did+"",type: ""+sect+""}, function(data){
        
        if(data.length >0) {
            $('#cat_'+sect).empty();
            $('#cat_'+sect).append('<option value=0> -- select a category --</option>');
            $('#cat_'+sect).append(data);
            
            var c = document.getElementById('cat_'+sect);
            if ((c.options.length > 0)){
              $('#submit_'+sect).removeAttr("disabled");
              {
                      for (var i=0; i<c.options.length; i++){
                        if (c.options[i].value == cat){
                            c.options[i].selected = true;
                        }
                    }
                  if (parseInt(cat==0)) c.selectedIndex = 0;                    
                } 
            } //else
              
        } else {
            $('#cat_'+sect).empty();
            $('#cat_'+sect).append('<option value=0> -- category is not available --</option>');
        }
    });
}

function submit_request(frm){
  var check_date;
  var do_submit = false;
  var qty;
  
  var target = document.getElementById('submit_loan');
    
  if (frm.portal.value == 'SERVICE'){
    check_date = frm.service_date.value
    
  } else  {
    check_date = frm.start_date.value
    qty = parseInt(frm.quantity.value)
    if (isNaN(qty) || (qty < 1)) {      		
      alert('Please fill in correct quantity to complete the request!');
      return;
    } else do_submit = true;
  }
  if (frm.purpose.value == '') {
    alert('Please fill in the purpose of loan to complete the request!');
    return;
  } else do_submit = true;
  //if (check_leadtime == 1) do_submit = (more_than_48(check_date));  

  if (do_submit){
    
    var spinner = new Spinner(opts).spin(target);

    var url = '<?php echo $_SERVER['REQUEST_URI']?>';
    $.post(url, $('#form_loan').serialize(), function(data){
      if (data == 'OK') {
       
        //var buttons = {'Close': function(e){$('#msgok').dialog('close');}};
        $('#msgok').dialog({
                modal: true, 
                title: 'Request Info', width: 350, height: 120,
                close: function(){
                  location.href = './?mod=portal&portal=loan';
                }
			  });
      } else {
        //var buttons = {'Close': function(e){$('#msgerr').dialog('close');}};
        $('#msgerr').dialog({
                modal: true, 
                title: 'Request Info', width: 350, height: 120,
                close: function(){
                  location.href = './?mod=portal&portal=loan';
                }
			  });
        
        }
        spinner.stop();
    } );
   } 
   
}

function fill(id, thisValue, onclick) {
    $('#'+id).val(thisValue);
    var suggest_for = id.substring(8);
    setTimeout("$('#suggestions_"+suggest_for+"').fadeOut();", 100);
}

function suggest(me, inputString){
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

$('#cat_loan').change(function(){
    if ($(this).val()>0) $('#submit_loan').removeAttr("disabled")
    else $('#submit_loan').attr("disabled", "disabled");
})  

$('#dept_loan').change(function(){department_change('loan')});

department_change('loan');

</script>

