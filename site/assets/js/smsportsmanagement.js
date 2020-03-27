//var windowWidth = jQuery(window).width(); //retrieve current window width
//var windowHeight = jQuery(window).height(); //retrieve current window height
//var documentWidth = jQuery(document).width(); //retrieve current document width
//var documentHeight = jQuery(document).height(); //retrieve current document height
//var vScrollPosition = jQuery(document).scrollTop(); //retrieve the document scroll ToP position
//var hScrollPosition = jQuery(document).scrollLeft(); //retrieve the document scroll Left position


//add 1.5 compatibility layer

//window.addEvent('domready', function() {
//	if($('adminForm')) {
//		$('adminForm').setProperty('name', 'adminForm');
//	}
//});


function get_windowPopUpWidth()
{
var documentWidth = jQuery(window).width();
return documentWidth - 100;
}

function get_windowPopUpHeight()
{
var documentHeight = jQuery(window).height();
return documentHeight - 100 ;
}


function sportsmanagement_changedoc(docid){
  if (docid != "" && docid.options[docid.options.selectedIndex].value!="") {
    window.location.href = docid.options[docid.options.selectedIndex].value;
  }
}

/**
 * toggle object visibility
 * @param obj the object to show/hide
 */       
function visibleMenu(obj) {
	var sportsmanagement_el = document.getElementById(obj);
	if ( sportsmanagement_el.style.visibility != "hidden" ) {
		sportsmanagement_el.style.visibility = 'hidden';
	}
	else {
		sportsmanagement_el.style.visibility = 'visible';
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
//jQuery('#' + obj).toggle();
	
jQuery('.jsmeventsshowhide').each(function(index) {
          if (jQuery(this).attr("id") == obj) {
               jQuery(this).toggle();
          }
          else {
               jQuery(this).hide();
          }
     });
	
//	var sportsmanagement_events = document.getElementById(obj);
//  	if ( sportsmanagement_events.style.display != "none" ) {
//  		sportsmanagement_events.style.display = 'none';
//  	}
//  	else {
//  		sportsmanagement_events.style.display = 'block';
//  	}
	
}

function switchMenuPart(obj) 
{
jQuery('#' + obj).toggle();
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

