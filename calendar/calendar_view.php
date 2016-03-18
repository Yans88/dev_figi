<?php

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
//$_d = isset($_GET['d']) ? $_GET['d'] : null;

if (isset($_POST['submitcode']) && ($_POST['submitcode'] == 1)){
    $_id = $_POST['remove'];
    $_part = $_POST['part'];
    $_recur = $_POST['repetition'];
    if ($_id > 0) {
        $remark = mysql_escape_string($_POST['remark']);
        delete_event($_id, $_d, USERID, $remark, $_part);
        if (strpos($_SERVER['HTTP_REFFERER'], 'portal')!==false)
            $url = './?mod=portal&portal=calendar';
        else
            $url = './?mod=calendar';
        $url .= '&act=view_month&d=' . $_d;
        echo <<<DELETED
        <script>
            alert('Selected event has been deleted!');
            location.href = "$url";
        </script>
DELETED;
        }
    return;
}

if (isset($_POST['date_start'])){
    $facility = get_facility($id_facility);
    $date_start = convert_date($_POST['date_start'], 'Y-m-d');
    $date_start_str = convert_date($_POST['date_start'], 'd-M-Y');
    $date_finish_str = convert_date($_POST['date_finish'], 'd-M-Y');
    //$timesheet = build_timesheet_book($date_start);
} else {
    $date_start_str = date('d-M-Y');
    $date_finish_str = date('d-M-Y', time_add_days(time(), 1));
    $facility['max_period'] = 1;
    $facility['lead_time'] = 1;
}

$_date = !empty($_GET['d']) ? $_GET['d'] : null;
if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})/', $_date, $matches)){
    list($none, $_year, $_mon, $_day) = $matches;
    $_mon = intval($_mon);
    $_day = intval($_day);

} else {
    $_day = date('j');
    $_mon = date('n');
    $_year = date('Y');    
}


//$facility_list = get_facility_list(true);	
$tm = mktime(0, 0, 0, $_mon, $_day, $_year);
$events = get_events_by_date($tm);

foreach($events as $event)
    if ($event['id_event'] == $_id) break;

$date_start_str = date('Y-M-d', $event['dt_start']);
$date_finish_str = date('Y-M-d', $event['dt_end']);
$event_date = date('Y-M-d', $event['start']);

$dt = $event['dt_start'];
$dte = $event['dt_end'];
$delta = $dte-$dt;
$dte  = date_add_day($tm, $delta);

if ($event['fullday']>0){
    //$event_date .= ' - ' . date('D, d M', $dte) . ', fullday event.';
} else {
    //$event_date .= ', ' . $event['time_start_fmt'];
    //$event_date .= ' - ' . date('D, d M', $dte) . ', ' . $event['time_finish_fmt'];
}

?>
<script type="text/javascript" src="./js/jquery.fancybox.pack.js?v=2.0.6"></script>
<link rel="stylesheet" type="text/css" href="./style/default/jquery.fancybox.css?v=2.0.6" media="screen" />


<div id="calendar_view" class="calendar">
     <form method="post" id="form_facility">
     <input type="hidden" id="submitcode" name="submitcode" value="">
     <input type="hidden" id="remove" name="remove" value="<?php echo $_id?>">
     <input type="hidden" id="remark" name="remark" value="">
     <input type="hidden" id="remark" name="repetition" value="<?php echo $event['repetition']?>">
     <input type="hidden" id="part" name="part" value="">
     
     <table width="100%" class="itemlist" cellpadding=4 cellspacing=0>
     <tr><th colspan=2><h3>Calendar Event Detail (Viewing)</h3></th></tr>
      <tr class="alt">
        <td align="left" width=100>Title</td>
        <td align="left"><?php echo $event['title']?></td>
      </tr>
      <tr>
        <td align="left">Location</td>
        <td align="left"><?php echo $event['location_name'];?></td>
      </tr>
      <tr class="alt">
        <td align="left">Date/Time</td>
        <td align="left"><?php echo $event_date; ?></td>
      </tr>
      <tr >
        <td align="left">Repeat</td>
        <td align="left">
            <?php 
            //echo $repetitions[$event['repetition']];
            if ($event['repetition'] != 'NONE'){
                echo 'Every '.$event['interval'].' '.$repeat_labels[$event['repetition']];
            if (($event['repetition']=='WEEKLY') && !empty($event['wd_start'])){ // weekly                
                    $options = explode(',', $event['wd_start']);
                    $days = array();
                    foreach ($options as $d)
                        $days[] = $day_names[$d];
                    echo ' on ' . implode(', ', $days);
                }
                
                if ($event['dt_last'] == 0) echo ' forever.';
                else echo ' until '. date('Y-m-d', $event['dt_last']) . '.';
         
            } 
        ?>
        </td>
      </tr>
      <tr class="alt">
        <td align="left">Description</td>
        <td align="left"><?php echo $event['description']?></td>
      </tr>
      <tr class="normal">
        <td align="left" >Created By</td>
        <td align="left"><?php echo $event['full_name'] ?></td>
      </tr>
     <tr >
        <th colspan=2 align="right">
            <button type="button" id="calbtn">Calendar View</button>
<?php
    if (defined('USERID') && USERID == $event['id_user']){
?>

            <a class="button" id="confirmbtn" href="#deleteoption">Delete</a>
            <button type="button" id="editbtn">Edit</button>
<?php
    }
?>
        </th>
      </tr>
     </table>
<?php
    if ($event['repetition']>0){
?>
     <table id="deleteoption" style="display: none;">
      <tr><th colspan=2 align="left">Delete Recurrent Event</th></tr>
      <tr><td colspan=2 >Do you want to delete only this event, all events in series or this event and following?</td></tr>
      <tr>
        <td>
            Delete reason:<br/>
            <textarea id="deletereason" name="deletereason" rows=5 cols=40></textarea>
        </td>
        <td>
            Which event to be deleted: &nbsp;<br/>
            <button type="button" name="deleteevent" value=1 class="delbtn">Only this event</button><br/> 
            <button type="button" name="deleteevent" value=2 class="delbtn">This and following events</button> <br/>
            <button type="button" name="deleteevent" value=3 class="delbtn">All events in this series</button> 
        </td>
      </tr> 
     </table>     
<?php
    } 
    else  {
?>
     <table id="deleteoption" style="display: none;">
      <tr><th colspan=2 align="left">Delete an Event</th></tr>
      <tr><td colspan=2 >Do you want to delete this event? Write the reason of deleting this event! <br/>&nbsp;</td></tr>
      <tr>
        <td>
            <textarea id="deletereason" name="deletereason" rows=5 cols=40></textarea>
        </td>
        <td>
            <button type="button" name="deleteevent" value=1 class="delbtn">Delete this event</button>
        </td>
      </tr> 
     </table>
<?php
    } // non-recurring
?>
     </form>
</div>
<script type="text/javascript">
var dt = new Date("<?php echo $event['date_start']?>");
$('#editbtn').click(function(e){
    location.href="./?mod=calendar&act=edit&id=<?php echo "$_id&d=$_date"?>";
});

//var referer = '<?php echo $_SERVER['HTTP_REFERER'];?>';
$('#confirmbtn').click(function (e){
    //$('#deleteoption').show();
});

$('.delbtn').click(function (e){
    if ($('#deletereason').val() != ''){
        if (confirm('Are you sure delete this event?')){
            $('#part').val($(this).val());
            $('#remark').val($('#deletereason').val());
            $('#submitcode').val(1);
            $('form').submit();
        }
    } else {
        alert('Please write the reason of deleting this event!');
    }
});

$('#delbtn').click(function (e){
	if (confirm('Are you sure delete this event?')){
            var remark = prompt('Delete for what reason: ');
             if (remark != null && remark != undefined){  
                $.post("calendar/event_delete.php", {id: <?php echo $_id?>, remark: ""+remark+"", submitcode: "remove"}, function(data){
                    //alert(data)
                    if (data == 'OK'){
                        alert('Selected calendar event has been deleted');
                        if (/portal/.test(location.href))
                            location.href="./?mod=portal&portal=calendar";
                        else
                            location.href="./?mod=calendar";
                    } else
                        alert('Fail to delete  calendar event');
                });
            }
        }
});

$('#calbtn').click(function(e){
    location.href="./?mod=calendar";
});
$("#confirmbtn").fancybox({'hideOnContentClick': true});
</script>
