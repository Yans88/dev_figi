 <style>
 ul { list-style: none}
 ul li { float: left; width: 100px;}
 </style>
<ul id="subportal">
    <li><a href="./portal/loan_history.php">Loan</a></li>
    <li><a href="./portal/service_history.php">Service</a></li>
    <li><a href="./portal/fault_history.php">Fault</a></li>
    <li><a href="./portal/facility_history.php">Facility</a></li>
</ul>
<br/>
<br/>
<?php
return;
if (!defined('FIGIPASS')) return;

$active_portals = array();
$i = 0;
$portals[] = 'deskcopy';

foreach ($portals as $portal_name){
    $path = 'portal/history_'. $portal_name . '.php';
    if (file_exists($path)){
        $active_portals[$i++] = $portal_name;
        include_once $path;
    }
}

$page_access = get_page_privileges(USERGROUP, get_page_id_by_name($_sub));
$i_can_view = (isset($page_access[CAN_VIEW] ) && ($page_access[CAN_VIEW] == 1));      // can see list/detail
$i_can_create = (isset($page_access[CAN_CREATE] ) && ($page_access[CAN_CREATE] == 1));// can create/make/submit request
$i_can_update = (isset($page_access[CAN_UPDATE] ) && ($page_access[CAN_UPDATE] == 1));// can make issue request / receive item
$i_can_delete = (isset($page_access[CAN_DELETE] ) && ($page_access[CAN_DELETE] == 1));// can approve request

$_msg = null;
$this_time = time();
$config = $configuration[$portal_name];
$lead_time = (ENABLE_REQUEST_LEADTIME) ? get_lead_time($config['request_leadtime']) : time();

?>
<script type="text/javascript">

if (!Array.prototype.indexOf)
{
  Array.prototype.indexOf = function(elt /*, from*/)
  {
    var len = this.length >>> 0;

    var from = Number(arguments[1]) || 0;
    from = (from < 0)
         ? Math.ceil(from)
         : Math.floor(from);
    if (from < 0)
      from += len;

    for (; from < len; from++)
    {
      if (from in this &&
          this[from] === elt)
        return from;
    }
    return -1;
  };
}

var months = new Array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
var min_date = <?php echo $lead_time ?>;
var lt48 = "<?php echo preg_replace('/[\r\n]/', "\\\r\n", $messages['loan_request_at_least']) ?>";
var check_leadtime = <?php echo (ENABLE_REQUEST_LEADTIME) ? 1 : 0?>;

function changetab(me, name){
  var id = 'tab_' + name;
  var tab = document.getElementById(id);
  var divs = document.getElementById('tabset').getElementsByTagName('div');
  
  for (i=0; i<divs.length; i++){
    if (divs[i].className.indexOf('tabset_content')>=0)
      divs[i].style.display = 'none';
  }
 
  tab.style.display = 'block';
  var ass = document.getElementById('buttonbox1').getElementsByTagName('a');
  for (i=0; i<ass.length; i++){
      ass[i].className = '';
  }

  me.className = 'active';
}

function msgbox_show(msg){
	if (msg == '') return;
    $('#message').html(msg);
	var ww = $(window).width();
	var wh = $(window).height();
	var mw = $('#msgbox').width();
	var mh = $('#msgbox').height();
    $('#msgbox').css('left', (ww-mw) /2);
    $('#msgbox').css('top', (wh-mh) /2);
    $('#msgbox').show();
    $('#close').focus();
}

function msgbox_hide(){
    $('#msgbox').hide();
}


</script>

<br/>
<h2 style="margin-top:-10px">FIGI Portal (History)</h2>
<div id="tabset" class="history">
  <ul class="buttonbox" id="buttonbox1">
  <?php
    foreach ($active_portals as $portal_name) 
        echo  '<li><a href="./?mod=portal&sub=history&portal='.$portal_name.'" id="'.$portal_name.'">'.ucfirst($portal_name)."</a></li> \n";
        //echo  '<li><a href="javascript:void(0)" id="'.$portal_name.'" onclick="changetab(this,\''.$portal_name.'\')"  >'.ucfirst($portal_name)."</a></li> \n";
    echo  '<li align="right"><a href="./?mod=portal&portal='.$_portal.'">Make a Request</a></li>'. "\n";
?>
 </ul>
<?php
    foreach ($active_portals as $portal_name) {
        $funcname = $portal_name . '_display_tabsheet';
        if (function_exists($funcname))
            call_user_func($funcname);    
    }
?>
<br/>&nbsp;
<br/>&nbsp;
</div>
<div class="notify" id="msgbox" style="display: none; ">
    <div id="message"></div><br/>
    <div><button type="button" id="close" onclick="msgbox_hide()"> Close </button></div>
</div>

<br/>
<script>
var curportal = '<?php echo $_portal?>';
if (curportal != '')
    changetab(document.getElementById(curportal),curportal)
//AnyTime.picker( "service_date", { format: "%e-%b-%Y %H:%i", firstDOW: 1 } );

var msg = '';
<?php 
    if (!empty($_msg)) {
        echo 'msg = "'. preg_replace('/[\r\n]/', "\\\r\n", $_msg) .'";';
    }
?>
msgbox_show(msg);


</script>
