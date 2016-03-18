<?php
$_year = date('Y');
$_mon = date('n');
$_day = date('j');
$_date = !empty($_GET['d']) ? $_GET['d'] : date('Y-n-j');
if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})/', $_date, $matches))
    list($none, $_year, $_mon, $_day) = $matches;
//print_r($_POST);

$_facility = isset($_GET['_facility']) ? $_GET['_facility'] : 0;
if ($_facility == 0) $_facility = isset($_GET['id_facility']) ? $_GET['id_facility'] : 0;
if ($_facility == 0) $_facility = isset($_POST['id_facility']) ? $_POST['id_facility'] : $_facility;
$facilities = get_facility_list();
if (count($facilities) == 0){
	$facilities[0] = '--none--';
	$result = '<div class="error" style="margin-top: 10px">Data is not available!.</div>';
} else
if ($_facility == 0){
    $fk = array_keys($facilities);
    $_facility = $fk[0];
}
$js_path = './js';//(preg_match('/portal/', $_SERVER['PHP_SELF'])) ? '../js' : './js';
$css_path = './style';//(preg_match('/portal/', $_SERVER['PHP_SELF'])) ? '../style' : './style';    

?>

<link rel='stylesheet' type='text/css' href='<?php echo $css_path?>/default/fullcalendar.css'/>
<link rel='stylesheet' type='text/css' href='<?php echo $css_path?>/default/application.css'/>
<script type='text/javascript' src='<?php echo $js_path?>/params.js'></script>
<script type='text/javascript'>
the_year = <?php echo $_year;?>;
the_month = <?php echo $_mon-1;?>;
is_editable = <?php echo defined('FIGIPASS') ? 'true' : 'false'?>;
is_selectable = is_editable;
current_user = '<?php echo USERID?>';
<?php if (defined('USERID') && USERID>0) echo "var authenticated = true;\n";?>

</script>
<script type='text/javascript' src='<?php echo $js_path?>/fullcalendar/fullcalendar.min.js'></script>
<script type='text/javascript' src='<?php echo $js_path?>/fullcalendar/underscore.js'></script>
<script type='text/javascript' src='<?php echo $js_path?>/fullcalendar/backbone.js'></script>
<script type='text/javascript' src='<?php echo $js_path?>/jquery/jquery.tooltip.pack.js'></script>
<form method="get" id="facilityform">
<input type="hidden" name="portal" value="facility">
<input type="hidden" name="mod" value="portal">
<input type="hidden" name="sub" value="history">
<h4>Booking Request for Facility <?php echo build_combo('_facility', $facilities, $_facility)?>
&nbsp;<label id="loadinglabel" style="font-size: 10px;display: none">loading data ....</label>
<button type="button" id="viewreport">View in Report</button>
</h4>
</form>
<span class="fc-header-center">
<span class="fc-button fc-button-next fc-state-default fc-corner-left"><span class="fc-button-inner"><span class="fc-button-content" id="my-prev-button">&nbsp;&#9668;&nbsp;</span><span class="fc-button-effect"></span></span></span>
<span class="fc-button fc-button-next fc-state-default fc-corner-left fc-corner-right"><span class="fc-button-inner"><span class="fc-button-content" id="my-today-button">&nbsp;today&nbsp;</span><span class="fc-button-effect"></span></span></span>
<span class="fc-button fc-button-next fc-state-default fc-corner-right"><span class="fc-button-inner"><span class="fc-button-content" id="my-next-button">&nbsp;&#9658;&nbsp;</span><span class="fc-button-effect"></span></span></span>
</span>
<div id='calendar'></div>
<div id='eventDialog' class='dialog ui-helper-hidden'>
    <form>
        <div>
            <label>Purpose:</label>
            <input id='title' class="field" type="text" >
        </div>
        <br>
        <div id="booked-by"></div>
        <div><h5 id="event-date"></h5></div>
    </form>
</div>
<script>
$('#calendar').fullCalendar('destroy');
</script>
<script type='text/javascript' src='<?php echo $js_path?>/booking.js'></script>
<div id='confirmDialog' class='dialog ui-helper-hidden'>
    <form>
        <div>Write some reason why you want to delete this event?</div>
        <div>
            <textarea name='reason' id='reason' cols=42 rows=4></textarea>
        </div>
    </form>
</div>
<div id='deleteDialog' class='dialog ui-helper-hidden'>
    <form>
        <div>Which event do you want to delete, all event, this, or this and following?</div>
        <div style="text-align: center">
            <button type="button" button class="deleteme" id="only-me">This Event Only</button><br/>
            <button type="button" class="deleteme" id="me-follow">This Event and Following</button><br/>
            <button type="button" class="deleteme" id="all-of-me">All Event in Series</button>
        </div>
    </form>
</div>

&nbsp;
<br>
<script type='text/javascript'>
$('#_facility').change(function(e){
    //$('#facilityform').submit()
    var loc = './?mod=facility&sub=booking&act=list&_facility=' + $(this).val();
    if (location.href.search(/portal/)!=-1){
        loc = './?mod=portal&portal=facility&sub=history&_facility=' + $(this).val();
    }
    //location.href = loc;
    loc = './portal/ajax.php?mod=history&portal=facility&_facility' + $(this).val();
    //$('#tabset').tabs('url', currtab, loc);
    //$('#tabset').tabs('load', currtab);

});
$('#calendar').fullCalendar('gotoDate', the_year, the_month-1);

$('#viewreport').click(function(e){
	var d = $('#calendar').fullCalendar('getDate');
	var dt = d.getDate()+'-'+d.getMonth()+'-'+d.getFullYear();
	location.href = './?mod=report&sub=facility&act=list&term=usage&by=location&l='+$('#_facility').val()+'&d='+dt;
});

</script>
