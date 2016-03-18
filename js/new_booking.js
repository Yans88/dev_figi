$(function(){
    var Event = Backbone.Model.extend({
        //idAtttribute: "key"
    });

    var Events = Backbone.Collection.extend({
        model: Event,
        url: './facility/events.php' //'events'
    }); 
 
    var EventsView = Backbone.View.extend({
        initialize: function(){
            _.bindAll(this); 
            
            this.collection.bind('reset', this.addAll);
            this.collection.bind('add', this.addOne);
            this.collection.bind('change', this.change);            
            this.collection.bind('destroy', this.destroy);
            this.el = $(this.el);
            this.eventView = new EventView();
        },
        render: function() {
            this.el.fullCalendar({
                header: {
                    left: 'prev,today,next',
                    center: 'title',
                    right: 'month,agendaWeek'
                },
                month: the_month,
                selectHelper: true,
                editable: is_editable,
                selectable: is_selectable,
                ignoreTimezone: false,                
                lazyFetching: false,
                select: this.select,
                eventClick: this.eventClick,
                eventDragStart: this.eventDragOrResizeStart,        
                eventResizeStart: this.eventDragOrResizeStart,        
                eventDrop: this.eventDropOrResize,        
                eventResize: this.eventDropOrResize,
                viewDisplay: this.viewDisplay,
                eventAfterRender: this.eventAfterRender,
                loading: this.loading,
                minTime: min_time,
                maxTime: max_time,
				defaultView: 'agendaWeek',
            });
        },
        loading: function(isLoading, view) {
          if (isLoading)
            $('#loadinglabel').show();
           else
            $('#loadinglabel').hide();
           //alert(isLoading)
        },
        addAll: function() {
            this.el.fullCalendar('addEventSource', this.collection.toJSON());
        },
        addOne: function(event) {
            //this.collection.
            //this.el.fullCalendar('renderEvent', event.toJSON());
            //this.el.fullCalendar('refetchEvents');
            this.fetch_events();
        },        
        select: function(startDate, endDate, allDay) {
            //var eventView = new EventView(); 
            var view = this.el.fullCalendar('getView');

			startDate.setHours(0);
			startDate.setMinutes(0);
			startDate.setSeconds(0);

            var dts = startDate.getTime() / 1000;
				endDate.setHours(23);
				endDate.setMinutes(59);
				endDate.setSeconds(59);
            var dte = endDate.getTime() / 1000;
            var dtt = new Date();
            dtt.setHours(0);
            dtt.setMinutes(0);
            dtt.setSeconds(0);
            var dtc = Math.round(dtt.getTime() / 1000);
            //if (dts==dte) dte += 86400;      
			//alert(startDate+"\r"+endDate)
            var f;
			/*
			if (view == 'month'){
				endDate.setHours(23);
				endDate.setMinutes(59);
				endDate.setSeconds(59);
				dte = endDate.getTime() / 1000;
				f = this.collection.filter(function(model){
						var v = (model.get('start')>=dts)  && (model.get('start') <= dte);
                        return (v);
                    });
            
			} else {
				f = this.collection.filter(function(model){
						var v = (model.get('start')>=dts)  && (model.get('start') <= dte);
                        return (v);
                    });
			}
			*/
				f = this.collection.filter(function(model){
						var v = (model.get('start')>=dts)  && (model.get('start') <= dte);
                        return (v);
                    });
                        
            var coll = this.collection;
			var booked = false;
            if (typeof(f)=='object' && f.length>0){
				for(var i=0; i<f.length; i++){
					//alert(f[i].get('title')+':'+f[i].get('allDay'));
					if (f[i].get('allDay')){
						booked = true;
						break;
					}
				}
			}
			if (booked){
                alert('The facility has been booked on selected date!');
            } else {
                this.eventView.collection = this.collection;
                this.eventView.model = new Event({start: startDate, end: endDate, allDay: allDay});
                this.eventView.render();            
            }
        },
        eventClick: function(fcEvent, jsEvent, view) {
            
            //alert('Event: ' + fcEvent.title);
            //alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
            //alert('View: ' + view.name);


            this.eventView.model = this.collection.get(fcEvent.id);
            this.eventView.render();
            //return false;
        },
        viewDisplay: function(view){
           
        },
        eventAfterRender: function(event, element, view){
           
        },
        change: function(event) {
            // Look up the underlying event in the calendar and update its details from the model
            var fcEvent = this.el.fullCalendar('clientEvents', event.get('id'))[0];
            fcEvent.title = event.get('title');
            fcEvent.color = event.get('color');
            this.el.fullCalendar('updateEvent', fcEvent);           
        },
        eventDropOrResize: function(fcEvent, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) {
            // Lookup the model that has the ID of the event and update its attributes
            this.collection.get(fcEvent.id).save({start: fcEvent.start, end: fcEvent.end});            
        },
        eventDragOrResizeStart: function( event, jsEvent, ui, view ) { 
            /*
            if (current_user == event.owner){
                event.editable = true;
            } else {
                event.editable = false;
            }
            */
        },
        destroy: function(event) {
            this.el.fullCalendar('removeEvents', event.id);         
        },
        fetch_events: function () {
            var start = this.el.fullCalendar('getView'). visStart;
            var end = this.el.fullCalendar('getView'). visEnd;
            //this.collection.reset();
            this.collection.fetch({data: "start="+DateToUnixtime(start)+"&end="+DateToUnixtime(end)+"&id_facility="+$('#_facility :selected').val()});
            //this.addAll();
            this.el.fullCalendar("removeEvents");
            this.el.fullCalendar("refetchEvents");
            
        }
    });

    var EventView = Backbone.View.extend({
        el : $('#eventDialog'), 
        cel : $('#confirmDialog'),
        dcl : $('#deleteDialog'),
        initialize: function() {
            _.bindAll(this); 
            this.el = $(this.el);
            this.cel = $(this.cel);
            this.dcl = $(this.dcl);
        },
        render: function() {            
            var buttons, the_title ;
            if (typeof(authenticated)!='undefined') {
                if (this.model.isNew() || (this.model.get('editable'))){ //(this.model.get('owner') == current_user)
                    buttons = {'Save': this.save};
                    if (!this.model.isNew()){
                        //if (this.model.get('editable'))
                    	_.extend(buttons, {'Delete': this.del});
                    }
                    _.extend(buttons, {'Detail': this.detail}); 
                    _.extend(buttons, {'Edit': this.edit});           
                _.extend(buttons, {'Cancel': this.close});            
                the_title = (this.model.isNew() ? 'New' : 'Edit') + ' Booking';
                } else {
                    buttons = {'Cancel': this.close};
                    the_title = 'View Booking Info';
                }
                  
            } else {
                the_title = 'View Booking Info';
                if (this.model.isNew()) return;
                buttons = {'Cancel': this.close};
            }
            
            this.el.dialog({
                modal: true,
                title: the_title,
                buttons: buttons,
                width: 420,
                open: this.open
            });

            return this;
        },        
        open: function() {
            this.$('#title').val(this.model.get('title'));
            this.$('#color').val(this.model.get('color'));
            var start = $.fullCalendar.parseDate(this.model.get('start'));
            var end = $.fullCalendar.parseDate(this.model.get('end'));
            //alert(start + ' ' + end)
            if (this.model.isNew()) {
				var fmt_start = $.fullCalendar.formatDate(start, 'dddd, dd MMM');
				var fmt_end = $.fullCalendar.formatDate(end, 'dddd, dd MMM');
	//			alert($.fullCalendar.('getView'));
				//if ($('#calendar').fullCalendar('getView'.name)=='agendaWeek')
					var fmt_start = $.fullCalendar.formatDate(start, 'dddd, dd MMM hh(:mm)t');
					
				this.$('#event-date').text(fmt_start);
			} else {
				var fmt_start = $.fullCalendar.formatDate(start, 'dddd, dd MMM hh(:mm)t');
				var fmt_end = $.fullCalendar.formatDate(end, 'dddd, dd MMM hh(:mm)t');
				this.$('#event-date').text(fmt_start + ' - ' + fmt_end);
			}
/*
 			var v = $('#calendar').fullCalendar('getView');           
			if (v.name=='month') this.model.set({'allDay': true});
			else this.model.set({'allDay': false});
*/
            this.$('#booked-by').text(this.model.get('full_name'));
            //editable = this.model.get('editable');
        },        
        save: function() {
            this.model.set({'title': this.$('#title').val(), 'color': this.$('#color').val()});
            var facility = $('#_facility :selected').val();
            this.model.set({'id_facility': facility});
            if (this.model.isNew()) {
                //var ori = this.model.get('end')-1;
                //if (this.model.get('start')!=this.model.get('end'))            
                //    this.model.set({'end': ori})
                this.collection.create(this.model, {success: this.close });
            } else {
                this.model.save({}, {success: this.close});
            }
        },
        detail: function() {
            this.el.dialog('close');
            var start = $.fullCalendar.parseDate(this.model.get('start')).getTime()/1000;
            var end = $.fullCalendar.parseDate(this.model.get('end')).getTime()/1000;
            var facility = $('#_facility :selected').val();
            var url = "./?mod=portal&portal=facility_view&dts="+start+"&dte="+end+"&id_facility="+facility;
            //if (location.href.indexOf('portal')>-1)
            //    url = "./ajax.php?portal=facility&dts="+start+"&dte="+end;
            
            if (this.model.get('id')){
                var fld = this.model.get('id').split('-');
                url += "&id="+fld[0];
            } else if (this.$('#title').val())
                url += "&title=" + this.$('#title').val();
            //alert(url)
            location.href = url; 
        },
        edit: function(){
        	this.el.dialog('close');
            var start = $.fullCalendar.parseDate(this.model.get('start')).getTime()/1000;
            var end = $.fullCalendar.parseDate(this.model.get('end')).getTime()/1000;
            var facility = $('#_facility :selected').val();
            var url = "./?mod=portal&portal=facility&dts="+start+"&dte="+end+"&id_facility="+facility;
            //if (location.href.indexOf('portal')>-1)
            //    url = "./ajax.php?portal=facility&dts="+start+"&dte="+end;
            
            if (this.model.get('id')){
                var fld = this.model.get('id').split('-');
                url += "&id="+fld[0];
            } else if (this.$('#title').val())
                url += "&title=" + this.$('#title').val();
            //alert(url)
            location.href = url; 
        },
        close: function() {
            this.el.dialog('close');
        },
        del: function() {
            this.el.dialog('close');

            var buttons = {'Delete': this.delete_confirm};
            _.extend(buttons, {'Cancel': this.close_confirm});
            $('#reason').val();
            this.cel.dialog({
                modal: true,
                title: 'Delete Confirmation',
                buttons: buttons,
                width: 450
            });
        },  
        delete_confirm: function() {
            this.cel.dialog('close');
            var buttons = {'Cancel': this.delete_cancel};
            var reason = $('#reason').val();
            
            if (reason.length > 1){
                if (this.model.get('repeated')){
                    this.dcl.dialog({
                        modal: true,
                        title: 'Delete Option',
                        buttons: buttons,
                        width: 400
                    });
                    $('.deleteme').click(this.deleteme);
                } else { // non-repeated booking
                    
                    var reason = $('#reason').val();
                    var id = this.model.get('id');            
                    var url = "./facility/delete-book.php?id="+id+"&reason="+reason+'&id_facility='+$('#_facility').val();
                    location.href = url;
                    
                }
            } else
                alert('Please provide a reason to delete this booked facility');
        },
        deleteme: function(e){
            var reason = $('#reason').val();
            var id = this.model.get('id');            
            var url = "./facility/delete-book.php?id="+id+"&opt="+e.target.id+"&reason="+reason+'&id_facility='+$('#_facility').val();
            this.dcl.dialog('destroy');
            //this.cel.dialog('close');
            this.el.dialog('close');
            location.href = url;
        },
        delete_cancel: function() {
            this.dcl.dialog('destroy');
            this.cel.dialog('close');
            //this.el.dialog('close');
        },
        close_confirm: function() {
            this.cel.dialog('close');
            //this.el.dialog('close');
        },
        destroy: function(data) {

            //this.model.destroy({success: this.close, data: data});
        }
    });
    
    var events = new Events();    
    var ev = new EventsView({el: $("#calendar"), collection: events});
	//.render();
    //events.fetch({data: "id_facility="+$('#_facility :selected').val()});
    //$("#calendar").fullCalendar('removeEvents');
	ev.render();
    ev.fetch_events();
	
    $('#_facility').change(function (e){
		ev.fetch_events();
		//$('#calendar').fullCalendar('refetchEvents');
		//$("#calendar").fullCalendar('removeEvents');
		//events.fetch({data: "id_facility="+$('#_facility :selected').val()});
		//$("#calendar").fullCalendar('rerenderEvents');
    });

	$('#my-next-button').click(function() {
		var v = $('#calendar').fullCalendar('getView');
		ev.el.fullCalendar('next');
		if (v.name == 'month'){
			ev.fetch_events();
			
		}
	});
	$('#my-today-button').click(function() {
		var v = $('#calendar').fullCalendar('getView');
		ev.el.fullCalendar('today');
		if (v.name == 'month'){
			ev.fetch_events();
		}
	});
	$('#my-prev-button').click(function() {
		var v = $('#calendar').fullCalendar('getView');
		ev.el.fullCalendar('prev');
		if (v.name == 'month'){
			ev.fetch_events();
		}
	});
	$('#my-week-button').click(function() {
		//var v = $('#calendar').fullCalendar('getView');
		ev.el.fullCalendar('changeView', 'agendaWeek');
		//if (v.name == 'month'){
		//	ev.fetch_events();
		//}
	});
	$('#my-month-button').click(function() {
		ev.el.fullCalendar('changeView', 'month');
	});
});

function DateToUnixtime(d)
{
    if (!d) d=new Date();
    
    return Math.round(d.getTime() / 1000);
}
