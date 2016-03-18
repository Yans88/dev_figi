<div style="margin-bottom: 30px"></div>
<script type="text/javascript" src='./js/jquery.MultiFile.js' language="javascript"></script>
<script type="text/javascript" src='./js/anytimec.js' language="javascript"></script>
<script type="text/javascript" src="./js/moment.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo STYLE_PATH ?>anytimec.css" />

<?php
include_once './fault/fault_util.php';
$config = $configuration['fault'];
$st = '';

if (!empty($_POST)){
	$ok= fault_save_request();
	if($ok){
		
		foreach($_FILES['attachment']['name'] as $key => $row){
			
			$filesize = $_FILES['attachment']['size'][$key];
			$filename =  $_FILES['attachment']['name'][$key];
			$filetemp =  $_FILES['attachment']['tmp_name'][$key];
			$errorcode =  $_FILES['attachment']['error'][$key];
			
			if (($filesize > 0) && ($errorcode == 0)){
				
				$data_attach = base64_encode(file_get_contents($filetemp));
				save_attachment_req($ok,$filename,$data_attach,'FAULTY');
				
			}
		}
		
		
		
	}
	$st = ($ok) ? 'OK' : 'FAIL';
}

function fault_save_request(){
    $userid = USERID;
    $fault_date = convert_date($_POST['fault_date'], 'Y-m-d H:i:s');
    $status = 'NOTIFIED' ;
    $query = "INSERT INTO fault_report(fault_category, id_location, fault_description, fault_status, fault_date, 
			  report_user, report_date) 
              VALUES ('$_POST[id_category]', '$_POST[id_location]', '$_POST[description]', '$status', '$fault_date', 
              $userid, now())"; 
    @mysql_query($query);
	
	// $submitted = false;
    if (mysql_affected_rows() > 0) {
        // $submitted = true;
        $_id = mysql_insert_id();              
        // sending email notification 
        // send_submit_fault_report_notification($_id);
    }
	return $_id;
}


$lead_time = (ENABLE_REQUEST_LEADTIME) ? get_lead_time($config['request_leadtime']) : time();
$next_two_day_str = date('j-M-Y H:i', $lead_time);
$day_until = strtotime('+1 day', $lead_time);
$day_until_str = date('j-M-Y H:i', $day_until);
$_category = (!empty($_POST['id_category'])) ? $_POST['id_category'] : -1;
/*
$_department = (!empty($_POST['id_department'])) ? $_POST['id_department'] : 0;
$department_list = get_department_list();
$dkeys = array_keys($department_list);
$first_dkey = !empty($dkeys[0]) ? $dkeys[0] : 0;
*/
$category_list = get_fault_category_list();
if (count($category_list) == 0)
	$category_list[0] = '--- no category available! ---';
	$location_list = get_location_list();
if (count($location_list) == 0)
	$location_list[0] = '--- no location available! ---';
?>
<style type="text/css">
  #fault_date { background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
</style>
 <div id="tab_fault" class="tabset_content">
      <div class="leftcol" style="width: 260px; text-align: left; padding-left: 5px" ><h2 style="color: #000; display: inline">Fault Report Form</h2></div>
     <div class="submenu" style="float: right">
        <a href="./?mod=portal&portal=fault">Fault Report Form</a> | 
        <a href="./?mod=portal&sub=history&portal=fault">Fault Report History</a>
     </div>
     <div class="clear"></div>
     <form method="post" id="form_fault" enctype="multipart/form-data">
     <input type=hidden name=portal value="fault">
     <input type=hidden name=submitcode value="">
     <table width="98%" class="itemlist" cellpadding=4 cellspacing=1 style="border: 1px solid #103821; padding: 2px 2px 2px 2px">
      <tr>
        <td width=130 align="left">Category</td>
        <td align="left">
		<select name="id_category" id="cat_fault" >
		<?php 
        echo build_option($category_list, $_category);
        ?>
		</select>
        </td>
      </tr>
      <tr class="alt">
        <td align="left">Date</td>
        <td align="left">
          <input type="text" size=24 id="fault_date" name="fault_date" value="<?php echo $next_two_day_str?>" >
		  <script type="text/javascript">
            var lt = new Date(<?php echo $lead_time*1000;?>);
            var dFormat = "%e-%b-%Y %H:%i";
            $('#fault_date').AnyTime_noPicker().AnyTime_picker({format: dFormat, earliest: lt});
		  </script>
        </td>
      </tr>
      </tr>
      <tr>
        <td align="left">Location</td>
        <td align="left">
		<select name="id_location" id="location" >
		<?php 
        echo build_option($location_list );
        ?>
		</select>
        </td>
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
      <tr>
        <td align="left">Description</td>
        <td align="left"><textarea rows=9 cols=63 id="description" name="description"></textarea></td>
      </tr>
      <tr class="alt">
        <td colspan=2 align="right"><button id="btn_submit_fault" type="button" class="">Submit Fault</button></td>
      </tr>
     </table>
     </form>
     &nbsp; <br/>
     <div class="note"><?php echo get_text('fault_submit_note')?>  </div>
  </div>
<div id='msgok' class='dialog ui-helper-hidden'>
    <div id="message" class="alertbox" style="text-align: center">
        <?php echo $messages['fault_request_success'];?>
    </div>
</div>
<div id="msgerr" class='dialog ui-helper-hidden'>
    <div class="alertbox" id="message" style="text-align: center">
        <?php echo $messages['fault_request_fail'];?> 
    </div>
</div>

<script>

var st = '<?php echo $st ?>';

if (st == 'OK'){
	$('#msgok').dialog({
		modal: true,
		title: 'Request Info', width: 350, height: 120});
		
} else if (st == 'FAIL'){
   
	$('#msgerr').dialog({
		modal: true,
		title: 'Request Info', width: 350, height: 120});

}



$('#btn_submit_fault').click(function(e){

    if ($('#cat_fault').val() <= 0){
        alert('Please select a category!');
        return;
    }
    if ($('#description').val() == ''){
        alert('Please describe your fault report!');
        return;
    }
    
    // $.post(url, $('#form_fault').serialize(), function(data){
        
        $('form').submit();
    // });
});
</script>
