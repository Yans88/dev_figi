<?php

function create_timesheet(){

    $result = array();

    $y = date('Y');

    $m = date('m');

    $d = date('d');

    $ss = explode(':', CALENDAR_TIME_START);

    $sf = explode(':', CALENDAR_TIME_FINISH);

    if (!is_array($ss)) $ss = array('07', '00');

    if (!is_array($sf)) $sf = array('17', '00');

    $tms = mktime($ss[0], $ss[1], 0, $m, $d, $y);

    $tmf = mktime($sf[0], $sf[1], 0, $m, $d, $y);

    $tm = $tms;

    $id_time = 1;

    while ($tm < $tmf){

        $result[$id_time] = array('id_time' => $id_time, 'time_start' => date('H:i', $tm));

        $tm = mktime(date('H', $tm), date('i', $tm)+30, 0, $m, $d, $y);

        $result[$id_time++]['time_end'] = date('H:i', $tm);

    }

    

    return $result;

}



function get_event_info($id){

	$data = array();

	if ($id > 0){

        $query = "SELECT ce.*

                    FROM calendar_view ce 

                    WHERE id_event = $id ";

                    

		$rs = mysql_query($query);

		//echo mysql_error().$query;

		if ($rs)

            $data = mysql_fetch_assoc($rs);

	}

	return $data;

}



function send_event_notification($_id){

    global $transaction_prefix, $configuration;

    $config = $configuration['service'];

    

    if ($config['enable_notification'] != 'true') return false;

    /*

    $id_str = implode(',', $ids);

	$data = get_facility_request($id_str);   

    if (count($data) == 0) return false;

    */

    $request_no = $transaction_prefix.$id;

    $figi_url = FIGI_URL;

    

    if ($config['enable_notification_email'] == 'true'){

        $emails = array($data['user_email']);

        $email_rec = get_notification_emails($data['id_department'], 0, 'facility');

        foreach ($email_rec as $rec)

            $emails[] = $rec['email'];

        if (count($emails) > 0) {

            $message = compose_message('messages/facility-request-submit.msg', $data);

            $to = array_shift($emails);

            $cc = implode(',', $emails);

            $subject = 'Facility ('. $data['facility_no'] . ') has been booked by ' . $data['full_name'];

            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);

            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'facility', 'email');

            process_notification($id_msg);

        }

    }

    

    if ($config['enable_notification_sms'] == 'true'){

        $message = null;

        $mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'facility');

        $mobiles = array_keys($mobile_rec);

        if (!empty($data['contact_no']))

            array_unshift($mobiles, $data['contact_no']);

        $to = implode(',', $mobiles);

        $message = compose_message('messages/facility-request-submit.sms', $data);

        //SendSMS(SMS_SENDER, $to, $message);

        $id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'facility', 'sms');

        process_notification($id_msg);

        writelog('send_submit_service_request_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);

    }



    $to = array_shift($emails);

    $cc = implode(',', $emails);



    SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);

}





function export_events($date, $id_facility){

    $crlf = "\r\n";

    ob_clean();

    ini_set('max_execution_time', 60);

    //$today = date('dMY');

	$facility = get_facility($id_facility);

    $fname = "figi_booking_schedule-$date-$facility[facility_no].csv";

    header("Content-type: text/x-comma-separated-values");

    header("Content-Disposition: attachment; filename=$fname");

    header("Pragma: no-cache");

    header("Expires: 0");

	echo build_timesheet_book_as_csv($date, $id_facility);

    ob_end_flush();

    exit;

}



function get_events($start_date, $end_date, $check_instance = false){

	$result = Array();

    // check for last generated instance, for recurring events

    if ($check_instance){ 

        $query = "SELECT * 

                    FROM calendar_events 

                    WHERE status = 0 AND repetition != 'NONE' AND (dt_last IS NULL OR dt_last > $start_date) 

                    AND (dt_instance IS NULL OR dt_instance < $end_date) "; 

                    //AND dt_start >= $start_date 

        $rs = mysql_query($query);

        //echo $query.mysql_error()."<br>";

        //putLog('check', array($query, mysql_error()));

        while ($rec = mysql_fetch_assoc($rs)){

            $last_generated = $rec['dt_instance'];

            if (empty($last_generated) || $last_generated == 0){

                $last_generated = $rec['dt_start'];

            }

            $last_date = ($rec['dt_last']>0 &&  $rec['dt_last']<$end_date) ? $rec['dt_last'] : $end_date;

            //echo "$rec[id_event] - $rec[dt_start] - $rec[dt_end]- $last_generated - $last_date - $start_date - $end_date<br>\n";

            if ($last_generated >= $rec['dt_start'] && ($last_generated < $last_date)){

                $start_instance = $last_generated+86400;

                $last_generated += ($rec['repetition'] == 'MONTHLY') ? 86400*90 : 86400*30;

                //echo "\n$start_instance - $last_generated ";

                generate_instances($rec['id_event'], $start_instance , $last_generated, false );

            }

        }

    }

    $query = "SELECT cei.*, ce.title, ce.location_name, ce.fullday, ce.repetition, ce.description,

                ce.id_location, ce.`interval`, ce.dt_last, id_user  

                FROM calendar_event_instances cei 

                LEFT JOIN calendar_view ce ON ce.id_event = cei.id_event 

                WHERE status = 0 AND cei.start >= $start_date  AND cei.end <= $end_date";

    $rs = mysql_query($query);

    error_log($query.mysql_error());

    while ($rec = mysql_fetch_assoc($rs)){

        $result[] = $rec;

    }

    //print_r($result);

	return $result;

}



function get_events_old($m, $y, $id_facility = 0){

	$result = Array();

    $bookinfo = array();

    $first_dttm = mktime(0, 0, 0, $m, 1, $y);

    $ym = date('Y-m', $first_dttm);

    $query = "SELECT *, date_format(date_start, '%Y%c%e') cal_code, UNIX_TIMESTAMP(repeat_until) repeat_until_t, 

                UNIX_TIMESTAMP(date_start) date_start_t, UNIX_TIMESTAMP(date_finish) date_finish_t, 

                date_format(date_start, '%d-%b-%Y') date_start_fmt, date_format(date_finish, '%d-%b-%Y') date_finish_fmt,  

                date_format(time_start, '%H:%i') time_start_fmt, date_format(time_finish, '%H:%i') time_finish_fmt,

                date_format(repeat_until, '%d-%b-%Y') repeat_until_fmt                  

                FROM calendar_view ce 

				WHERE date_format(ce.date_start, '%Y-%m') <= '$ym' AND

                status IN ('BOOK', 'COMMENCE') ";//AND date_format(date_finish, '%Y-%c') <= '$y-$m'

    //if ($id_facility>0) $query .= " AND ce.id_facility = $id_facility ";

    $query.= " ORDER BY cdate ASC";

    

	$rs = mysql_query($query);	

    //echo $query.mysql_error();

	while ($rec = mysql_fetch_assoc($rs))

        $bookinfo[$rec['id_event']] = $rec;

    //print_r($bookinfo);

    $start_date_of_the_month = mktime(0, 0, 1, $m, 1, $y);

    $last_date_of_the_month = mktime(23, 59, 59, $m, date('t', $start_date_of_the_month), $y);

    foreach ($bookinfo as $id_event => $rec){

        if (!empty($rec)){

            $cal_code = $rec['cal_code'];

            //if (!is_array($result[$cal_code])) $result[$cal_code] = array();

            

            $dtend = strtotime($rec['repeat_until'] . ' 23:59:59');

            //$dtend = $rec['repeat_until_t'];

            if (empty($dtend) || $dtend > $last_date_of_the_month)

                $dtend = $last_date_of_the_month;

            if ($rec['repetition'] == 1) { // daily 

                cal_fill_daily($result, $rec, $dtend);

            } else

            if ($rec['repetition'] == 2) { // weekly

                cal_fill_weekly($result, $rec, $dtend);

            } else

            if ($rec['repetition'] == 3) { // monthly

                cal_fill_monthly($result, $rec, $dtend);

            } else {

                $rec['cur_event_date'] = date('D, d M Y', $rec['date_start_t']);

                $result[$cal_code][] = $rec;

            }

        }

	}

    //print_r($result);

	return $result;

}



function get_upcoming_events($cnt = 5){

    return;

    function _cal_fill_daily(&$res, $rec, $dtend, $fdt = 0){

        $dt = $rec['date_start_t'];

        while ($dt < $dtend){

            $cal_code = date('Ymd', $dt);

            $rec['cur_event_date'] = date('D, d M Y', $dt);

            if ($fdt > 0){

                if ($dt == $fdt){

                    $res[$cal_code][] = $rec;

                    break;

                }

            } else

                $res[$cal_code][] = $rec;

            $dt = date_add_day($dt, $rec['repeat_interval']);

                break;

        }

    }





    function _cal_fill_monthly(&$res, $rec, $dtend, $fdt = 0){

        $dt = $rec['date_start_t'];

        $dte = $rec['date_finish_t'];

        $delta = $dte-$dt;

        $dom = date('md', $dt);

        while ($dt < $dtend){

            $sdt = $dt;

            $long = $dt+$delta;

            while ($dt <= $long){

                $cal_code = date('Ymd', $dt);

                $rec['cur_event_date'] = date('D, d M Y', $dt);

                if ($fdt > 0){

                    if ($dt == $fdt){

                        $res[$cal_code][] = $rec;

                        break;

                    }

                } else

                    $res[$cal_code][] = $rec;

                $dt = date_add_day($dt, 1);

            }

            

            $dt = date_add_months($sdt, $rec['repeat_interval']);

            break;

        }

    }



    function _cal_fill_weekly(&$res, $rec, $dtend, $fdt = 0){

        $dt = $rec['date_start_t'];

        $dte = $rec['date_finish_t'];

        $dte  = date_add_day($dt, 6);

        $delta = $dte-$dt;

        $options = array();

        if (!empty($rec['repeat_option']))

            $options = explode(',', $rec['repeat_option']);

        $dw = date('w', $dt);

        if (empty($options)) $options = array($dw);

        

        while (!in_array($dw, $options)){ // find exptected dow

            $dt = date_add_day($dt, 1);

            $dw = date('w', $dt);

        }

        while ($dt < $dtend){

            $sdt = $dt;

            $long = $dt+$delta;

            while ($dt <= $long){

                $dw = date('w', $dt);

                if (in_array($dw, $options)){

                    //echo date('D, d M Y. w, ', $dt).$rec['title'].'<br>';

                    $cal_code = date('Ymd', $dt);

                    $rec['cur_event_date'] = date('D, d M Y', $dt);

                    

                    if ($fdt > 0){

                        if ($dt == $fdt){

                            $res[$cal_code][] = $rec;

                            break;

                        }

                    } else

                        $res[$cal_code][] = $rec;

                }

                $dt = date_add_day($dt, 1);

                //break;

            }

            

            $dt = date_add_day($sdt, $rec['repeat_interval']*7);

        }

    }



	$result = Array();

    $bookinfo = array();

    $days_of_upcoming_events = UPCOMING_EVENTS_PERIOD;

    $today = date('Y-m-d');

    $uptotm = date_add_day(time(), $days_of_upcoming_events);

    $upto =  date('Y-m-d', $uptotm);

    $query = "SELECT *, date_format(date_start, '%Y%m%d') cal_code, UNIX_TIMESTAMP(repeat_until) repeat_until_t, 

                UNIX_TIMESTAMP(date_start) date_start_t, UNIX_TIMESTAMP(date_finish) date_finish_t, 

                date_format(date_start, '%d-%b-%Y') date_start_fmt, date_format(date_finish, '%d-%b-%Y') date_finish_fmt,  

                date_format(time_start, '%H:%i') time_start_fmt, date_format(time_finish, '%H:%i') time_finish_fmt  

                FROM calendar_view ce 

				WHERE ce.date_start <= '$today' AND  (ce.repeat_until <= '$upto' OR  ce.repeat_until = '9999-00-00') AND

                status IN ('BOOK', 'COMMENCE') ";//AND date_format(date_finish, '%Y-%c') <= '$y-$m'

    //if ($id_facility>0) $query .= " AND ce.id_facility = $id_facility ";

    $query.= " ORDER BY cdate ASC";

    

	$rs = mysql_query($query);	

    //echo $query.mysql_error();

	while ($rec = mysql_fetch_assoc($rs))

        $bookinfo[$rec['id_event']] = $rec;

    //print_r($bookinfo);

    $m = date('n');

    $y = date('Y');

    $start_date_of_the_month = mktime(0, 0, 0, $m, 1, $y);

    $last_date_of_the_month = mktime(0, 0, 0, $m, date('t', $start_date_of_the_month), $y);

    foreach ($bookinfo as $id_event => $rec){

        if (!empty($rec)){

            $rec['date_start_t'] = time();

            //$rec['cal_code'] = date('Ymd');

            $cal_code = $rec['cal_code'];

            //if (!is_array($result[$cal_code])) $result[$cal_code] = array();

            

            $dtend = $rec['repeat_until_t'];

            if (empty($dtend) || $dtend > $last_date_of_the_month)

                $dtend = $last_date_of_the_month;

            if ($rec['repetition'] == 1) { // daily 

                _cal_fill_daily($result, $rec, $dtend);

            } else

            if ($rec['repetition'] == 2) { // weekly

                _cal_fill_weekly($result, $rec, $dtend);

            } else

            if ($rec['repetition'] == 3) { // monthly

                _cal_fill_monthly($result, $rec, $dtend);

            } else {

                $rec['cur_event_date'] = date('D, d M Y', $rec['date_start_t']);

                $result[$cal_code][] = $rec;

            }

        }

	}

    ksort($result);

    $tmp = array();

    $i = 0;

    foreach ($result as $cal_code => $rec)

        foreach ($rec as $event){

            if ($i++ >= $cnt) break;

            $tmp[$cal_code][]= $event;

        }

    //print_r($tmp);

    $result = $tmp;

	return $result;

}



function get_events_by_date($dt){

	$result = Array();

    $d = date('Y-m-d', $dt);

    $dtonly = mktime(23, 59, 59,date('n', $dt),date('j', $dt),date('Y', $dt));

    $query = "SELECT cei.*, ce.title, ce.location_name, ce.fullday, ce.repetition, ce.description,

                ce.id_location, ce.`interval`, ce.dt_last, id_user, ce.full_name   

                FROM calendar_event_instances cei 

                LEFT JOIN calendar_view ce ON ce.id_event = cei.id_event 

                WHERE cei.start<= $dt AND cei.end >= $dt ";

    

	$rs = mysql_query($query);	

    //echo $query.mysql_error();

	while ($rec = mysql_fetch_assoc($rs))

        $result[] = $rec;



	return $result;

}



function count_events_by_creator($id_user){

	$result = 0;

    $query = "SELECT COUNT(*) FROM calendar_view ce  WHERE id_user = '$id_user' ";    

	$rs = mysql_query($query);	

    

	if ($rec = mysql_fetch_row($rs))

        $result = $rec[0];

	return $result;

}



function get_events_by_creator($id_user, $start, $limit){

	$result = Array();

    $query = "SELECT *

                FROM calendar_view ce 

				WHERE id_user = '$id_user' 

                ORDER BY cdate ASC LIMIT $start, $limit";

    

	$rs = mysql_query($query);	

	while ($rec = mysql_fetch_assoc($rs))

        $result[] = $rec;

	return $result;

}



function cal_fill_daily(&$res, $rec, $dtstart, $dtend, $fdt = 0){

    $dt = $rec['date_start_t'];

    $dte = $rec['date_finish_t'];

    $delta = ($dte-$dt) / (24 * 60 * 60 );

    if ($dt < $dtstart) $dt = $dtstart;

    $repeat_interval = !empty($rec['repeat_interval']) ? $rec['repeat_interval'] : 1;

    $repeat_until = strtotime($rec['repeat_until'] . ' 23:59:59');

    //echo date('Y-m-d H:i:s', $dt) . ' --- '. date('Y-m-d H:i:s', $dtstart) . ' --- '. date('Y-m-d H:i:s', $dtend). ' --- '. date('Y-m-d H:i:s', $repeat_until); 

    if ($repeat_until < $dtend) $dtend = $repeat_until;

    while ($dt <= $dtend){

        $cal_code = date('Ynj', $dt);

        $rec['cur_event_date_code'] = date('Ymd', $dt);

        $rec['cur_event_date'] = date('D, d M', $dt) . ', ' . $rec['time_start_fmt'] ;

        $rec['cur_event_date'] .= ' - ' . $rec['time_finish_fmt'] ;

        $rec['date_start'] = date('Y-m-d', $dt);

        $duration = date_add_day($dt, $delta);

        $rec['date_finish'] = date('Y-m-d', $duration);

        if ($fdt > 0){

            if ($dt == $fdt){

                $res[$cal_code][] = $rec;

                break;

            }

        } else

            $res[$cal_code][] = $rec;

        $dt = date_add_day($dt, $repeat_interval);

    }

}





function cal_fill_monthly(&$res, $rec, $dtend, $fdt = 0){

    $dt = $rec['date_start_t'];

    $dte = $rec['date_finish_t'];

    $delta = ($dte-$dt) / (24 * 60 * 60 );

    $dom = date('md', $dt);

    while ($dt <= $dtend){

        $sdt = $dt;

        $long = $dt+$delta;

        while ($dt <= $long){

            $cal_code = date('Ynj', $dt);

            $duration = date_add_day($dt, $delta);

            $rec['cur_event_date_code'] = date('Ymd', $dt);

            $rec['cur_event_date'] = date('D, d M', $dt) . ', ' . $rec['time_start_fmt'] ;

            $rec['cur_event_date'] .= ' - ' . date('D, d M', $duration) . ', ' . $rec['time_finish_fmt'] ;

            if ($fdt > 0){

                if ($dt == $fdt){

                    $res[$cal_code][] = $rec;

                    break;

                }

            } else

                $res[$cal_code][] = $rec;

            $dt = date_add_day($dt, 1);

        }

        

        $dt = date_add_months($sdt, $rec['repeat_interval']);

    }

}



function cal_fill_weekly(&$res, $rec, $dtstart, $dtend, $fdt = 0){

    $dt = $rec['date_start_t'];

    $dte = $rec['date_finish_t'];

    $delta = ($dte-$dt) / (24 * 60 * 60 );

    

    //echo "$rec[date_start_fmt]-$rec[date_finish_fmt] - $delta";



    $repeat_interval = !empty($rec['repeat_interval']) ? $rec['repeat_interval'] : 1;

    $repeat_until = strtotime($rec['repeat_until'] . ' 23:59:59');

    if ($repeat_until < $dtend) $dtend = $repeat_until;

    

    // bypass dates before required start date

    /*

    while ($dt < $dtstart){

        $sdt = $dt;

        $dt = date_add_day($dt, $repeat_interval*7);

    }

    //$dt = $sdt;

    */

    if ($dt < $dtstart) $dt = $dtstart;

    $dw = date('w', $dt);

    

    if (!empty($rec['repeat_option']))

        $options = explode(',', $rec['repeat_option']);

    else $options = array($dw);

    /*

    while (!in_array($dw, $options)){ // find exptected dow

        $dt = date_add_day($dt, 1);

        $dw = date('w', $dt);

    }

    */

    //echo date('Y-m-d H:i:s', $dt) . ' --- '. date('Y-m-d H:i:s', $dtstart) . ' --- '. date('Y-m-d H:i:s', $dtend);

    //return;

    while ($dt <= $dtend){

        $sdt = $dt;

        $long  = date_add_day($dt, 7);

        //$long = $dt+$delta;

        while ($dt <= $long && $dt <= $dtend){

        //for($i=0; $i<7; $i++){

            $dw = date('w', $dt);

            if (in_array($dw, $options)){

                $cal_code = date('Ynj', $dt);

                $duration = date_add_day($dt, $delta);

                $rec['cur_event_date_code'] = date('Ymd', $dt);

                $rec['cur_event_date'] = date('D, d M', $dt) . ', ' . $rec['time_start_fmt'] ;

                $rec['cur_event_date'] .= ' - ' . date('D, d M', $duration) . ', ' . $rec['time_finish_fmt'] ;

                $rec['date_start'] = date('Y-m-d', $dt);

                $rec['date_finish'] = date('Y-m-d', $duration);

                

                if ($fdt > 0){

                    //echo "$dt : $fdt<br>";

                    if ($dt >= $fdt){

                        $res[$cal_code][] = $rec;

                        break;

                    }

                } else

                    $res[$cal_code][] = $rec;

            }

            $dt = date_add_day($dt, 1);

            //break;

        }

        

        $dt = date_add_day($sdt, $repeat_interval*7);

    }

}





function save_delete_reason($id, $user, $remark){

    $result = false;

    $remark = mysql_real_escape_string($remark);

    $query = "INSERT INTO calendar_event_delete(id_event, id_user, delete_remark) 

                VALUE($id, $user, '$remark')";

    @mysql_query($query);

    $result = mysql_affected_rows() > 0;

    return $result;

}



function delete_event($id, $seldate, $id_user, $remark, $part = 1){

    $result = false;

    $event = get_event_info($id);

    if ($event['repetition'] == REPEAT_NONE){

        // clear instance

        $query = "DELETE FROM calendar_event_instances WHERE id_event = '$id'";

        @mysql_query($query);

        $query = "UPDATE calendar_events SET status = 1 WHERE id_event = '$id'";

        @mysql_query($query);

        $result = mysql_affected_rows() > 0;

        if ($result)

            save_delete_reason($id, $id_user, $remark);

    } 

    else {

        // 1 - to date, 2 - to date and following, 3 - all in series

        $new_dttm = strtotime($seldate);

        if ($part == 2){

            // limit for original event to date, will stop recurring

            $query = "UPDATE calendar_events SET dt_last = $new_dttm WHERE id_event = '$id'";

            @mysql_query($query);

            $query = "DELETE FROM calendar_event_instances WHERE id_event = '$id' && DATE_FORMAT(FROM_UNIXTIME(start), '%Y-%m-%d') >= '$seldate' ";

            @mysql_query($query);

            //echo "2: $query";

            $result = mysql_affected_rows() > 0;

            if ($result)

               save_delete_reason($id, $id_user, $remark);

        } else 

        if ($part == 1){

            /*

            $new_event = duplicate_event($id);

            //$new_dts = date('Y-m-d', $new_dttm);

            $dttm_diff = ($event['date_finish_t']-$event['date_start_t']);

            if ($dttm_diff>0) $dttm_diff = round($dttm_diff/60*60*24);

            $dttm_diff++;

            $query = "UPDATE calendar_events SET date_start = DATE_ADD('$seldate', INTERVAL 1 DAY), date_finish = DATE_ADD('$seldate', INTERVAL $dttm_diff DAY) WHERE id_event = '$new_event'";

            @mysql_query($query);

            // limit for original event to date

            $query = "UPDATE calendar_events SET repeat_until = DATE_SUB('$seldate', INTERVAL 1 DAY) WHERE id_event = '$id'";

            */

            $query = "DELETE FROM calendar_event_instances WHERE id_event = '$id' && DATE_FORMAT(FROM_UNIXTIME(start), '%Y-%m-%d') = '$seldate' ";

            @mysql_query($query);

            //echo "1: $query";

            $result = mysql_affected_rows() > 0;

            if ($result)

               save_delete_reason($id, $id_user, $remark);

        } else 

        if ($part == 3){

            $query = "DELETE FROM calendar_event_instances WHERE id_event = '$id'";

            @mysql_query($query);

            //echo "3: $query";

            $query = "UPDATE calendar_events SET status = '1' WHERE id_event = '$id'";

            @mysql_query($query);

            $result = mysql_affected_rows() > 0;

            if ($result)

               save_delete_reason($id, $id_user, $remark);

        }

        putLog('query', array($query, mysql_error()));

    }

    return $result;

}



function duplicate_event($id, $single = false){

    $query = "INSERT INTO calendar_events(cdate, id_user, id_location, date_start, date_finish, time_start, time_finish, 

                fullday, repetition, repeat_interval, repeat_period, repeat_until, repeat_option, title, description, status, type)

                SELECT now(), id_user, id_location, date_start, date_finish, time_start, time_finish, 

                fullday, repetition, repeat_interval, repeat_period, repeat_until, repeat_option, title, description, status, type 

                FROM calendar_events WHERE id_event = $id"; 

    mysql_query($query);

    //echo mysql_error().$query;

    if (mysql_affected_rows()>0)

        return mysql_insert_id();

    return false;

}



function generate_instances($id, $start = 0, $end = 0, $init = false){

    $result = array();

    $event = get_event_info($id);    

    if (empty($event)) return false;

    

    $dtend = $event['dt_last'];

    if (empty($dtend)||$dtend ==0) $dtend = $end;

    //echo "$id . ".date("Y-m-d",$start )." . ".date("Y-m-d",$end)." . ".date("Y-m-d",$dtend)."  <br>";

    

    if ($dtend > $start) { // process active event in selected range

        switch ($event['repetition']) { 

        case REPEAT_DAILY:

            get_daily($result, $event, $start, $end);

            break;

        case REPEAT_WEEKLY:

            get_weekly($result, $event, $start, $end);

            break;

        case REPEAT_MONTHLY:

            get_monthly($result, $event, $start, $end, $init);

            //print_r($result);

            break;

        case REPEAT_NONE:

            $rec['id_event']  = $event['id_event'];

            $rec['start']  = $event['dt_start'];

            $rec['end']  = $event['dt_end'];

            $result[] = $rec;

        }

        save_instances($result);

    }

}



function save_instances($instances){

    if (is_array($instances)){

        $values = array();

        foreach($instances as $rec){

            $values[] = "($rec[id_event], $rec[start], $rec[end])";

        }

        if (count($values)>0){

            $id_event = $instances[0]['id_event'];

            //$query = 'DELETE FROM calendar_event_instances WHERE id_event = ' . $instances[0]['id_event'];

            //mysql_query($query);

            $query = 'INSERT INTO calendar_event_instances(id_event, start, end) VALUES ';

            $query .= implode(', ', $values);

            mysql_query($query);

            //echo $query.mysql_error();

            if (mysql_affected_rows()>0){

                $query = "UPDATE calendar_events ce 

                            SET dt_instance = (SELECT MAX(end) FROM calendar_event_instances cei WHERE cei.id_event = $id_event) 

                            WHERE ce.id_event = $id_event";

                mysql_query($query);

            }

            //if ($id_event == 79)             echo $query.mysql_error()."<br>\n";

        }

        //putLog('instance', $values);

    }

}



function get_latest_instance($id){

    $query = "SELECT MAX(end) FROM calendar_event_instances WHERE id_event = $id";

    $rs = mysql_query($query);

    $rec = mysql_fetch_row($rs);

    return $rec[0];

}



function delete_instance($id, $dts = 0, $dte = 0){

    $query = "DELETE FROM calendar_event_instances WHERE id_event = $id ";

    if ($dts > 0){

        if ($dte > 0)

            $query .= " AND start >= $dts AND end <= $dte"; // range of instance

        else if ($dte == -1)

            $query .= " AND start >= $dts "; // selected intance and future

        else

            $query .= " AND start = $dts"; // an intance

    }

    //echo $query;

    mysql_query($query);

}



function get_daily(&$res, $rec, $dtstart, $dtend, $fdt = 0){

    $dt = $rec['dt_start'];

    $dte = $rec['dt_end'];

    $delta = ($dte-$dt) / (24 * 60 * 60 );

    if ($dt < $dtstart) $dt = $dtstart;

    $interval = !empty($rec['interval']) ? $rec['interval'] : 1;

    $repeat_until = $rec['dt_last'];

    if ($repeat_until > 0 && $repeat_until < $dtend) $dtend = $repeat_until;

    while ($dt <= $dtend){

        $duration = date_add_day($dt, $delta);

        $rec['start'] = $dt;

        $rec['end'] = $duration;                

        $res[] = $rec;

        $dt = date_add_day($dt, $interval);

    }

}





function get_monthly(&$res, $rec, $dtstart, $dtend, $init = false){

    $dt = $rec['dt_start'];

    $dte = $rec['dt_end'];

    $delta = ($dte-$dt) / (24 * 60 * 60 );

    $dom = date('d', $dt);

    $interval = !empty($rec['interval']) ? $rec['interval'] : 1;

    $repeat_until = $rec['dt_last'];

    if ($repeat_until > 0 && $repeat_until < $dtend) $dtend = $repeat_until;

    if ($dt < $dtstart) $dt = $dtstart;

    // make sure start date is selected date of month

    $dt = mktime(date('H', $dt), date('i', $dt), date('s', $dt), date('m', $dt), $dom, date('Y', $dt));

    //echo date('Y-m-d', $dt) . '--'.date('Y-m-d', $dtend)."\n<br>";

    if (!$init)

        $dt = date_add_months($dt, $interval); // if not the initial generating, then continual

    //echo date('Y-m-d', $dt) ."\n<br>";

    while ($dt <= $dtend){

        $sdt = $dt;

        $duration = date_add_day($dt, $delta);

        $rec['start'] = $dt;

        $rec['end'] = $duration;                

        $res[] = $rec;

        $dt = date_add_months($sdt, $interval);

    }

}



function get_weekly(&$res, $rec, $dtstart, $dtend){

    $dt = $rec['dt_start'];

    $dte = $rec['dt_end'];    

    $delta = round(($dte - $dt) / (24 * 60 * 60 ));



    $interval = !empty($rec['interval']) ? $rec['interval'] : 1;

    $repeat_until = $rec['dt_last'];

    if ($repeat_until > 0 && $repeat_until < $dtend) $dtend = $repeat_until;

    

    if ($dt < $dtstart) $dt = $dtstart;

    $dw = date('w', $dt);

    

    if (!empty($rec['wd_start']))

        $options = explode(',', $rec['wd_start']);

    else $options = array($dw);

    while ($dt <= $dtend){

        $sdt = $dt;

        $long  = date_add_day($dt, 6);

        while ($dt <= $long && $dt <= $dtend){

            $dw = date('w', $dt);

            if (in_array($dw, $options)){

                $duration = ($delta == 0) ? $dt : date_add_day($dt, $delta);

                $rec['start'] = $dt;

                $rec['end'] = $duration;                

                $res[] = $rec;

                //echo date('Y-m-d', $dt) ." - $dw - $delta #$rec[id_event]<br>\n";

            }

            $dt = date_add_day($dt, 1);

        }

        $dt = date_add_day($sdt, $interval*7);

    }

}


function save_new_event($data){

    $_id = 0;

    $query = "INSERT INTO calendar_events(id_user, id_location, dt_start, dt_end, duration, 

                fullday, repetition, `interval`, dt_last, wd_start, title, description, status)

                VALUE ('$data[userid]', $data[_idloc], $data[dt_start], $data[dt_end], $data[duration], 

                $data[fullday], '$data[repetition]', $data[interval], $data[dt_last], $data[wd_start], 

                '$data[title]', '$data[description]', $data[status])"; 

    mysql_query($query);

    //echo $query.mysql_error();

    if (mysql_affected_rows()>0){

        $_id = mysql_insert_id();

        $num_of_days = ($data['repetition'] == 'MONTHLY') ? 90 : 30;

        $instance_start = $data['dt_start'];

        $instance_end = date_add_day($instance_start, $num_of_days);

        generate_instances($_id, $instance_start, $instance_end, true);    

    }

    return $_id;

}


/*UPDATE
SERVICE FOR REPORT
*/


function query_service_request_by_all_status(){
	$dept = defined('USERDEPT') ? USERDEPT : 0;
	$query  = "SELECT lr.id_loan, date_format(start_loan, '%d-%b-%Y %H:%i') as start_loan, date_format(end_loan, '%d-%b-%Y') as end_loan, 
			 date_format(request_date, '%d-%b-%Y %H:%i') as request_date, 
			 user.full_name as requester, category_name, category_type, quantity, remark, status, 
			 approved_by, approval_date, approval_remark, issued_by, issue_date, issue_remark, returned_by, 
			 date_format(return_date, '%d-%b-%Y') as return_date, 
			 return_remark, received_by, receive_date, receive_remark, acknowledged_by, acknowledge_date, acknowledge_remark 	 
			 FROM loan_request lr 
			 LEFT JOIN user ON requester = user.id_user 
			 LEFT JOIN category ON lr.id_category = category.id_category 
			 LEFT JOIN loan_process lp ON lp.id_loan = lr.id_loan  
			 WHERE category.category_type = 'SERVICE' ";
    if (!SUPERADMIN)
        $query .= " AND category.id_department = $dept ";
	
	$query .= " AND (status = 'COMPLETED' OR status = 'PENDING' OR status = 'ISSUED') ";
	$query .= " ORDER BY request_date DESC ";
	error_log($query.mysql_error());	
	$rs = mysql_query($query);
	//echo $query;
	return $rs;
}


function get_service_request_by_statuses($status){
	$result = array();
	$rs = query_service_request_by_statuses($status);
	if ($rs && (mysql_num_rows($rs)>0))
		while ($rec = mysql_fetch_assoc($rs))
			$result[] = $rec;    
	return $result;
}

// FAULT QUERY FOR REPORT

function total_fault_per_month_by_status($status, $dept=0){
	
	$query  = "
	SELECT fr.id_fault, DATE_FORMAT( fault_date,  '%d-%b-%Y %H:%i' ) AS fault_date, DATE_FORMAT( report_date,  '%d-%b-%Y %H:%i' ) AS report_date, COUNT( report_date ) AS total_per_day, DATE_FORMAT( rect.rectify_date,  '%d-%b-%Y %H:%i' ) AS rectify_date, DATE_FORMAT( rect.completion_date,  '%d-%b-%Y %H:%i' ) AS completion_date, rectify_remark, completion_remark
	FROM fault_report fr
	LEFT JOIN user ON report_user = user.id_user
	LEFT JOIN fault_category fc ON fr.fault_category = fc.id_category
	LEFT JOIN fault_rectification rect ON rect.id_fault = fr.id_fault
	LEFT JOIN location loc ON loc.id_location = fr.id_location
	WHERE fault_status =  '".$status."'
	AND fc.id_department = ".$dept."
	GROUP BY  DATE_FORMAT( report_date,  '%d-%b-%Y' )";

	$query .= " ORDER BY report_date DESC";

	$rs = mysql_query($query);

    //echo mysql_error();
	error_log($query.mysql_error());

	return $rs;

}



?>

