<?php
$id_book = isset($_GET['id']) ? $_GET['id'] : 0;
$booked_date = isset($_GET['booked_date']) ? $_GET['booked_date'] : 0;
$id_time = isset($_GET['id_time']) ? $_GET['id_time'] : 0;

$url = "./?mod=booking";
if($id_book > 0 ){
	$book = book_remove_by_period($id_book, $booked_date, $id_time);
	
	
	if($book){
		redirect($url, "Your data has been removed.");
	} else {
		redirect($url, "Your data cannot be removed.");
	}
} else {
	echo "Remove data failed";
}

?>