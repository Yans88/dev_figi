<?php

$month = isset($_GET['m']) ? $_GET['m'] : date('n');
$year  = isset($_GET['y']) ? $_GET['y'] : date('Y');
$page = isset($_GET['page']) ? $_GET['page'] : 1;

$id_facility = isset($_POST['id_facility']) ? $_POST['id_facility'] : 0;
$id_subject = isset($_POST['id_subject']) ? $_POST['id_subject'] : 0;
$id_user = isset($_POST['id_user']) ? $_POST['id_user'] : 0;
$date_start = isset($_POST['start']) ? $_POST['start'] : null;
$date_end = isset($_POST['end']) ? $_POST['end'] : null;
$user_name = isset($_POST['user_name']) ? $_POST['user_name'] : null;

if (!empty($_POST['removesome'])){
	$this_time = time();
	$nod = 0;
	foreach($_POST['books'] as $id_book){
		$book = book_info($id_book);
		$period = book_earliest_period($id_book);
		if (!empty($period)){
			if ($this_time < $period['booked_date'])
				if (book_remove($id_book))
					$nod++;
		}
	}
	if ($nod>0)
		$msg = "Some of selected booking has been deleted!";
	else
		$msg = "None of selected booking deleted!";
	$url = $current_url;//$mod_url.'&act=list&view=table';
	redirect($url, $msg);
} 

$start = 0; 
$limit = 10;//RECORD_PER_PAGE; 
if ($page > 0) $start = ($page-1) * $limit; 

$filter = array();
if (USERGROUP!=GRPADM) $filter['id_user'] = USERID;
else {
	if (isset($_POST)){
		$filter['start'] = strtotime($_POST['date_start']);
		$filter['end'] = strtotime($_POST['date_end']);
		$filter['id_facility'] = $_POST['id_facility'];
		$filter['id_subject'] = $_POST['id_subject'];
		$filter['id_user'] = $_POST['id_user'];
	}
}
$total = booking_count($filter);
$total_page = ceil($total/$limit);
$books = booking_rows($filter, $start, $limit);

/*
$date_start = date('d-M-Y');
$date_end = $date_start;
*/
$facility_list = array('0' => '* select a facility')+bookable_facility_list();
$booked_by = null;
$subject_list = array('0' => '* select a subject')+booking_subject_list();
?>
<link rel="stylesheet" type="text/css" href="./style/default/anytimec.css" />
<script type="text/javascript" src="./js/anytimec.js"></script>

<form method="post" id="frm_list">
<input type="hidden" name="id_user">
<div style="" >
<table class="filter round-corner middle" style="display:none" id="filter_table">
<tr><td>Range of Dates</td>
	<td>
		<input type="text" name="date_start" id="date_start" style="width: 80px" value="<?php echo $date_start?>">
		 &nbsp; to &nbsp;
		<input type="text" id="date_end" name="date_end" style="width: 80px" value="<?php echo $date_end?>">
	</td>
</tr>
<tr><td>Facility</td><td><?php echo build_combo('id_facility', $facility_list, $id_facility)?>	</td></tr>
<tr><td>Subject</td><td><?php echo build_combo('id_subject', $subject_list, $id_subject)?>	</td></tr>
<tr><td>Booked By</td><td>
	<input type="text" name="user_name" id="user_name" size=28 value="<?php echo $user_name?>" autocomplete="off" 
	onKeyUp="suggest(this, this.value);" onBlur="fill('user_name', this.value);">
	<div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500;"> 
		<div class="suggestionList" id="suggestionsList"> &nbsp; </div>
	</div>          
</td></tr>
<tr><td colspan=2 class="center"> 
	<button type="button" id="hide_filter"> Hide Filter </button>
	<button name="apply"> Apply Filter </button>
</td></tr>
</table>
<div><a class="button" href="#" id="toggle_btn">Show Filter</a></div>
</div>
<script>
	var oneDay = 24*60*60*1000;
	var dateformat = "%e-%b-%Y";
	var rangeConv = new AnyTime.Converter({format:dateformat});
	/*
	$("#rangeToday").click( function(e) {
	  $("#date_start").val(rangeConv.format(new Date())).change(); } );
	$("#rangeClear").click( function(e) {
	  $("#date_start").val("").change(); } );
	*/
	var toDay = new(Date);
	$("#date_start").AnyTime_picker({format:dateformat});
	$("#date_start").change(
		function(e) {
		  try {
			var fromDay = rangeConv.parse($("#date_start").val()).getTime();
			var dayLater = new Date(fromDay+oneDay);
			dayLater.setHours(0,0,0,0);
			var fiveDaysLater = new Date(fromDay+(6*oneDay));
			fiveDaysLater.setHours(23,59,59,999);
			$("#date_end").
			  AnyTime_noPicker().
			  removeAttr("disabled").
			  val(rangeConv.format(fiveDaysLater)).
			  AnyTime_picker( {
				earliest: dayLater,
				format: dateformat,
				//latest: fiveDaysLater
				} );
			}
		  catch(e) {
			$("#date_end").val("").attr("disabled","disabled");
			}
		  } 
	  );

function fill(id, thisValue, onclick) 
{
	
    if (thisValue.length>0 ){
		var col = thisValue.split('|');
        $('input[name=id_user]').val(col[0]);
        $('#'+id).val(col[1]);
    }
    setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString)
{
    if(inputString.length == 0) {
        $('#suggestions').fadeOut();
    } else {
        var pd = {queryString: ""+inputString+"", inputId: ""+me.id+""};
        $.post("booking/suggest_user.php", pd, function(data){
            if(data.length >0) {
                $('#suggestions').fadeIn();
                $('#suggestionsList').html(data);
            } else
                $('#suggestions').fadeOut();
        });
    }
}

$('#user_name').change(function(){
	var v = $(this).val();
	if (v.length == 0)
		$('#id_user').val();
});

/*
	  $('#date_end').change(function(){
	  	var v = $(this).val();
		if (v.length>0)
			$('button[name=save]').removeAttr('disabled');
		else
			$('button[name=save]').attr('disabled', true);
	  });
	$('button[name=cancel]').click(function(){
		$('#frm_period_term').hide();
	});

*/

<?php
	if (isset($_POST['apply'])){
		echo '$("#filter_table").show();';
		echo '$("#toggle_btn").hide();';
	}
?>
</script>

<table class="bookinglist">
<tr>
	<th width=25>No</th>
	<th>Booked By</th>
	<th width=80>Booked On</th>
	<th>Facility</th>
	<th width=120>Book Start</th>
	<th>Subject</th>
	<th>Reason</th>
	<th width=60> <input type="checkbox" id="cbc" > </th>
</tr>
<?php
if ($total > 0){
	$not_mine = 0;
	$no = $start + 1;
	foreach($books as $book){
		if ($book['id_user']!=USERID) $not_mine++;
		$action = '<a href="'.$mod_url.'&act=view&id='.$book['id_book'].'">view</a> ';
		$action .= '<input type="checkbox" name="books[]" class="cb" value="'.$book['id_book'].'">';
		echo <<<ROW
	<tr>
		<td>$no</td> 
		<td>$book[booked_by]</td> 
		<td class="center" width=120>$book[book_date_display]</td> 
		<td>$book[facility_name]</td> 
		<td>$book[first_booked_period]</td> 
		<td>$book[subject]</td> 
		<td>$book[purpose]</td> 
		<td class="center" width=40>$action</td>
ROW;
		$no++;
	}
} else {
	echo '<tr><td colspan=8 class="center">Data is not available!</td></tr>';
}
?>
</table>
<div>
<?php
if ($total>0){
	if (USERGROUP==GRPADM || $not_mine==0){
		echo'<div style="float: right; padding: 2px;"><button type="button" id="removesome">Delete Selected Booking</button></div>';
	}

	echo '<div class="bookinglist nav">';
	echo make_paging($page, $total_page, $current_url.'&page=');
	echo '</div>';
}
?>
</div>
</form>
<script>
$('#toggle_btn').click(function(){
	$('#filter_table').toggle();
	$('#toggle_btn').hide();
});

$('#hide_filter').click(function(){
	$('#filter_table').toggle();
	$('#toggle_btn').show();
});


$('#cbc').change(function(){
	if (this.checked)
		$('.cb').each(function(){ this.checked = true; });
	else
		$('.cb').each(function(){ this.checked = false; });
});

$('#removesome').click(function(){
	if (confirm('Only selected beyond booking will be deleted. Do you sure delete some of them?')){ 
		with($('#frm_list')){
			append('<input type="hidden" name="removesome" value="yes">');
			submit();
		}
	}
});
</script>
