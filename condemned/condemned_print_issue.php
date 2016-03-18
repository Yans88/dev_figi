<?php


if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$request = get_condemned_issue($_id);
$need_approval = REQUIRE_CONDEMNED_APPROVAL;

$item_list = get_item_by_condemned_in_table($_id);

if ($request['issue_status'] == APPROVED) 
	$caption = 'Condemned Issue Approved (In-Process)';
elseif ($request['issue_status'] == REJECTED) 
	$caption = 'Condemned Issue Rejected';
else {
    if ($need_approval)
        $caption = 'Condemned Issue Pending Approval';
    else
        $caption = 'Pending Condemned Issue';
}
ob_clean();
$style_path = defined('STYLE_PATH') ? STYLE_PATH : '';
echo <<<TEXT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>FiGi Productivity Tools</title>
<link rel="shortcut icon" type="image/x-icon" href="images/figiicon.ico" />
<link rel="stylesheet" href="{$style_path}style_print.css" type="text/css"  />
<script>
function print_it(){
    var btn = document.getElementById("printbutton");
    if (btn){
        btn.style.display = "none";
        print();
    }
}
</script>
</head>
<body>
<div id="contentcenter" align="center" >
    <div id="printout">
        <div id="header"><img src="images/logo_print.png" /></div>

<br/><br/>
<h4>$caption</h4>

TEXT;


$request['item_list'] = $item_list;
display_condemn_issue($request);
echo '<img src="images/space.gif" width=1 height=5 border=0>';

$users = get_user_list();
if ($need_approval){ // request created as approval type
    if (preg_match('/RECOMMENDED|APPROVED|CONDEMNED|DISPOSED/', $request['issue_status'])) {
        display_condemn_recommendation($request);
        if (CONDEMNATION_FLOW_TYPE==2){
            $attachment_list = build_condemn_attachment_list($_id);
?>
<img src="images/space.gif" width=1 height=5 border=0>
<table cellpadding=3 cellspacing=1 class="condemnview approve" >
    <tr align="left"><th align="left">Offline Signatured Documents</th></tr>
    <tr valign="top"><td align="left"><?php echo $attachment_list?></td></tr>    
</table>
<?php
        } // flow type =2
        echo '<img src="images/space.gif" width=1 height=5 border=0>';
        if (($request['issue_status']=='RECOMMENDED2') || 
            ((defined('ENABLE_SECOND_RECOMMENDATION') && ENABLE_SECOND_RECOMMENDATION))){
            display_condemn_recommendation2($request);
            echo '<img src="images/space.gif" width=1 height=5 border=0>';
        }  
    } // recommended
    //if (($request['issue_status'] == 'APPROVED') || ($request['issue_status'] == 'CONDEMNED')) {
    if (preg_match('/APPROVED|CONDEMNED|DISPOSED/', $request['issue_status'])) {
        if (CONDEMNATION_FLOW_TYPE==2)
            display_condemn_verification($request);
        else
            display_condemn_approval($request);
        echo '<img src="images/space.gif" width=1 height=5 border=0>';
    } // approved
} // approval type     
if ($request['issue_status'] == REJECTED) {
    display_condemn_rejection($request);
    echo '<img src="images/space.gif" width=1 height=5 border=0>';
} //rejected

if (($request['issue_status'] == 'CONDEMNED') || ($request['issue_status'] == 'DISPOSED')){
    $disposal = get_disposal_info($request['id_issue']);
    if (empty($disposal)){
      $disposal['disposal_method'] = '1';
      $disposal['disposal_date'] = 'n/a';
      $disposal['disposal_cost'] = 'n/a';
      $disposal['disposal_reference'] = 'n/a';
      $disposal['vendor_name'] = 'n/a';
      $disposal['vendor_address'] = 'n/a';
      $disposal['contact_number'] = 'n/a';
      $disposal['contact_person'] = 'n/a';
      
    }
    $attachments = get_disposal_file($request['id_issue']);
    $attachment_list = '-- attachment is not available! --';
    if (count($attachments) > 0){
      $attachment_list = '<script type="text/javascript" src="./js/slimbox2.js"></script>
                            <link rel="stylesheet" href="style/default/slimbox2.css" type="text/css" media="screen" title="no title" charset="utf-8" />
                            <ul class="attachments" >';
      foreach ($attachments as $attachment){
          $href = './?mod=condemned&act=get_disposal_attachment&name=' .urlencode($attachment['filename']);
          $attachment_list .= '<li id="att'.$attachment['id_file'].'"><a href="'.$href.'" rel="lightbox" >' . $attachment['filename'].'</a></li>';
      }
      $attachment_list .= '</ul>';
    } 
    $disposal['attachment_list']=$attachment_list;
    display_condemn_condemnation($request, $disposal);
} // condemned

?>
 	<br/><br/>
    <button id="printbutton" class="print" onclick="print_it()" >Click to Print (button disappear)</button>
    </div>
</div>
<style>
table.disposal tr { background-color: transparent; }
table.disposal td { background-color: transparent; }
table.disposal td.data { min-width: 100px}
div.data { min-width: 100px; display: inline-block}
</style>

</body>
</html>
