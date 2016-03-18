<?php 
if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
	    return;
		}

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_cat = 0;

$result = 0;
if ($_id > 0) {
    $field = get_extra_field($_id);
	$_cat = $field['id_category'];
    if (count($field)>0){
        $_name = $field['field_name'];
        
        $query = "DELETE FROM extra_form_field WHERE id_field = '$_id'";
        mysql_query($query);
        $result = mysql_affected_rows();
        
        user_log(LOG_UPDATE, 'Update extra_form '. $_name. '(ID:'. $_id.')');
    }
}
//echo $result;
?>
<script type="text/javascript">
    
        location.href = "./?mod=service&act=extraform&cat=<?php echo $_cat?>";
        
</script>
