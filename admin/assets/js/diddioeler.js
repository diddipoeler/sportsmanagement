// ajax save substitution
jQuery(document).ready(function($){
	
// neuen kommentar speichern 	
$(".button-save-comment").on('click', function(event){
    save_new_comment();
});	
// neuen wechsel speichern    
$(".button-save-subst").on('click', function(event){
    save_new_subst();
});	
// neues ereignis speichern  
$(".button-save-event").on('click', function(event){
    save_new_event();
});	
// hier wird die funktion für das löschen der
// wechsel hinzugefügt
//$(".button-delete-subst").on('click', function(event){
//    button_delete_subst();
//});	
// hier wird die funktion für das löschen der
// kommentare hinzugefügt	
//$(".button-delete-commentary").on('click', function(event){
//    button_delete_commentary();
//});		
// hier wird die funktion für das löschen der
// ereignis hinzugefügt	
//$(".button-delete-event").on('click', function(event){
//    button_delete_event();
//});			

	

});

// hier sind die funktionen

function reqsent() 
{
jQuery("#ajaxresponse").addClass('ajax-loading');
jQuery("#ajaxresponse").text('anfrage gesendet');
}

function reqfailed() 
{
jQuery("#ajaxresponse").removeClass('ajax-loading');
jQuery("#ajaxresponse").text(response);
}

function save_new_event()
{
jQuery("#ajaxresponse").html(baseajaxurl);
jQuery("#ajaxresponse").addClass('ajax-loading');
//var rowid = this.id.substr(5);
var url = baseajaxurl + '&task=matches.saveevent&tmpl=component&';
var player = jQuery("#teamplayer_id").val();
var event = jQuery("#event_type_id").val();
var team = jQuery("#team_id").val();
var time = jQuery("#event_time").val();
var notice = encodeURIComponent(jQuery("#notice").val());
var event_sum = jQuery("#event_sum").val();
var querystring = 'teamplayer_id=' + player +
	'&projectteam_id=' + team + 
	'&event_type_id=' + event + 
	'&event_time=' + time + 
	'&match_id=' + matchid +
	'&useeventtime=' + useeventtime +
    '&projecttime=' + projecttime + 
	'&event_sum=' + event_sum +
	'&notice=' + notice +
    '&doubleevents=' + doubleevents;

jQuery.ajax({
  type: 'POST', // type of request either Get or Post
  url: url + querystring, // Url of the page where to post data and receive response 
  dataType:"json",
  success: eventsaved, //function to be called on successful reply from server
  error: function (xhr, ajaxOptions, thrownError) {
        alert(xhr.status);
        alert(thrownError);
		console.log("useeventtime : " + useeventtime);
		console.log("projecttime : " + projecttime);
		console.log("doubleevents : " + doubleevents);
		
		console.log("url : " + url);
		console.log("querystring : " + querystring);
      }
});
        
}

function eventsaved(response) 
{
jQuery("#ajaxresponse").removeClass('ajax-loading');
// first line contains the status, second line contains the new row.
var resp = response.split('&');

if (resp[0] != '0') 
{
//var team = jQuery("#team_id").val();
var player = jQuery("#teamplayer_id option:selected").text();
var team = jQuery("#team_id option:selected").text();
	  
jQuery("#table-event").last().append('<tr id="rowevent-' 
    + resp[0] + '"><td>' 
	+ team + '</td><td>' + player + '</td><td>' 
    + jQuery("#event_type_id option:selected").text() + '</td><td>' 
	+ jQuery("#event_sum").val() + '</td><td>' 
    + jQuery("#event_time").val() + '</td><td>' 
    + jQuery("#notice").val() + '</td><td><input id="deleteevent-' + resp[0] 
    + '" type="button" onClick="deleteevent(' + resp[0] + ')" class="inputbox button-delete-event" value="' 
    + str_delete + '"</td></tr>');

console.log("team : " + team);
console.log("player : " + player);
console.log("event_type_id : " + jQuery("#event_type_id option:selected").text());
console.log("event_sum : " + jQuery("#event_sum").val());
console.log("event_time : " + jQuery("#event_time").val());
console.log("notice : " + jQuery("#notice").val());
	
jQuery("#ajaxresponse").addClass("ajaxsuccess");
jQuery("#ajaxresponse").text(resp[1]);
jQuery("#notice").val('');
jQuery("#event_time").val('');
jQuery("#event_sum").val('');
}
else 
{
jQuery("#ajaxresponse").addClass("ajaxerror");
jQuery("#ajaxresponse").text(resp[1]);
} 	
	
}

function deleteevent(eventid)
{
jQuery("#ajaxresponse").html(baseajaxurl);
jQuery("#ajaxresponse").addClass('ajax-loading');	
//var eventid = this.id.substr(12);  
var url = baseajaxurl + '&task=matches.removeEvent&tmpl=component';
var querystring = '&event_id=' + eventid;

jQuery.ajax({
 type: 'POST', // type of request either Get or Post
 url: url + querystring, // Url of the page where to post data and receive response 
 dataType:"json",
 success: eventdeleted,   //function to be called on successful reply from server
 error: function (xhr, ajaxOptions, thrownError) 
 {
       jQuery("#ajaxresponse").html(xhr);
       alert(xhr.status);
       alert(thrownError);
     }
});   	
}	

function eventdeleted(response) 
{
jQuery("#ajaxresponse").removeClass('ajax-loading');
var resp = response.split("&");
var eventid = resp[2]; 

if (resp[0] != '0') 
{
jQuery("#rowevent-" + eventid).remove();
jQuery("#ajaxresponse").addClass("ajaxsuccess");
jQuery("#ajaxresponse").text(resp[1]);
}
else 
{
jQuery("#ajaxresponse").addClass("ajaxerror");
jQuery("#ajaxresponse").text(resp[1]);
}
	
}

function save_new_comment()
{
jQuery("#ajaxresponse").html(baseajaxurl);
jQuery("#ajaxresponse").addClass('ajax-loading');
var url = baseajaxurl + '&task=matches.savecomment&tmpl=component';
//var rowid = this.id.substr(5);
var ctype = jQuery("#ctype").val();
var token = jQuery("#token").val();
var comnt = encodeURIComponent(jQuery("#notes").val())
var time = jQuery("#c_event_time").val();
var querystring = '&type=' + ctype + '&event_time=' + time + '&matchid='
	+ matchid + '&notes='
	+ comnt + '&projecttime=' + projecttime;

jQuery.ajax({
  type: 'POST', // type of request either Get or Post
  url: url + querystring, // Url of the page where to post data and receive response 
  dataType:"json",
  success: commentsaved, //function to be called on successful reply from server
  error: function (xhr, ajaxOptions, thrownError) {
        alert(xhr.status);
        alert(thrownError);
      }
});
}

function commentsaved(response) 
{
jQuery("#ajaxresponse").removeClass('ajax-loading');
// first line contains the status, second line contains the new row.
var resp = response.split('&');
	
if (resp[0] != '0') 
{
jQuery("#table-commentary").last().append('<tr id="rowcomment-' 
    + resp[0] + '"><td>' 
    + jQuery("#ctype").val() + '</td><td>' 
    + jQuery("#c_event_time").val() + '</td><td>' 
    + jQuery("#notes").val() + '</td><td><input	id="deletecomment-' + resp[0] 
    + '" type="button" class="inputbox button-delete-commentary" value="' 
    + str_delete + '"</td></tr>');
		
    jQuery("#ajaxresponse").addClass("ajaxsuccess");
    jQuery("#ajaxresponse").text(resp[1]);
      jQuery("#notes").val('');
      jQuery("#c_event_time").val('');
		
}
else 
{
jQuery("#ajaxresponse").addClass("ajaxerror");
jQuery("#ajaxresponse").text(resp[1]);
}
}

function deletecommentary(commentaryid)
{
jQuery("#ajaxresponse").html(baseajaxurl);
jQuery("#ajaxresponse").addClass('ajax-loading');
//var eventid = this.id.substr(14);  
var token = jQuery("#token").val();       
var url = baseajaxurl + '&task=matches.removeCommentary&tmpl=component';
var querystring = '&event_id=' + commentaryid;

jQuery.ajax({
 type: 'POST', // type of request either Get or Post
 url: url + querystring, // Url of the page where to post data and receive response 
 dataType:"json",
 success: commentarydeleted,   //function to be called on successful reply from server
 error: function (xhr, ajaxOptions, thrownError) 
 {
       jQuery("#ajaxresponse").html(xhr);
       alert(xhr.status);
       alert(thrownError);
     }
});         	
}
/*
function button_delete_commentary()
{
jQuery("#ajaxresponse").html(baseajaxurl);
jQuery("#ajaxresponse").addClass('ajax-loading');
var eventid = this.id.substr(14);  
var token = jQuery("#token").val();       
var url = baseajaxurl + '&task=matches.removeCommentary&tmpl=component';
var querystring = '&event_id=' + eventid;

jQuery.ajax({
 type: 'POST', // type of request either Get or Post
 url: url + querystring, // Url of the page where to post data and receive response 
 dataType:"json",
 success: commentarydeleted,   //function to be called on successful reply from server
 error: function (xhr, ajaxOptions, thrownError) 
 {
       jQuery("#ajaxresponse").html(xhr);
       alert(xhr.status);
       alert(thrownError);
     }
});           
    
    
}
*/
function commentarydeleted(response) 
{
jQuery("#ajaxresponse").removeClass('ajax-loading');
var resp = response.split("&");
var eventid = resp[2]; 

if (resp[0] != '0') 
{
jQuery("#rowcomment-" + eventid).remove();
jQuery("#ajaxresponse").addClass("ajaxsuccess");
jQuery("#ajaxresponse").text(resp[1]);
}
else 
{
jQuery("#ajaxresponse").addClass("ajaxerror");
jQuery("#ajaxresponse").text(resp[1]);
}
}

function save_new_subst()
{
jQuery("#ajaxresponse").html(baseajaxurl);
jQuery("#ajaxresponse").addClass('ajax-loading');
          
var playerin = jQuery("#in").val();
var playerout = jQuery("#out").val();
var position = jQuery("#project_position_id").val();
var time = jQuery("#in_out_time").val();
var querystring = '&in=' + playerin + '&out=' + playerout
	+ '&project_position_id=' + position + '&in_out_time='
	+ time + '&teamid=' + teamid + '&matchid=' + matchid
	+  '&projecttime=' + projecttime;
	var url = baseajaxurl + '&task=matches.savesubst&tmpl=component';
        
jQuery.ajax({
  type: 'POST', // type of request either Get or Post
  url: url + querystring, // Url of the page where to post data and receive response 
  //data: data, // data to be post
  dataType:"json",
  success: substsaved //function to be called on successful reply from server
});    
}

function substsaved(response) 
{
jQuery("#ajaxresponse").removeClass('ajax-loading');
// first line contains the status, second line contains the new row.
var resp = response.split('&');
	
if (resp[0] != '0') 
{
jQuery("#table-substitutions").last().append('<tr id="sub-' 
    + resp[0] + '"><td>'
    + jQuery("#out option:selected").text() + '</td><td>'  
    + jQuery("#in option:selected").text() + '</td><td>' 
    + jQuery("#project_position_id option:selected").text() + '</td><td>' 
    + jQuery("#in_out_time").val() + '</td><td><input	id="deletesubst-' + resp[0] 
    + '" type="button" class="inputbox button-delete-subst" value="  onClick="deletesubst(' + resp[0] + ')"  ' 
    + str_delete + '"</td></tr>');
		
jQuery("#ajaxresponse").addClass("ajaxsuccess");
jQuery("#ajaxresponse").text(resp[1]);
jQuery("#in_out_time").val('');
jQuery('#in option:selected').removeAttr('selected')
jQuery('#out option:selected').removeAttr('selected')	
jQuery('#project_position_id option:selected').removeAttr('selected')
//$$(".button-delete-subst").addEvent('click', button_delete_subst);				
}
else 
{
jQuery("#ajaxresponse").addClass("ajaxerror");
jQuery("#ajaxresponse").text(resp[1]);
}
}

function deletesubst(substid)
{
jQuery("#ajaxresponse").html(baseajaxurl);
jQuery("#ajaxresponse").addClass('ajax-loading');

//var substid = this.id.substr(12); 
var token = jQuery("#token").val();       
var url = baseajaxurl + '&task=matches.removeSubst&tmpl=component';
var querystring = '&substid=' + substid;

jQuery.ajax({
 type: 'POST', // type of request either Get or Post
 url: url + querystring, // Url of the page where to post data and receive response 
 data: {
            'token': '1' // <-- THIS IS IMPORTANT
            
        }, // data to be post
 //data: jQuery("#component-form").serialize(),
 dataType:"json",
 success: substdeleted,   //function to be called on successful reply from server
 error: function (xhr, ajaxOptions, thrownError) 
 {
       jQuery("#ajaxresponse").html(xhr);
       //alert(xhr);
       alert(xhr.status);
       alert(thrownError);
     }
});  	
}
/*
function button_delete_subst()
{
jQuery("#ajaxresponse").html(baseajaxurl);
jQuery("#ajaxresponse").addClass('ajax-loading');

var substid = this.id.substr(12); 
var token = jQuery("#token").val();       
var url = baseajaxurl + '&task=matches.removeSubst&tmpl=component';
var querystring = '&substid=' + substid;

jQuery.ajax({
 type: 'POST', // type of request either Get or Post
 url: url + querystring, // Url of the page where to post data and receive response 
 data: {
            'token': '1' // <-- THIS IS IMPORTANT
            
        }, // data to be post
 //data: jQuery("#component-form").serialize(),
 dataType:"json",
 success: substdeleted,   //function to be called on successful reply from server
 error: function (xhr, ajaxOptions, thrownError) 
 {
       jQuery("#ajaxresponse").html(xhr);
       //alert(xhr);
       alert(xhr.status);
       alert(thrownError);
     }
});      
}
*/
function substdeleted(response) 
{
jQuery("#ajaxresponse").removeClass('ajax-loading');
var resp = response.split("&");
var substid = resp[2]; 
  
if (resp[0] != '0') 
{
jQuery("#sub-" + substid).remove();
jQuery("#ajaxresponse").addClass("ajaxsuccess");
jQuery("#ajaxresponse").text(resp[1]);
}
else 
{
jQuery("#ajaxresponse").addClass("ajaxerror");
jQuery("#ajaxresponse").text(resp[1]);
}
	
}














/**
 * updatePlayerSelect
 */
function updatePlayerSelect() {
	if(jQuery('#cell-player'))
		jQuery('#cell-player').empty().append(
			getPlayerSelect(jQuery('#team_id')[0].selectedIndex));
}


/**
 * return players select for specified team
 *
 * @param 0 for home, 1 for away
 * @return dom element
 */
function getPlayerSelect(index) {
	// homeroster and awayroster must be defined globally (in the view calling the script)
	var roster = rosters[index];
	// create select
	var select = jQuery("<select>").attr({id: 'teamplayer_id',class:'span2'});
	// add options
	for (var i = 0, n = roster.length; i < n; i++) {
		select.append(jQuery("<option>").attr({value : roster[i].value}).text(roster[i].text));
	}
	return select;
}

  
