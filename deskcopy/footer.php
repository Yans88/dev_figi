</div>
<div id="note">
Please note it is an offence to use ID of others without their permission.
</div>
<div id="footer">
    <div id="leftfoot">    
        <div id="datetime"></div> 
    </div>
    <div id="rightfoot"> 
        <div class="buttonfoot">
<?php
    if (defined('IN_LOAN')){
        if ($_id_user > 0)
            echo '<a class="button" href="javascript:void(0)" onclick="cancel_this()"><img src="../images/cancel_loan.gif"></a>';
        if (count($items) > 0)
            echo '<a class="button" href="javascript:void(0)" onclick="confirm_this()"><img src="../images/confirm_loan.gif"></a>';
        if ($_confirm){
            echo '<a id="newloan" class="button" href="javascript:void(0)" onclick="new_loan()"><img src="../images/new_loan.gif"></a>';
            echo "<script>$('#newloan').focus();</script>";
        }

    } else {
        if (count($items) > 0)
            echo '<a class="button" href="javascript:void(0)" onclick="clear_this()"><img src="../images/clear_list.gif"></a>';
    }
    
?>      </div>
    </div>
</div>
</div>
<script>
function resize_win(){
    var w = $(window).width();
    var h = $(window).height();
    
    $('#footer').css('top', h-$('#footer').height());
    $('#footer').css('left', 0);
    var top = parseInt($('#footer').css('top'));    
    var toc = $('#toc').offset();    
    h = $(window).height();
    $('#form').height(h-toc.top-60);
    $('#note').css('top', -66);
}

function update_datetime(){
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var dt = new Date();
    var d = (dt.getDate() < 10) ? '0'+dt.getDate() : dt.getDate();
    var h = (dt.getHours() < 10) ? '0'+dt.getHours() : dt.getHours();
    var m = (dt.getMinutes() < 10) ? '0'+dt.getMinutes() : dt.getMinutes();
    var s = (dt.getSeconds() < 10) ? '0'+dt.getSeconds() : dt.getSeconds();
    var dtstr = dt.getFullYear()+'-'+months[dt.getMonth()]+'-'+ d + '<br/>' +
                h +':'+m+':'+s;
    $('#datetime').html(dtstr);
    
    setTimeout('update_datetime()', 1000);
}
update_datetime();
window.onresize = resize_win;
$(window).resize();
</script>
</body>
</html>
