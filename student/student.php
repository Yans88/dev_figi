<?php
if (!defined('FIGIPASS')) exit;
if (empty($_act)) $_act = 'list';

$modact_url = $submod_url.'&act='.$_act;
$_path = 'student/student_' . $_act . '.php';
if (!file_exists($_path)) $_act = 'list';
$_path = 'student/student_' . $_act . '.php';
if (!empty($_POST['dele'])){
	ob_clean();
	$ok = del_student($_POST['id_student']);
	if ($ok)
		echo 'DELETERESULT:OK';
	else
		echo 'DELETERESULT:ERROR';
	ob_end_flush();
	exit;
}
$_class = !empty($_POST['class']) ? $_POST['class'] : null;
$class_list = array(''=>'* select class')+get_class_list();
?>

<div class="submod_wrap">
  <div class="submod_title"><h4>Student List</h4></div>
  <div class="submod_links">
	<a class='button fancybox fancybox.iframe' href='./?mod=student&sub=student&act=edit'>Add Student</a>
	<a class='button fancybox fancybox.iframe' href='./?mod=student&act=import'>Import Student</a>
	<a class='button fancybox' href='#filters' >Filters</a>
  </div>
</div>
<div class="clear"></div>

<div id="filters" class ="filter student " style="display: none">
<form method="post">
<strong>Display Filter</strong><br>
<div class="center">
<label>Class</label> &nbsp; <span><?php echo build_combo('class', $class_list, $_class);?></span>
<div style="margin-top: 5px;">
<button>Apply Filter</button>
</div>
</div>
</form>
</div>

<script>
	$(document).ready(function() {
		//$('.fancybox').fancybox({padding: 5});
	});
//$('a[href=#filter]').
</script>
<div style="color:#fff;">
<?php

require($_path);
?>
</div>

