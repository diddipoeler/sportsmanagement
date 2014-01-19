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


