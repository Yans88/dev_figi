<?php

include '../util.php';
include '../common.php';
include 'loan_util.php';

$the_list = null;
$_cat = !empty($_POST['category']) ? $_POST['category']:0;
$items = get_checklist_items($_cat);
if (empty($items)) {
	exit;
}
$the_list = "<table width='100%' class='itemlist' id='checklist'>";
//$the_list .= "<tr><th width=10>No</th><th colspan=2>Title</th><th width=160>Otion</th></tr>";
$the_list .= "<tr><td width=20></td><td colspan=2></td><td width=160></td></tr>";
$no = 1;
$row = 1;
$is_same_root = true;

foreach ($items as $id_check => $rec){
	if ($rec['is_enabled']!=1) continue;
	$_chk = $rec['id_check'];
	$is_mandatory = ($rec['is_mandatory']) ? 'mandatory' : 'optional';
	$is_enabled = ($rec['is_enabled']) ? 'Enabled' : 'Disabled';
	$cn = ($row % 2 == 0) ? 'alt' : '';
	$mandatory_sign = ($rec['is_mandatory']) ? '<span style="color: red">*</span>' : '';
	if (empty($rec['items'])){
		$cb  = '<input type="radio" class="chk '.$is_mandatory.'" id="cbo-'.$id_check.'-yes" name="cbo['.$id_check.']" value="yes"><label for="cbo-'.$id_check.'-yes">Yes</label> &nbsp;';
		$cb .= '<input type="radio" class="chk '.$is_mandatory.'" id="cbo-'.$id_check.'-no" name="cbo['.$id_check.']" value="no"><label for="cbo-'.$id_check.'-no">No</label> &nbsp;';
		$cb .= '<input type="radio" class="chk '.$is_mandatory.'" id="cbo-'.$id_check.'-na" name="cbo['.$id_check.']" value="na" checked><label for="cbo-'.$id_check.'-na">N/A</label>';
	} else { 
		$cb = null;
		$mandatory_sign = null;
	}
	if (empty($rec['title'])) $rec['title']='-';
	$the_list .= "<tr class='itemrow $cn' id='row-$id_check'><td class='center'>$no.</td>";
	$the_list .= "<td colspan=2 class='item'><span  class='ct'>$rec[title]</span> $mandatory_sign</td> ";
	$the_list .="<td class='co center'>$cb</td></tr>";
	$row++;

	if (!empty($rec['items'])){
//print_r($rec['items']);
		foreach ($rec['items'] as $id_child => $child){
			if ($child['is_enabled']!=1) continue;
			$is_mandatory = ($child['is_mandatory']) ? 'mandatory' : 'optional';
			$is_enabled = ($child['is_enabled']) ? 'Enabled' : 'Disabled';
			$cn = ($row % 2 == 0) ? 'alt' : '';
			$mandatory_sign = ($rec['is_mandatory']) ? '<span style="color: red">*</span>' : '';
			$cb  = '<input type="radio" class="chk '.$is_mandatory.'" id="cbo-'.$id_child.'-yes" name="cbo['.$id_child.']" value="yes"><label for="cbo-'.$id_child.'-yes">Yes</label> &nbsp;';
			$cb .= '<input type="radio" class="chk '.$is_mandatory.'" id="cbo-'.$id_child.'-no" name="cbo['.$id_child.']" value="no"><label for="cbo-'.$id_child.'-no">No</label> &nbsp;';
			$cb .= '<input type="radio" class="chk '.$is_mandatory.'" id="cbo-'.$id_child.'-na" name="cbo['.$id_child.']" value="na" checked><label for="cbo-'.$id_child.'-na">N/A</label>';
			$the_list .= "<tr class='itemrow $cn' id='row-$child[id_check]'><td></td><td width=20>&nbsp; </td>";
			$the_list .= "<td class='item'><span  class='ct'>$child[title]</span> $mandatory_sign </td>";
			$the_list .="<td class='co center'>$cb</td></tr>";
			$row++;
		}
	}
	$no++;
}
$the_list .= '</table>';

//error_log($the_list);
echo $the_list;
