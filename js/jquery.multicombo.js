
/** example: $(elementcombo).comboMulti(elementToReplace); **/

/*
$.fn.exists = function () {
    return this.length !== 0;
}
*/

jQuery.fn.comboMulti=function(toElm){
	
	if($(toElm).length) var toElement = $(toElm); //if selector found
	else if(toElm==null) var toElement = this;    //if selector null or no filled use parent selector $.fn
	else var toElement = false;					  //if selector not found

	var name=$(this).attr('name');
	var opts = $(this)[0].options;

	var arraycombo = $.map(opts, function( elem ) {
		if(elem.value >0){ return  '<label><li>' +elem.text+ '<input style=\'position:relative;margin-left:22px;display:inline;float:right;\' id=\'' +name+ '\' name=\'' +name+ '[]\' type=\'checkbox\' text=\'' +elem.text+ '\' value=\'' +elem.value+ '\'></li></label>\n'};
	});

	defaultText = $(this + 'option:selected').text();
	defaultValue = $(this + 'option:selected').val();
	toElement.replaceWith('<span id=\'combomulti\'><label>'+defaultText+'</label><a class=\'arrowdown\'></a></span><ul id=\'openbox\'></ul>');
	
	$("#openbox").css({'display':'none'}).html(arraycombo.join(""));
	
	var position = $("#combomulti").position();
	
	$("#combomulti").click(function(){
		$("#openbox").css({'position':'absolute','left':position.left, 'top':position.top +9}).toggle();
		if($("#openbox").height() > 240) $("#openbox").css({'height':'240px','padding-right':'5px','overflow-y':'scroll'});
	});


	/** when event onchange checkbox **/
	$("[id='"+name+"']:checkbox").change(function(){
		var countChecked = $(this+":checked").length;
		//var countUnChecked = $('[id="'+name+'"]:checkbox:not(":checked")').length;
		var countUnChecked = $('[id="'+name+'"]:checkbox').not(':checked').length;
		var combomulti = $("span#combomulti label");

		if(countChecked == 1) combomulti.text($(this+":checked").attr('text'));
		else if(countChecked >1) combomulti.text(countChecked +' checks');
		else combomulti.text(defaultText);
		if(countUnChecked==0){combomulti.text('Checked All');}
	});

}
