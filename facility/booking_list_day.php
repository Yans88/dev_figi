<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
$_self = './?mod=facility&sub=booking&';
$_facility = !empty($_POST['id']) ? $_POST['id'] : 0;
if ($_facility == 0)
    $_facility = !empty($_GET['id']) ? $_GET['id'] : 0;
$_do = isset($_GET['do']) ? $_GET['do'] : null;
$_date = !empty($_GET['d']) ? $_GET['d'] : date('Ynj');

if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})/', $_date, $matches)){
    list($none, $_year, $_mon, $_day) = $matches;
} else {
    $_day = date('j');
    $_mon = date('n');
    $_year = date('Y');    
}

$_time = mktime(date('H'), date('i'), date('s'), $_mon, $_day, $_year);
$prev_day_time = mktime(date('H'), date('i'), date('s'), $_mon, $_day-1, $_year);
$next_day_time = mktime(date('H'), date('i'), date('s'), $_mon, $_day+1, $_year);

if ($_do == 'export'){
    export_booking_schedule($_GET['date'], $_GET['facility']);
}


$result = null;

$facilities = get_facility_list();
if (count($facilities) == 0){
	$facilities[0] = '--none--';
	$result = '<div class="error" style="margin-top: 10px">Data is not available!.</div>';
}

//$books = get_book_by_date($_mon, $_year, $_day);
$result = get_book_by_date($_time, $_facility);

?>
<link rel="stylesheet" type="text/css" href="<?php echo STYLE_PATH ?>cal.css" media="screen" />
<script type="text/javascript" src="js/cal.js"></script>
<style type="text/css">
  #book_for_date { background-image: none; /*url("images/cal.jpg");*/
    background-position:right center; background-repeat:no-repeat;
    border:0;color:#fff; font-weight:bold;
    background-color: #062312;}
</style>
<div style="width:800px">
<div id="reference"  class="calendar">
<form method="Post" id="form_facility">
<div  id="booking_list">
     <p >
	 Facility: <?php echo build_combo('id_facility', $facilities, $_facility)?> 
        <input type="text" size=10 id="book_for_date" name="book_for_date" value="<?php echo date('d-M-Y', $_time)?>" >
	  <script>
		$('#book_for_date').AnyTime_picker({format: "%e-%b-%Y"});
	  </script>
	  <button type="button" id="prevbtn" >&larr;</button><button type="button" id="today">Today</button><button type="button" id="nextbtn" >&rarr;</button>
	  <button type="button" id="viewmonthly" >View&nbsp;Monthly</button>
     <!--
	 &nbsp; <button type="submit" id="display" name="display_schedule" >View Schedule</button>
	 &nbsp; <button type="button" id="export" name="export" onclick="export_schedule()" >Export Schedule</button>
     -->
	 </p>
	<div  id="view_day">
	<?php echo $result ?>
	</div>
</div>
</form>
</div>
</div>
<br/><br/>
<script type="text/javascript">
var d = document.getElementById('id_facility');
var conv = new AnyTime.Converter({format:  "%e-%b-%Y"});
var curdate = "<?php echo date("Y-n-j", $_time);?>";

desc_height = -45;

if (d && (d.options.length == 1) && (d.options[0].value == 0)){
	$('#id_facility').attr('disabled', 'disabled');
	$('#display').attr('disabled', 'disabled');
	$('#book_for_date').attr('disabled', 'disabled');
}

function export_schedule()
{
	var book_date = $('#book_for_date').val();
	location.href="./?mod=facility&sub=booking&act=list&date="+book_date+"&facility="+$('#id_facility').val()+"&do=export";
}

function view_date(dt){
    location.href="<?php echo $_self ?>&d="+dt+"&do=list_day&id="+$('#id_facility').val();    
}

$('#prevbtn').click(function(e){
    view_date("<?php echo date('Y-n-j', $prev_day_time)?>");
});

$('#nextbtn').click(function(e){
    view_date("<?php echo date('Y-n-j', $next_day_time)?>");
});

$('#viewmonthly').click(function(e){
    var dt = conv.parse($('#book_for_date').val());
    var sdt = dt.getFullYear()+"-"+(dt.getMonth()+1)+"-"+dt.getDate();
    location.href="./?mod=facility&sub=booking&act=list&date="+sdt+"&facility="+$('#id_facility').val();
});

$('#today').click(function(e){
    var dt = new Date();
    var sdt = dt.getFullYear()+"-"+(dt.getMonth()+1)+"-"+dt.getDate();
    view_date(sdt);
});

$('#id_facility').change(function(e){
    var dt = conv.parse($('#book_for_date').val());
    var sdt = dt.getFullYear()+"-"+(dt.getMonth()+1)+"-"+dt.getDate();
    view_date(sdt);
});

$('#book_for_date').change(function(e){
    var dt = conv.parse($('#book_for_date').val());
    var sdt = dt.getFullYear()+"-"+(dt.getMonth()+1)+"-"+dt.getDate();
    if (sdt != curdate){
        view_date(sdt);
        curdate = sdt;
    }
});

$('.bookitem').click(function(e){
    location.href="<?php echo $_self?>do=view&id="+e.target.id.substring(4)+'&d='+e.target.href.substring(e.target.href.lastIndexOf('#')+1);
});

$('.bookinfo').mouseover(function(e){
    var id = e.target.id.substring(5);
    $(this).css('background-color', '#eee');
    $('#plus-'+id).show();
});

$('.bookinfo').mouseout(function(e){
    var id = e.target.id.substring(5);
    $(this).css('background-color', '#fff');
    $('#plus-'+id).hide();
});

$('.plus').mouseover(function(e){
    $(this).show();
});

$('.plus').mouseout(function(e){
    $(this).hide();
});

$('.addbtn').click(function(e){
    var id = e.target.parentNode.id.substring(5);
    var d = e.target.id.substring(7);
    var st=id.substr(0,2)+':'+id.substr(2,3);
    location.href="./?mod=portal&portal=facility&d="+d+"&t="+st;    
    return false;
});

</script>
