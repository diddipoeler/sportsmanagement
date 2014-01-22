var windowWidth = $(window).width(); //retrieve current window width
var windowHeight = $(window).height(); //retrieve current window height
var documentWidth = $(document).width(); //retrieve current document width
var documentHeight = $(document).height(); //retrieve current document height
var vScrollPosition = $(document).scrollTop(); //retrieve the document scroll ToP position
var hScrollPosition = $(document).scrollLeft(); //retrieve the document scroll Left position



//this will move selected items from source list to destination list   
function move_list_items(sourceid, destinationid)
{
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

function EditshowPersons() 
{
//alert('hier bin ich');
var selected = jQuery( "#jform_person_art" ).val();
//alert(selected);

if (selected == 1) 
{
		document.getElementById('jform_person_id1').style.display = 'none';
		document.getElementById('jform_person_id2').style.display = 'none';
		document.getElementById('jform_person_id1-lbl').style.display = 'none';
		document.getElementById('jform_person_id2-lbl').style.display = 'none';
} 
if (selected == 2) 
{
		document.getElementById('jform_person_id1').style.display = 'block';
		document.getElementById('jform_person_id2').style.display = 'block';
		document.getElementById('jform_person_id1-lbl').style.display = 'block';
		document.getElementById('jform_person_id2-lbl').style.display = 'block';
}
	
}

function StartEditshowPersons(selected) 
{
//alert('hier bin ich');
//var selected = jQuery( "#jform_person_art" ).val();
//alert(selected);

if (selected == 1) 
{
		document.getElementById('jform_person_id1').style.display = 'none';
		document.getElementById('jform_person_id2').style.display = 'none';
		document.getElementById('jform_person_id1-lbl').style.display = 'none';
		document.getElementById('jform_person_id2-lbl').style.display = 'none';
} 
if (selected == 2) 
{
		document.getElementById('jform_person_id1').style.display = 'block';
		document.getElementById('jform_person_id2').style.display = 'block';
		document.getElementById('jform_person_id1-lbl').style.display = 'block';
		document.getElementById('jform_person_id2-lbl').style.display = 'block';
}
	
}

function registerhome(homepage,notes,homepagename,isadmin)
	{
var url='http://sportsmanagement.fussballineuropa.de/jsmpaket.php';		
var data = 'homepage='+homepage+'&notes='+notes+'&homepagename='+homepagename+'&isadmin='+isadmin;
var url2='http://sportsmanagement.fussballineuropa.de/jsmpaket.php?'+'homepage='+homepage+'&notes='+notes+'&homepagename='+homepagename+'&isadmin='+isadmin;
var request = new Request({
                        url: url2,
                        method:'post',
                        data: data
                        }).send();
                        		
		}
