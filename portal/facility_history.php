<?php
//if (!defined('FIGIPASS')) exit;

/*

find book request by the user
show as list
 - can see detail
 - can see on calendar
 
*/


$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$_do = isset($_GET['do']) ? $_GET['do'] : null;

if ($_do == 'export'){}

$start = 0;
$limit = RECORD_PER_PAGE;
if ($_page > 0) $start = ($_page-1) * $limit;

$total_item = count_book_request_by_user(USERID);
$total_page = ceil($total_item/$limit);
$data = get_book_request_by_user(USERID, $start, $limit);

?>
<h4>My Booking List</h4>
<?php
if ($total_item > 0){
   
?>
<style type="text/css">
 
/* Z-index of #mask must lower than #boxes .window */
#mask {
  left: 0;
  top: 0;
  position:absolute;
  z-index:9000;
  background-color:#000;
  display:none;
}
   
#boxes .window {
  position:fixed;
  /*
  width:440px;
  height:340px;
  border: 1px solid yellow;
  */
  display:none;
  z-index:9999;
  padding:1px;
}
 
 
/* Customize your modal window here, you can add background image too */
#boxes #dialog {
  width:840px; 
  height:320px;
  background-color: #062312;
  border: 1px solid #888;
}
#dialogtop {
    text-align: right;
}
</style>
<div id="boxes">
    <div id="dialog" class="window">
        <div id="dialogtop"><a href="#" class="close" alt="Close"  >[ X ]</a> &nbsp; </div>
        <div id="dialogcontent"></div>
    </div>
    <div id="mask"></div>
</div>

<table width="100%" cellpadding=2 cellspacing=1 class="facility_table" >
<tr>
  <th>Facility/Room</th>
  <th>Purpose</th>
  <th width=130>Start Use</th>
  <th width=150>Repetition</th>
  <th width=40>Action</th>
</tr>
<?php
$row = 0;
foreach ($data as $rec){
	$row++;
	$class =($row % 2 == 0 ) ? ' class="alt"' : ' class="normal"';
	$link  = '<a href="javascript:void(0)" alt="view booking detail" id="vb-'.$rec['id_book'].'"><img class="viewbtn" id="vbi'.$rec['id_book'].'" class="icon" src="images/loupe.png" border=0></a> ';
    $repetition = $repetitions[$rec['repetition']];
    if ($rec['repetition']>0)
        $repetition .= '. Every ' . $rec['interval'] . ' ' . $repeat_labels[$rec['interval']] ;
    if ($rec['fullday'])
        $booked_time = date('d M Y', $rec['dt_start']) . ' to ' . date('d M Y', $rec['dt_end']) . ' (Full day)';
    else
        $booked_time = date('d M Y H:i', $rec['dt_start']) . ' to ' . date('d M Y H:i', $rec['dt_end']);
    $booked_time = date('d M Y H:i', $rec['dt_start']);
	echo <<<ROW
<tr $class>
	<td align="left">$rec[location_name]</td>
	<td align="left">$rec[purpose]</td>
	<td align="center">$booked_time</td>
	<td align="left">$repetition</td>
	<td align="center">$link</td>
</tr>

ROW;
	}

echo '<tr ><td colspan=6 class="pagination">';
echo make_paging($_page, $total_page, './?mod=portal&sub=history&portal=facility&act=list&page=');
echo '<div class="exportdiv">';
echo '<a href="./?mod=portal&sub=history&portal=facility&act=view_month" class="button">Calendar View</a> ';
echo '<a href="./?mod=portal&portal=facility" class="button">Book a facility</a> ';
echo '<!--a href="./?mod=portal&sub=history&portal=facility&act=list&do=export" class="button">Export Data</a-->';
echo '</div></td></tr></table>';
        
} else
    echo '<p class="error" style="margin-top: 10px">Data is not available!.<br/>&nbsp;<br/>
            Click <a href="./?mod=portal&sub=history&portal=facility&act=view_month">Calendar View</a> to see booked facility in this month. 
            Or <a href="./?mod=portal&portal=facility">booking</a> to book a facility!
            <br/>&nbsp;<br/>
        </p>';
?>


<script type="text/javascript">


$('.viewbtn').click(function (e){
    //./?mod=portal&sub=history&portal=facility&act=view&id='.$rec['id_book'].'
    var url = './?mod=portal&sub=history&portal=facility&header=no&act=view&id='+e.target.id.substring(3);
    location.href=url;
    /*
    openwin('#dialog');
    $.get(url, function(data) {
        $('#dialogcontent').html(data);
        //alert('Load was performed.');
    });
    */
});

function openwin(id)
{
    //Get the screen height and width
    var maskHeight = $(document).height();
    var maskWidth = $(window).width();
 
    //Set height and width to mask to fill up the whole screen
    $('#mask').css({'width':maskWidth,'height':maskHeight});
     
    //transition effect     
    $('#mask').fadeIn(1000);    
    $('#mask').fadeTo("slow",0.8);  
 
    //Get the window height and width
    var winH = $(window).height();
    var winW = $(window).width();
           
    //Set the popup window to center
    $(id).css('top',  winH/2-$(id).height()/2);
    $(id).css('left', winW/2-$(id).width()/2);
 
    //transition effect
    $(id).fadeIn(2000); 
}

$(document).ready(function() {  
 
    //select all the a tag with name equal to modal
    $('a[name=modal]').click(function(e) {
        //Cancel the link behavior
        e.preventDefault();
        //Get the A tag
        var id = $(this).attr('href');
     
        openwin(id);
     
    });
     
    //if close button is clicked
    $('.window .close').click(function (e) {
        //Cancel the link behavior
        e.preventDefault();
        $('#mask, .window').hide();
    });     
     
    //if mask is clicked
    $('#mask').click(function () {
        $(this).hide();
        $('.window').hide();
    });         
     
});
 
</script>
