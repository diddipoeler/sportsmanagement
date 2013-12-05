// ajax save substitution
jQuery(document).ready(function() {	
  
jQuery.ajaxSetup({
        // put your favorite error function here:
        error:
            function(XMLHttpRequest, textStatus, errorThrown) {
                // release any existing ui blocks
                jQuery.unblockUI;
                var errorObj = JSON.parse(XMLHttpRequest.responseText);
                // send the user to the system error page if system error, otherwise popup the user error div
                if (!errorObj.Success) {
                    if (errorObj.ErrorType != "system") {
                        jQuery('#UserError').html(errorObj.Message);
                        jQuery.blockUI({ message: $('#UserErrorWrapper'),
                        css: { width: '400px', height: '300px', overflow: 'scroll' }
                     });
                }
                else {
                    window.location = errorObj.ErrorPageUrl;
                }
            }
        }
    });

    
jQuery("#save-new-subst").click(function(){
          jQuery("#ajaxresponse").html(baseajaxurl);
          jQuery("#ajaxresponse").addClass('ajax-loading');
          
var playerin = jQuery('in').value;
				var playerout = jQuery('out').val();
				var position = jQuery('project_position_id').val();
				var time = jQuery('in_out_time').val();
				var querystring = '&in=' + playerin + '&out=' + playerout
						+ '&project_position_id=' + position + '&in_out_time='
						+ time + '&teamid=' + teamid + '&matchid=' + matchid
						+  '&projecttime=' + projecttime;
				var url = baseajaxurl + '&task=matches.savesubst';
        jQuery("#ajaxresponse").html(url);
        
        jQuery.ajax({
  type: 'POST', // type of request either Get or Post
  url: url + querystring, // Url of the page where to post data and receive response 
  //data: data, // data to be post
  dataType:"json",
  success: substsaved //function to be called on successful reply from server
  
});
        
        
    });    
    
  jQuery("#save-new-event").click(function(){
          jQuery("#ajaxresponse").html(baseajaxurl);
          jQuery("#ajaxresponse").addClass('ajax-loading');
          var rowid = this.id.substr(5);
					var url = baseajaxurl + '&task=matches.saveevent&';
					var player = jQuery('teamplayer_id').val();
					var event = jQuery('event_type_id').val();
					var team = jQuery('team_id').val();
					var time = jQuery('event_time').val();
          var notice = encodeURIComponent(jQuery('notice').val());
					var querystring = 'teamplayer_id=' + player +
					'&projectteam_id=' + team + 
					'&event_type_id=' + event + 
					'&event_time=' + time + 
					'&match_id=' + matchid +
          '&projecttime=' + projecttime + 
					'&event_sum=' + jQuery('event_sum').value +
					'&notice=' + notice;
          jQuery("#ajaxresponse").html(querystring);
          
        //alert("hallo");
        //jQuery("#ajaxresponse").html("hallo");
    });
    
  jQuery("#save-new-comment").click(function(){
          jQuery("#ajaxresponse").html(baseajaxurl);
          jQuery("#ajaxresponse").addClass('ajax-loading');
          var url = baseajaxurl + '&task=matches.savecomment';
          var rowid = this.id.substr(5);

				var ctype = jQuery("#ctype").val();
        var comnt = encodeURIComponent(jQuery("#notes").val())
				var time = jQuery("#c_event_time").val();
				
				var querystring = '&type=' + ctype + '&event_time=' + time + '&matchid='
				+ matchid + '&notes='
				+ comnt + '&projecttime=' + projecttime;
         jQuery("#ajaxresponse").html(querystring); 
         
jQuery.ajax({
  type: 'POST', // type of request either Get or Post
  url: url + querystring, // Url of the page where to post data and receive response 
  //data: data, // data to be post
  dataType:"json",
  success: commentsaved //function to be called on successful reply from server
  
});
        
    });

jQuery(".button-delete-commentary").click(function()
{
alert('löschen');
jQuery("#ajaxresponse").html(baseajaxurl);
jQuery("#ajaxresponse").addClass('ajax-loading');
var eventid = this.id.substr(14);          
var url = baseajaxurl + '&task=matches.removeCommentary';
var querystring = '&event_id=' + eventid;

jQuery("#ajaxresponse").html(url + querystring);

alert(eventid);
	
jQuery.ajax({
  type: 'POST', // type of request either Get or Post
  url: url + querystring, // Url of the page where to post data and receive response 
  //data: data, // data to be post
  dataType:"json",
  success: commentarydeleted   //function to be called on successful reply from server
  
});           
        
    });











function reqsent() 
{
	jQuery("#ajaxresponse").addClass('ajax-loading');
  jQuery("#ajaxresponse").className = "";
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
	
	alert(resp[0]);
	alert(resp[1]);
	
	if (resp[0] != '0') 
  {
		
//    jQuery("#table-commentary").last().append('<tr id="rowcomment-' 
//    + resp[0] + '"><td>' 
//    + jQuery("#ctype").val() + '</td><td>' 
//    + jQuery("#c_event_time").val() + '</td><td>' 
//    + jQuery("#notes").val() + '</td><td><input	id="deletecomment-' + resp[0] 
//    + '" type="button" class="inputbox button-delete-commentary" value="' 
//    + str_delete + '"</td></tr>');
		
    jQuery("#ajaxresponse").className = "ajaxsuccess";
		jQuery("#ajaxresponse").text(resp[1]);
	}
   else 
   {
  jQuery("#ajaxresponse").className = "ajaxerror";
	jQuery("#ajaxresponse").text(resp[1]);
	}
}


function commentsaved(response) 
{
	jQuery("#ajaxresponse").removeClass('ajax-loading');
	// first line contains the status, second line contains the new row.
	var resp = response.split('&');
	
	alert(resp[0]);
	alert(resp[1]);
	
	if (resp[0] != '0') 
  {
		
    jQuery("#table-commentary").last().append('<tr id="rowcomment-' 
    + resp[0] + '"><td>' 
    + jQuery("#ctype").val() + '</td><td>' 
    + jQuery("#c_event_time").val() + '</td><td>' 
    + jQuery("#notes").val() + '</td><td><input	id="deletecomment-' + resp[0] 
    + '" type="button" class="inputbox button-delete-commentary" value="' 
    + str_delete + '"</td></tr>');
		
    jQuery("#ajaxresponse").className = "ajaxsuccess";
		jQuery("#ajaxresponse").text(resp[1]);
	}
   else 
   {
  jQuery("#ajaxresponse").className = "ajaxerror";
	jQuery("#ajaxresponse").text(resp[1]);
	}
}

function commentarydeleted(response) 
{
	var resp = response.split("&");

    alert(resp[0]);
    alert(resp[1]);
//    alert(this.options.rowid);

	if (resp[0] != '0') 
  {
		var currentrow = jQuery('rowcomment-' + this.options.rowid);
		currentrow.dispose();
	}

	jQuery('ajaxresponse').removeClass('ajax-loading');
    jQuery('ajaxresponse').className = "ajaxsuccess";
	jQuery('ajaxresponse').text(resp[1]);
}

     
});
  