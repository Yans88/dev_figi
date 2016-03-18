<?php
if (!empty($_POST['delper'])){
	if (preg_match('/(\d+)-(\d+)-(\d+)/', $_POST['delper'], $matches)){
		$id_book = $matches[1];
		$id_time = $matches[2];
		$booked_date = $matches[3];
		$query = "DELETE FROM booking_list_period WHERE id_book=$id_book AND id_time=$id_time AND booked_date=$booked_date";
		mysql_query($query);
		error_log(mysql_error().$query);
		$ok = mysql_affected_rows() > 0;
		if ($ok) $msg = 'Period has been deleted from the list';
		else $msg = 'Fail on period deletion!';
		redirect('./?mod=booking&act=view&id='.$id_book, $msg);
	}
}

redirect('./?mod=booking');
?>
