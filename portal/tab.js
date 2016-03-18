function changetab(me, name){
  var id = 'tab_' + name;
  var tab = document.getElementById(id);
  var divs = document.getElementById('tabset').getElementsByTagName('div');
  for (i=0; i<divs.length; i++){
    if (divs[i].className == 'tabset_content')
      divs[i].style.display = 'none';
  }
 
  tab.style.display = 'block';
  var ass = document.getElementById('buttonbox1').getElementsByTagName('a');
  for (i=0; i<ass.length; i++){
      ass[i].className = '';
  }

  me.className = 'active';
}

function show_message(id){
	var div = document.getElementById(id)
	var sw = $(window).width();
	var sh = $(window).height();
	if (div) {
		div.style.display = '';
		div.style.left = ((sw - div.offsetWidth) / 2) + 'px';
		div.style.top  = ((sh - div.offsetHeight) / 2) +30+ 'px';
	}
}

function hide_message(id){
	var div = document.getElementById(id)	
	if (div) {
		div.style.display = 'none';
	}
}
