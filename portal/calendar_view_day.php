<?php
$from_portal = true;

echo '<div style="width:700px; background-color: #062312; border: 1px solid #000; ">';
require 'calendar/calendar_view_day.php';
echo '</div>';

return;

if (!defined('FIGIPASS')) exit;


$_self = './?mod=portal&portal=calendar&act=view_day';
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
/*
$facilities = get_facility_list();
if (count($facilities) == 0){
	$facilities[0] = '--none--';
	$result = '<div class="error" style="margin-top: 10px">Data is not available!.</div>';
}
*/
//$books = get_book_by_date($_mon, $_year, $_day);
$result = get_events_by_date($_time);
if (!empty($result)){
    $books = array_values($result);
    $books = $books[0];
}

$cur_event_date = date('D, d M Y', $_time);
//print_r($books);
$event_list = null;
$fullday_list = null;
$timed_list = '';
$sheets = create_timesheet();
$rowsheet_top = ' top';
$no = 1;
$differ = -1; $id_event = -1;
$events = array();
foreach ($books as $rec){
    $id = str_replace(':', '', $rec['time_start_fmt']);
    $id_event = $rec['id_event'];
    if ($rec['fullday']>0){
        $info = 'Fullday event. ' . $rec['title'] . ' @ ' . $rec['location_name'];
        //$fullday_list .= '<div class="rowsheet">';
        //$fullday_list .= '<div id="sheet'.$id_time.'" class="timesheet">' . $sheet['time_start'] . '</div>';
        $fullday_list .= '<div id="info'.$id_event.'" class="bookinfo">' ;
        $fullday_list.= '<div class="bookedtime"><a class="bookitem" id="book'.$id_event.'" onmousemove="show_desc(event,\''.$id_event.'\')" onmouseout="hide_desc(event,\''.$id_event.'\')" class="event" href="#'.$id_event.'">' . $info . "</a></div>\n";
        $fullday_list .=<<<DESC
        <div class="desc" id="desc-$id_event">
        Title: $rec[title]<br/>
        Date: $rec[cur_event_date]<br/> 
        Time: Fullday event<br/> 
        Location: $rec[location_name]<br/> 
        Description: $rec[description] <br/> 
        Owner: $rec[full_name] 
        </div>\n
DESC;
        
        $fullday_list .= '</div>';
    } else {
        $events[] = $rec;
        /*
        $timerange = $rec['time_start_fmt'] . '-' . $rec['time_finish_fmt'];
        $info = $rec['title'] . ' @ ' . $rec['location_name'];
        $timed_list.= '<div style="display:none;z-index: 992" id="info-'.$id.'-'.$id_event.'" class="bookedtime"><a class="bookitem" id="book'.$id_event.'" onmousemove="show_desc(event,\''.$id_event.'\')" onmouseout="hide_desc(event,\''.$id_event.'\')" class="event" href="#'.$id_event.'">' . $info . "</a></div>\n";
        */
    }
}

$js = "var starts = new Array();\nvar events = new Array();\n";
$i = 0;
/*
foreach ($events as $rec){
    $info = $rec['title'] . ' @ ' . $rec['location_name'];
    $id = str_replace(':', '', $rec['time_start_fmt']);
    $js .= "starts[$i]='$id';\n";
    $js .= "events[$i]='$rec[id_event]';\n";
    
    $i++;
}
*/
foreach ($sheets as $id_time => $sheet){
    //if (substr($sheet['time_start'], 3, 2) != '00') continue;
    $odd = ($no++ % 2 != 0);
    $id = str_replace(':', '', $sheet['time_start']);
    $event_list .= '<div id="row'.$id_time.'" class="rowsheet '.$rowsheet_top.'">';
    $event_list .= '<div id="sheet'.$id_time.'" class="timesheet">' . $sheet['time_start'] . '</div>';
    $event_list .= '<div id="info-'.$id.'" class="bookinfo">' ;
    $rowsheet_top = null;
    if (!empty($events)){
        foreach ($events as $rec){
            $id_event = $rec['id_event'];
            if ($rec['cur_event_date'] == $cur_event_date){
                //print_r($rec);
                //echo $rec['time_start_fmt'] .'>='. $sheet['time_start'].' ===== '.$rec['time_finish_fmt'] .'>='. $sheet['time_end'].'<br/>';
                if (($rec['time_start_fmt'] == $sheet['time_start']) ){ //&& ($rec['time_finish_fmt'] >= $sheet['time_end'])
                    //$events[] = $rec;
                    $timerange = $rec['time_start_fmt'] . '-' . $rec['time_finish_fmt'];
                    if ($rec['fullday']>0)
                        $timerange = 'Fullday event';
                    $info = $timerange . '. ' . $rec['title'] . ' @ ' . $rec['location_name'];
                    $event_list.= '<div class="bookedtime"><a class="bookitem" id="book'.$id_event.'" onmousemove="show_desc(event,\''.$id_event.'\')" onmouseout="hide_desc(event,\''.$id_event.'\')" class="event" href="#'.$id_event.'">' . $info . "</a></div>\n";
                    $event_list .=<<<BOOKDESC
                    <div class="desc" id="desc-$id_event">
                    Title: $rec[title]<br/>
                    Date: $rec[cur_event_date]<br/> 
                    Time: $timerange<br/> 
                    Location: $rec[location_name]<br/> 
                    Description: $rec[description] <br/> 
                    Owner: $rec[full_name] 
                    </div>\n
BOOKDESC;
                }
            }
        
        }
    }
    $event_list .= '</div></div>';
}

?>
<link rel="stylesheet" type="text/css" href="<?php echo STYLE_PATH ?>cal.css" media="screen" />
<script type="text/javascript" src="js/calendar.js"></script>
<style type="text/css">
  #event_date { background-image: none; /*url("images/cal.jpg");*/
    background-position:right center; background-repeat:no-repeat;
    border:0;color:#fff; font-weight:bold;
    background-color: transparent;}
</style>
<form method="Post" id="form_facility">
<div style="width:700px;  background-color: #062312; border: 1px solid #000; ">
<div style="text-align: center;">
    <a href="javascript:void(0)" class="button" id="monthlybtn" >View Month</a>
    <a href="javascript:void(0)" class="button" id="todaybtn" >Today</a>
    <a href="javascript:void(0)" class="button" id="prevbtn" >&lt;</a>
    <a href="javascript:void(0)" class="button" id="nextbtn" >&gt;</a>
     <input type="text" size=16 id="event_date" name="event_date" value="<?php echo date('d-M-Y', $_time)?>" >
  <script>
    $('#event_date').AnyTime_picker({format: "%e-%b-%Y"});
  </script>
</div>
<div id="reference"  class="calendar">
<div  id="booking_list">
	<div id="view_day">
<?php   
    echo $fullday_list;
    echo $event_list;

?>
	</div>
<br/>
</div>
</form>
</div>
</div>

<?php echo $timed_list; ?>

<script type="text/javascript">
var d = document.getElementById('id_facility');
var conv = new AnyTime.Converter({format:  "%e-%b-%Y"});
var curdate = "<?php echo date("Y-n-j", $_time);?>";

if (d && (d.options.length == 1) && (d.options[0].value == 0)){
	$('#id_facility').attr('disabled', 'disabled');
	$('#display').attr('disabled', 'disabled');
	$('#event_date').attr('disabled', 'disabled');
}

function export_schedule()
{
	var book_date = $('#event_date').val();
	location.href="./?mod=facility&sub=booking&act=list&date="+book_date+"&facility="+$('#id_facility').val()+"&do=export";
}

function view_date(dt){
    location.href="<?php echo $_self ?>&d="+dt;    
}

$('#prevbtn').click(function(e){
    view_date("<?php echo date('Y-n-j', $prev_day_time)?>");
});

$('#nextbtn').click(function(e){
    view_date("<?php echo date('Y-n-j', $next_day_time)?>");
});

$('#monthlybtn').click(function(e){
    var dt = conv.parse($('#event_date').val());
    var sdt = dt.getFullYear()+"-"+(dt.getMonth()+1)+"-"+dt.getDate();
    location.href="./?mod=portal&portal=calendar&d="+sdt;
});

$('#todaybtn').click(function(e){
    var dt = new Date();
    var sdt = dt.getFullYear()+"-"+(dt.getMonth()+1)+"-"+dt.getDate();
    view_date(sdt);
});

$('#id_facility').change(function(e){
    var dt = conv.parse($('#event_date').val());
    var sdt = dt.getFullYear()+"-"+(dt.getMonth()+1)+"-"+dt.getDate();
    view_date(sdt);
});

$('#event_date').change(function(e){
    var dt = conv.parse($('#event_date').val());
    var sdt = dt.getFullYear()+"-"+(dt.getMonth()+1)+"-"+dt.getDate();
    if (sdt != curdate){
        view_date(sdt);
        curdate = sdt;
    }
});

$('.bookitem').click(function(e){
    location.href="./?mod=portal&portal=calendar&act=view&id="+e.target.id.substring(4)+'&d='+e.target.href.substring(e.target.href.lastIndexOf('#')+1);
});

<?php
echo $js;
?>
if (starts.length>0){
    var s
    for(var i=0; i<starts.length; i++){
        var tm = starts[i];
        var html = $('#info-'+tm+'-'+events[i]).html();
        var p = $('#info-'+tm).offset();
        $('#info-'+tm+'-'+events[i]).show();
        $('#info-'+tm+'-'+events[i]).offset({top: p.top, left: 0});
    }
}
</script>
