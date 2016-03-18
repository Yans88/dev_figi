<?php
$_year = date('Y');
$_mon = date('n');
$_day = date('j');
$_date = !empty($_GET['d']) ? $_GET['d'] : date('Y-n-j');$_part = $_GET['part'];	if(!$_part){	header('location: ./?mod=report');}
if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})/', $_date, $matches))
    list($none, $_year, $_mon, $_day) = $matches;
$js_path = './js';//(preg_match('/portal/', $_SERVER['PHP_SELF'])) ? '../js' : './js';
$css_path = './style';//(preg_match('/portal/', $_SERVER['PHP_SELF'])) ? '../style' : './style';    
?>

<link rel='stylesheet' type='text/css' href='<?php echo $css_path?>/default/fullcalendar.css'/>
<link rel='stylesheet' type='text/css' href='<?php echo $css_path?>/default/application.css'/>

<script type='text/javascript' src='<?php echo $js_path?>/params.js'></script>
<script type='text/javascript'>
the_year = <?php echo $_year;?>;
the_month = <?php echo $_mon;?>;
is_editable = <?php echo defined('FIGIPASS') ? 'true' : 'false'?>;
is_selectable = is_editable;
current_user = '<?php echo USERID?>';
<?php if (defined('USERID') && USERID>0) echo "var authenticated = true;\n";?>
</script>
<script type='text/javascript' src='<?php echo $js_path?>/fullcalendar/fullcalendar.min.js'></script>
<script type='text/javascript' src='<?php echo $js_path?>/fullcalendar/underscore.js'></script>
<script type='text/javascript' src='<?php echo $js_path?>/fullcalendar/backbone.js'></script>
<script type='text/javascript' src='<?php echo $js_path?>/fault_<?php echo $_part;?>.js'></script>
<script type='text/javascript' src='<?php echo $js_path?>/jquery/jquery.tooltip.pack.js'></script>
<span class="fc-header-center">
<span class="fc-button fc-button-next fc-state-default fc-corner-left"><span class="fc-button-inner"><span class="fc-button-content" id="my-prev-button">&nbsp;&#9668;&nbsp;</span><span class="fc-button-effect"></span></span></span>
<span class="fc-button fc-button-next fc-state-default fc-corner-left fc-corner-right"><span class="fc-button-inner"><span class="fc-button-content" id="my-today-button">&nbsp;today&nbsp;</span><span class="fc-button-effect"></span></span></span>
<span class="fc-button fc-button-next fc-state-default fc-corner-right"><span class="fc-button-inner"><span class="fc-button-content" id="my-next-button">&nbsp;&#9658;&nbsp;</span><span class="fc-button-effect"></span></span></span>
</span>

<div id='calendar'></div>
<div id='eventDialog' class='dialog ui-helper-hidden'>
    <form>
        <div>
            <label>Title:</label>
            <input id='title' class="field" type="text" >
        </div>
        
        <div class="clear">&nbsp;</div>
        <h5 id="event-date" style="color: #000"></h5>
        <h5 id="location" style="color: #000"></h5>
    </form>
</div>
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
$('#calendar').fullCalendar('gotoDate', the_year, the_month-1);
//alert(current_user)
//$('#calendar').fullCalendar('rerenderEvents');
//$('#calendar').fullCalendar();
</script>
