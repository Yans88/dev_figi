<?php
if (!defined('FIGIPASS')) exit;
  
if ($_sub == null) 	$_sub = 'item';
if ($_act == null)	$_act = 'list';
$_path = 'item/' . $_sub . '.php';

include 'item/item_util.php';
if (!file_exists($_path)) 
	return;
 
$page_access = get_page_privileges(USERGROUP, get_page_id_by_name($_sub));
$i_can_view = (isset($page_access[CAN_VIEW] ) && ($page_access[CAN_VIEW] == 1));
$i_can_create = (isset($page_access[CAN_CREATE] ) && ($page_access[CAN_CREATE] == 1));
$i_can_update = (isset($page_access[CAN_UPDATE] ) && ($page_access[CAN_UPDATE] == 1));
$i_can_delete = (isset($page_access[CAN_DELETE] ) && ($page_access[CAN_DELETE] == 1));

?>
<div align="center" id="item_management">
 <?php if (USERGROUP != GRPSYSTEMADMIN){?>
<table id="modhead">
<tr>
 
  <td align="left" width="20%" id="modtitle" ><h3>Item Management</h3></td>
  <td align="right" id="modmenu">
  <!--
	<a class="button <?php echo ($_sub=='item')?'active':null?>" href="?mod=item&sub=item">Item</a>
	-->
	<a class="button <?php echo ($_sub=='category')?'active':null?>" href="?mod=item&sub=category">Category</a>	
	<a class="button <?php echo ($_sub=='accessories')?'active':null?>" href="?mod=item&sub=accessories">Accessories</a>
	
	<?php if (SUPERADMIN && specifications_enabled){?>
		<a class="button <?php echo ($_sub=='specification')?'active':null?>" href="?mod=item&sub=specification">Specification</a>
	<?php }?>	
	<a class="button <?php echo ($_sub=='vendor')?'active':null?>" href="?mod=item&sub=vendor">Vendor</a>
	<a class="button <?php echo ($_sub=='manufacturer')?'active':null?>" href="?mod=item&sub=manufacturer">Manufacturer</a>
	<a class="button <?php echo ($_sub=='brand')?'active':null?>" href="?mod=item&sub=brand">Brand</a>

  </td>
</tr>
<?php if ($_mod=='item'){ ?>
<tr>
  <td align="right" id="modmenu" colspan=6>
	<a class="button <?php echo ($_sub=='item')?'active':null?>" href="?mod=item&sub=item">Equipment Item</a>	
	<a class="button <?php echo ($_sub=='compare')?'active':null?>" href="?mod=item&sub=compare">Item Comparison</a>
	<?php } if (!SUPERADMIN){
		if(mobile_cart){
		?>
		<a class="button <?php echo ($_sub=='mobilecart')?'active':null?>" href="?mod=item&sub=mobilecart">Mobile Cart</a>	
		<?php }?>
		<a class="button <?php echo ($_mod=='machrec')?'active':null?>" href="?mod=machrec">Machine Record</a>
		<?php if(deskcopy_item_enabled){
			$page_quickDI = get_page_privileges(USERGROUP, get_pages_id_by_name('Deskcopy Item'));
			$i_can_view_quickDI = (isset($page_quickDI[CAN_VIEW] ) && ($page_quickDI[CAN_VIEW] == 1));
			if($page_quickDI){
			?>
			<a class="button <?php echo ($_mod=='deskcopy')?'active':null?>" href="?mod=deskcopy">Deskcopy Item</a>
			<?php }}?>
		<a class="button <?php echo ($_mod=='keyloan')?'active':null?>" href="?mod=keyloan">Key Loan</a>
	<?php if(alternate_import_enabled){
		$page_quickAI = get_page_privileges(USERGROUP, get_pages_id_by_name('Alternate Item Import'));
		$i_can_view_quickAI = (isset($page_quickAI[CAN_VIEW] ) && ($page_quickAI[CAN_VIEW] == 1)); 
		
		if($i_can_view_quickAI){
		?>
		<a class="button <?php echo ($_sub=='comparison_asset')?'active':null?>" href="?mod=item&sub=comparison_asset&act=import">Alternate Item Import</a>
		<?php }} ?>
	<!-- <a class="button <?php echo ($_sub=='room_usage')?'active':null?>" href="?mod=item&sub=student_add">Students Usage</a> -->	
		
	<?php if(CONSUMABLE_ITEM){?>
		<a class="button <?php echo ($_mod=='consumable')?'active':null?>" href="?mod=consumable">Consumable Item</a>		
	<?php } if(EXPENDABLE_ITEM){ 
		$page_quickEI = get_page_privileges(USERGROUP, get_pages_id_by_name('Expendables Item'));
		$i_can_view_quickEI = (isset($page_quickEI[CAN_VIEW] ) && ($page_quickEI[CAN_VIEW] == 1)); 
		if($i_can_view_quickEI){
	?>	
		<a class="button <?php echo ($_mod=='expendable')?'active':null?>" href="?mod=expendable">Expendables Item</a>	
		<?php }} if(student_usage){
			$page_quickSU = get_page_privileges(USERGROUP, get_pages_id_by_name('Student Usage '));
			$i_can_view_quickSU = (isset($page_quickSU[CAN_VIEW] ) && ($page_quickSU[CAN_VIEW] == 1));
			if($i_can_view_quickSU){
			?>
		<a class="button <?php echo ($_mod=='portal')?'active':null?>" href="?mod=portal&portal=student_usage">Student Com Lab Usage</a>		
			<?php }}?>
		<a class="button <?php echo ($_sub=='fixed_item')?'active':null?>" href="?mod=item&sub=fixed_item">Student Usage Management </a>	
  </td>
</tr>
 <?php }} // mod=item ?>
</table>
<?php
  include($_path);
?>
</div>
<br>&nbsp;<br>
<br>&nbsp;<br>