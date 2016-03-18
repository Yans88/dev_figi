<script type="text/javascript" src='./js/jquery.MultiFile.js' language="javascript"></script>
<?php
//ob_start();
include_once './facility/facility_util.php';
$config = $configuration['facility'];

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_title = isset($_GET['title']) ? $_GET['title'] : null;
$now = time();
$dts = isset($_GET['dts']) ? $_GET['dts'] : $now;
$dte = isset($_GET['dte']) ? $_GET['dte'] : $now;
$st = '';

$timesheet = null;
$id_facility = !empty($_POST['id_facility']) ? $_POST['id_facility'] : 0;
if ($id_facility == 0)
    $id_facility = !empty($_GET['id_facility']) ? $_GET['id_facility'] : 0;

$facility = get_facility($id_facility);




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
//http://localhost/~elbas/afigi/portal/?portal=facility&dts=1353603600&dte=1353603600
$time_start_str = $_t;
$dttm = mktime(date('G', $dttm)+1,intval(date('i', $dttm)),intval(date('s', $dttm)),date('n', $dttm),date('j', $dttm),date('Y', $dttm));
$time_finish_str = date('H:i', $dttm);    

if (isset($_POST['date_start'])){
    $date_start = convert_date($_POST['date_start'], 'Y-m-d');
    $date_start_str = convert_date($_POST['date_start'], 'j-M-Y');
    $date_finish_str = convert_date($_POST['date_finish'], 'j-M-Y');
    //$timesheet = build_timesheet_book($date_start);
} else {
    //$date_start_str = date('j-M-Y', $tm);
    //$date_finish_str = date('j-M-Y', time_add_days($tm, 1));
    $date_start_str = date('j-M-Y', $dts);
    $date_finish_str = date('j-M-Y', $dte);
    $time_start_str = date('H:i', $dts);
    $time_finish_str = date('H:i', $dte);
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
$instance = null;
$caption =  'View Detailed Facility Booking' ;
$enable_recurrent = true;

if (!empty($book)){
	//print_r($book);
	$instance = get_instance_info($_id, $dts, $dte);
	if ($instance){
		//($instance['fullday'] == $book['fullday']) && 
		$enable_recurrent = (date('Hi', $book['dt_start'])==date('Hi', $dts)) && (date('Hi', $book['dt_end'])==date('Hi', $dte));
		if (!empty($instance['remark'])) $book['remark'] = $instance['remark'];
		if (!empty($instance['purpose'])) $book['purpose'] = $instance['purpose'];
		$book['dt_start'] = $dts;
		$book['dt_end'] = $dte;
	}
	//echo date('Hi', $book['dt_start']).'!='.date('Hi', $dts) .'||' .date('Hi', $book['dt_end']).'!='.date('Hi', $dte);
	//print_r($instance);
	//print_r($book);
	
    $facility = get_facility($book['id_facility']);
    $repeat_until = !empty($book['dt_last']) ? 1 : 0;
    $repeat_until_str =  ($repeat_until == 0) ? null : date('j-M-Y', $book['dt_last']);
    $weekdays = explode(',', $book['wd_start']);
    $selected_repetition = $book['repetition'];
    $id_facility= $book['id_facility'];
    if ((USERGROUP != GRPADM) || ($book['id_user'] != USERID))
        $caption = 'View Booking Info';
    if ($selected_repetition=='NONE'){
        $date_start_str = date('j-M-Y', $book['dt_start']);
        $date_finish_str = date('j-M-Y', $book['dt_end']);
        
    }
    
} else {
    $book['purpose'] = $_title;
    $book['remark'] = null;
    $book['fullday'] = 1;
}


?>
<script type='text/javascript' src='./js/params.js'></script>
<script type='text/javascript' src='./js/fullcalendar/fullcalendar.min.js'></script>
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
      <div class="leftcol" style="width: 260px; text-align: left; padding-left: 5px" ><h2 style="color: #000; display: inline">Facility Booking Form</h2></div>
     <div class="submenu" style="float: right">
        <a href="./?mod=portal&portal=facility" class="linkthis">Facility Booking Form</a> | 
        <a href="./?mod=portal&sub=history&portal=facility" class="linkthis">Facility Booking History</a>
     </div>
     <div class="clear"></div>
     <form method="post" id="form_facility"  enctype="multipart/form-data">
     <input type="hidden" name="portal" value="FACILITY">
     <input type="hidden" name="submitcode" value="">
     <input type="hidden" name="remove" value="">
     <input type="hidden" name="savepart" id="savepart" value="<?php echo (!$enable_recurrent) ? 'only-me' : 'all-of-me';?>">
     <table width="98%" class="cellform" cellpadding=3 cellspacing=1 style="border: 1px solid #103821; padding: 2px 2px 2px 2px">
      <tr>
        <th align="center" colspan=2><?php echo $caption?></th>
      </tr>
      <tr>
        <td align="left" width=130>Facility/Room</td>
        <td align="left">
		
		<?php echo $facility_list[$id_facility];?>
		
		</td>
      </tr>
      <tr class="normal">
        <td align="left">Date/Time</td>
        <td align="left">
          From: <?php echo $date_start_str?> 
          &nbsp;<?php echo $time_start_str?> 
          To: &nbsp; 
          <?php echo $date_finish_str?>
          &nbsp;<?php echo $time_finish_str?>
          <!--
		  <br/><input type="checkbox" id="fullday" name="fullday" value="yes"><label for="fullday">Book for full day</label>
		  -->
		  <script type="text/javascript">
                    var oneDay = 24*60*60*1000;
                    var dateFormat = "%e-%b-%Y";      
                    var today = new Date();
                    $('#date_start').AnyTime_noPicker().AnyTime_picker({earliest: today, format: dateFormat});
                    var dateConv = new AnyTime.Converter({format:dateFormat});
                    //today.setHours(today.getHours()+1);
                    $('#date_finish').AnyTime_noPicker().AnyTime_picker({earliest: today, format: dateFormat});

		  </script>
          <!--&nbsp; <input type="submit" name="display_timesheet" value="View Schedule">-->
        </td>
      </tr>
<?php if ($enable_recurrent){ ?>      
      <tr class="normal" >
        <td align="left" valign="top">Repetition</td>
        <td align="left">
            <select id="repetition" name="repetition" readonly disabled><?php echo build_option($repetitions, $selected_repetition);?></select>
            <div id="repetition_option" style="display: none">
            <div id="repetition_interval" >
            <div class="leftcol">Repeat every:</div>
            <div class="rightcol">
                <select id="interval" name="interval" class="time" readonly disabled></select> 
                <span id="interval_name" ></span>
            </div>
            </div>
            <div id="repeat_option_weekly" style="display: none">
                <div class="leftcol">Repeat on: </div>
                <div class="rightcol">
                <input type="checkbox" readonly disabled name="repeat_option[]" value=0 <?php if (in_array(0, $weekdays)) echo ' checked';?>>S &nbsp;
                <input type="checkbox" readonly disabled name="repeat_option[]" value=1 <?php if (in_array(1, $weekdays)) echo ' checked';?>>M &nbsp;
                <input type="checkbox" readonly disabled name="repeat_option[]" value=2 <?php if (in_array(2, $weekdays)) echo ' checked';?>>T &nbsp;
                <input type="checkbox" readonly disabled name="repeat_option[]" value=3 <?php if (in_array(3, $weekdays)) echo ' checked';?>>W &nbsp;
                <input type="checkbox" readonly disabled name="repeat_option[]" value=4 <?php if (in_array(4, $weekdays)) echo ' checked';?>>T &nbsp;
                <input type="checkbox" readonly disabled name="repeat_option[]" value=5 <?php if (in_array(5, $weekdays)) echo ' checked';?>>F &nbsp;
                <input type="checkbox" readonly disabled name="repeat_option[]" value=6 <?php if (in_array(6, $weekdays)) echo ' checked';?>>S &nbsp;
                </div>
            </div>
            <div id="repeat_option_monthly" style="display: none">
            <!--
                <div class="leftcol">Repeat per:</div>
                <div class="rightcol">
                    <input type="radio" readonly disabled name="repeat_option[]" value=0 <?php if ($repeat_option=='0') echo ' checked';?> >date of month
                    <input type="radio" readonly disabled name="repeat_option[]" value=1 <?php if ($repeat_option=='1') echo ' checked';?> >day of week
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
            $('#date_until').AnyTime_noPicker({earliest: dateuntil, format: dateFormat});
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
<?php } // enable_recurrent 
?>
	<tr>
        <td align="left">Attachment File</td>
        <td align="left">
        		<?php 
        			if($id>0){
        				$book_att = get_booking_attachment($id);
        				
        				foreach($book_att as $row){
        					echo '<a href="./?mod=portal&portal=facility_attachment&sub=portal&id_item='.$row['id_attach'].'" style="color: blue">'.$row['filename'].'</a>';
        				}
        			}
        		?>
			
		</td>
    </tr>
      <tr class="normal">
        <td align="left">Purpose of Use</td>
        <td align="left">
            <?php echo $book['purpose']?>
               
            </div>
        </td>
      </tr>
      <tr class="normal">
        
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
    //if ($_id>0)
        //echo '<button type="button" id="newbook">Create New</button>';
?>
            <a href="./?mod=portal&portal=facility&dts=<?php echo $dts ?>&dte=<?php echo $dte ?>&id_facility=<?php echo $id_facility ?>&id=<?php echo $_id ?>"><button type="button" id="booking">Edit Booking</button></a>
            <br/>&nbsp;
        </td>
      </tr>
     </table>
     </form>

    
     

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

<script type="text/javascript" src="./js/jquery.json.js"></script>
<script type="text/javascript" src="./js/portal_facility.js"></script>
<script type="text/javascript">

max_period = '<?php echo $facility['max_period']?>';
lead_time = '<?php echo $facility['lead_time']?>';
repetitions = '<?php echo implode(',', $repetitions);?>'.split(',');

tm_start = "<?php echo $time_start_str?>";
tm_finish = "<?php echo $time_finish_str?>";

var bookinfo_json = '<?php echo json_encode($book);?>';
bookinfo_json = bookinfo_json.replace(/\n/g,'\\n');
bookinfo_json = bookinfo_json.replace(/\r/g,'\\r');
var book = JSON.parse(bookinfo_json);
var enable_recurrent = <?php echo ($enable_recurrent) ? 1: 0?>;

if ($('#id_facility').val()>0)	
    $('#id_facility option').trigger("change");
var st = '<?php echo $st ?>';
if(st!=''){
if (st == 'OK') {           
                // var buttons = {'Close': function(e){$('#msgok').dialog('close');}};
                $('#msgok').dialog({
                        modal: true, 
                        title: 'Request Info', width: 350, height: 120,
			beforeClose: function(event,ui){
				location.href='./?mod=portal&portal=facility&sub=history&id_facility='+$('#id_facility').val();
				}});
            } else if (st.substring(0,8) == 'CONFLICT') {
                 $('#msgconflict').dialog({ modal: true, 
                         title: 'Request Info', width: 400, height: 120});
            } else {
                //var buttons = {'Close': function(e){$('#msgerr').dialog('close');}};
                $('#msgerr').dialog({
                        modal: true, 
                        title: 'Request Info', width: 350, height: 120});            
            }
}
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

function saveme_please(changes){
    var dateFormat = "%e-%b-%Y %H:%i:%s";      
    var dateConv = new AnyTime.Converter({format:dateFormat});
    var sd = new Date(dateConv.parse($("#date_start").val()+' '+$('#time_start').val()+':00'));
    var dd = new Date(dateConv.parse($("#date_finish").val()+' '+$('#time_finish').val()+':00'));

	var confirm_text = "Do you wish to proceed?\n";
	if (_recur && $('#savepart').val()=='all-of-me')
		//confirm_text =  'Book from '+$('#date_start').val()+' to '+$('#date_finish').val()+ " will be changed!\n"+confirm_text;
	if (changes[0] || changes[1]){
		var format = "dS MMM yyyy{ - [dS MMM yyyy]} '('hh:mmtt{-hh:mmtt}')'";
		confirm_text = 'Your current booking '+$.fullCalendar.formatDates(new Date(book.dt_start*1000),new Date(book.dt_end*1000),format)+
						' will be changed to '+$.fullCalendar.formatDates(sd,dd,format)+
						'. Do you wish to proceed?';
	}
    var saveme = confirm(confirm_text);
    if (saveme){
		$('#form_facility').submit();
        // var url = '<?php echo $_SERVER['REQUEST_URI']?>';
        // $.post(url, $('#form_facility').serialize(), function(data){
        
            //      
        // });
    }
}


function get_changes()
{
	var changes = new Array();
	changes[0] = false; // date 
	changes[1] = false; // time
	changes[2] = false; // repetition
	changes[3] = false;	// fullday
	changes[4] = false; // other
	var dFormat = '%e-%b-%Y';
    var dConv = new AnyTime.Converter({format:dFormat});
	var start = <?php echo ($dts)?>*1000;
	var end = <?php echo ($dte)?>*1000;
	if (book){
		if (book.purpose != $('#purpose').val())  changes[4] = true;
			//changes.push('Purpose changes: '+$('#purpose').val());
		if (book.remark != null && book.remark != $('#remark').val())  changes[4] = true;
			//changes.push('Remark changes: '+$('#remark').val());
		var date_start = dConv.parse($('#date_start').val()).getTime();
		if (cmp_date(start, date_start) != 0) changes[0] = true;
			//changes.push('Date Start changes: '+$('#date_start').val()+date_start+'--'+start);
		var date_end = dConv.parse($('#date_finish').val()).getTime();
		if (cmp_date(end,date_end) != 0)  changes[0] = true;
			//changes.push('Date End changes: '+$('#date_finish').val());
		if (book.fullday!= ($('#fullday').attr('checked')))  changes[3] = true;
			//changes.push('Repetition changes: '+$('#fullday').val());
			/*
		if (book.repetition != $('#repetition').val()) changes[2] = true
		alert($('#repetition').val())
		if ($('#repetition').val() != 'NONE'){
			date_end = dConv.parse($('#date_until').val()).getTime();
			if (cmp_date(book.dt_last*1000, date_end) != 0) changes[2] = true;
			if (book.interval != $('#interval').val()) changes[2] = true;
			//changes.push('Repetition changes: '+$('#repetition').val());
			}
			*/
		dConv = new AnyTime.Converter({format: '%H:%i:%s'});
		if (!$('#fullday').attr('checked')){
			date_start = dConv.parse($('#time_start').val()+':00');//.getTime();
			if (cmp_time(start, date_start) != 0) changes[1] = true;
			date_finish = dConv.parse($('#time_finish').val()+':00').getTime();
			if (cmp_time(end, date_finish) != 0) changes[1] = true;
		}
	}
	
	return changes;
}

function cmp_date(d1, d2)
{
	var dt1 = new Date(d1);
	var dt2 = new Date(d2);
	dt1 = new Date(dt1.getFullYear(),dt1.getMonth(),dt1.getDate());
	dt2 = new Date(dt2.getFullYear(),dt2.getMonth(),dt2.getDate());
	//alert(dt1.getTime()+' <> '+dt2.getTime() + ', '+dt1+' <> '+dt2)
	return parseInt(dt1.getTime()) - parseInt(dt2.getTime());

}

function cmp_time(d1, d2)
{
	var dt1 = new Date(d1);
	var dt2 = new Date(d2);
	dt1 = new Date(0, 0, 0, dt1.getHours(),dt1.getMinutes(),0);
	dt2 = new Date(0, 0, 0, dt2.getHours(),dt2.getMinutes(),0);
	//alert(d1+' <> '+d2 + ', '+dt1+' <> '+dt2)
	return dt1.getTime() - dt2.getTime();

}

function fill(id, thisValue, onclick) 
{
    $('#'+id).val(thisValue);
    setTimeout("$('#suggestions_facility').fadeOut();", 100);
}

function suggest(me, inputString)
{
    var dept, url, suggest_for;
	if(inputString.length == 0) {
		$('#suggestions_facility').fadeOut();
	} else {
      url = "./facility/suggest_purpose.php"; dept = $('#dept_facility option:selected').val();  
		$.post(url, {queryString: ""+inputString+"", inputId: ""+me.id+"", deptId: ""+dept+""}, function(data){
			if(data.length >0) {
				$('#suggestions_facility').fadeIn();
				$('#suggestionsList_facility').html(data);
			}else
                $('#suggestions_facility').fadeOut();
		});
	}
}


$('.linkthis').click(function(e){
	this.href +='&id_facility='+$('#id_facility').val();
});

function isModified()
{
	var modified = false;
	if (book){
		
	}
}

</script>