var desc_height = -75;
var desc_width = 160;

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
        var frm = document.getElementById("form_calendar");
        frm.remove.value = id ;
        frm.submitcode.value = "submit" ;
        frm.submit();
    }
}


function setMinimumTime(){
    var d = new Date();
    // start
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

function get_date(str)
{
    var cols = str.split('-');
    var dt = new Date(cols[2], months.indexOf(cols[1]), cols[0]);
    return dt;
}

$('#date_start').change(function (e){
    time_check();
    /*
    try {
        var oneDay = 24*60*60*1000;
        var dFormat = "%e-%b-%Y";
        var dConv = new AnyTime.Converter({format:dFormat});
        var fromDay = dConv.parse($(this).val()).getTime();
        var dayLater = new Date(fromDay);
        dayLater.setHours(23,59,59,999);
        $("#date_finish").
          AnyTime_noPicker().
          removeAttr("disabled").
          val(dConv.format(dayLater)).
          AnyTime_picker(
              { earliest: dayLater,
                format: dFormat
              } );
        } catch(e){ $("#date_finish").val("").attr("disabled","disabled"); 
    } 
*/    
    setMinimumTime();
});
$('#date_finish').change(function (e){
    time_check();
    setMinimumTime();
});


$('#check_schedule').click(function(e){
	var facility = $('#id_facility').val();
	var start = $('#date_start').val();
	var finish = $('#date_finish').val();

	$.post("facility/get_timesheet.php", {id_facility: ""+facility+"", date_start: ""+start+"", date_finish: ""+finish+"", readonly: 1}, function(data){
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
    var checked = $(this).attr('checked');
    if (checked){
        $('#time_start').attr('disabled', 'disabled'); 
        $('#time_finish').attr('disabled', 'disabled'); 
        
        //$('#time_start').hide(); 
        //$('#time_finish').hide(); 
    } else {
        $('#time_start').removeAttr('disabled'); 
        $('#time_finish').removeAttr('disabled'); 
        //$('#time_start').show(); 
        //$('#time_finish').show(); 
    }
});

$('#repetition').change(function(e){
    $('#repeat_option_weekly').hide(); 
    $('#repeat_option_monthly').hide(); 
    if($('#repetition option:selected').val()!='NONE'){
        $('#repetition_option').show();
        var label = 'no repetition';
        switch ($('#repetition option:selected').val()){
            case 'DAILY': label = 'day(s)'; break;
            case 'WEEKLY': label = 'week(s)'; $('#repeat_option_weekly').show(); break;
            case 'MONTHLY': label = 'month(s)'; /*$('#repeat_option_monthly').show();  */break;
            case 'YEARLY': label = 'year(s)'; break;
        }
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

function show_desc(event,id){
	var e = event || window.event;
    var pos = getRelativeCoordinates(event, document.getElementById('reference'));    
    //var pos = getAbsolutePosition(document.getElementById('reference'));    
	var obj = document.getElementById('desc-'+id);
	var objh = $('#desc-'+id).height();
    var ref = $('#reference').position();
	if (obj){
		//obj.style.top = (pos.y+ref.top-objh-15)+'px';
		//obj.style.left = (pos.x+ref.left+5)+'px';
		obj.style.top = (pos.y+desc_height)+'px';
		obj.style.left = (pos.x+desc_width)+'px';
		obj.style.display = 'block';
	}
}

function hide_desc(event,id){
	var obj = document.getElementById('desc-'+id)
	if (obj){
		obj.style.display = 'none';
	}
}

function cellover(event, elm){
	elm.style.backgroundColor = '#ffe';
}

function cellout(event, elm){
	//elm.style.backgroundColor = '#ffe';
}

/* 
	bagian bawah ini, comot punya orang di 
	http://acko.net/blog/mouse-handling-and-absolute-positions-in-javascript
*/

/**
 * Retrieve the absolute coordinates of an element.
 *
 * @param element
 *   A DOM element.
 * @return
 *   A hash containing keys 'x' and 'y'.
 */
function getAbsolutePosition(element) {
  var r = { x: element.offsetLeft, y: element.offsetTop };
  if (element.offsetParent) {
    var tmp = getAbsolutePosition(element.offsetParent);
    r.x += tmp.x;
    r.y += tmp.y;
  }
  return r;
};

/**
 * Retrieve the coordinates of the given event relative to the center
 * of the widget.
 *
 * @param event
 *   A mouse-related DOM event.
 * @param reference
 *   A DOM element whose position we want to transform the mouse coordinates to.
 * @return
 *    A hash containing keys 'x' and 'y'.
 */
function getRelativeCoordinates(event, reference) {
  var x, y;
  event = event || window.event;
  var el = event.target || event.srcElement;

  if (!window.opera && typeof event.offsetX != 'undefined') {
    // Use offset coordinates and find common offsetParent
    var pos = { x: event.offsetX, y: event.offsetY };

    // Send the coordinates upwards through the offsetParent chain.
    var e = el;
    while (e) {
      e.mouseX = pos.x;
      e.mouseY = pos.y;
      pos.x += e.offsetLeft;
      pos.y += e.offsetTop;
      e = e.offsetParent;
    }

    // Look for the coordinates starting from the reference element.
    var e = reference;
    var offset = { x: 0, y: 0 }
    while (e) {
      if (typeof e.mouseX != 'undefined') {
        x = e.mouseX - offset.x;
        y = e.mouseY - offset.y;
        break;
      }
      offset.x += e.offsetLeft;
      offset.y += e.offsetTop;
      e = e.offsetParent;
    }

    // Reset stored coordinates
    e = el;
    while (e) {
      e.mouseX = undefined;
      e.mouseY = undefined;
      e = e.offsetParent;
    }
  }
  else {
    // Use absolute coordinates
    var pos = getAbsolutePosition(reference);
    x = event.pageX  - pos.x;
    y = event.pageY - pos.y;
  }
  // Subtract distance to middle
  return { x: x, y: y };
}
