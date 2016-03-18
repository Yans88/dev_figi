<script type="text/javascript" src="./js/jquery.fancybox.pack.js?v=2.0.6"></script>
<link rel="stylesheet" type="text/css" href="./style/default/jquery.fancybox.css?v=2.0.6" media="screen" />
<?php

$msgbox = null;
$msgdesc = null;
if (defined('MODULE_CALENDAR_LOADED') && MODULE_CALENDAR_LOADED){
    $msgdesc = '<div style="display:none; float: both">';
    $events = get_upcoming_events(MAX_UPCOMING_EVENTS);
    if (!empty($events)){
        $msgbox .=<<<EVENT
    <div class="upcoming_events">
        <div class="header">&nbsp;
            <h4>Upcoming events</h4>
            <h4 class="foldtoggle"><a id="btn_upcoming_event_list" rel="open" href="javascript:void(0)">&uarr;</a></h4>
        </div>
        <div class="notification message" id="upcoming_event_list">
EVENT;
    if (!empty($events)){
        $msgbox .= '<table style="min-width: 400px; " cellpadding=3 cellspacing=0 >';
        foreach($events as $cal_code => $recs){
            foreach ($recs as $rec){
                $date_start_fmt = date('D, d M Y', $rec['date_start_t']);
                $date_start_fmt = $rec['cur_event_date'];
                $event_name = $rec['title'] . ' @ ' . $rec['location_name'];
                $msgbox .=<<<ITEM
                <tr>
                    <td>$date_start_fmt</td>
                    <td align="center">$rec[time_start_fmt] - $rec[time_finish_fmt]</td>
                    <td align="left">
                        <a class="event_item" href="#desc-$rec[id_event]">$event_name</a>
                    </td>
                </tr>
ITEM;
                $msgdesc .= <<<DESC
<div id="desc-$rec[id_event]" ><pre>
<strong>Event Detail</strong>
Title: $rec[title]
Date Start: $rec[date_start_fmt]
Time: $rec[time_start_fmt] - $rec[time_finish_fmt]
Location: $rec[location_name]
Desc.: $rec[description]  
Owner: $rec[full_name] 
</pre></div>
DESC;
            }
        }
            $msgbox .=<<<END
            </table>
    <script>$("a.event_item").fancybox({'hideOnContentClick': true});</script>
END;
        } else
            $msgbox .= '<div style="text-align: center; ">No Event Found!</div>';
            
        if (strpos($_SERVER['QUERY_STRING'], 'portal')>0)
            $view_link = '<a href="./?mod=portal&portal=calendar&act=view_month">view calendar</a>';
        else
            $view_link = '<a href="./?mod=calendar&act=view_month">view calendar</a>';
        $msgbox .= '<div style="text-align: right;">'.$view_link.'</div></div> </div>';
    }
    $msgdesc .= '</div>';
}
echo "$msgbox\n$msgdesc";
?>
<script>
$('#btn_upcoming_event_list').click(function (e){
    toggle_fold(this);
});
</script>