<?php
if (!defined('FIGIPASS')) return;

$_sub = !empty($_GET['sub']) ? strtolower($_GET['sub']) : null;

if(ALTERNATE_PORTAL_STATUS){
$_portal = !empty($_GET['portal']) ? strtolower($_GET['portal']) : 'alternate';
} else {
$_portal = !empty($_GET['portal']) ? strtolower($_GET['portal']) : 'loan';
}

$_portal = !empty($_POST['portal']) ? strtolower($_POST['portal']) : $_portal;

define('PORTAL', $_portal);
$xtra = (!empty($_GET)) ? '&'.http_build_query($_GET) : null;
$history_link = './?mod=history&portal='.$_portal;

$active_portals = array(); $i = 0; $tabIndex = 0;
foreach ($portals as $portal_name){
    $path = 'portal/portal_'. $portal_name . '.php';
    //echo "$portal_name == $_portal<br>";
    if ($portal_name == $_portal) $tabIndex = $i;
    if (file_exists($path)){
        $active_portals[$i] = $portal_name;
        //include_once $path;
    }
    $i++;
}

?>

<div id="menubar" style="width: auto">
  <ul class="buttonboxX" id="buttonbox1">
<?php
	if(SUPERADMIN) echo '<li><a '. (($_mod==null) ? 'class="current"' : '').' href="./">Home</a></li>';
	$report = './?mod=report';
	if(USERGROUP == GRPSYSTEMADMIN){
		$report = './?mod=report&sub=item&act=view&term=list&by=reportgeneralitem';
	}
	if (SUPERADMIN)
        echo '<li><a ' . (($_mod=='user') ? 'class="current"' : '') . ' href="./?mod=user">Users</a></li>'.$crlf;
    if (in_array('item', $accessible_modules))
        echo '<li><a ' . (($_mod=='item') ? 'class="current"' : '') . ' href="./?mod=item">Items</a></li>'.$crlf;
    if (in_array('loan', $accessible_modules))
        echo '<li><a ' . (($_mod=='loan') ? 'class="current"' : '') . ' href="./?mod=loan">Loans</a></li>'.$crlf;
	//if (in_array('receive', $accessible_modules)){
//		echo '<li><a ' . (($_mod=='receive') ? 'class="current"' : '') . ' href="./?mod=receive">Receive</a></li>'.$crlf;
//	}
    if (SUPERADMIN && in_array('service', $accessible_modules))
        echo '<li><a ' . (($_mod=='service') ? 'class="current"' : '') . ' href="./?mod=service">Services</a></li>'.$crlf;
    if (SUPERADMIN && in_array('fault', $accessible_modules))
        echo '<li><a ' . (($_mod=='fault') ? 'class="current"' : '') . ' href="./?mod=fault">Faults</a></li>'.$crlf;
    if (SUPERADMIN && in_array('facility', $accessible_modules))
        echo '<li><a ' . (($_mod=='facility' || $_mod=='booking') ? 'class="current"' : '') . ' href="./?mod=booking">Facilities</a></li>'.$crlf;
	
	if (SUPERADMIN && in_array('facility', $accessible_modules))
        echo '<li><a ' . (($_mod=='maintenance') ? 'class="current"' : '') . ' href="./?mod=maintenance">Maintenance</a></li>'.$crlf;
	
    if (SUPERADMIN && in_array('condemned', $accessible_modules))
        echo '<li><a ' . (($_mod=='condemned') ? 'class="current"' : '') . ' href="./?mod=condemned">Condemn</a></li>'.$crlf;
    if (SUPERADMIN && in_array('student', $accessible_modules))
        echo '<li><a ' . (($_mod=='student') ? 'class="current"' : '') . ' href="./?mod=student">Students</a></li>'.$crlf;
    if (in_array('report', $accessible_modules))
        echo '<li><a ' . (($_mod=='report') ? 'class="current"' : '') . ' href="'.$report.'">Reports</a></li>'.$crlf;
	 if (in_array('faq', $accessible_modules))
	  echo '<li><a ' . (($_mod=='faq') ? 'class="current"' : '') . ' href="./?mod=faq">FAQ</a></li>'.$crlf;
	
?>
	<!--<li><h3 style="float: right;margin: 0 10px">FIGI  Portal</h3></li>-->
 </ul>  
</div>
<div class="clear" ></div>

<?php



	if (!empty($_sub)){
        $path = $_sub . '_' . $_portal;
    } else {
			$path = $_mod . '_' . $_portal;
	}
	
	$path .= '.php';
	
    require($path);
?>
<br/>&nbsp;<br/>
