<div style="margin-bottom: 30px"></div>
<script type="text/javascript" src='./js/jquery.MultiFile.js' language="javascript"></script>
<?php
include_once './service/service_util.php';
include_once './item/item_util.php';
$config = $configuration['service'];
$st = '';

if (!empty($_POST) && !empty($_POST['submitcode'])){
    $ok = service_save_request();
	
	if($ok){
		
		foreach($_FILES['attachment']['name'] as $key => $row){
			
			$filesize = $_FILES['attachment']['size'][$key];
			$filename =  $_FILES['attachment']['name'][$key];
			$filetemp =  $_FILES['attachment']['tmp_name'][$key];
			$errorcode =  $_FILES['attachment']['error'][$key];
			
			if (($filesize > 0) && ($errorcode == 0)){
				
				$data_attach = base64_encode(file_get_contents($filetemp));
				save_attachment_req($ok,$filename,$data_attach,'SERVICE');
				
			}
		}
	}
	$st = ($ok) ? 'OK' : 'FAIL';
  
}

function service_save_request(){
    $userid = USERID;
    $start_date = convert_date($_POST['service_date'], 'Y-m-d H:i:s');
    $end_date = $start_date;
    $loan_type = 'SERVICE';
    $quantity = (!empty($_POST['quantity'])) ? $_POST['quantity'] : 0;
    $without_approval = (REQUIRE_SERVICE_APPROVAL) ? 1 : 0;
    $status = 'PENDING' ;
    $remark = mysql_real_escape_string($_POST['remark']);
    $purpose = mysql_real_escape_string($_POST['purpose']);
    //$status = (REQUIRE_SERVICE_APPROVAL) ? 'PENDING' : 'APPROVED' ;
    $query = "INSERT INTO loan_request(requester, id_category, start_loan, end_loan, 
                quantity, remark, purpose, request_date, status, without_approval) 
                VALUES ($userid, $_POST[id_category], '$start_date', '$end_date',
                $quantity, '$remark', '$purpose', now(), '$status', $without_approval)"; 

    @mysql_query($query);
    //echo mysql_error();
	// $submitted = false;
    if (mysql_affected_rows() > 0) {
        // $submitted = true;
        $_id = mysql_insert_id();
              
        // store additional field if any
        $_category = (!empty($_POST['id_category'])) ? $_POST['id_category'] : -1;
        $id_page = get_page_id_by_name('service');
        $field_list = get_extra_field_list($_category, $id_page);
        foreach ($field_list as $field){
            $fid = $field['id_field'];
            if (!empty($_POST['field-' . $fid])){
                save_extra_data($fid, $_POST['field-' . $fid]);
            }
        }
        // sending email notification 
        send_submit_service_request_notification($_id);
    }
	return $_id;
}


$lead_time = (ENABLE_REQUEST_LEADTIME) ? get_lead_time($config['request_leadtime']) : time();
$next_two_day_str = date('j-M-Y H:i', $lead_time);
$day_until = strtotime('+1 day', $lead_time);
$day_until_str = date('j-M-Y H:i', $day_until);
$_department = (!empty($_POST['id_department'])) ? $_POST['id_department'] : 0;
$_category = (!empty($_POST['id_category'])) ? $_POST['id_category'] : -1;
$department_list = array('0' => '-- select a department --') + get_department_list();
$dkeys = array_keys($department_list);
$first_dkey = !empty($dkeys[0]) ? $dkeys[0] : 0;
$id_page = get_page_id_by_name('service');
$category_list = get_category_list('SERVICE', $_department);
if (count($category_list) > 0) {
    if ($_category <= 0) {
        $cats = array_keys($category_list);
        $_category = $cats[0];
    }
} else
    $_category = -1;

?>

<link rel="stylesheet" type="text/css" href="./style/default/anytimec.css" />
<script type="text/javascript" src="./js/anytimec.js"></script>
 <style type="text/css">
  #service_date { background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
</style>


 <div id="tab_service" class="tabset_content">
      <div class="leftcol" style="width: 260px; text-align: left; padding-left: 5px" ><h2 style="color: #000; display: inline">Service Request Form</h2></div>
     <div class="submenu" style="float: right">
        <a href="./?mod=portal&portal=service">Service Request Form</a> | 
        <a href="./?mod=portal&sub=history&portal=service">Service Request History</a>
     </div>
     <div class="clear"></div>
    <form method="post" id="form_service" enctype="multipart/form-data">
     <input type="hidden" name="portal" value="service">
     <input type="hidden" name="mod" value="portal">
     <input type="hidden" name="submitcode" id="submitcode" value="">
     <table width="98%" class="itemlist" cellpadding=4 cellspacing=1 style="border: 1px solid #103821; padding: 2px 2px 2px 2px">
      <tr>
        <td width=130 align="left">Department</td>
        <td align="left">
		<select name="id_department" id="dept_service" >
		<?php echo build_option($department_list, $_department)?>
		</select>
	</td>
      </tr>
      <tr>
        <td width=130 align="left">Category</td>
        <td align="left">
		<select name="id_category" id="cat_service" >
		<?php 
        echo build_option($category_list, $_category)
        ?>
		</select>
        </td>
      </tr>
      <tr class="alt">
        <td align="left">Date</td>
        <td align="left">
          <input type="text" size=24 id="service_date" name="service_date" value="<?php echo $next_two_day_str?>" >
		  <script type="text/javascript">
            var lt = new Date(<?php echo $lead_time*1000;?>);
            var dFormat = "%e-%b-%Y %H:%i";
            $('#service_date').AnyTime_noPicker().AnyTime_picker({format: dFormat, earliest: lt});
		  </script>
        </td>
      </tr>
      <tr>
        <td align="left">Purpose</td>
        <td align="left">
            <input type="text" size=55 name="purpose" id="purpose_service" onKeyUp="suggest(this, this.value);" autocomplete="off" >
                <div class="suggestionsBox" id="suggestions_service" style="display: none; z-index: 500;"> 
                <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                <div class="suggestionList" id="suggestionsList_service"> &nbsp; </div>
            </div>
        </td>
      </tr>
      <tr class="alt">
        <td align="left">Remarks / <br> Special Requirements</td>
        <td align="left"><textarea rows=3 cols=70 name="remark"></textarea></td>
      </tr>
	  <tr >
        <td align="left">Attachment File</td>
        <td align="left">
			<input type="file" id="drawnout1" name="attachment[]" class="multi max-5 accept-gif|jpg|jpeg|png|pdf|xls|xlsx|doc|docx|ppt|pptx" />
			<div id="attachment-list"></div>
			<script type="text/javascript" language="javascript">
			$(function(){ // wait for document to load 
				$('#attachment').MultiFile({ 
					list: '#drawnout-list'
				}); 
			});
			</script>
		</td>
      </tr>
	  
<?php
    $field_list = get_extra_field_list($_category, $id_page);
   // echo "$_category --- $id_page";
    $no = 1;
    foreach ($field_list as $field){
        $field_input = null;
        $class_name = ($no++ % 2 == 0) ? 'alt' : 'normal';
        $field_name = 'field-' . $field['id_field'];
        switch (strtoupper($field['field_type'])){
        case 'BOOLEAN':
            $field_input .= '<input type="radio" name="'.$field_name.'" value="1"> Yes';
            $field_input .= '<input type="radio" name="'.$field_name.'" value="0"> No';
            break;
        case 'NUMERIC':
            $field_input .= '<input type="text" name="'.$field_name.'" size=10>';
            break;
        default: 
			if ($field['field_size'] > 30)
				$field_input .= '<textarea name="'.$field_name.'" cols=30 rows=3></textarea>';
			else
				$field_input .= '<input type="text" name="'.$field_name.'" size=30>';
        }
        $field_title = (!empty($field['field_desc'])) ? $field['field_desc'] : $field['field_name'];
        echo <<<ROW
    <tr class='$class_name' valign="top">
        <td>$field[field_name]</td>
        <td>$field_input &nbsp; <a class="hint" title="$field_title">?</a></td>
    </tr>
ROW;
    }
    $class_name = ($no++ % 2 == 0) ? 'alt' : 'normal';
?>
		
      <tr class="<?php echo $class_name?>">
        <td colspan=2 align="right">
            <button id="submit_service" type="button" <?php if ($_category<=0) echo ' disabled '?> >Submit Request</button>
        </td>
      </tr>
     </table>
     </form>
     &nbsp; <br/>
     <div class="note"><?php echo get_text('service_submit_note')?>  </div>
  </div>
  <div id='msgok' class='dialog ui-helper-hidden'>
    <div id="message" class="alertbox" style="text-align: center">
        <?php echo $messages['service_request_success'];?>
    </div>
</div>
<div id="msgerr" class='dialog ui-helper-hidden'>
    <div class="alertbox" id="message" style="text-align: center">
        <?php echo $messages['service_request_fail'];?> 
    </div>
</div>

<script type="text/javascript">
var st = '<?php echo $st ?>';

if (st == 'OK') {
    var buttons = {'Close': function(e){$('#msgok').dialog('close');}};
    $('#msgok').dialog({
        modal: true, 
        title: 'Request Info', width: 400, height: 130});
    }
else if(st == 'FAIL'){
    var buttons = {'Close': function(e){$('#msgerr').dialog('close');}};
    $('#msgerr').dialog({
        modal: true, 
        title: 'Request Info', width: 400, height: 130});
	}

var selcat = "<?php echo $_category?>";
$('#cat_service').change(function (e){
   $('#form_service').submit();
});

$('#dept_service').change(function(e){
service_department_change('service');
});

service_department_change('service', selcat);
function service_department_change(sect, cat)
{
    var d = $('#dept_'+sect)[0];
    var did = d.options[d.selectedIndex].value;
    
    $.post("./item/get_category_by_department.php", {queryString: ""+did+"",type: ""+sect+""}, function(data){
        if(data.length >0) {
            $('#cat_'+sect).empty();
            $('#cat_'+sect).append('<option value=0> -- select a category --</option>');
            $('#cat_'+sect).append(data);
            
            var c = document.getElementById('cat_'+sect);
            if ((c.options.length > 0) ){ //&& (c.options[0].value > 0)
                  $('#submit_'+sect).removeAttr("disabled");
                  {
                      for (var i=0; i<c.options.length; i++){
                        if (c.options[i].value == cat){
                            c.options[i].selected = true;
                        }

                    }
                  if (parseInt(cat==0))
                    c.selectedIndex = 0;
                    
                } 
            } else
              $('#submit_'+sect).attr("disabled","disabled");
        } else {
            $('#cat_'+sect).empty();
            $('#cat_'+sect).append('<option value=0> -- category is not available --</option>');
        }
    });
}

$('#submit_service').click(function (event){
    
  if ($('#cat_service').val() <= 0){
    alert('Please select a category!');
    return;
  }
  if ($('#purpose_service').val() == ''){
    alert('What are your purpose need the service?');
    return;
  }
	$('#submitcode').val(1);
	$('form').submit();
	
  // $.post(url, $('#form_service').serialize(), function(data){
		
       
      
  // });
});

function fill(id, thisValue, onclick) 
{
    $('#'+id).val(thisValue);
    var suggest_for = id.substring(8);
    setTimeout("$('#suggestions_"+suggest_for+"').fadeOut();", 100);
}

function suggest(me, inputString)
{
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



</script>
