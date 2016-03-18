<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : null;
if (empty($_id))
	$_id = isset($_POST['id']) ? $_POST['id'] : 0;
$_msg = null;

function save_period_terms($id_facility, $terms)
{
	$values = array();
	$query = 'DELETE FROM facility_period_map WHERE id_facility = '. $id_facility;
	mysql_query($query);
	foreach($terms as $id_term)
		$values[] = "($id_facility, $id_term)";
	if (count($values)>0){
		$query = 'INSERT IGNORE facility_period_map(id_facility, id_term) VALUES'.implode(', ', $values);
		mysql_query($query);
	}
}

if (isset($_POST['save'])) {
	$period_duration = !empty($_POST['period_duration']) ? $_POST['period_duration'] : 0;
	$max_period = !empty($_POST['max_period']) ? $_POST['max_period'] : 0;
	$lead_time = !empty($_POST['lead_time']) ? $_POST['lead_time'] : 0;
	$time_start = !empty($_POST['time_start']) ? $_POST['time_start'] : 0;
	$time_end = !empty($_POST['time_end']) ? $_POST['time_end'] : 0;
	//if (( !$exists && ($_id == 0)) or ($_id > 0) ) 
	$notif = !empty($_POST['notif']) ? $_POST['notif'] : 0;
	$email = !empty($_POST['emails']) ? $_POST['emails'] : "";
	$hp = !empty($_POST['my_hp']) ? $_POST['my_hp'] : "";
    $description = mysql_real_escape_string($_POST['description']);
	
	if ($_id > 0) { // edit
		$query = "UPDATE facility SET period_duration = $period_duration, max_period = $max_period, lead_time = $lead_time, 
                  description = '$description', time_start = '$time_start', time_end = '$time_end', status_notification = '$notif', email = '$email',
				  handphone = '$hp' WHERE id_facility = $_id";				  
				  //echo $query;
		$rs = mysql_query($query);
		if ($rs){
			user_log(LOG_UPDATE, 'Facility '. $_POST['id_location']. '(ID:'. $_id.')');
			if (!empty($_POST['terms']))
				save_period_terms($_id, $_POST['terms']);
			$msg = "Facility data has been updated!";
			$url = "./?mod=facility&sub=facility&act=view&id=$_id";
		} else $_msg = 'Fail updating facility data!';
	} else { // new facility
		// check if duplicate no
		$query  = "SELECT count(*) FROM facility WHERE id_location = '$_POST[id_location]'";
		$rs = mysql_query($query);
		$rec = mysql_fetch_row($rs);
		if ($rec[0] == 0){ // no duplicate, save! 
			$query = "INSERT facility(id_location, period_duration, max_period, lead_time, description, time_start, time_end) 
					  VALUE('$_POST[id_location]', '$period_duration', '$max_period', '$lead_time', '$description', '$time_start', '$time_end')";
			$rs = mysql_query($query);
			if (mysql_affected_rows()>0){
				$_id = mysql_insert_id();
				user_log(LOG_CREATE, 'Facility '. $_POST['id_location']. '(ID:'. $_id.')');
				if (!empty($_POST['terms']))
					save_period_terms($_id, $_POST['terms']);
				$msg = 'New facility has been created!';
				$url = "./?mod=facility&sub=facility&act=view&id=$_id";
			} else $_msg = 'Create new facility failed!';

		} else  
			$_msg = "Error : duplicated facility's name!";
	} // new facility	
	if (!empty($url))
		redirect($url, $msg);
	
} else if (isset($_POST['delete'])) {
	$_id = isset($_POST['id']) ? $_POST['id'] : 0;
	/*
	ob_clean();
	header('Location: ./?mod=facility&sub=facility&act=del&type='.$_type.'&id=' . $_id);
	ob_flush();
	ob_end_flush();
	exit;
	*/
	redirect('./?mod=facility&sub=facility&act=del&id=' . $_id);
}		
	
if ($_id > 0) {
    $query  = "SELECT * FROM facility WHERE id_facility = $_id";
    $rs = mysql_query($query);
    $data_item = mysql_fetch_array($rs);
    $data_item['time_start'] = substr($data_item['time_start'], 0, 5);
    $data_item['time_end'] = substr($data_item['time_end'], 0, 5);
    $caption = 'Edit Existing Facility';
} else {
    $data_item['time_start'] = '07:00';
    $data_item['time_end'] = '19:00';
    $data_item['lead_time'] = 1;
    $data_item['max_period'] = 3;
    $data_item['period_duration'] = 30;
    $caption = 'Create New Facility';
    $data_item['id_location'] = 0;
    $data_item['description'] = @$description;
}

$location_list = get_location_list();
$new_system =  (defined('USE_NEW_BOOKING') && USE_NEW_BOOKING);
if ($new_system){
	$period_term_list = period_term_list();
	$facility_period_term_list = facility_period_term_list($_id);
}

if(! empty($data_item['email'])){
	$email = explode(',', $data_item['email']);
	$count_email = count($email);
	for($x=0;$x < $count_email; $x++){
		$em = explode('|', $email[$x]);
		$email_list .= '<a onclick="del_email_2(\''.$em[0].'\')"><img class="icon" src="images/delete.png" alt="delete"></a>'.$email[$x]."<br />";
	}
} else {
$email_list = '--- empty list ---';
}


$_emails = '';


if(! empty($data_item['handphone'])){
	$handphone = explode(',', $data_item['handphone']);
	$count_hp = count($handphone);
	for($x=0;$x < $count_hp; $x++){
		$em = explode('|', $handphone[$x]);
		$hp_list .= '<a onclick="del_hp_2(\''.$em[0].'\')"><img class="icon" src="images/delete.png" alt="delete"></a>'.$handphone[$x]."<br />";
	}
} else {
$hp_list = '--- empty list ---';
}


$_hp = '';
?>


<br/>
<br/>
<style type="text/css">
  #time_start { background-image:url("images/clock.png");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:bold}
  #time_end { background-image:url("images/clock.png");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:bold}
</style>
<form method="POST">
<table width=500 class="itemlist" cellpadding=2 cellspacing=1>
<tr><th colspan=2><?php echo $caption?></th></tr>
<tr valign="top">
  <td width=100>Facility No</td>
  <td>
  	<?php
		if ($_id>0)
			echo $location_list[$data_item['id_location']];
		else {
			$facility_list = get_facility_list();
			$location_list = array_diff($location_list, $facility_list);
			echo build_combo('id_location', $location_list);
		}
    ?>
  </td>
 </tr>
<tr valign="top" class="alt">
  <td>Description</td>
  <td><textarea name="description" cols=40 rows=3><?php echo @$data_item['description']?></textarea></td>
 </tr>
<?php
if ($new_system){
?>
<tr valign="top" class="">
  <td>Period Terms</td>
  <td>
  	<select id="terms" name="terms[]" multiple size=9>
  	<?php echo build_option($period_term_list, $facility_period_term_list);?>
	</select>
	<div class="field-note">* mutliple selection by ctrl/cmd(mac)</div>
  </td>
</tr>
<tr valign="top" class="">
  <td>Status Notification</td>
  <td>
	<?php if ($_id > 0) { 
		if($data_item['status_notification'] == 1){ 
	?>
		<input type="radio" value='0' name='notif'> Disable
		<input type="radio" value='1' name='notif' checked> Enable
	<?php 
	} else {
	?>
		<input type="radio" value='0' name='notif' checked> Disable
		<input type="radio" value='1' name='notif'> Enable
	<?php 
	}
	
	} else { ?>
		<input type="radio" value='0' name='notif' checked> Disable
		<input type="radio" value='1' name='notif'> Enable
	<?php } ?>
  </td>
</tr>
<tr valign="top" class="alt">
  <td>Email</td>
  <td>
	<?php
	if(! empty($data_item['email'])){
		$_emails = $data_item['email'];
	} else {
		$_emails = $_emails;
	}
	
	if(! empty($data_item['handphone'])){
		$_hp = $data_item['handphone'];
	} else {
		$_hp = $_hp;
	}
	
	?>
	<?php if($_id > 1) { ?>
	<ul id="email_list">
	<?php echo $email_list?>
	</ul>
	<input type="hidden" id="emails" name="emails" value="<?php echo $_emails?>">
  	<input type="text" name="email" onKeyUp="return suggest(this, this.value)" onBlur="fill('email', this.value);" id='email'>
	
	<button type="button" id="add_button" onclick="add_email()">Add</button>	
	<div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
	<?php } else { ?>
	<input type="hidden" id="emails" name="emails" value="<?php echo $data_item['email'];?>">
	<ul id="email_list">
		<?php echo $email_list?>
	</ul>
	<input type="text" name="email" onKeyUp="return suggest(this, this.value)" onBlur="fill('email', this.value);" id='email'>	
	<div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
	
	<?php }?>
  </td>
</tr>

<tr>
<td>Handphone</td>
<td>	
	<input type="hidden" id="my_hp" name="my_hp" value="<?php echo $_hp?>">
	<ul id="hp_list">
		<?php echo $hp_list?>
	</ul>
	<input type="text" name="hp" onKeyUp="return suggest_hp(this, this.value)" onBlur="fill('hp', this.value);" id='hp'>
	<button type="button" id="add_button_hp" onclick="add_hp()">Add</button>	
	<div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;">         
        <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
    </div>
</td>
<tr/>
<?php
} else {
?>
<tr valign="top" class="alt">
  <td>Duration / Periode</td>
  <td>  
    <input type="text" name="period_duration" value="<?php echo @$data_item['period_duration']?>" size=5>
    minutes
  </td>
 </tr>
<tr valign="top">
  <td>Max. Number of Periode can be taken</td>
  <td><input type="text" name="max_period" value="<?php echo @$data_item['max_period']?>" size=5></td>
 </tr>
<tr valign="top" class="alt">
  <td>Lead Time </td>
  <td>
    <input type="text" name="lead_time" value="<?php echo @$data_item['lead_time']?>" size=5> day(s)
 </td>
 </tr>
<tr valign="top">
  <td>Time Usage</td>
  <td>
      <input type="text" size=5 id="time_start" name="time_start" value="<?php echo @$data_item['time_start']?>" readonly>
      &nbsp; &nbsp; to &nbsp; &nbsp; 
      <input type="text" size=5 id="time_end" name="time_end" value="<?php echo @$data_item['time_end']?>" readonly>
      <script>
        $('#time_start').AnyTime_picker({format: "%H:%i"});
        $('#time_end').AnyTime_picker({format: "%H:%i"});
      </script>
  </td>
 </tr>
 <tr valign="top" class="">
  <td>Status Notification</td>
   <td>
	<?php if ($_id > 0) { 
		if($data_item['status_notification'] == 1){ 
	?>
		<input type="radio" value='0' name='notif'> Disable
		<input type="radio" value='1' name='notif' checked> Enable
	<?php 
	} else {
	?>
		<input type="radio" value='0' name='notif' checked> Disable
		<input type="radio" value='1' name='notif'> Enable
	<?php 
	}
	
	} else { ?>
		<input type="radio" value='0' name='notif' checked> Disable
		<input type="radio" value='1' name='notif'> Enable
	<?php } ?>
  </td>
</tr>
<tr valign="top" class="alt">
  <td>Email</td>
  <td>
  	<input type="text" name="email">	
	</td>
</tr>

 <?php } // old ?>
<tr valign="top">
  <th colspan=2>
	
	<button  type="submit" name="save" > Save </button>
	<button  type="reset" name="reset" > Reset </button>
	<button  type="button" onclick='cancel()'> Cancel </button>
<?php
	if ($_id > 0) {
		//echo '<button type="button" id="btn_manage_period_terms">Manage Period Terms</button>';
		//echo '<script>function goto_timesheet() { location.href="./?mod=facility&sub=timesheet&act=view&id='.$_id.'"; }</script>';
	}
?>
</th>
  </tr>
</table>
<br/>
<input type="hidden" name="id" value="<?php echo $_id?>" > 
</form>
<br/>
<?php
if ($_msg != null)
	echo '<div class="error">' . $_msg . '</div>';
?>
<script type="text/javascript">
 function save_item(){
  var frm = document.forms[0]
  frm.save.value = 1;
  frm.submit();
 }
 
 function cancel(){
    location.href="./?mod=facility&sub=facility&act=list";
 }

function enable_location()
{
    $('#id_location').removeAttr('disabled');
}
$('#btn_manage_period_terms').click(function(){
    location.href="./?mod=facility&sub=period&act=term_list";
});


function suggest(me, inputString){
	var frm = document.forms[0];
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
        var path = "user/suggest_email.php";
        
		$.post(path, {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
		if(data.length >0) {
			$('#suggestions').fadeIn();
			$('#suggestionsList').html(data);
			var pos = $('#email').offset();  
			var w = $('#email').width();
			$('#suggestions').css('position', 'absolute');
			$('#suggestions').offset({top:pos.bottom, left:pos.left});
			$('#suggestions').width(w);
			}
		});
	}
}

function suggest_hp(me, inputString){
	var frm = document.forms[0];
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
        var path = "user/suggest_mobile.php";
        
		$.post(path, {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
		if(data.length >0) {
			$('#suggestions').fadeIn();
			$('#suggestionsList').html(data);
			var pos = $('#hp').offset();  
			var w = $('#hp').width();
			$('#suggestions').css('position', 'absolute');
			$('#suggestions').offset({top:pos.bottom, left:pos.left});
			$('#suggestions').width(w);
			}
		});
	}
}


function fill(id, thisValue, onclick) {
	if (thisValue.length>0 && onclick){
		var cols = thisValue.split('|');
		$('#'+id).val(cols[1] + ' (' + cols[0] + ')');
	}
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function add_email(){
	var email = $('#email').val();
	if (email == '') return;
	var emails = $('#emails').val();
	var cols = email.match(/([^ ]+) *\((.+)\)/);
	if  (emails.search(new RegExp(cols[1])) == -1){
		cols.shift();
		if (emails == '') emails = cols.join('|');
		else emails += ',' + cols.join('|');
		$('#emails').val(emails);
        $('#email').val('');
	} else
        alert('Email already exists!');
    display_list(emails);
}

function add_hp(){
	var hp = $('#hp').val();
	if (hp == '') return;
	var my_hp = $('#my_hp').val();
	var cols = hp.match(/([^ ]+) *\((.+)\)/);
	if  (my_hp.search(new RegExp(cols[1])) == -1){
		cols.shift();
		if (my_hp == '') my_hp = cols.join('|');
		else my_hp += ',' + cols.join('|');
		$('#my_hp').val(my_hp);
        $('#hp').val('');
	} else
        alert('Handphone already exists!')
	
	display_list_hp(my_hp);
}

function display_list(emails){
	var text = '';
	var name = '';
    var email = '';
    var recs = emails.split(',');
    if (emails != '' && recs.length > 0){
        for (var i=0; i < recs.length; i++){
            cols = recs[i].split('|');
            email = cols[0];
            name = cols[1];
            text += '<li class="an_email" id="' + email + '">' ;
            text += '<a onclick="del_email(\''+ email +'\')"><img class="icon" src="images/delete.png" alt="delete"></a> ';
            text += '<a onclick="edit_email(\''+ recs[i] +'\')">' +  email +  ' (' + name + ')</a></li>';
        }
    } else
        text = '--- empty list ---';
	$('#email_list').html(text);
}

function display_list_hp(my_hp){
	var text = '';
	var name = '';
    var hp = '';
    var recs = my_hp.split(',');
	console.log(my_hp);
    if (my_hp != '' && recs.length > 0){
        for (var i=0; i < recs.length; i++){
            cols = recs[i].split('|');
            hp = cols[0];
            name = cols[1];
            text += '<li class="an_hp" id="' + hp + '">' ;
            text += '<a onclick="del_hp(\''+ hp +'\')"><img class="icon" src="images/delete.png" alt="delete"></a> ';
            text += '<a onclick="edit_hp(\''+ recs[i] +'\')">' +  hp +  ' (' + name + ')</a></li>';
        }
    } else
        text = '--- empty list ---';
	
	$('#hp_list').html(text);
}


function del_email(email){
    if (confirm("Are you sure delete the email?"+email)){
        var emails = $('#emails').val();
        var recs = emails.split(',');
        var newrecs = new Array();
        for (var i=0; i < recs.length; i++){
            //cols = recs[i].split('|');
            if (recs[i].search(new RegExp(email)) == -1){
                newrecs.push(recs[i]);
                //alert('ok');
            }
        }
        $('#emails').val(newrecs);
        display_list(newrecs.join(','));
	}
}


function del_hp(hp){	
    if (confirm("Are you sure delete the hp "+hp+" ?")){
        var my_hp = $('#my_hp').val();
        var recs = my_hp.split(',');
        var newrecs = new Array();
        for (var i=0; i < recs.length; i++){
            //cols = recs[i].split('|');
            if (recs[i].search(new RegExp(hp)) == -1){
                newrecs.push(recs[i]);
                //alert('ok');
            }
        }
        $('#my_hp').val(newrecs);
        display_list_hp(newrecs.join(','));
	}
}

function del_email_2(email){
    if (confirm("Are you sure delete the email?"+email)){
        var emails = $('#emails').val();
        //var recs = email.match(/([^ ]+) *\((.+)\)/);
        var recs = emails.split(',');
        var newrecs = new Array();
        for (var i=0; i < recs.length; i++){
            //cols = recs[i].split('|');
            if (recs[i].search(new RegExp(email)) == -1){
                newrecs.push(recs[i]);
                //alert('ok');
            }
        }
        $('#emails').val(newrecs);
        display_list(newrecs.join(','));
	}
}

function del_hp_2(hp){
    if (confirm("Are you sure delete the hp "+hp+" ?")){
        var my_hp = $('#my_hp').val();
        //var recs = email.match(/([^ ]+) *\((.+)\)/);
        var recs = my_hp.split(',');
        var newrecs = new Array();
        for (var i=0; i < recs.length; i++){
            //cols = recs[i].split('|');
            if (recs[i].search(new RegExp(hp)) == -1){
                newrecs.push(recs[i]);
                //alert('ok');
            }
        }
        $('#my_hp').val(newrecs);
        display_list_hp(newrecs.join(','));
	}
}
</script>
	
