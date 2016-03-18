<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}

/*
if ((USERGROUP == GRPHOD)||SUPERADMIN) 
    echo<<<LINK1
<a href="./?mod=loan&sub=loan&status=pending">Request Pending Approval</a> | 
<a href="./?mod=loan&sub=loan&status=unapproved">Rejected Request</a> | 
<a href="./?mod=loan&sub=loan&status=returned">Loaned Items Pending Acknowlegment</a> <br/>
LINK1;
if (USERGROUP == GRPADM)
    echo <<<LINK2
<a href="./?mod=loan&sub=loan&status=approved">Approved Request (In-Process)</a> | 
<a href="./?mod=loan&sub=loan&status=loaned">Requests already Loaned Ou</a>
<br/>
LINK2;
*/

    echo<<<LINK1
<style>
h4 {color: #fff}
</style>
<div  align="left" style="width: 800px">
<ul>
	<li><h4>Inventory Tracking Report</h4>
		<ul>
			<li><a href="./?mod=loan&sub=report&act=view&term=tracking&by=department">Inventory Tracking by Department</a></li>
			<li><a href="./?mod=loan&sub=report&act=view&term=tracking&by=category">Inventory Tracking by Category</a> </li>
		</ul>
	</li>
	<li><h4>Item Age Report</h4>
		<ul>
		<li><a href="./?mod=loan&sub=report&act=view&term=age&by=category">Item Age by Category</a></li>
		<li><a href="./?mod=loan&sub=report&act=view&term=age&by=vendor">Item Age by Vendor</a></li>
		</ul>
	</li>
	<li><h4>Item Warranty Report</h4>
		<ul>
		<li><a href="./?mod=loan&sub=report&act=view&term=warranty&by=category">Warranty Expire Year by Category</a></li>
		<li><a href="./?mod=loan&sub=report&act=view&term=warranty&by=filter">Warranty Expire Filtered by Category and Month</a></li>
		</ul>
	</li>
	<!--
	<li><h4>Item Loan Report</h4>
		<ul>
		<li><a href="./?mod=loan&sub=report&act=view&term=loan&by=frequency">Loaned Item Frequency</a></li>
		</ul>
	</li>
	-->
</ul> 
<br/> 
</div>
LINK1;

//include 'loan/report_'. $_status . '.php';
?>