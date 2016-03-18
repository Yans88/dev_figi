<?php

function get_faq_list(){
	$rs = null;
	$query = "select * from faq_figi order by question ASC";
	$rs = mysql_query($query);	
    return $rs;
}

function save_faq($data){
	$question = $data['question'];
	$answer = $data['answer'];
	$query = "insert into faq_figi (question,answer) values ('$question','$answer')";
	return mysql_query($query);
	if (mysql_affected_rows() > 0){
        $id = mysql_insert_id();       
    }
	
	
}

function edit_faq($data){	
	$id_faq = $data['id_faq'];
	$question = $data['question'];
	$answer = $data['answer'];
	$query = "REPLACE INTO faq_figi(id_faq, question, answer)
              VALUES($id_faq, '$question', '$answer')";
	return mysql_query($query);
}

function get_faq_by_id($id=0){	
	$result = array();
	$query = "select * from faq_figi where id_faq = '$id'" ;
	$rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs))
        $result = mysql_fetch_assoc($rs);	
    return $result;
}