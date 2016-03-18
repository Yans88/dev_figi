<?php
if (!defined('FIGIPASS')) exit;

$_path = $_mod . '/' . $_sub . '_' . $_act . '.php';
if (!file_exists($_path)) $_act = 'list';
$_path = $_mod . '/' . $_sub . '_' . $_act . '.php';

$submod_url = $mod_url . '&sub=period_timesheet';
$modact_url = $submod_url."&act=$_act";
$current_url = $modact_url;
$i_can_create = true;
$i_can_delete = true;

$id_term = isset($_GET['id']) ? $_GET['id'] : 0;
$term = period_term_get($id_term);
$term_caption = $term['term'];
?>

<div class="submod_wrap">
    <div class="submod_links"><h4>Manage Period Term : <?php echo $term_caption?></h4> </div>
    <div class="clear"> </div>
</div>
<script>
function add(){
	var id_term = $('input[name=id_term]').val();
	if (id_term > 0)
		location.href = '<?php echo $current_url?>';
	$('#frm_period_term').show();
}
</script>

<?php

require $_path;


