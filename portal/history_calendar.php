<?php


function delete_avent()
{
    
    $_id = isset($_GET['id']) ? $_GET['id'] : 0;
    if (delete_book($_id)){
        echo '
                <script>
                    alert("Seleted event information has been deleted!");
                    location.href = "./?mod=portal&sub=history&portal=calendar";
                </script>';
    }
}
?>

<div id="tab_calendar" class="tabset_content history">
    &nbsp; <br/>

    <div class="portal_history" id="calendar_history">
<?php  
    $act = !empty($_GET['act']) ? $_GET['act'] : null;
    if (defined('PORTAL') && (PORTAL == 'calendar'))
        switch ($act){
            case 'view': require 'calendar_view.php'; break;
            case 'add': require 'calendar_add.php'; break;
            case 'edit': require 'calendar_edit.php'; break;
            case 'delete': delete_a_book(); break;
            case 'view_day': require 'calendar_view_day.php'; break;
            case 'view_month': require 'calendar_view_month.php'; break;
            default: require 'calendar_history.php'; 
        }
?>
  </div>
  &nbsp;
</div>