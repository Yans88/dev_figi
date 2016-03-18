<?php

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_d = isset($_GET['d']) ? $_GET['d'] : date('Y-n-j');
$id_facility = !empty($_GET['id_facility']) ? $_GET['id_facility'] : 0;

if (isset($_POST['submitcode']) && ($_POST['submitcode'] == 1)){
    $_id = $_POST['remove'];
    $_part = $_POST['part'];
    $_recur = $_POST['repetition'];
    $id_facility = !empty($_POST['id_facility']) ? $_POST['id_facility'] : $id_facility;
    if ($_id > 0) {
        $remark = mysql_escape_string($_POST['remark']);
        delete_book($_id, $_d, USERID, $remark, $_part);
        if (strpos($_SERVER['HTTP_REFERER'], 'portal')!==false)
            $url = './?mod=portal&portal=facility&sub=history&act=view_month';
        else
            $url = './?mod=facility&sub=booking&do=list';
        $url .= '&id_facility='.$id_facility.'&d=' . $_d;
        echo <<<DELETED
        <script>
            alert('Selected booking has been deleted!');
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
} else {
    $_day = date('j');
    $_mon = date('n');
    $_year = date('Y');    
}


$facility_list = get_facility_list(true);	

$book = get_booking_info($_id);
$tms = $book['dt_start'];
$tmf = $book['dt_end'];
$tmd = ($tmf-$tms) / (24 * 60 * 60);
if ($book['repetition'] > 0){ // monthly
    $sd = date('j', $tms);
    $d = $_day-$sd;
    $tms = mktime(0, 0, 0, $_mon, $_day-$d, $_year);
    $date_start_str = date('d-M-Y', $tms);
    $tmf = date_add_day($tms, $tmd);
    $date_finish_str = date('d-M-Y', $tmf);
}

?>
<script type="text/javascript" src="./js/jquery.fancybox.pack.js?v=2.0.6"></script>
<link rel="stylesheet" type="text/css" href="./style/default/jquery.fancybox.css?v=2.0.6" media="screen" />

<div id="facility_view">
    &nbsp; <br/>
     <form method="post" id="form_facility">
     <input type="hidden" id="submitcode" name="submitcode" value="">
     <input type="hidden" id="remove" name="remove" value="<?php echo $_id?>">
     <input type="hidden" id="remark" name="remark" value="">
     <input type="hidden" id="remark" name="repetition" value="<?php echo $book['repetition']?>">
     <input type="hidden" id="remark" name="id_facility" value="<?php echo $book['id_facility']?>">
     <input type="hidden" id="part" name="part" value="">
     
     <table width="100%" id="tab_facility" class="itemlist" cellpadding=4 cellspacing=1 style="border: 1px solid #103821">
     <tr><th colspan=2><h4>View Booking Detail</h4></th></tr>
      <tr class="normal">
        <td align="left" width=130>Booked By</td>
        <td align="left"><?php echo $book['full_name'] ?></td>
      </tr>
      <tr class="alt">
        <td align="left" width=130>Book Date/Time</td>
        <td align="left"><?php echo date('d M y', $book['book_date']) ?></td>
      </tr>
      <tr class="normal">
        <td align="left" width=130>Facility/Room</td>
        <td align="left"><?php echo $book['location_name'];?></td>
      </tr>
      <tr class="alt">
        <td align="left">Date/Time</td>
        <td align="left">
          From: <?php echo "$date_start_str &nbsp;"; if ($book['fullday']!=1) echo "$book[time_start_fmt]"?> &nbsp; 
          To: <?php echo "$date_finish_str &nbsp;"; if ($book['fullday']!=1) echo "$book[time_finish_fmt]"?>
          <?php
        if ($book['fullday']==1) 
          echo '<input type="checkbox" readonly checked>Book for full day';
    ?>
        </td>
      </tr>
      <tr class="normal" >
        <td align="left">Repetition</td>
        <td align="left">
            <?php 
            
            echo $repetitions[$book['repetition']];
            if ($book['repetition'] != 'NONE')
                echo '. Repeat every '.$book['interval'].' '.$repeat_labels[$book['repetition']];
            if (($book['repetition']=='WEEKLY') && !empty($book['wd_start'])){ // weekly                
                $options = explode(',', $book['wd_start']);
                $days = array();
                foreach ($options as $d)
                    $days[] = $day_names[$d];
                echo ' on ' . implode(', ', $days);
            }
            if ($book['dt_last'] > 0) 
                echo ' until '. date('d M Y.', $book['dt_last']) ;
            else 
                echo ' forever.';
            
?>
        </td>
      </tr>
      <tr class="alt">
        <td align="left">Purpose of Use</td>
        <td align="left"><?php echo $book['purpose']?></td>
      </tr>
      <tr class="normal">
        <td align="left">Remarks / <br> Special Requirements</td>
        <td align="left"><?php echo $book['remark']?></td>
      </tr>
     <tr >
        <td colspan=2 align="right">
<?php
    if (($book['id_user'] == USERID) || (USERGRP == GRPADM)){
?>
            <button type="button" id="confirmbtn">Delete</button>
            <button type="button" id="editbtn">Edit</button>
<?php } ?>
        </td>
      </tr>
     </table>
    <div id="deleteDialog" class="dialog ui-helper-hidden">
<?php
    if ($book['repetition']>0){
?>
     <table id="deleteoption" width="100%" cellpadding=3 cellspacing=1>
      <tr><th align="left">Delete Recurrent Booking</th></tr>
      <tr><td >Do you want to delete only this booking, all bookings in series or this booking and following?</td></tr>
      <tr>
        <td>
            Delete reason:<br/>
            <textarea id="deletereason" name="deletereason" rows=4 cols=50></textarea>
        </td>
    </tr>
    <tr>
        <td>
            Which event to be deleted: &nbsp;<br/>
            <button type="button" name="deleteevent" value=1 class="delbtn">Only this booking</button><br/> 
            <button type="button" name="deleteevent" value=2 class="delbtn">This and following bookings</button>
            <button type="button" name="deleteevent" value=3 class="delbtn">All bookings in this series</button> 
        </td>
      </tr> 
     </table>  
   
<?php
    } 
    else  {
?>
     <table id="deleteoption" width="100%" cellpadding=3 cellspacing=1 >
      <tr><th align="left">Delete a Booking</th></tr>
      <tr><td >Do you want to delete this booking? Write the reason of deleting this booking! <br/>&nbsp;</td></tr>
      <tr>
        <td>
            <textarea id="deletereason" name="deletereason" rows=4 cols=50></textarea>
        </td>
    </tr>
    <tr>
        <td>
            <button type="button" name="deleteevent" value=1 class="delbtn">Delete this booking</button>
        </td>
      </tr> 
     </table>
<?php
    } // non-recurring
?>     
    </div>
     </form>
     &nbsp; <br/>
</div>

<script type="text/javascript">
var dt = new Date("<?php echo $book['dt_start']?>");
$('#editbtn').click(function(e){
    location.href="./?mod=portal&portal=facility&id=<?php echo "$_id&d=$_date"?>";
});
/*

$('#delbtn').click(function (e){
    //$('#deleteoption').show();
	if (confirm('Are you sure delete this book?')){
            var remark = prompt('Delete for what reason: ');
             if (remark != null && remark != undefined){  
                $.post("facility/booking_delete.php", {id: <?php echo $_id?>, remark: ""+remark+"", submitcode: "remove"}, function(data){
                   // alert(data)
                    if (data == 'OK'){
                        alert('Selected facility booking has been deleted');
                       location.href="./?mod=facility&sub=booking";
                    } else
                        alert('Fail to delete selected facility booking');
                });
            }
        }
});
$('button[name="deleteevent"]').click(function (e){
        var opt = $(this).val();    
	if (confirm('Are you sure delete this book?')){
            var remark = prompt('Delete for what reason: ');
             if (remark != null && remark != undefined){  
                $.post("facility/booking_delete.php", {id: <?php echo $_id?>, remark: ""+remark+"", option: ""+opt+"", submitcode: "remove"}, function(data){
                   // alert(data)
                    if (data == 'OK'){
                        alert('Selected facility booking has been deleted');
                       location.href="./?mod=facility&sub=booking";
                    } else
                        alert('Fail to delete selected facility booking');
                });
            }
        }
});
*/
$('.delbtn').click(function (e){
    if ($('#deletereason').val() != ''){
        if (confirm('Are you sure delete this booking?')){
            $('#part').val($(this).val());
            $('#remark').val($('#deletereason').val());
            $('#submitcode').val(1);
            $('form').submit();
        }
    } else {
        alert('Please write the reason of deleting this booking!');
    }
});
//$(".confirmbtn").fancybox({'hideOnContentClick': true});
$("#confirmbtn").click( function(e){
    $('#deleteDialog').dialog({
        modal: true,
        title: 'Delete Confirmation',
        width: 500
    });
});
</script>
