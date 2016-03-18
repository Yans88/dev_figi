<?php
if (!defined('FIGIPASS')) exit;

$_path = $_mod . '/' . $_sub . '_' . $_act . '.php';
if (!file_exists($_path)) $_act = 'list';
$_path = $_mod . '/' . $_sub . '_' . $_act . '.php';

$submod_url = $mod_url . '&sub=period_term';
$modact_url = $submod_url."&act=$_act";
$current_url = $modact_url;

$i_can_create = true;
$i_can_delete = true;

?>

<div class="submod_wrap">
    <div class="submod_title"><h4>Manage Term Period</h4></div>
    <div class="submod_links"></div>
    <div class="clear"> </div>
</div>
<script>

</script>
<?php
require $_path;
