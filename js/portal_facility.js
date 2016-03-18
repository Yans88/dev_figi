var max_period;
var lead_time;
var repetitions;
var days = new Array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
var months = new Array('Jan','Feb', 'Mar', 'Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
var fullmonths = new Array('January','February', 'March', 'April','May','June','July','August','September','October','November','December');
var weeks = new Array('First', 'Second', 'Third', 'Fourth', 'Fifth');
var interval;
var tm_start;
var tm_finish;

function check_time(cb)
{
	if (cb.checked){
		if (selected_period.length == max_period){
			alert("You may book "+max_period+" periods only!");
			cb.checked = false;
		} else
			selected_period.push(cb.value);	
	} else { // perhaps uncheck checked checkbox
		i = 0; 
		while (i < selected_period.length){
			if (selected_period[i] == cb.value)
				selected_period.splice(i, 1);
			i++;
		}
	}
}

function remove_this(id){
    if (confirm("Do you want to confirm remove the booking?")){
        var frm = document.getElementById("form_facility");
        frm.remove.value = id ;
        frm.submitcode.value = "submit" ;
        frm.submit();
    }
}


$('#id_facility').change(function (e){
    var me = e.target;
	$.post("./facility/get_time_period.php", {id_facility: ""+me.value+""}, function(data){
		if(data.length >0) {
            //alert(data)
			var js = $.evalJSON(data);
			max_period = js.info.max_period;
			$('#time_start').html(js.time_start.join(''));
			$('#time_finish').html(js.time_finish.join(''));
            $('#time_start option').each(function(){
                if ($(this).val() == tm_start)
                    $(this).attr('selected', 'selected');
            });
            $('#time_finish option').each(function(){
                if ($(this).val() == tm_finish)
                    $(this).attr('selected', 'selected');
            });
            setMinimumTime();

        }
	});
});

function get_date(str)
{
    var cols = str.split('-');
    var dt = new Date(cols[2], months.indexOf(cols[1]), cols[0]);
    return dt;
}

$('#date_start').change(function (e){
    try {
        var oneDay = 24*60*60*1000;
        var dFormat = "%e-%b-%Y";
        var dConv = new AnyTime.Converter({format:dFormat});
        var fromDay = dConv.parse($(this).val()).getTime();
        var dayLater = new Date(fromDay);
        $("#date_finish").
          AnyTime_noPicker().
          removeAttr("disabled").val($("#date_start").val()).
          AnyTime_picker(
              { earliest: dayLater,
                format: dFormat
              } );
		if ($('#date_until').val()){
			var untilDay = dConv.parse($('#date_until').val()).getTime();
			if (untilDay<fromDay)
				$("#date_until").
				  AnyTime_noPicker().
				  removeAttr("disabled").val($("#date_start").val()).
				  AnyTime_picker(
					  { earliest: dayLater,
						format: dFormat
					  } );
		}
        } catch(e){ $("#date_finish").val("").attr("disabled","disabled"); 
    }     
    setMinimumTime();
    addOneHour();
});

function addOneHour(){
    var dFormat = "%e-%b-%Y";
    var dConv = new AnyTime.Converter({format:dFormat});
    var dts = new Date(dConv.parse($('#date_start').val()).getTime());
    var dtf = new Date(dConv.parse($('#date_finish').val()).getTime());
    var tms = $('#time_start').val();
    if (tms == null) return;
    
    var atms = tms.split(':');
    var tmf = $('#time_finish').val();    
    var atmf = tmf.split(':');
    
    dtf.setHours(parseInt(atms[0])+1, atms[1]);
    var options = $('#time_finish option');
    var ntmf = zerofill(dtf.getHours().toString(), 2) +':'+zerofill(dtf.getMinutes().toString(), 2) ;
    for(var i=0; i<options.length; i++){
        options[i].selected = false;    
        if (options[i].value == ntmf)
            options[i].selected = true;
    }
}

$('#time_start').change(function (e){setMinimumTime();addOneHour();});
    
$('#check_schedule').click(function(e){
	var facility = $('#id_facility').val();
	var start = $('#date_start').val();
	var finish = $('#date_finish').val();

	$.post("./facility/get_timesheet.php", {id_facility: ""+facility+"", date_start: ""+start+"", date_finish: ""+finish+"", readonly: 1}, function(data){
		if(data.length >0) {
			$('#schedule_text').html(data);
			$('#schedule_space').show();	
		} else {
			$('#schedule_space').hide();	
		}
	});
});

$('#hide_timesheet').click(function (e){
	$('#schedule_space').hide(); 
	});

$('#fullday').change(function (e){
    var checked =   $('input:checkbox[name=fullday]:checked').val() == "yes";
    if (checked){
        //$('#time_start').attr('disabled', 'disabled'); 
        //$('#time_finish').attr('disabled', 'disabled'); 
        $('#time_start').hide(); 
        $('#time_finish').hide(); 
    } else {
        //$('#time_start').removeAttr('disabled'); 
        //$('#time_finish').removeAttr('disabled'); 
        $('#time_start').show(); 
        $('#time_finish').show(); 
    }
});

$('#repetition').change(function(e){
    var dFormat = "%e-%b-%Y";
    $('#repeat_option_weekly').hide(); 
    $('#repeat_option_monthly').hide(); 
    if($('#repetition option:selected').val()!='NONE'){
        $('#repetition_option').show();
        var label = 'no repetition';
        switch ($('#repetition option:selected').val()){
            case 'DAILY': label = 'day(s)'; break;
            case 'WEEKLY': label = 'week(s)'; $('#repeat_option_weekly').show(); break;
            case 'MONTHLY': label = 'month(s)'; /*$('#repeat_option_monthly').show();*/  break;
            case 'YEARLY': label = 'year(s)'; break;
        }
		var dConv = new AnyTime.Converter({format:dFormat});
		var dateuntil = $("#date_finish").val();
		var dfinish = dConv.parse(dateuntil).getTime();
		//dateuntil = $('#date_until').val();
		if ($('#date_until').val()) dateuntil = $('#date_until').val();
		var untilDay = dConv.parse(dateuntil).getTime();
		if (untilDay-1<dfinish)
			$("#date_until").
			  AnyTime_noPicker().val(dateuntil).
			  AnyTime_picker(
				  { earliest:dateuntil, 
					format: dFormat
				  } );
		
        $('#interval_name').html(label);
    } else
        $('#repetition_option').hide();
});

function fill_interval(){
    var option = null;
    var max_repetition = 20;
    for (var i=1; i<=max_repetition; i++)
        option += '<option value="'+i+'">'+i+'</option>';
    $('#interval').html(option);
}

function setMinimumTime(){
    var d = new Date();
    var dateFormat = "%e-%b-%Y";      
    var dateConv = new AnyTime.Converter({format:dateFormat});
    var sd = new Date(dateConv.parse($("#date_start").val()).getTime());
    var options = $('#time_start option');
    
    for(var i=0; i<options.length; i++)
        options[i].disabled = false;
    
    if (d.getFullYear()>=sd.getFullYear() && d.getMonth()>=sd.getMonth() && d.getDate()>=sd.getDate()){
        for(var i=0; i<options.length; i++){
            hours = options[i].value.split(':');
            if (parseInt(hours[0]) <= d.getHours()){
                    options[i].disabled = true;
                if (parseInt(hours[1]) <= d.getMinutes()){
                    options[i].disabled = true;
                }
            } else
                options[i].disabled = false;
            if (options[i].disabled && options[i].selected)
                options[i].selected = false;
                
        }
    }
    // finish
    var sd = new Date(dateConv.parse($("#date_finish").val()).getTime());
    var options = $('#time_finish option');

    for(var i=0; i<options.length; i++)
        options[i].disabled = false;    
    
    if (d.getFullYear()>=sd.getFullYear() && d.getMonth()>=sd.getMonth() && d.getDate()>=sd.getDate()){
        for(var i=0; i<options.length; i++){
            hours = options[i].value.split(':');
            if (parseInt(hours[0]) <= d.getHours()){
                    options[i].disabled = true;
                if (parseInt(hours[1]) <= d.getMinutes()){
                    options[i].disabled = true;
                }
            } else
                options[i].disabled = false;
            if (options[i].disabled && options[i].selected)
                options[i].selected = false;
        }
    }
    
}


