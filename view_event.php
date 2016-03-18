<?php

$_id = isset($_GET['id']) ? $_GET['id'] : 0;

if (isset($_POST['submitcode']) && ($_POST['submitcode'] == 1)){
    $_id = $_POST['remove'];
    //delete_book($_id, USERID, $_POST['remark']);
    echo <<<DELETED
    <script>
        alert('Seleted facility booking has been deleted!');
        location.href = "./?mod=facility&sub=booking";
    </script>
DELETED;
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
} else {
    $_day = date('j');
    $_mon = date('n');
    $_year = date('Y');    
}


$facility_list = get_facility_list(true);	

$event = get_event_info($_id);
$date_start_str = $event['date_start'];
$date_finish_str = $event['date_finish'];
?>
<div id="calendar_view" class="calendar">
    &nbsp; <br/>
     <form method="post" id="form_facility">
     <input type="hidden" id="submitcode" name="submitcode" value="">
     <input type="hidden" id="remove" name="remove" value="">
     <input type="hidden" id="remark" name="remark" value="">
     
     <table width="98%" class="itemlist" cellpadding=4 cellspacing=0>
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
        <td align="left">
          From: <?php echo "$date_start_str &nbsp;"; if ($event['fullday']!=1) echo "$event[time_start]"?> &nbsp; 
          To: <?php echo "$date_finish_str &nbsp;"; if ($event['fullday']!=1) echo "$event[time_finish]"?>
          <?php
        if ($event['fullday']==1) 
          echo '<input type="checkbox" readonly checked>Full day event';
    ?>
        </td>
      </tr>
      <tr >
        <td align="left">Repetition</td>
        <td align="left">
            <?php 
            echo $repetitions[$event['repetition']];
            if ($event['repetition']>0)
                echo '. Repeat every '.$event['repeat_interval'].' '.$repeat_labels[$event['repetition']];
            if ($event['repeat_until'] == '9999-00-00') echo ' forever.';
            else 
                echo ' until '. $event['repeat_until_fmt'] . '.';
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
            <button type="button" id="calbtn">Calendar</button>
            <button type="button" id="delbtn">Delete</button>
            <button type="button" id="editbtn">Edit</button>
        </th>
      </tr>
     </table>
     </form>
     &nbsp; <br/>
</div>
<script type="text/javascript">
var dt = new Date("<?php echo $event['date_start']?>");
$('#editbtn').click(function(e){
    location.href="./?mod=calendar&act=edit&id=<?php echo "$_id&d=$_date"?>";
});

$('#delbtn').click(function (e){
	if (confirm('Are you sure delete this event?')){
            var remark = prompt('Delete for what reason: ');
             if (remark != null && remark != undefined){  
                $.post("calendar/event_delete.php", {id: <?php echo $_id?>, remark: ""+remark+"", submitcode: "remove"}, function(data){
                    //alert(data)
                    if (data == 'OK'){
                        alert('Selected calendar event has been deleted');
                       location.href="./?mod=calendar";
                    } else
                        alert('Fail to delete seletected calendar event');
                });
            }
        }
});

$('#calbtn').click(function(e){
    location.href="./?mod=calendar";
});

</script>
