<?php
if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
if (empty($_GET['status'])){
    if (REQUIRE_CONDEMNED_APPROVAL){
        if (USERGROUP == GRPHOD)
            $_status = 'pending';
        else if (USERGROUP == GRPADM)
            $_status = 'approved';
        else if ((USERGROUP == GRPPRI)||(USERGROUP == GRPDIR)){
            if ((USERGROUP == GRPPRI) && defined('ENABLE_SECOND_RECOMMENDATION') && ENABLE_SECOND_RECOMMENDATION)
                $_status = 'recommended2';
            else
                $_status = 'recommended';
            
        }
    } else
        $_status = 'pending';
} else
    $_status = $_GET['status'];

echo '<div style="width:800px">';
if (REQUIRE_CONDEMNED_APPROVAL){
    echo '<a href="./?mod=condemned&sub=condemned&status=pending">Pending</a> | ';
    if (CONDEMNATION_FLOW_TYPE == 2){
        echo '<a href="./?mod=condemned&sub=condemned&status=recommended">Recommended</a> | ';
        echo '<a href="./?mod=condemned&sub=condemned&status=verified">Verified</a> | ';
    } else {
        echo '<a href="./?mod=condemned&sub=condemned&status=recommended">Recommended by HOD</a> | ';
        if (defined('ENABLE_SECOND_RECOMMENDATION') && ENABLE_SECOND_RECOMMENDATION)
            echo '<a href="./?mod=condemned&sub=condemned&status=recommended2">Recommended by Director</a> | ';
        echo '<a href="./?mod=condemned&sub=condemned&status=approved">Approved (In-Process)</a> | ';
    }
    echo <<<LINK
<a href="./?mod=condemned&sub=condemned&status=rejected">Rejected</a> | 
<a href="./?mod=condemned&sub=condemned&status=condemned">Condemned</a> <br/>
<br/>
LINK;
} 
else { // non-approval
        echo<<<LINK3
<a href="./?mod=condemned&sub=condemned&status=pending">Pending</a> | 
<a href="./?mod=condemned&sub=condemned&status=condemned">Condemned</a> | 
<a href="./?mod=condemned&sub=condemned&status=rejected">Rejected</a>  <br/>
LINK3;

}
include 'condemned/condemned_list_'. $_status . '.php';
echo '</div>';
?>