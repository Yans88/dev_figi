<?php

$subject_list = array('0'=> '* select a subject')+booking_subject_list();
$periods = isset($_POST['periods']) ? $_POST['periods'] : array();
$id_facility = $_POST['_facility'];
$facility = bookable_facility_info($id_facility);
$facility_name = $facility['facility_name'];
$description = $facility['description'];
if (empty($description)) $description = '-NA-';
$equipment_list = get_equipments($id_facility);
$term = period_term_get(0, $id_facility, 1);
$facility_periods = period_timesheet_rows($term['id_term']);
$tp_periods = array();
foreach($facility_periods as $rec){
	$tp_periods[$rec['id_time']] = "$rec[start_time] - $rec[end_time]";
}

?>
<script type="text/javascript" src='js/jquery.MultiFile.js' language="javascript"></script>
<script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" type="text/css" href="style/default/jquery.fancybox.css" media="screen" />

<div id="resource_wrap">
<div id="wrap_header">
<div id="wrap_title">Book Resource</div>
<div class="clear"></div>
</div>
<form method="post" id="bookingform" enctype="multipart/form-data">
<input type="hidden" name="step" value=2>
<input type="hidden" name="id_facility" value="<?php echo $id_facility?>">
<input type="hidden" name="periods" value='<?php echo serialize($periods)?>'>
<table id="resource">
<tr><td width=100>Facility</td><td><?php echo $facility_name; ?></td></tr>
<tr><td>Description</td><td><?php echo $description; ?></td></tr>
<tr><td></td><td><button type="button" id="equipment"> Equipment </button></td><td>
<tr><td>Recurring</td><td>
<input type="radio" name="recurring" value="none" checked> None 
<input type="radio" name="recurring" value="weekly" > Weekly
<input type="radio" name="recurring" value="fortnightly" > Fortnightly
<input type="radio" name="recurring" value="monthly" > Monthly
<div id="recurring_option" style="padding: 5px 5px; display: none">
	How many times? <input type="text" name="many" style="width: 20px" value=1>
	<div id="weekly_recurring_option">
	</div>
</div>
</td></tr>
<tr><td>Subject infused</td><td><?php echo build_combo('id_subject', $subject_list); ?> </td></tr>
<tr><td>Reason</td><td><input type="text" name="purpose" id="purpose" style="width: 400px"></td></tr>
<tr><td>Instruction</td><td><textarea name="remark" id="remark" rows=3 style="width: 405px"></textarea></td></tr>
<tr>
	<td>Attachment </td>
	<td style="text-align: left">
	<div id="attachment-list" class="filelist">
	<input type="file" id="attachment" name="attachment[]" class="multi max-5 accept-gif|jpg|jpeg|png|pdf|xls|doc|ppt|xlsx|docx|pptx maxsize-2048 with-preview" multi >
			<script type="text/javascript" language="javascript">
			$(function(){ // wait for document to load 
			 $('#attachment').MultiFile({ }); 
			});
		</script>    
	</div>
	</td>
</tr>
<tr><td>Periods</td><td><button type="button" id="toggle_period">Show / hide selected periods</button></td></tr>
<tr class="tr_period_list" style="display: none"><td colspan=2 >
<table class="tbl_period_list">
<tr><th width=80>Date</th><th width=80>Period</th><th>Subject</th><th>Reason</th><th>Instruction</th></tr>
<?php
	//print_r($periods);
	foreach($periods as $line){
		list($dt, $id_time) = explode('-', $line);
		$booked_date = date('d-M-Y', $dt);
		
		$period_label = $tp_periods[$id_time];
		$subject = build_combo("subjects[$line]", $subject_list);
		$purpose = '<input type="text" name="purposes['.$line.']">';
		$remark = '<input type="text" name="remarks['.$line.']">';
		echo "<tr><td>$booked_date</td><td>$period_label</td><td class='left'>$subject</td><td class='left'>$purpose</td><td class='left'>$remark</td></tr>";
	}
?>
</table>
</td></tr>

<tfoot>
<tr><td colspan=2>
<!--
<button type="button" id="separate_btn" class="leftcol">Set Separate Period Info</button>
-->
<button type="button" id="confirm">Confirm Booking</button>
<button type="button" id="cancel">Cancel</button>
</td></tr>
</tfoot>
</table>
</form>
<form id="equipment_dialog">
<div id="equipment_list" style="display: none">
<div class="center" style="padding: 3px 0"> <strong>Equipment List Issued to "<?php echo $facility_name?>"</strong></div>
<table width="100%">
<tr><th width=30>No</th><th>Equipment</th><th width=60>Quantity</th></tr> 
<?php
	if (!empty($equipment_list)){
		$no = 0;
		foreach ($equipment_list as $category_name => $quantity){
			$no++;
			echo '<tr><td>'.$no.'</td><td>'.$category_name.'</td><td class="center">'.$quantity.'</td></tr>';
		}
	} else echo '<tr><td colspan=3 class="center ">Data is not available!</td></tr>';
?>
</table>
<br>
<div style="margin: 0 auto; text-align: center">
<button type="button" id="cancel_use"> Close </button>
</div>
</div>


</form>
</div>

<div id="separate_info_dialog" style="display: none; min-width: 400px; min-height: 100px; ">
<form id="separate_info_form">
<strong>Set Separate Info for Period</strong>
<br>
<table style="padding: 5px 5px">
<tr><td>Date Period </td><td><select id="the_date" name="the_date"></select></td></tr>
<tr><td>Time Period </td><td><div id="div_period"> </div> </td></tr>
<tr><td>Subject </td><td><?php echo build_combo('the_subject', $subject_list); ?></td></tr>
<tr><td>Reason </td><td><input type="text" name="the_purpose" id="the_purpose" style="width:300px"></td></tr>
<tr><td>Instruction </td><td><textarea name="the_remark" id="the_remark" style="width:305px"></textarea></td></tr>
<tr><td colspan=2>
<button type="button" id="set_period_info"> Save </button>
</td></tr>
</table>
</form>
</div>

<script>
var seleced_periods = new Array();
var facility_periods = new Array();
var months = new Array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
<?php

$no=0;
foreach($periods as $line){
	echo "seleced_periods[$no] = \"$line\";\r\n";
	$no++;
}
foreach($tp_periods as $id_time => $per_time){
	echo "facility_periods[$id_time] = \"$per_time\";\r\n";
}
?>
function build_time_checks(dt){
	var times = new Array();
	for (var i=0; i<seleced_periods.length; i++){
		var cols = seleced_periods[i].split('-');
		if (cols[0]==dt)
			times.push(cols[1]);
	}
	var option = '';
	for (var i=0; i<times.length; i++){
		option += '<input type="checkbox" name="the_periods" id="the_periods" value="'+dt+'-'+times[i]+'">'+facility_periods[times[i]]+"<br>\r\n";
	}
	return option;
}

$('#separate_btn').click(function(){
	
	$.fancybox.open({
		href: '#separate_info_dialog',
		padding: 5
	});
	var datestr = "";
	for (var i=0; i<seleced_periods.length; i++){
		var cols = seleced_periods[i].split('-');
		if (datestr.search(cols[0])<0)
			datestr += cols[0]+',';
	}
	var dates = datestr.split(',');
	var option = '';
	for(var i=0; i<dates.length; i++){
		var ds = dates[i];
		if (ds.length>0){
			var d = new Date(parseInt(ds)*1000);
			
			var dMY = d.getDate()+'-'+months[d.getMonth()]+'-'+d.getFullYear();
			option += '<option value='+ds+'>'+dMY+'</option>';
		}
	}
	$('#the_date').append(option);
	
	$('#the_date').change(function(){
		$('#div_period').html(build_time_checks(this.value));
	});
	$('#the_date').trigger('change');
});

$('#confirm').click(function(){
	var ok = true;
	var reason = $('input[name=purpose]').val();
	if (reason.length == 0){
		alert('Please enter the reason for this booking!');
		$('input[name=purpose]').focus();
		ok = false;
	}
	var recurring = $('input[name=recurring]:checked').val();
	if (recurring != 'none'){
		var many = $('input[name=many]').val();
		if (many.length>0 && parseInt(many)==0) {
			alert('Please enter correct number of booking recurrance!');
			$('input[name=many]').focus();
			ok = false;
		}
	}
	if (ok){
		$('#bookingform').append('<input type="hidden" name="confirm" value=1>');
		$('#bookingform').submit();
		
	}
});
$('#cancel').click(function(){
	location.href='<?php echo $mod_url?>';
});
$('#equipment').click(function(){
    $.fancybox.open({
        /*href: 'equipment_list.php?id='+id_book,*/
        href: '#equipment_list',
        /*type: 'iframe',*/
        padding: 5
        });
});
$('#use_equipment').click(function(){
	$('.cb').each(function(){
		if (this.checked){
			var id = $(this).val();
			var qty = $('#qty-'+id);
			//$('#bookingform').append(qty);
			$('#bookingform').append('<input type="hidden" name="'+$(qty).attr('name')+'" value="'+$(qty).val()+'">');
		}
	});

	$.fancybox.close();	
});
$('#cancel_use').click(function(){
	$('.cb').each(function(){
		this.checked = false;
		$(this).trigger('change');
	});
	$.fancybox.close();	
});

$('.cb').change(function(){
	var id = $(this).val();
	if (this.checked){
		$('#qty-'+id).removeAttr('disabled');
		$('#qty-'+id).focus();
	} else {
		$('#qty-'+id).attr('disabled', true);
		$('#qty-'+id).val(0);
	}
});

$('#reason_for_all').change(function(){
	var id = $(this).val();
	if (this.checked){
		
		$('#periods_checks').hide();
	} else {
		$('#periods_checks').show();
	}
});

$('input[name=recurring]').change(function(){
	var recurring = $('input[name=recurring]:checked').val();

	if (recurring == 'weekly'){

	} else if (recurring == 'monthly'){

	} 
	if (recurring.length > 0 && recurring != 'none'){
		$('#recurring_option').show();
	} else
		$('#recurring_option').hide();
});

$('#toggle_period').click(function(){
	var d = $('.tr_period_list').css('display');
	if (d == 'none')
		$('.tr_period_list').show();
	else
		$('.tr_period_list').hide();

});

</script>

