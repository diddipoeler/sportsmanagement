
//add 1.5 compatibility layer
window.addEvent('domready', function() {
	if($('adminForm')) {
		$('adminForm').setProperty('name', 'adminForm');
	}
});

function joomleague_changedoc(docid){
  if (docid != "" && docid.options[docid.options.selectedIndex].value!="") {
    window.location.href = docid.options[docid.options.selectedIndex].value;
  }
}

/**
 * toggle object visibility
 * @param obj the object to show/hide
 */       
function visibleMenu(obj) {
	var joomleague_el = document.getElementById(obj);
	if ( joomleague_el.style.visibility != "hidden" ) {
		joomleague_el.style.visibility = 'hidden';
	}
	else {
		joomleague_el.style.visibility = 'visible';
	}
}

function switchMenu(obj) {
	var joomleague_el = document.getElementById(obj);
	if ( joomleague_el.style.display != "none" ) {
		joomleague_el.style.display = 'none';
	}
	else {
		joomleague_el.style.display = 'block';
	}
}

/**
 * hide objects
 * @param array objs the objects to hide
 */
function collapseAll(objs) {
  var i;
  for (i=0;i<objs.length;i++ ) {
    objs[i].style.display = 'none';
  }
}

