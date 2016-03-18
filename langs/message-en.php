<?php

global $messages, $sms_messages;

/* loan messages */
$messages['loan_issue_note'] =<<<MSG1
This is to verify that I have withdrawn the item above from the IT Department, whereby all the items are in good working condition. I am fully responsible for the items listed.<br/>
I acknowledge receipt of the equipment which has been issued to me by my employer and agree to return the item issued at the end of my employment.<br/>
If any equipment is lost or damaged through my negligence, I agree to the employer to pay at a rate that we both agree.
MSG1;

$messages['loan_request_note'] =<<<MSG2
Important Note for Requestor:<br/> 
Only request made more than 48 hours before Loan Date will be able to submit.<br/> 
Requests made less than 48 hours need to contact HOD IT directly.
MSG2;

$messages['loan_request_at_least'] =<<<MSG3
Your date loan/service is less than 48-hours from date of request. Please contact HOD IT directly.<br/>
Thank You.
MSG3;

$messages['loan_request_success'] =<<<MSG4
Your request has been successfully submitted.<br/>
Please check your mailbox regularly for the latest status of your loan Request.<br/>
Thank You.
MSG4;

$messages['loan_request_fail'] =<<<MSG5
Your request failed to proceed.<br/> Please contact HOD IT directly.<br/>
Thank You.
MSG5;

/* service messages */
$messages['service_request_note'] =<<<MSG6
Important Note for Reporter:<br/> 
MSG6;

$messages['service_request_at_least'] =<<<MSG7
Your date service is less than 48-hours from date of request. Please contact HOD IT directly.<br/>
Thank You.
MSG7;

$messages['service_request_success'] =<<<MSG8
Your request has been successfully submitted.<br/>
Please check your mailbox regularly for the latest status of your service Request.<br/>
Thank You.
MSG8;

$messages['service_request_fail'] =<<<MSG9
Your request failed to proceed. Please contact HOD IT directly.<br/>
Thank You.
MSG9;

/* facility messages */
$messages['facility_request_note'] =<<<MSG10
Important Note for Requestor:<br/> 
Only request made more than 48 hours before Service Date will be able to submit.<br/> 
Requests made less than 48 hours need to contact HOD IT directly.
MSG10;

$messages['facility_request_at_least'] =<<<MSG11
Your date facility is less than 48-hours from date of request. Please contact HOD IT directly.<br/>
Thank You.
MSG11;

$messages['facility_request_success'] =<<<MSG12
You have made a facility booking successfully.
MSG12;

$messages['facility_request_fail'] =<<<MSG13
Your request failed to proceed. Please contact HOD IT directly.<br/>
Thank You.
MSG13;

$messages['facility_request_conflict'] =<<<MSG13a
Facility booking will not processed further. Conflict with another booking time for the facility.<br/>
Please find other available time for selected facility.
MSG13a;

/* fault reporting messages */
$messages['fault_request_note'] =<<<MSG13
Important Note for Reporter:<br/> 
Before submitting fault report, please include serial no or asset no of item if available.<br/> 
MSG13;

$messages['fault_request_at_least'] =<<<MSG14
Your date fault is less than 48-hours from date of request. Please contact HOD IT directly.<br/>
Thank You.
MSG14;

$messages['fault_request_success'] =<<<MSG15
Your report has been successfully submitted.<br/>
We will follow up your fault report.<br/>
Thank You.
MSG15;

$messages['fault_request_fail'] =<<<MSG16
Your report failed to submit. <br/>Please contact HOD IT directly.<br/>
Thank You.
MSG16;

$messages['loan_term_conditions'] =<<<MSG17
<h4>Term and Conditions</h4>
This is a term and conditions for loan.
MSG17;

$messages['long_term_loan_term_conditions'] =<<<MSG18
<h4>Term and Conditions</h4>
This is a term and conditions for long term loan.
MSG18;

?>