<?php if (!defined('FIGIPASS')) exit; 


include "fault/fault_util.php";

$_page = isset($_GET['page']) ? $_GET['page'] : 1;

$_changeorder = isset($_GET['chgord']) ? true : false;

$dt = $_GET['dt'];

$_limit = RECORD_PER_PAGE;
$_start = 0;
$total_item = count_fault_request_by_date('NOTIFIED', $dt);
$total_page = ceil($total_item/$_limit);
if ($_page > $total_page) $_page = 1;
if ($_page > 0)	$_start = ($_page-1) * $_limit;



echo "<h1>Fault Date : ".date('d M Y', strtotime($dt))."</h1><br />";
echo "<h3 style='text-align:center;'>Total Data : ". $total_item ." | <a class='button' href='./?mod=report&sub=item&act=view&term=fault&by=calendar&spec=view_month&part=number_of_fault'>Back</a></h3>";


$data = get_fault_request_by_date('NOTIFIED', $_start, $_limit, $dt);


?>



<table cellpadding=2 cellspacing=1 class="fault_table" width="800px" >

<tr height=30 valign="top" align="center">

  <th width=25>No</th><th width=115>Date of Report</th>

  <th >Reporter</th><th width=110>Fault Date</th>

  <th >Category</th><th>Description</th><th width=50>Action</th>

</tr>



<?php

$counter = 1;

if ($total_item > 0) {

    foreach ($data as $rec) {

        $desc = substr($rec['fault_description'], 0, 35) . ' ...';
		$no = 1;
        $_class = ($counter % 2 == 0) ? 'class="alt"':null;

        echo '

    <tr '.$_class.' valign="top">

    <td align="center"> FCG'.$rec['id_fault'].' </td>

    <td align="center">'.$rec['report_date'].'</td>

    <td>'.$rec['full_name'].'</td>

    <td align="center">'.$rec['fault_date'].'</td>

    <td>'.$rec['category_name'].'</td>

    <td>'.$desc.'</td>

    <td align="center">

    <a href="./?mod=fault&sub=fault&act=view&id='.$rec['id_fault'].'" title="view"><img class="icon" src="images/view.png" alt="view"></a> 
	';

    echo '</td></tr>';

  $counter++;

    } 

    echo '<tr ><td colspan=9 class="pagination">';

    echo make_paging($_page, $total_page, './?mod=report&sub=fault_detail&dt='.$dt.'&page=');

    //echo  '<div class="exportdiv"><a href="./?mod=fault&act=list&status=notified&do=export" class="button">Export Data</a></div></td></tr>';



}else

	echo '<tr><td colspan=9 align="Center" >Data is not available!</td></tr>';

?>

</table>



