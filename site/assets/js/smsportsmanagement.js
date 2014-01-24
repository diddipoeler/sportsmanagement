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


function hideclubplandate()
{
// Get the value from a dropdown select
var teamartsel = jQuery( "#teamartsel" ).val();
var teamprojectssel = jQuery( "#teamprojectssel" ).val();
var teamseasonssel = jQuery( "#teamseasonssel" ).val();

//alert(teamartsel);
//alert(teamprojectssel);
//alert(teamseasonssel);

if (teamartsel == '' && teamprojectssel == 0 && teamseasonssel == 0 ) 
{
jQuery('div.clubplandate').show();
}
else
{
jQuery('div.clubplandate').hide();
}

}

function switchMenu(obj) 
{
jQuery('div.rankingteam').hide();
//alert(obj);
jQuery("#page-" + obj ).show();


//  	var joomleague_el = document.getElementById(obj);
//  	if ( joomleague_el.style.display != "none" ) {
//  		joomleague_el.style.display = 'none';
//  	}
//  	else {
//  		joomleague_el.style.display = 'block';
//  	}
	
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

