function msgbox_show(msg){
	if (msg == '') return;
    $('#message').html(msg);
	var ww = $(window).width();
	var wh = $(window).height();
	var mw = $('#msgbox').width();
	var mh = $('#msgbox').height();
    $('#msgbox').css('left', (ww-mw) /2);
    $('#msgbox').css('top', (wh-mh) /2);
    $('#msgbox').show();
    $('#close').focus();
}

function msgbox_hide(){
    $('#msgbox').hide();
}

function zerofill(s, l, p){
	var t = '';
	for(var i=0;i<(l-s.length); i++)
		t += '0';
	return t+s;
}

$.fn.quickChange = function(handler) {
    return this.each(function() {
        var self = this;
        self.qcindex = self.selectedIndex;
        var interval;
        function handleChange() {
            if (self.selectedIndex != self.qcindex) {
                self.qcindex = self.selectedIndex;
                handler.apply(self);
            }
        }
        $(self).focus(function() {
            interval = setInterval(handleChange, 100);
        }).blur(function() { window.clearInterval(interval); })
        .change(handleChange); //also wire the change event in case the interval technique isn't supported (chrome on android)
    });
};