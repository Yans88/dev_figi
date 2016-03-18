<?php
include './facility/facility_util.php';

function delete_a_book()
{
    
    $_id = isset($_GET['id']) ? $_GET['id'] : 0;
    if (delete_book($_id)){
        echo '
                <script>
                    alert("Seleted booking information has been deleted!");
                    location.href = "./?mod=portal&sub=history&portal=facility";
                </script>';
    }
}


?>

<div id="tab_facility" class="tabset_content history">
     <div class="leftcol" style="width: 300px; text-align: left; padding-left: 5px"><h2 style="color: #000; display: inline">Facility Booking History</h2></div>
     <div class="submenu" style="float: right">
        <a href="./?mod=portal&portal=facility" class="linkthis">Facility Booking Form</a> | 
        <a href="./?mod=portal&sub=history&portal=facility" class="linkthis">Facility Booking History</a>
     </div>
<script>
$('.linkthis').click(function(e){
	this.href +='&id_facility='+$('#_facility').val();
});
</script>
    <br>
    <br>
    <div class="portal_history" id="facility_history">
<?php  
    $act = !empty($_GET['act']) ? $_GET['act'] : null;
    //if (defined('PORTAL') && (PORTAL == 'facility'))
        switch ($act){
            case 'view': require 'facility_book_view.php'; break;
            case 'edit': require 'facility_book_edit.php'; break;
            case 'delete': delete_a_book(); break;
            case 'view_day': require 'facility_book_view_day.php'; break;
            case 'view_month': require 'facility_book_view_month.php'; break;
            default: require 'facility_book_view_month.php'; 
        }
?>
  </div>
  &nbsp;
</div>
