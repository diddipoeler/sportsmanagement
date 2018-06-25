jQuery(document).ready(function()  {

// neuen wechsel speichern     
//jQuery("#save-new-subst").addEvent('click', save_new_subst);
//document.getElementById('save-new-subst').onclick = save_new_subst();
//jQuery("#ajaxresponse").html('test');
//alert('hallo');
});

// hier sind die funktionen
function updatePlayerSelect() {
if(jQuery('#cell-player'))
jQuery('#cell-player').empty().append(
getPlayerSelect(jQuery('#team_id')[0].selectedIndex));
}

function getPlayerSelect(index) {
var roster = jQuery("#team_id").val();

jQuery(".hide" + roster ).css("display", "none");
jQuery(".show" + roster ).css("display", "block");
	
// homeroster and awayroster must be defined globally (in the view calling
// the script)
//var roster = rosters[index];
// build select
//    	var select = jQuery("<select>").attr({id: 'teamplayer_id',class:'span3'});

//    for (var i = 0, n = roster.length; i < n; i++) {
//		select.append(jQuery("<option>").attr({value : roster[i].value}).text(roster[i].text));
//	}

//return select;
}

function save_new_event(matchid,projecttime,baseajaxurl)
{
jQuery("#ajaxresponse").html(baseajaxurl);
          jQuery("#ajaxresponse").addClass('ajax-loading');
          
					var url = baseajaxurl + '&task=matches.saveevent&tmpl=component&';
					//var player = jQuery("#teamplayer_id").val();
					var event = jQuery("#event_type_id").val();
					var team = jQuery("#team_id").val();
	var player = jQuery("#" + team ).val();
					var time = jQuery("#event_time").val();
          var notice = encodeURIComponent(jQuery("#notice").val());
          var event_sum = jQuery("#event_sum").val();
					var querystring = 'teamplayer_id=' + player +
					'&projectteam_id=' + team + 
					'&event_type_id=' + event + 
					'&event_time=' + time + 
					'&match_id=' + matchid +
          '&projecttime=' + projecttime + 
					'&event_sum=' + event_sum +
					'&notice=' + notice;
         
//jQuery("#ajaxresponse").html(url + querystring);
	
jQuery.ajax({
  type: 'POST', // type of request either Get or Post
  url: url + querystring, // Url of the page where to post data and receive response 
  dataType:"json",
  success: eventsaved, //function to be called on successful reply from server
  error: function (xhr, ajaxOptions, thrownError) {
        alert(xhr.status);
        alert(thrownError);
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
  var team = jQuery("#team_id").val();
var player = jQuery("#" + team + ' option:selected' ).text();
	  
    jQuery("#table-commentary").last().append('<tr id="rowevent-' 
    + resp[0] + '"><td>' 
    + jQuery("#event_type_id option:selected").text() + ' ' + player + '</td><td>' 
    + jQuery("#event_time").val() + '</td><td>' 
    + jQuery("#notes").val() + '</td><td><input	id="deleteevent-' + resp[0] 
    + '" type="button" class="inputbox button-delete-event" value="' 
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
// hier wird die funktion für das löschen der
// kommentare hinzugefügt
$$(".button-delete-event").addEvent('click', button_delete_event);	   
	}	
	
}

function save_new_comment(matchid,projecttime,baseajaxurl)
{
jQuery("#ajaxresponse").html(baseajaxurl);
          jQuery("#ajaxresponse").addClass('ajax-loading');
          var url = baseajaxurl + '&task=matches.savecomment&tmpl=component';
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
// hier wird die funktion für das löschen der
// kommentare hinzugefügt
$$(".button-delete-commentary").addEvent('click', button_delete_commentary);	   
	}
}

function button_delete_commentary(eventid,baseajaxurl)
{
jQuery("#ajaxresponse").html(baseajaxurl);
jQuery("#ajaxresponse").addClass('ajax-loading');
//var eventid = this.id.substr(14);  

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
	
	
function save_new_subst(matchid,teamid,projecttime,baseajaxurl)
{
//jQuery("#ajaxresponse").html(matchid);
//jQuery("#ajaxresponse").html('hallo');
//alert(baseajaxurl);

var playerin = jQuery("#in").val();
				var playerout = jQuery("#out").val();
				var position = jQuery("#project_position_id").val();
				var time = jQuery("#in_out_time").val();
				var querystring = '&in=' + playerin + '&out=' + playerout
						+ '&project_position_id=' + position + '&in_out_time='
						+ time + '&teamid=' + teamid + '&matchid=' + matchid
						+  '&projecttime=' + projecttime;
				var url = baseajaxurl + '&task=matches.savesubst&tmpl=component';
jQuery("#ajaxresponse").html(url + querystring);

jQuery.ajax({
  type: 'POST', // type of request either Get or Post
  url: url + querystring, // Url of the page where to post data and receive response 
  //data: data, // data to be post
  dataType:"json",
  success: substsaved //function to be called on successful reply from server
  
}); 


}

function delete_subst(substid,baseajaxurl)
{
jQuery("#ajaxresponse").html(baseajaxurl);
jQuery("#ajaxresponse").addClass('ajax-loading');
var url = baseajaxurl + '&task=matches.removeSubst&tmpl=component';
var querystring = '&substid=' + substid;

jQuery("#ajaxresponse").html(url + querystring);

jQuery.ajax({
 type: 'POST', // type of request either Get or Post
 url: url + querystring, // Url of the page where to post data and receive response 
 dataType:"json",
 success: substdeleted   //function to be called on successful reply from server

}); 

}



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

function substsaved(response) 
{
	jQuery("#ajaxresponse").removeClass('ajax-loading');
	// first line contains the status, second line contains the new row.
	var resp = response.split('&');
	
	//alert(resp[0]);
	//alert(resp[1]);
	
	if (resp[0] != '0') 
  {
               		
    jQuery("#table-substitutions").last().append('<tr id="sub-' 
    + resp[0] + '"><td>'
    + jQuery("#out option:selected").text() + '</td><td>'  
    + jQuery("#in option:selected").text() + '</td><td>' 
    + jQuery("#project_position_id option:selected").text() + '</td><td>' 
    + jQuery("#in_out_time").val() + '</td><td><input	id="deletesubst-' + resp[0] 
    + '" type="button" onclick="delete_subst(' + resp[0] + ',baseajaxurl)" class="inputbox button-delete-subst" value="' 
    + str_delete + '"</td></tr>');
		
    jQuery("#ajaxresponse").addClass("ajaxsuccess");
		jQuery("#ajaxresponse").text(resp[1]);
//$$(".button-delete-subst").addEvent('click', button_delete_subst);				
	}
   else 
   {
  jQuery("#ajaxresponse").addClass("ajaxerror");
	jQuery("#ajaxresponse").text(resp[1]);
	}
}

function substdeleted(response) 
{
    jQuery("#ajaxresponse").removeClass('ajax-loading');
	var resp = response.split("&");
  var substid = resp[2]; 
  
//    alert(resp[0]);
//    alert(resp[1]);
//    alert('substdeleted -> ' + substid);

	if (resp[0] != '0') 
  {
//		var currentrow = jQuery('rowcomment-' + this.options.rowid);
//		currentrow.dispose();
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

