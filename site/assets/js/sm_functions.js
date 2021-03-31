//var windowWidth = jQuery(window).width(); //retrieve current window width
//var windowHeight = jQuery(window).height(); //retrieve current window height
//var documentWidth = jQuery(document).width(); //retrieve current document width
//var documentHeight = jQuery(document).height(); //retrieve current document height
//var vScrollPosition = jQuery(document).scrollTop(); //retrieve the document scroll ToP position
//var hScrollPosition = jQuery(document).scrollLeft(); //retrieve the document scroll Left position

var windowObjectReference = null; // global variable
var PreviousUrl; /* global variable which will store the
                    url currently in the secondary window */


jQuery(document).ready(function(){
var windowWidth = jQuery(window).width(); //retrieve current window width
var windowHeight = jQuery(window).height(); //retrieve current window height	
console.log("document.URL : "+document.URL);
console.log("document.location.href : "+document.location.href);
console.log("document.location.origin : "+document.location.origin);
console.log("document.location.hostname : "+document.location.hostname);
console.log("document.location.host : "+document.location.host);
console.log("document.location.pathname : "+document.location.pathname);
console.log("title : "+jQuery("title").text());

console.log("current window width : "+windowWidth );
console.log("current window height : "+windowHeight );
console.log("jquery version : "+jQuery().jquery);
//console.log("bootstrap version : "+jQuery.fn.tooltip.Constructor.VERSION);

//    alert("Embedded block of JS here");
});

function setseasonname()
{
var seasonname = jQuery( "#jform_season_id option:selected" ).text();
var projectname = jQuery("#jform_name").val();
console.log("seasonname : " + seasonname);
console.log("projectname : " + projectname);	
	
}


function openRequestedSinglePopup(strUrl,Width,Height) {

Width = (screen.width/2);
Height = (screen.height/2);	
var left = (screen.width/2)-(Width/2);
var top = (screen.height/2)-(Height/2);

  if(windowObjectReference == null || windowObjectReference.closed) {
    windowObjectReference = window.open(strUrl, "SingleSecondaryWindowName",
         "resizable,scrollbars,status,width=" + Width  + ",height=" + Height + ",top="+top+", left="+left);
  } else if(PreviousUrl != strUrl) {
    windowObjectReference = window.open(strUrl, "SingleSecondaryWindowName",
      "resizable=yes,scrollbars=yes,status=yes,width=" + Width  + ",height=" + Height + ",top="+top+", left="+left);
    /* if the resource to load is different,
       then we load it in the already opened secondary window and then
       we bring such window back on top/in front of its parent window. */
    windowObjectReference.focus();
  } else {
    windowObjectReference.focus();
  };

  PreviousUrl = strUrl;
  /* explanation: we store the current url in order to compare url
     in the event of another call of this function. */
}

/*
function toggle_altdecision() {
	if (document.getElementById('jform_alt_decision').value == 0) {
		document.getElementById('alt_decision_enter').style.display = 'none';
		document.getElementById('team1_result_decision').disabled = true;
		document.getElementById('team2_result_decision').disabled = true;
		document.getElementById('decision_info').disabled = true;
	} else {
		document.getElementById('alt_decision_enter').style.display = 'block';
		document.getElementById('team1_result_decision').disabled = false;
		document.getElementById('team2_result_decision').disabled = false;
		document.getElementById('decision_info').disabled = false;
	}
}
*/


function get_documentWidth()
{
var documentWidth = jQuery(document).width();
return documentWidth ;
}

function get_documentHeight()
{
var documentHeight = jQuery(document).height();
return documentHeight ;
}


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


//this will move selected items from source list to destination list   
function move_list_items(sourceid, destinationid)
{
console.log("move_list_items sourceid : "+sourceid);
console.log("move_list_items destinationid : "+destinationid);

//alert(sourceid);
//alert(destinationid);
jQuery("#"+sourceid+"  option:selected").appendTo("#"+destinationid);
}

//this will move all selected items from source list to destination list
function move_list_items_all(sourceid, destinationid)
{
jQuery("#"+sourceid+" option").appendTo("#"+destinationid);
}

function move_up(sourceid) 
{
    var selected = jQuery("#"+sourceid).find(":selected");
    var before = selected.prev();
    if (before.length > 0)
        selected.detach().insertBefore(before);
}

function move_down(sourceid) 
{
    var selected = jQuery("#"+sourceid).find(":selected");
    var next = selected.next();
    if (next.length > 0)
        selected.detach().insertAfter(next);
}

function changePlayground()
{

var selected = jQuery( "#jform_picture" ).val();
console.log('background image = ' + selected);
jQuery('#roster').css("background-image", "url(../images/com_sportsmanagement/database/rosterground/"+selected+")");
var x = new Image();
x.src = "/images/com_sportsmanagement/database/rosterground/"+selected+"";
width = x.width;
height = x.height;
console.log('width image = ' + width);
console.log('height image = ' + height);
console.log('src image = ' + x.src);

var bBreite = jQuery("#roster").width();
var bHoehe = jQuery("#roster").height();  
console.log('bBreite image = ' + bBreite);  
console.log('bHoehe image = ' + bHoehe);  

jQuery('#roster').css({'width' : width + 'px' , 'height' : height + 'px'});
  
}


var modjlnav = {
updateProjects : function(response)
	{
alert(response);
	}

};

function EditshowPersonsPositions() 
{
alert('hier bin ich');
var selected = jQuery( "#jform_sports_type_id" ).val();
alert(selected);
var url = 'index.php?option=com_sportsmanagement&task=ajax.personpositionoptions&tmpl=component&format=json&sports_type_id='+selected;
	    var myXhr = new Request(
	                    {
	                    	url: url,
	                    method: 'post',
	                    onSuccess: modjlnav.updateProjects.bind(this)
	                    }
	        );
	        
}






/*
function EditshowPersons() 
{
//alert('hier bin ich');
var selected = jQuery( "#jform_request_person_art" ).val();
//alert(selected);

if (selected == 1) 
{
		document.getElementById('jform_request_person_id1').style.display = 'none';
		document.getElementById('jform_request_person_id2').style.display = 'none';
		document.getElementById('jform_request_person_id1-lbl').style.display = 'none';
		document.getElementById('jform_request_person_id2-lbl').style.display = 'none';
} 
if (selected == 2) 
{
		document.getElementById('jform_request_person_id1').style.display = 'block';
		document.getElementById('jform_request_person_id2').style.display = 'block';
		document.getElementById('jform_request_person_id1-lbl').style.display = 'block';
		document.getElementById('jform_request_person_id2-lbl').style.display = 'block';
}
	
}
*/
function StartEditshowPersons(selected) 
{
//alert('hier bin ich');
var selected = jQuery( "#jform_request_person_art" ).val();
//alert(selected);

if (selected == 1) 
{
		document.getElementById('jform_request_person_id1').style.display = 'none';
		document.getElementById('jform_request_person_id2').style.display = 'none';
		document.getElementById('jform_request_person_id1-lbl').style.display = 'none';
		document.getElementById('jform_request_person_id2-lbl').style.display = 'none';
} 
if (selected == 2) 
{
		document.getElementById('jform_request_person_id1').style.display = 'block';
		document.getElementById('jform_request_person_id2').style.display = 'block';
		document.getElementById('jform_request_person_id1-lbl').style.display = 'block';
		document.getElementById('jform_request_person_id2-lbl').style.display = 'block';
}
	
}

function registerproject(homepage,project,homepagename,isadmin)
	{
var url='http://www.fussballineuropa.de/jsmprojectexport.php';		
var data = 'homepage='+homepage+'&project_id='+project+'&homepagename='+homepagename+'&isadmin='+isadmin;
var url2='http://www.fussballineuropa.de/jsmprojectexport.php?'+'homepage='+homepage+'&project_id='+project+'&homepagename='+homepagename+'&isadmin='+isadmin;
var request = new Request({
                        url: url2,
                        method:'post',
                        data: data
                        }).send();
                        		
		}
