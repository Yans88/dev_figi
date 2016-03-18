<?php
if (!defined('FIGIPASS')) exit;

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$request = get_request($_id);
$requester = get_user($request['id_user']);
$parent_info = get_parent_info($request['id_user']);
$parent_name = $parent_info['father_name'];
$class = get_class_info($request['id_user']);

$issue['name'] = $requester['full_name'];
$issue['nric'] = $requester['nric'];
$issue['contact_no'] = $requester['contact_no'];
$today = date('j-M-Y');

ob_clean();
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=AUP_".$issue['name']."_".$today.".doc");

echo "<html>";
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">";
    echo "<body style='font-family:arial;'>";
   echo "<div style='font-size:16pt'><b>Acceptable Use Policy (AUP) Agreement</b><br/><br/></div>
Dear Student,<br/>";
echo "<div style='text-align:justify;'><div>The computing device and internet access are provided to support your learning beyond the school. 
In order to ensure that you have a safe, conducive online learning experience, 
please abide by the following rules and regulations when you are using the computing device as well as accessing the internet.</div><br/>";

echo "<b>General</b><ul><li>I will take responsibility and personal ownership over the well-being of the computing device and internet dongle</li>";
echo "<ul><li>I understand that the laptop is schools property and I will not share it with anyone.</li>";
echo "<li>I will report any mishap or damage immediately to the ICT Department. </li>";
echo "<li>I will provide a statement to explain my account of any mishap or damage to the ICT Department. </li>";
echo "<li>If the mishap or damage is found to have happened due to personal negligence, I may be liable to pay for any repair(s) needed. </li></ul>";
echo "<li>I will use the computing device and internet dongle for the purpose of learning and research only.  </li>";
echo "<li>I will not use the laptop for any illegal activities (e.g. hacking, accessing illegal websites etc.)   </li>";
echo "<li>I will ensure that my internet access does not exceed the data plan provided.   </li></ul>";
echo "<b>Account Ownership </b>";
echo "<ul><li>I will keep my password a secret and not share with anyone.</li>";
echo "<li>I will not share my personal information (e.g. home address, email address, phone numbers, account IDs, 
	passwords and/or personal pictures) with anyone online. </li></ul>";

	
	
echo "<b>Use of Internet </b>";
echo "<ul><li>I will obey the terms of use of social media platforms use (e.g. Facebook, Instagram, Twitter require users to be at least 13 years old)</li>";
echo "<li>I will take care to check for reliability and accuracy of information that I access and note that the content of some websites may not be correct or truthful. </li>";
echo "<li>I will not download, copy or share videos, music, pictures or other people’s work without permission.</li>";
echo "<li>I will report any online encounters with strangers to a trusted adult immediately. </li>";
echo "<li>I will not harm, bully or say unkind words to others.</li>";
echo "<li>I will not take and post a picture or video of others without their permission.</li>";
echo "</ul><br/>";
echo "<div style='font-size:12pt; text-align:justify;'>
		<b>Failure to adhere to the rules above will result in disciplinary action in accordance to the schools discipline policy.</b></div><br/>";
echo "<div><b>Student</b> I understand and agree to follow the rules stated in this Agreement.</div>";

echo "<br/>";
echo "<table style='border:none;'><tr>";
echo "<td><b>Name</b></td><td>:</td><td>".$issue['name']."</td></tr>";
echo "<tr><td><b>Class</b></td><td>:</td><td>".$class."</td></tr>";
echo "<tr><td height=50><b>Signature</b></td><td>:</td><td>_________________</td></tr>";
echo "<tr><td><b>Date</b></td><td>:</td><td>_________________</td></tr>";
echo "</table><br/>";

echo "<div style='text-align:justify';><b>Parent : </b>
	  I have read, discussed and explained the Agreement to my child. 
	  I will engage my child to have a better understanding about his/her daily online activities. 
	  I have also noted that as stated in the terms of use of social media platforms 
	  (e.g. Facebook, Instagram, Twitter), the minimum age required to set up an account is 13 years of age</div>.<br/>"; 

echo "<table style='border:none;'><tr>";
echo "<td><b>Name</b></td><td>:</td><td>".$parent_name."</td></tr>";

echo "<tr><td height=50><b>Signature</b></td><td>:</td><td>_________________</td></tr>";
echo "<tr><td><b>Date</b></td><td>:</td><td>_________________</td></tr>";
echo "</table>";
	echo "</div></body>";
    echo "</html>";





ob_end_flush();
exit;
?>