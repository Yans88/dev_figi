<?php

if (!defined('LOG_PATH')) define('LOG_PATH', 'debug.log');

/*
 * item_sync_util.php
 * utility routines for data synchronization with handheld/pda device for stock taking processes 
 *
 * routines:
 *  item_export()
 *  item_import()
 *  status_export()
 *  category_export()
 *  location_export();
 *  department_export()
 *  authenticate()
 *  start_stocktake()
 *  end_stocktake()
 */


if (!defined('FIGIPASS')) exit;

function get_department_item()
{
    $result = null;
    if (!empty($_REQUEST['pwd'])){
        list($id_user, $id_dept) = authenticate_me($_REQUEST, true);
        if ( $id_user > 0 && $id_dept > 0){
             $result = item_export($id_dept);
        } else $result = -2;
    } else $result = -2;
    return $result;
}

/*
 * export
 *
 * fields: id_item, asset_no, serial_no, category, department, status, location 
 * 
 */

function item_export($dept = 0)
{
    //$dept = (defined('USERDEPT')) ? USERDEPT : 0;

	$department_list = get_department_list();
	if (($dept == 0) && (count($department_list)>0)){
	  $dkeys = array_keys($department_list);
	  $dept = $dkeys[0];
	} else
		$department_list [0] = '--none--';

	$dept_name = preg_replace('/[\)\(\/]/', '', $department_list[$dept]);
	$fname = 'item-' . $dept_name . '.csv';
    $path = TMPDIR .'/'. session_id().'-'.$fname;
    item_export_csv($path, $dept);
    if (file_exists($path)) {
        ob_clean();        
        header("Content-type: text/x-comma-separated-values");
        header("Content-Disposition: inline; filename=\"$fname\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile($path);
        ob_end_flush();        
        exit;
    }
} // end of item_export()

function item_export_csv($path, $dept = 0, $cat = 0, $status = 0)
{
    $dtf = '%d-%b-%Y %H:%i:%s';

    $query =<<<SQL
SELECT item.id_item, item.asset_no, item.serial_no, item.id_category, department_category.id_department, id_location, id_status  
FROM item 
LEFT JOIN category ON category.id_category = item.id_category 
LEFT JOIN department_category ON department_category.id_category = item.id_category 
WHERE item.id_item > 0
SQL;
    if ($dept>0) $query .= " AND department_category.id_department = '$dept' ";
    if ($cat>0) $query .= " AND item.id_category = '$cat' ";
    if ($status>0) $query .= " AND item.id_status = '$status' ";
    $query .= ' ORDER BY item.id_item ';

    $fp = fopen($path, 'w');
    //$header  = 'Item ID, Asset No, Serial No, Category, Department, Location, Status';
    //fputs($fp, $header."\r\n");
    $i = 0;
    $rs = mysql_query($query);
    //echo $query.mysql_error();
    if (mysql_num_rows($rs)) {
        while ($rec = mysql_fetch_row($rs)){
	    	fputs($fp, implode(',', $rec) . "\r\n");
        }
    }
    fclose($fp);
}

/*
 * item_import
 * read from posted file and store into db
 * post fields:
 *	- stocktake=yes
 *	- file=csv-file-path
 *	  with fields: id_item,status,remark
 *	- user=username
 *  - dept=department-id
 */
function item_import()
{
	$result = 0;
	if (!empty($_POST) && strtolower($_POST['stocktake'])=='yes'){
    	    list($id_user, $id_dept) = authenticate_me($_REQUEST, true);
		if ( $id_user > 0 && $id_dept > 0) {
			if (!empty($_FILES)){
				$path = $_FILES['file']['tmp_name'];
				$filename= $_FILES['file']['name'];
				if (is_uploaded_file($path)){
					if (is_compressed($path))
						$path = extract_file($path);
					if (!empty($path)){
						//echo 'data accepted: ' . filesize($path). ' bytes';
						if (strtolower(substr($filename, -4)) == '.csv'){
                            $id_take = save_stock_info($id_user, $id_dept);
                            if ($id_take>0){
							error_log('id_take: '.$id_take);
                                $numrows = item_import_csv($path, $id_take);
                                $result = $numrows;
                            } else $result = -8; // failed create stock take transaction
						} else $result = -3; // not a csv file
					} else $result = -4; // file extraction failed
				} else $result = -1; // upload problem
			} else $result = -1;
		} else $result = -2; // authentication problem
	} else $result = -9; // unknown process
	return $result;
}

function get_user_department($id_user)
{
    $user = get_user($id_user);
    return @$user['id_department'];
}

function item_import_csv($path, $id_take)
{
	/*
	 * expected fields
	 * v1	id_item, take_status, take_date, take_remark
	 * v2 id_item, id_location, take_status, take_date, take_remark
	 * v3 id_item, id_location,  take_date 
	 *
	 */
	$numrows = 0;
	if ($id_take > 0){
	error_log($path);
        $fp = fopen($path, 'r');
        if ($fp){
            $values = array();
            while (($rec = fgetcsv($fp, 1024, ',')) !== FALSE){
                $numrows++;	
                $remark = '';
                //if (!empty($rec[3])) $remark = mysql_real_escape_string($rec[3]);
                $take_date = $rec[2];
                // v1
                // $values[] = "($id_take, $rec[0], $rec[1], '$take_date', '$remark')";
                // v2
                // $values[] = "($id_take, $rec[0], $rec[1], $rec[2], '$take_date', '$remark')";
                // v3
                $values[] = "($id_take, $rec[0], $rec[1], $rec[1], '$take_date')";
				error_log(serialize($rec));
            }
            if (count($values)>0){
                // v1 
                // $query = 'INSERT stock_taken_item(id_take, id_item, take_status, take_date, take_remark) VALUES '. implode(', ', $values);
                // v2
                // $query = 'INSERT stock_taken_item(id_take, id_item, id_location, take_status, take_date, take_remark) VALUES '. implode(', ', $values);
                // v3
                $query = 'INSERT stock_taken_item(id_take, id_item, id_location, old_location, take_date) VALUES '. implode(', ', $values);
                if (mysql_query($query))
                    $numrows = mysql_affected_rows();
                error_log($query.mysql_error());
                if ($numrows){
                    // update item's old location for logging
                    $query = 'UPDATE stock_taken_item sti, item i  
                                SET sti.old_location = i.id_location 
                                WHERE i.id_item = sti.id_item AND i.id_location != sti.id_location 
                                AND sti.id_take = '. $id_take;
                    if (mysql_query($query)){
                        $affected = mysql_affected_rows();
                        if ($affected > 0){
                            // update item for location changes
                            $query = 'UPDATE item i, stock_taken_item sti 
                                        SET i.id_location = sti.id_location 
                                        WHERE i.id_item = sti.id_item AND i.id_location != sti.id_location 
                                        AND sti.id_take = '. $id_take;
                            mysql_query($query);
                            // update stock take session for number of changes
                            $query = 'UPDATE stock_takes SET changes = '.$numrows.' WHERE id_take = '. $id_take;
                            mysql_query($query);
                        }
                    }
                }
            }
        fclose($fp);
        }
    }
	return $numrows;
}

function department_export()
{

	$data = get_department_list();
	if (JSON){
		$result = pair_to_json($data);
		output_as_json($result);
	} else {
		$result = pair_to_csv($data);
		output_as_csv($result, 'department.csv');
	}
}

function category_export($id_dept = 0)
{

	$data = get_category_list('EQUIPMENT', $id_dept);
	if (JSON){
		$result = pair_to_json($data);
		output_as_json($result);
	} else {
		$result = pair_to_csv($data);
		output_as_csv($result, 'category.csv');
	}
}

function location_export()
{
	$result = null;
	$data = get_location_list();
	if (JSON){
		$result = pair_to_json($data);
		output_as_json($result);
	} else {
		$result = pair_to_csv($data);
		output_as_csv($result, 'location.csv');
	}
}

function status_export()
{

	$data = get_status_list();
	if (JSON){
		$result = pair_to_json($data);
		output_as_json($result);
	} else {
		$result = pair_to_csv($data);
		output_as_csv($result, 'status.csv');
	}
}
function pair_to_json($data)
{
	return json_encode($data);
}

function pair_to_csv($data)
{
	$result = null;
	$values = array_values($data);
	$indexes = array_keys($data);
	$len = count($indexes);
	$tmp = array();
	for($i = 0; $i < $len; $i++){
		$tmp[] = $indexes[$i] . ',"' . $values[$i].'"';
	}
	$result = implode("\r\n", $tmp);
	return $result;
}

function logme($log)
{
	global $loghandler;
	if (defined('LOG_ENABLED') && LOG_ENABLED){ 
		if (!is_resource($loghandler)){
			$loghandler = fopen(LOG_PATH, 'a');
		}
		if (is_resource($loghandler)){
			fputs($loghandler, $log);
		}
	}
}

function output_as_csv($data, $name = 'data.csv')
{
	ob_clean();        
	header("Content-type: text/x-comma-separated-values");
	header("Content-disposition: inline; filename=\"$name\"");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo($data);
	ob_end_flush();        
	exit;

}

function output_as_json($data)
{
	ob_clean();        
	header("Content-type: application/json");
	header("Content-disposition: inline");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo($data);
	ob_end_flush();        
	exit;
}

function authenticate_me($data, $asarray = false)
{
	$res = array(-2, -1);
	$rec = authenticate($data['uid'], $data['pwd']);
	if (is_array($rec)){
		$res = array($rec['id_user'],$rec['id_department']);
		if (!$asarray) $res = $rec['id_user'].','.$rec['id_department'];
	}
	return $res;
}

function is_compressed($path)
{
	/*
	$finfo = finfo_open(FILEINFO_MIME); 
	$mimetype = finfo_file($finfo, $path);
	finfo_close($finfo);
	*/
	$bytes = file_get_contents($path, FALSE, NULL, 0, 2);
    $ext = strtolower(substr($path, - 4));

	return ($ext == '.zip' && $bytes == 'PK'); 
}

function extract_file($path)
{
	$dest  = TMPDIR;
	//error_log('TMPDIR: '.$dest);
	$info = pathinfo($path);
	$fn = $info['basename'];
	//if (strtolower($info['extension']) == 'zip')
		$newpath = $dest .'/' . substr($fn, 0, -4); // remove .zip ext
	unzip($path, $dest);
	return (file_exists($newpath)) ? $newpath : false;
}

function extract_file_shell($path, $dest)
{
	$cmd = "unzip -d \"$dest\" \"$path\" ";
	shell_exec($cmd);
}

function save_stock_info($id_user, $id_dept)
{
	$res = 0;
	$query = "INSERT stock_takes(take_date, id_department, id_user) VALUE(now()+0, $id_dept, $id_user)";
	if (mysql_query($query)){
		$res = mysql_insert_id();
	}
	//error_log($query.mysql_error().$res);
	return $res;
}

function stocktake_start()
{
    list($id_user, $id_dept) = authenticate_me($_REQUEST, true);
    $result = $id_user > 0;
    return $result;
}

function stocktake_end()
{
    return item_import();
}



?>
