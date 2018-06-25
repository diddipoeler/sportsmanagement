var windowWidth = jQuery(window).width(); //retrieve current window width
var windowHeight = jQuery(window).height(); //retrieve current window height
var documentWidth = jQuery(document).width(); //retrieve current document width
var documentHeight = jQuery(document).height(); //retrieve current document height
var vScrollPosition = jQuery(document).scrollTop(); //retrieve the document scroll ToP position
var hScrollPosition = jQuery(document).scrollLeft(); //retrieve the document scroll Left position


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
//alert(selected);
jQuery('#roster').css("background-image", "url(../images/com_sportsmanagement/database/rosterground/"+selected+")");
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