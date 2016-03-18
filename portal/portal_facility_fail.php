<?php
include_once './facility/facility_util.php';
$config = $configuration['facility'];

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_title = isset($_GET['title']) ? $_GET['title'] : null;

if (!empty($_POST)){
  ob_clean();
  $ok = facility_save_request();
  echo ($ok) ? 'OK' : 'ERR';
  ob_end_flush();
  return;
}

function facility_save_request(){
    global $_id;
    $userid = USERID;
    $_booked_date = convert_date($_POST['date_start'], 'Y-m-d H:i:s');
    $_times = !empty($_POST['times']) ? $_POST['times'] : array();
    $_facility = !empty($_POST['id_facility']) ? $_POST['id_facility'] : array();
    $saved = 0;
    // save / process request
    $ids = array();
    $purpose = mysql_real_escape_string($_POST['purpose']);
    $remark = mysql_real_escape_string($_POST['remark']);
    $date_start = !empty($_POST['date_start']) ? $_POST['date_start']: date('Y-m-d');
    $date_finish = !empty($_POST['date_finish']) ? $_POST['date_finish']: date('Y-m-d');
    $time_start = !empty($_POST['time_start']) ? $_POST['time_start'] . ':00' : '00:00:00';
    $time_finish = !empty($_POST['time_finish']) ? $_POST['time_finish'] . ':00' : '01:00:00';
    $fullday = (isset($_POST['fullday']) && ($_POST['fullday'] == 'yes')) ? 1 : 0;
    $dt_start = strtotime($date_start . ' ' .$time_start);
    $dt_end = strtotime($date_finish. ' ' .$time_finish);
    $duration = round(($dt_end-$dt_start) / (24*60*60));
    $repetition = !empty($_POST['repetition']) ? $_POST['repetition'] : 'NONE';
    $interval = isset($_POST['interval']) ?  $_POST['interval'] : 1;
    $repeat_period = isset($_POST['period']) ? $_POST['period'] : 0;
    $repeat_until = isset($_POST['repeat_until']) ? $_POST['repeat_until'] : null;
    $date_until = convert_date(@$_POST['date_until']);
    if ($repetition == 'WEEKLY'){
        $wd_start = "'" . implode(',', $_POST['repeat_option']) . "'";
    } else
        $wd_start = 'null';
    if ($repeat_until == 0) $dt_last = 'null'; // no end
    else {
        $date_until = isset($_POST['date_until']) ? $_POST['date_until'] : date('Y-m-d', $dt_end);
        if ($fullday>0) $dt_last = strtotime("$date_until 23:59:59");
        else $dt_last = strtotime("$date_until ".date('H:i', $dt_end).":59");
    }
    //print_r($_POST);
    $id_conflict = -1;
    if ($_id<=0){
        $dte = 0;
        if ($fullday) $dte = $dt_start;
        $id_conflict = check_conflict_book($_facility, $dt_start, $dte);
    }
    //return;
    if ($id_conflict>0){
        //echo '<script>alert("Conflict with other booking for timing of the facility")</script>';
        //$_msg = 'Please choose the other timing for the facility';
        echo 'CONFLICT:' . $id_conflict;
        exit;
    } else {
        $status = 'BOOK';
        $data = compact('userid', '_facility', 'dt_start', 'dt_end', 'duration', 'fullday', 'status', 'repetition', 
                    'interval', 'dt_last', 'wd_start', 'purpose', 'remark', '_id', 'purpose','remark');
        
        if ($_id > 0){ //update
            $book = get_booking_info($_id);
            $savepart = !empty($_POST['savepart']) ? $_POST['savepart'] : 'single';
            
            if ($savepart == 'only-me'){
                // create new book (and instanciate), remove old instance 
                $_id = save_new_book($data);
                delete_booking_instance($book['id_book'], $dts);
                $saved++;
            } else 
            if ($savepart == 'me-follow'){
                // create new book (and instanciate), remove old instance and the rest, set the original dt_last before new dt_start
                $_id = save_booking($data);
                delete_booking_instance($book['id_book'], $dts, -1); // start at selected book
                $dt_instance = get_latest_booking_instance($book['id_book']);
                $query = "UPDATE facility_book SET dt_last = $dt_start-86400, mdate = now(), dt_instance = $dt_instance  
                            WHERE id_book=$book[id_book] AND (dt_instance IS NULL OR dt_instance > $dt_instance)"; 
                mysql_query($query);
                $saved++;
            } else 
            if ($savepart == 'all-of-me'){ // all-of-me
                $dt_start = $book['dt_start'];
                $dt_end = $book['dt_end'];
                $data = compact('userid', '_facility', 'dt_start', 'dt_end', 'duration', 'fullday', 'status', 'repetition', 
                                'interval', 'dt_last', 'wd_start', 'title', 'description', '_id');

                $query = "UPDATE facility_book SET 
                            id_facility = $_facility, dt_start = $dt_start, dt_end = $dt_end, duration = $duration,
                            fullday = $fullday, dt_last = $dt_last, repetition = '$repetition', `interval` = $interval, 
                            wd_start = $wd_start, purpose = '$purpose', remark = '$remark', 
                            mdate = now() 
                            WHERE id_book=$_id"; 
                mysql_query($query);
                if (mysql_affected_rows()>0){
                    $need_instance_changes = ($dt_start != $book['dt_start']) || ($dt_end != $book['dt_end']) || ($dt_last != $book['dt_last'])
                                            || ($repetition != $book['repetition']) || ($interval != $book['interval']) 
                                            || ($wd_start != $book['wd_start']);
                    
                    //if ($need_instance_changes){
                        delete_instance($book['id_book']); // delete old instances
                        $dt_instance = 'null';
                        $instance_start = $book['dt_start'];
                        $instance_end = date_add_day($instance_start, 31);
                        $instance_start = $dt_start;
                    //}
                    //$instance_start = get_latest_instance($_id);
                    $query = "UPDATE facility_book SET dt_instance = $dt_instance WHERE id_book=$book[id_book]"; 
                    mysql_query($query);
                    generate_booking_instances($_id, $instance_start, $instance_end, true);    
                }
                $saved++;
            }
            else
            if ('NONE' == $book['repetition']){
                delete_booking_instance($_id);
                $query = "UPDATE facility_book SET 
                            id_facility = $_facility, dt_start = $dt_start, dt_end = $dt_end, duration = $duration,
                            fullday = $fullday, dt_last = $dt_last, repetition = '$repetition', `interval` = $interval, 
                            wd_start = $wd_start, purpose = '$purpose', remark = '$remark', 
                            mdate = now() 
                            WHERE id_book=$_id"; 
                mysql_query($query);
                //echo $query.mysql_error();
                if (mysql_affected_rows() > 0){
                    $saved++;
                    generate_booking_instances($_id); 
                }
            }
            
        } 
        else        
        { //new
            
            $_id = save_booking($data);
        }
        if ($_id>0) $saved++;
        {
        /*
        $query = "INSERT INTO facility_book(book_date, id_user, id_facility, dt_start, dt_end, fullday, repetition, 
                    repeat_interval, repeat_period, repeat_until, repeat_option, purpose, remark, status)
                    VALUES (UNIX_TIMESTAMP(), $userid, $_facility, $dt_start, $dt_end, $fullday, '$repetition', 
                    $interval, $duration, $dt_last, $wd_start, '$purpose', '$remark', 'BOOK')"; 
        mysql_query($query);
        //echo $query.mysql_error();
        if (mysql_affected_rows()>0){
            $_id = mysql_insert_id();
            
            // find out if this event overlapped 
        }
        */
        /*
        if (USERGROUP == GRPADM){
            $_time = strtotime($date_start);
            $result = get_book_on_date($_time, $_facility);
            $_tm_before = date_add_day($_time, -1);
            $day_before = date('Y-m-d', $_tm_before);
            $_tm_after = date_add_day(strtotime($date_finish), 1);
            $day_after = date('Y-m-d', $_tm_after);
            $delta = (strtotime($date_finish)-$_time)/(24*60*60);
            $_tm_next = date_add_day($_tm_after, $delta);
            $next_date_finish = date('Y-m-d', $_tm_next);
            
            $books = array();
            if (!empty($result)){
                $books = array_values($result);
                $books = $books[0];
            }
            
            foreach ($books as  $rec){
                //print_r($rec);
                if (($rec['time_start']>=$time_start || $rec['time_finish']<=$time_finish) || ($fullday>0)){
                    if (($rec['id_group'] != GRPADM) ){ //&& ($rec['repetition']>0)
                        // stop repetition just before override date for current book
                        $query = "UPDATE facility_book SET repeat_until='$day_before' WHERE id_book='$rec[id_book]'";
                        mysql_query($query);
                        
                        // duplicate current book and start after override book
                        $query = "INSERT INTO facility_book(book_date, id_user, id_facility, date_start, date_finish, time_start, time_finish, 
                                    fullday, repetition, repeat_interval, repeat_period, repeat_until, repeat_option, purpose, remark, status)
                                    VALUES ('$rec[book_date]', '$rec[id_user]', $_facility, '$day_after', '$next_date_finish', '$rec[time_start]', 
                                    '$rec[time_finish]', '$rec[fullday]', '$rec[repetition]', '$rec[repeat_interval]', '$rec[repeat_period]', 
                                    '$rec[repeat_until]', '$rec[repeat_option]', '$rec[purpose]', '$rec[remark]', 'BOOK')"; 
                        mysql_query($query);
                        //echo $query;
                    }
                }
            }
        }
        */
        }
    }
    if ($saved > 0){
        $submitted = true;     
              
        // sending email notification 
        //send_submit_facility_request_notification($_id);
        
    } else
        $submitted = false;
        
	return $submitted;
}



$timesheet = null;
$id_facility = !empty($_POST['id_facility']) ? $_POST['id_facility'] : 0;
if ($id_facility == 0)
    $id_facility = !empty($_GET['id_facility']) ? $_GET['id_facility'] : 0;
$d = !empty($_POST['d']) ? $_POST['d'] : 0;
if ($d == 0)
    $d = !empty($_GET['d']) ? $_GET['d'] : date('Y-m-d');
$tm = strtotime($d);
$_t = isset($_GET['t']) ? $_GET['t'] : null;
if ($_t == null){
    $dttm = $tm;
    if (date('i', $dttm) <= 30){
        $dttm = mktime(date('G'), 30,intval(date('s')),date('n'),date('j'),date('Y'));
        $_t = date('H:', $dttm) . '30';
    } else {
        $dttm = mktime(date('G'), 0,intval(date('s')),date('n'),date('j'),date('Y'));
        $_t = date('H:', $dttm).'00';
    }
} else {
    list($h, $m) = explode(':', $_t);
    $dttm = mktime($h, $m,intval(date('s')),date('n'),date('j'),date('Y'));
}

$time_start_str = $_t;
$dttm = mktime(date('G', $dttm)+1,intval(date('i', $dttm)),intval(date('s', $dttm)),date('n', $dttm),date('j', $dttm),date('Y', $dttm));
$time_finish_str = date('H:i', $dttm);    

if (isset($_POST['date_start'])){
    $facility = get_facility($id_facility);
    $date_start = convert_date($_POST['date_start'], 'Y-m-d');
    $date_start_str = convert_date($_POST['date_start'], 'j-M-Y');
    $date_finish_str = convert_date($_POST['date_finish'], 'j-M-Y');
    //$timesheet = build_timesheet_book($date_start);
} else {
    $date_start_str = date('j-M-Y', $tm);
    $date_finish_str = date('j-M-Y', time_add_days($tm, 1));
    $facility['max_period'] = 1;
    $facility['lead_time'] = 1;
}


$facility_list = get_facility_list(true);	
$repeat_until = 0;
$repeat_until_str = $date_finish_str;
$repeat_option = 0;
$weekdays = array(date('w'));
$selected_repetition = implode(',', $weekdays);

$book = get_booking_info($_id);
$caption = ($_id>0) ? 'Edit Facility Booking' : 'Book a Facility';

if (!empty($book)){
    $facility = get_facility($book['id_facility']);
    $repeat_until = !empty($book['dt_last']) ? 1 : 0;
    $repeat_until_str =  ($repeat_until == 0) ? null : date('d-M-Y', $book['dt_last']);
    $weekdays = explode(',', $book['wd_start']);
    $selected_repetition = $book['repetition'];
    $id_facility= $book['id_facility'];
    if ((USERGROUP != GRPADM) || ($book['id_user'] != USERID))
        $caption = 'View Booking Info';
    if ($selected_repetition=='NONE'){
        $date_start_str = date('j-M-Y', $book['dt_start']);
        $date_finish_str = date('j-M-Y', $book['dt_end']);
        
    }
    //print_r($book);
} else {
    $book['purpose'] = $_title;
    $book['remark'] = null;
    $book['fullday'] = 1;
}


?>
<style type="text/css">
  #date_start { background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
  #date_finish{ background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
  #date_until{ background-image:url("images/cal.jpg");
    background-position:right center; background-repeat:no-repeat;
    border:1px solid #5FC030;color:#000;font-weight:normal}
</style>
<div id="tab_facility" class="tabset_content">
    &nbsp; <br/>
     <form method="post" id="form_facility" action="<?php echo $_SERVER['SCRIPT_NAME']?>">
     <input type="hidden" name="portal" value="FACILITY">
     <input type="hidden" name="submitcode" value="">
     <input type="hidden" name="remove" value="">
     <input type="hidden" name="savepart" id="savepart" value="">
     <table width="100%" class="cellform" cellpadding=3 cellspacing=1 style="border: 1px solid #103821; padding: 2px 2px 2px 2px">
      <tr>
        <th align="center" colspan=2><?php echo $caption?></th>
      </tr>
      <tr>
        <td align="left" width=130>Facility/Room</td>
        <td align="left">
		<select id="id_facility" name="id_facility">
		<?php echo build_option($facility_list, $id_facility)?>
		</select>
		</td>
      </tr>
      <tr class="normal">
        <td align="left">Date/Time</td>
        <td align="left">
          From: <input type="text" size=14 id="date_start" name="date_start" value="<?php echo $date_start_str?>" > 
          <select class="time" name="time_start" id="time_start"> </select> &nbsp; 
          To: &nbsp; 
          <input type="text" size=14 id="date_finish" name="date_finish" value="<?php echo $date_start_str?>"  >
          <select class="time" name="time_finish" id="time_finish"> </select>
          <br/><input type="checkbox" id="fullday" name="fullday" value="yes"><label for="fullday">Book for full day</label>
		  <script type="text/javascript">
                    var oneDay = 24*60*60*1000;
                    var dateFormat = "%e-%b-%Y";      
                    var today = new Date();
                    $('#date_start').AnyTime_noPicker().AnyTime_picker({earliest: today, format: dateFormat});
                    var dateConv = new AnyTime.Converter({format:dateFormat});
                    $('#date_finish').AnyTime_noPicker().AnyTime_picker({earliest: today, format: dateFormat});

		  </script>
          <!--&nbsp; <input type="submit" name="display_timesheet" value="View Schedule">-->
        </td>
      </tr>
      <tr class="normal" >
        <td align="left" valign="top">Repetition</td>
        <td align="left">
            <select id="repetition" name="repetition"><?php echo build_option($repetitions, $selected_repetition);?></select>
            <div id="repetition_option" style="display: none">
            <div id="repetition_interval" >
            <div class="leftcol">Repeat every:</div>
            <div class="rightcol">
                <select id="interval" name="interval" class="time"></select> 
                <span id="interval_name" ></span>
            </div>
            </div>
            <div id="repeat_option_weekly" style="display: none">
                <div class="leftcol">Repeat on: </div>
                <div class="rightcol">
                <input type="checkbox" name="repeat_option[]" value=0 <?php if (in_array(0, $weekdays)) echo ' checked';?>>S &nbsp;
                <input type="checkbox" name="repeat_option[]" value=1 <?php if (in_array(1, $weekdays)) echo ' checked';?>>M &nbsp;
                <input type="checkbox" name="repeat_option[]" value=2 <?php if (in_array(2, $weekdays)) echo ' checked';?>>T &nbsp;
                <input type="checkbox" name="repeat_option[]" value=3 <?php if (in_array(3, $weekdays)) echo ' checked';?>>W &nbsp;
                <input type="checkbox" name="repeat_option[]" value=4 <?php if (in_array(4, $weekdays)) echo ' checked';?>>T &nbsp;
                <input type="checkbox" name="repeat_option[]" value=5 <?php if (in_array(5, $weekdays)) echo ' checked';?>>F &nbsp;
                <input type="checkbox" name="repeat_option[]" value=6 <?php if (in_array(6, $weekdays)) echo ' checked';?>>S &nbsp;
                </div>
            </div>
            <div id="repeat_option_monthly" style="display: none">
            <!--
                <div class="leftcol">Repeat per:</div>
                <div class="rightcol">
                    <input type="radio" name="repeat_option[]" value=0 <?php if ($repeat_option=='0') echo ' checked';?> >date of month
                    <input type="radio" name="repeat_option[]" value=1 <?php if ($repeat_option=='1') echo ' checked';?> >day of week
                </div>
                -->
            </div>
            <div class="leftcol">Repeat until:</div>
            <div class="rightcol">
            <ul >
                <li>
                <input type="hidden" name="repeat_until" value=2> 
                <input type="text" size=14 id="date_until" name="date_until" value="<?php echo $repeat_until_str?>"  >
                <script>
            var dateuntil = new Date(today+oneDay);
            $('#date_until').AnyTime_noPicker().AnyTime_picker({earliest: dateuntil, format: dateFormat});
        </script>
                </li>
                <!--
                <li><input type="radio" name="repeat_until" value=1 <?php if ($repeat_until==1) echo ' checked';?>>After <input type="text" value=2 name="occurrences" size=3> occurrences</li>
                <li><input type="radio" name="repeat_until" value=0 <?php if ($repeat_until==0) echo ' checked';?>>No ends</li>
                -->
            </ul>
            </div>
            </div>
        </td>
      </tr>
      <tr class="normal">
        <td align="left">Purpose of Use</td>
        <td align="left">
            <input type="text" size=55 name="purpose" id="purpose" onKeyUp="suggest(this, this.value);" autocomplete="off" 
             value="<?php echo $book['purpose']?>" >
                <div class="suggestionsBox" id="suggestions_facility" style="display: none; z-index: 500;"> 
                <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                <div class="suggestionList" id="suggestionsList_facility"> &nbsp; </div>
            </div>
        </td>
      </tr>
      <tr class="normal">
        <td align="left">Remarks / <br> Special Requirements</td>
        <td align="left"><textarea rows=3 cols=70 id="remark" name="remark"><?php echo $book['remark']?></textarea></td>
      </tr>
<?php if (!empty($timesheet)){ ?>	  
       <tr class="alt" valign="top">
        <!-- <td align="left">Time sheet</td> -->
        <td align="left" colspan=2><?php echo $timesheet?></td>
      </tr>
<?php } ?>
     <tr class="normal" >
        <td colspan=2 align="right">
            <br/>
<?php
    if ($_id>0)
        echo '<button type="button" id="newbook">Create New</button>';
?>
            <button type="button" id="booking">Save Booking</button>
            <br/>&nbsp;
        </td>
      </tr>
     </table>
     </form>
     <!--
    <div id="deleteDialog" class="dialog ui-helper-hidden">
        <form>
        <div>Write some reason why do you want to delete this booking?</div>
        <div>
            <textarea name='reason' id='reason' cols=42 rows=4></textarea>
            <br/>&nbsp;
        </div>
        <div>Which booking do you want to delete: all booking, this, or this and following?</div>
        <div style="text-align: center">
            <button type="button" button class="deleteme" id="only-me">This Event Only</button><br/>
            <button type="button" class="deleteme" id="me-follow">This Event and Following</button><br/>
            <button type="button" class="deleteme" id="all-of-me">All Event in Series</button>
        </div>
        </form>
    </div>
    -->
    <div id="saveDialog" class="dialog ui-helper-hidden">
        <form>
        <div>Changes will be applied to this booking only, this and following or all booking?</div>
        <div style="text-align: center">
            <button type="button" button class="saveme" id="only-me">This Booking Only</button><br/>
            <button type="button" class="saveme" id="me-follow">This Booking and Following</button><br/>
            <button type="button" class="saveme" id="all-of-me">All Booking in Series</button>
        </div>
        </form>
    </div>     
     
     &nbsp; <br/>
</div>
<div id='msgok' class='dialog ui-helper-hidden'>
    <div class="alertbox" style="text-align: center">
        <?php echo $messages['facility_request_success'];?>
    </div>
</div>
<div id="msgerr" class='dialog ui-helper-hidden'>
    <div class="alertbox" id="message" style="text-align: center">
        <?php echo $messages['facility_request_fail'];?> 
    </div>
</div>
<div id="msgconflict" class='dialog ui-helper-hidden'>
    <div class="alertbox" id="message" style="text-align: center">
        <?php echo $messages['facility_request_conflict'];?> 
    </div>
</div>

<script type="text/javascript" src="js/jquery.json.js"></script>
<script type="text/javascript" src="js/portal_facility.js"></script>
<script type="text/javascript">
max_period = '<?php echo $facility['max_period']?>';
lead_time = '<?php echo $facility['lead_time']?>';
repetitions = '<?php echo implode(',', $repetitions);?>'.split(',');

tm_start = "<?php echo $time_start_str?>";
tm_finish = "<?php echo $time_finish_str?>";

if ($('#id_facility').val()>0)	
    $('#id_facility option').trigger("change");

fill_interval();
$('#date_start').trigger('change');

$('#newbook').click(function (e){
    var dFormat = "%e-%b-%Y";
    var dConv = new AnyTime.Converter({format:dFormat});
    var dTime = dConv.parse($("#date_start").val()).getTime();
    var dDate = new Date(dTime);
    var d = dDate.getFullYear()+'-'+(dDate.getMonth()+1)+'-'+dDate.getDate();
    location.href = "./?mod=portal&portal=facility&id_facility="+$('#id_facility').val()+'&d='+d;
});

$('#bookedbtn').click(function (e){
    var dFormat = "%e-%b-%Y";
    var dConv = new AnyTime.Converter({format:dFormat});
    var dTime = dConv.parse($("#date_start").val()).getTime();
    var dDate = new Date(dTime);
    var d = dDate.getFullYear()+'-'+(dDate.getMonth()+1)+'-'+dDate.getDate();
    location.href = "./?mod=portal&sub=history&portal=facility&act=view_month&id_facility="+$('#id_facility').val()+'&d='+d;
});


$('#repetition').trigger("change");

$('#interval option').each(function(){
    if ($(this).val() == interval)
        $(this).attr('selected', 'selected');
});

var id_book = "<?php echo (!empty($book['id_book'])) ? $book['id_book'] : 0;?>";
var _recur = <?php echo (!empty($book['repetition']) && $book['repetition']!='NONE') ? 'true' : 'false'?>;
var fullday = "<?php echo (!empty($book) && ($book['fullday']==1)) ? 1 : 0;?>";
if (fullday == 1){
	$('#fullday').trigger("click");
	$('#fullday').trigger("change");
}

function saveme_please(){
    var saveme = confirm("Do you want to confirm this booking?\n");
    if (saveme){
        var url = '<?php echo $_SERVER['REQUEST_URI']?>';
        $.post(url, $('#form_facility').serialize(), function(data){
            //alert(data);
            if (data == 'OK') {           
                //var buttons = {'Close': function(e){$('#msgok').dialog('close');}};
                $('#msgok').dialog({
                        modal: true, 
                        title: 'Request Info', width: 350, height: 120});
                location.href = './?mod=portal&portal=facility&sub=history'
            } else if (data.substring(0,8) == 'CONFLICT') {
                $('#msgconflict').dialog({
                        modal: true, 
                        title: 'Request Info', width: 400, height: 120});
            } else {
                //var buttons = {'Close': function(e){$('#msgerr').dialog('close');}};
                $('#msgerr').dialog({
                        modal: true, 
                        title: 'Request Info', width: 350, height: 120});            
            }     
        });
    }
}

$('#booking').click(function (e){
    var dateFormat = "%e-%b-%Y";      
    var dateConv = new AnyTime.Converter({format:dateFormat});
    var sd = new Date(dateConv.parse($("#date_start").val()).getTime());
    var dd = new Date(dateConv.parse($("#date_finish").val()).getTime());
    if (!$('#fullday').attr('checked')){
        var sh = $('#time_start option:selected').val();
        var hm = sh.split(':');
        sd.setHours(hm[0]);
        sd.setMinutes(hm[1]);
        var dh = $('#time_finish option:selected').val();
        hm = dh.split(':');
        dd.setHours(hm[0]);
        dd.setMinutes(hm[1]);
    } else {
        sd.setHours(0);
        sd.setMinutes(0);
        dd.setHours(23);
        dd.setMinutes(59);
    }
    if (dd-sd < 1){
        alert("It is not allowed book a facility for previous date/time");
        return false;
    }
    if ($('#purpose').val() == ''){
		alert("Write your purpose of booking the facility!");
		return false;
	}
    var saveme = false;
    if (id_book > 0 && _recur){
        $('#saveDialog').dialog({
            modal: true,
            title: 'Save option',
            width: 450
        });
        $('.saveme').click(function(e){
            $('#saveDialog').dialog('close');
            $('#savepart').val(e.target.id);
            saveme_please();
        });
    } else 
        saveme_please();

});

function fill(id, thisValue, onclick) 
{
    $('#'+id).val(thisValue);
    setTimeout("$('#suggestions_facility').fadeOut();", 100);
}

function suggest(me, inputString)
{
    var dept, url;
	if(inputString.length == 0) {
		$('#suggestions_facility').fadeOut();
	} else { url = "facility/suggest_purpose.php"; dept = $('#dept_facility option:selected').val();
        
		$.post(url, {queryString: ""+inputString+"", inputId: ""+me.id+"", deptId: ""+dept+""}, function(data){
			if(data.length >0) {
				$('#suggestions_facility').fadeIn();
				$('#suggestionsList_facility').html(data);
			}else
                $('#suggestions_facility').fadeOut();
		});
	}
}




</script>
