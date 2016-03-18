<div style="color:#fff;">
<?php

if (!defined('FIGIPASS')) exit;
/*
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
*/
if ($_sub != null){
	include 'report/'. $_sub . '.php';
	return;
}
$link_item_by_department = '';
$link_loan_by_user = '';
$link_item_report = '';
if (SUPERADMIN){
	$link_item_by_department = '<li><a href="./?mod=report&sub=item&act=view&term=tracking&by=department">Inventory Tracking by Department</a></li>';
	$link_loan_by_user = '<li><a href="./?mod=report&sub=loan&act=view&term=tracking&by=user">User\'s Loan History</a></li>';
	$link_item_report = '<li><a href="./?mod=report&sub=item&act=view&term=list&by=department">Filtered by Department</a> </li>';
	$link_item_report .= '<li><a href="./?mod=report&sub=item&act=view&term=list&by=store">Filtered by Store Type</a> </li>';
}
$link_condemned = '';
$consumable_item_report = '';
$student_usage = '';
if(condemned){
	$link_condemned = '<li><a href="./?mod=report&sub=item&act=view&term=list&by=condemn_date">Filtered by Projected Condemn Date</a> </li>';
}

if(consumable_item_report){
	$consumable_item_report = '<li><h4>Consumable Item Report</h4>
		<ul>
        <!--
		<li><a href="./?mod=report&sub=consumable&act=view&term=summary&by=category">Usage Summary</a></li>
        -->
		<li><a href="./?mod=report&sub=consumable&act=view&term=frequency&by=category">Frequency of Item Movement</a></li>
		</ul>
	</li>';
}
if(maintenance){
	$maintenance = '<li><h4>Machine History Report</h4>
		<ul>
		<li><a href="./?mod=report&sub=machine&act=view&term=summary&by=category">Machine Record Summary</a></li>
		</ul>
	</li>';
}
if(student_usage){
	$student_usage = '<li>
	<h4>Students usage</h4>
		<ul>
		<li><a href="./?mod=report&sub=facility&act=view&term=fixed&by=user">Facility usage by User</a></li>
		<li><a href="./?mod=report&sub=facility&act=view&term=fixed&by=item">Facility usage by item</a></li>
		<li><a href="./?mod=report&sub=facility&act=view&term=fixedused&by=user">Facility item usage by User</a></li>
		<li><a href="./?mod=report&sub=facility&act=view&term=fixedused&by=student">Facility item usage by Student</a></li>
		</ul>
	</li>';
}
echo<<<LINK1
<style>
h4 {color: #fff}
</style>
<div  align="left" style="width: 800px">
<ul>
	<li><h4>Item Listing</h4>
		<ul>
			<li><a href="./?mod=report&sub=item&act=view&term=list&by=reportgeneralitem">Report General Item</a> </li>
			<li><a href="./?mod=report&sub=item&act=view&term=list&by=invoice">Based on an Invoice</a> </li>
			<li><a href="./?mod=report&sub=item&act=view&term=list&by=brand">Filtered by Brand</a> </li>
			$link_condemned
			<li><a href="./?mod=report&sub=item&act=view&term=list&by=category_status">Filtered by Category and Status</a> </li>
			<li><a href="./?mod=report&sub=item&act=view&term=list&by=asset">Search by Asset No or Serial No</a> </li>
			<li><a href="./?mod=report&sub=item&act=view&term=list&by=location">Filtered by Location</a> </li>
			$link_item_report
		</ul>
	</li>
	<li><h4>Inventory Tracking Report</h4>
		<ul>
			$link_item_by_department
			<li><a href="./?mod=report&sub=item&act=view&term=tracking&by=category">Inventory Tracking by Category</a> </li>
		</ul>
	</li>
	<li><h4>Item Age Report</h4>
		<ul>
		<li><a href="./?mod=report&sub=item&act=view&term=age&by=category">Item Age by Category</a></li>
		<li><a href="./?mod=report&sub=item&act=view&term=age&by=vendor">Item Age by Vendor</a></li>
		</ul>
	</li>
	<li><h4>Item Warranty Report</h4>
		<ul>
		<li><a href="./?mod=report&sub=item&act=view&term=warranty&by=category">Warranty Expire Year by Category</a></li>
		<li><a href="./?mod=report&sub=item&act=view&term=warranty&by=filter">Warranty Expire Filtered by Category and Month</a></li>
		</ul>
	</li>
	<li><h4>Item Issuance Report</h4>
		<ul>
		<li><a href="./?mod=report&sub=item&act=view&term=issued-out&by=category">Issued-Out Item Summary</a></li>
		$link_loan_by_user
		</ul>
	</li>
	<li><h4>Item Stock Take Report</h4>
		<ul>
		<li><a href="./?mod=report&sub=item&act=view&term=stock-take&by=validation">Take Report by Validation</a></li>
		<li><a href="./?mod=report&sub=item&act=view&term=stock-take&by=unstock">Rest of Items UnStock Take</a></li>
		<li><a href="./?mod=report&sub=item&act=view&term=stock-take&by=location">Take Report by Location</a></li>
		<li><a href="./?mod=report&sub=item&act=view&term=stock-take&by=handheld">Take Report by Handheld</a></    li>
		</ul>
	</li>
	$consumable_item_report
    <!--
	<li><h4>Item Loan Report</h4>
		<ul>
		<li><a href="./?mod=report&sub=item&act=view&term=report&by=fa">Loaned Item Frequency</a></li>
		$link_loan_by_user
		</ul>
	</li>
    -->
	
	<li><h4>Facility Report</h4>
		<ul>
		<li><a href="./?mod=report&sub=facility&act=list&term=usage&by=location">Facility Usage List by Location</a></li>
		<li><a href="./?mod=report&sub=facility&act=view&term=usage&by=period">Facility Usage by Facility</a></li>
		<li><a href="./?mod=report&sub=facility&act=view&term=usage&by=user">Facility Booking by User</a></li>
		<li><a href="./?mod=report&sub=facility&act=view&term=cancel&by=user">Cancelled Booking by User</a></li>
		</ul>
	</li>
	
	$student_usage
	
	
	<li><h4>Fault Reports</h4>
		<ul>
		<li><a href="./?mod=report&sub=fault&act=view&term=frequency&by=status">Fault Frequency Status</a></li>
		<li><a href="./?mod=report&sub=fault&act=view&term=frequency&by=user">Fault Frequency Reported</a></li>
		<li><a href="./?mod=report&sub=fault&act=view&term=frequency&by=period">Fault Status in a Period</a></li>
		<li><a href="./?mod=report&sub=item&act=view&term=fault&by=calendar&spec=view_month&part=number_of_fault">Show number of fault reported every month</a></li>
		<li><a href="./?mod=report&sub=fault&act=view&term=frequency&by=leadtime">Show lead time (No. of days) between Fault Report -> Rectifying -> Completed</a></li>
		
		</ul>
	</li>
	$maintenance
	
	<li><h4>Loan Transaction Info</h4>
		<ul>
			<li><a href="./?mod=report&sub=item&act=view&term=loan&by=transactioninfo">Items Frequency of Loan</a></li>
			<li><a href="./?mod=report&sub=item&act=view&term=loan&by=username">Items Frequency of Loan By Username</a></li>
			<li><a href="./?mod=report&sub=item&act=view&term=loan&by=category">Items Frequency of Loan By Category</a></li>
		</ul>
	</li>
	<li><h4>Service</h4>
		<ul>	
			<!--<li><a href="./?mod=report&sub=item&act=view&term=service&by=calendar&spec=view_month&part=preparation">Show by Calendar (Date/Time of Service Preparation)</a></li>-->
			<li><a href="./?mod=report&sub=item&act=view&term=service&by=calendar&spec=view_month&part=implementation">Show by Calendar (Service Transaction)</a></li>
		</ul>
	</li>
	
</ul> 
</div>
<br/> 
LINK1;

?>

</div>