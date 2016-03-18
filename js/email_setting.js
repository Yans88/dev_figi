$.fn.selectRange = function(start, end) {
    return this.each(function() {
        if (this.setSelectionRange) {
            this.focus();
            this.setSelectionRange(start, end);
        } else if (this.createTextRange) {
            var range = this.createTextRange();
            range.collapse(true);
            range.moveEnd('character', end);
            range.moveStart('character', start);
            range.select();
        }
    });
};


function fill(id, thisValue, onclick) {
	if (thisValue.length>0 && onclick){
		var cols = thisValue.split('|');
		$('#'+id).val(cols[1] + ' (' + cols[0] + ')');
	}
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString){
	var frm = document.forms[0];
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
        var path = "user/suggest_email.php";
        var tab = $('#tab').val();
        if (tab == 'mobile')
            path = "user/suggest_mobile.php";
        
		$.post(path, {queryString: ""+inputString+"", inputId: ""+me.id+"", tab: ""+tab+""}, function(data){
		if(data.length >0) {
			$('#suggestions').fadeIn();
			$('#suggestionsList').html(data);
			var pos = $('#edit_email').offset();  
			var w = $('#edit_email').width();
			$('#suggestions').css('position', 'absolute');
			$('#suggestions').offset({top:pos.bottom, left:pos.left});
			$('#suggestions').width(w);
			}
		});
	}
}

function add_email(){
	var email = $('#edit_email').val();
	if (email == '') return;
	var emails = $('#emails').val();
	var cols = email.match(/([^ ]+) *\((.+)\)/);
	if  (emails.search(new RegExp(cols[1])) == -1){
		cols.shift();
		if (emails == '') emails = cols.join('|');
		else emails += ',' + cols.join('|');
		$('#emails').val(emails);
        $('#edit_email').val('');
	} else
        alert('Email already exists!');
    display_list(emails);
}

function load_notification_emails(dept, cat, mod){
    $('#emails').val('');
    $('#edit_email').val('');           
    $.post('get_notification_emails.php', {dept: ""+dept+"", cat: ""+cat+"", mod: ""+mod+""}, function(data){
        if(data.length >0)
            $('#emails').val(data);
        display_list(data);
    });
    
}

function load_notification_mobiles(dept, cat, mod){
    $('#emails').val('');
    $('#edit_email').val('');        
    $.post('get_notification_mobiles.php', {dept: ""+dept+"", cat: ""+cat+"", mod: ""+mod+""}, function(data){
        if(data.length >0) 
            $('#emails').val(data);
        display_list(data);
    });
}

function display_list(emails){
	var text = '';
	var name = '';
    var email = '';
    var recs = emails.split(',');
    if (emails != '' && recs.length > 0){
        for (var i=0; i < recs.length; i++){
            cols = recs[i].split('|');
            email = cols[0];
            name = cols[1];
            text += '<li class="an_email" id="' + email + '">' ;
            text += '<a onclick="del_email(\''+ email +'\')"><img class="icon" src="images/delete.png" alt="delete"></a> ';
            text += '<a class="email" onclick="edit_email(\''+ recs[i] +'\')">' +  email +  ' (' + name + ')</a></li>';
        }
    } else
        text = '--- empty list ---';
	$('#email_list').html(text);
}

function edit_email(email){
	$('#edit_email').val(email);	
}

function del_email(email){
    if (confirm("Are you sure delete the email?")){
        var emails = $('#emails').val();
        //var recs = email.match(/([^ ]+) *\((.+)\)/);
        var recs = emails.split(',');
        var newrecs = new Array();
        for (var i=0; i < recs.length; i++){
            //cols = recs[i].split('|');
            if (recs[i].search(new RegExp(email)) == -1){
                newrecs.push(recs[i]);
                //alert('ok');
            }
        }
        $('#emails').val(newrecs);
        display_list(newrecs.join(','));
	}
}

