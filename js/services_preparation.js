
$(function(){
    var Event = Backbone.Model.extend({
        //idAtttribute: "key"
    });

    var Events = Backbone.Collection.extend({
        model: Event,
        //url: './calendar/events.php' //'events'
		url: './calendar/services_preparation.php'
    }); 
 
    var EventsView = Backbone.View.extend({
        initialize: function(){
            _.bindAll(this); 
            
            this.collection.bind('reset', this.addAll);
            this.collection.bind('add', this.addOne);
            this.collection.bind('change', this.change);            
            this.collection.bind('destroy', this.destroy);

            this.eventView = new EventView();
        },
        render: function() {
            this.el.fullCalendar({
                header: {
                    //center: 'prev,next today',
                    left: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },								
                selectHelper: true,
				defaultView : 'agendaWeek',
                editable: is_editable,
                //selectable: is_selectable,
                ignoreTimezone: false,                
                //lazyFetching: false,
                select: this.select,
                eventClick: this.eventClick,
                eventDrop: this.eventDropOrResize,        
                eventResize: this.eventDropOrResize,
                viewDisplay: this.viewDisplay,				
            });
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
            
            this.eventView.collection = this.collection;
            this.eventView.model = new Event({start: startDate, end: endDate, allDay: allDay});
            this.eventView.render();            
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
        destroy: function(event) {
            this.el.fullCalendar('removeEvents', event.id);         
        },
        fetch_events: function () {
            var start = this.el.fullCalendar('getView'). visStart;
            var end = this.el.fullCalendar('getView'). visEnd;
            this.el.fullCalendar ("removeEvents");
            this.collection.reset();
            this.collection.fetch({data: "start="+DateToUnixtime(start)+"&end="+DateToUnixtime(end)});
            this.addAll();
        }
    });

    var EventView = Backbone.View.extend({
        el : $('#eventDialog'), 
        cel : $('#confirmDialog'),
        dcl : $('#deleteDialog'),
        initialize: function() {
            _.bindAll(this); 
        },
        render: function() {            
            var buttons, the_title ;
            if (typeof(authenticated)!='undefined') {
                if (this.model.isNew() || (this.model.get('owner') == current_user)){
                    buttons = {'Save': this.save};
                    if (!this.model.isNew())
                        _.extend(buttons, {'Delete': this.del});
						_.extend(buttons, {'Detail': this.detail});
						_.extend(buttons, {'Cancel': this.close});            
                    the_title = (this.model.isNew() ? 'New' : 'Edit') + ' Event';
                } else {
                    
                    the_title = 'Event Detail';
					buttons = {'Cancel': this.close};
					_.extend(buttons, {'Detail': this.detail});
					
					_.extend(buttons, {'Cancel': this.close});            
					
					
                }
            } else {
                the_title = 'View Eventsss';
                if (this.model.isNew()) return;
                buttons = {'Cancel': this.close};
            }
            
            this.el.dialog({
                modal: true,
                title: the_title,
                buttons: buttons,
                width: 400,
                open: this.open
            });

            return this;
        },        
        open: function() {
            this.$('#title').val(this.model.get('title'));
            this.$('#color').val(this.model.get('color'));
            var loc = this.model.get('location');
            if ((loc!=undefined) && (loc.length>0))
                this.$('#location').text('Location: '+loc);
            else 
                this.$('#location').text('');
            var start = $.fullCalendar.parseDate(this.model.get('start'));
            var end = $.fullCalendar.parseDate(this.model.get('end'));
            //alert(start + ' ' + end)
            var fmt_start = $.fullCalendar.formatDate(start, 'dddd, dd MMM');
            var fmt_end = $.fullCalendar.formatDate(end, 'dddd, dd MMM');
            this.$('#event-date').text(fmt_start + ' - ' + fmt_end);
            
        },        
        save: function() {
            this.model.set({'title': this.$('#title').val(), 'color': this.$('#color').val()});
            
            if (this.model.isNew()) {
                this.collection.create(this.model, {success: this.close});
            } else {
                this.model.save({}, {success: this.close});
            }
        },
        detail: function() {
            this.el.dialog('close');
            var id = this.model.get('id');
            var url = "./?mod=service&sub=service&act=view&id="+id;

            location.href = url; 
        },
        close: function() {
            this.el.dialog('close');
        },
        del: function() {
            
            var buttons = {'Delete': this.delete_confirm};
            _.extend(buttons, {'Cancel': this.close_confirm});
            this.cel.dialog({
                modal: true,
                title: 'Delete Confirmation',
                buttons: buttons,
                width: 450
            });
        },  
        delete_confirm: function() {
            var reason = $('#reason').val();
            var buttons = {'Cancel': this.delete_cancel};
            if (reason.length > 1){
                this.dcl.dialog({
                    modal: true,
                    title: 'Delete Option',
                    buttons: buttons,
                    width: 400
                });
                $('.deleteme').click(this.deleteme);
                
                this.cel.dialog('close');
                this.el.dialog('close');
            } else
                alert('Please provide reason of deletion of the event to proceed');
        },
        deleteme: function(e){
            var reason = $('#reason').val();
            var id = this.model.get('id');
            var url = "calendar/delete-event.php?id="+id+"&opt="+e.target.id+"&reason="+reason;
                this.dcl.dialog('close');
                this.cel.dialog('close');
                this.el.dialog('close');
                location.href = url;
        },
        delete_cancel: function() {
            this.dcl.dialog('close');
            this.cel.dialog('close');
            this.el.dialog('close');
        },
        close_confirm: function() {
            this.cel.dialog('close');
            this.el.dialog('close');
        },
        destroy: function(data) {

            //this.model.destroy({success: this.close, data: data});
        }
    });
    
    var events = new Events();    
    var ev = new EventsView({el: $("#calendar"), collection: events});
  	ev.render();
    ev.fetch_events();
	//events.fetch();
    //$("#calendar").fullCalendar('removeEvents');
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
});

function DateToUnixtime(d)
{
    if (!d) d=new Date();
    
    return Math.round(d.getTime() / 1000);
}
