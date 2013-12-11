// ajax save substitution
jQuery(document).ready(function() {	

updatePlayerSelect();
//	if(jQuery('team_id')) 
//  {
//		jQuery('team_id').addEvent('change', updatePlayerSelect);
//   }
      
//jQuery.ajaxSetup({
//        // put your favorite error function here:
//        error:
//            function(XMLHttpRequest, textStatus, errorThrown) {
//                // release any existing ui blocks
//                jQuery.unblockUI;
//                var errorObj = JSON.parse(XMLHttpRequest.responseText);
//                // send the user to the system error page if system error, otherwise popup the user error div
//                if (!errorObj.Success) {
//                    if (errorObj.ErrorType != "system") {
//                        jQuery('#UserError').html(errorObj.Message);
//                        jQuery.blockUI({ message: jQuery('#UserErrorWrapper'),
//                        css: { width: '400px', height: '300px', overflow: 'scroll' }
//                     });
//                }
//                else {
//                    window.location = errorObj.ErrorPageUrl;
//                }
//            }
//        }
//    });

    
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
				var url = baseajaxurl + '&task=matches.savesubst&tmpl=component';
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
					var url = baseajaxurl + '&task=matches.saveevent&tmpl=component&';
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
          var url = baseajaxurl + '&task=matches.savecomment&tmpl=component';
          var rowid = this.id.substr(5);

				var ctype = jQuery("#ctype").val();
				var token = jQuery("#token").val();
        var comnt = encodeURIComponent(jQuery("#notes").val())
				var time = jQuery("#c_event_time").val();
				
				var querystring = '&type=' + ctype + '&event_time=' + time + '&matchid='
				+ matchid + '&notes='
				+ comnt + '&projecttime=' + projecttime;
         jQuery("#ajaxresponse").html(url + querystring); 

//alert(token);
         
jQuery.ajax({
  type: 'POST', // type of request either Get or Post
  url: url + querystring, // Url of the page where to post data and receive response 
  data: {
            'token': '1' // <-- THIS IS IMPORTANT
            
        }, // data to be post
  //data: jQuery("#component-form").serialize(),
  dataType:"json",
//  success: commentsaved
  success: commentsaved, //function to be called on successful reply from server
  error: function (xhr, ajaxOptions, thrownError) {
        alert(xhr.status);
        alert(thrownError);
      }
  
//  error: function (xhr, ajaxOptions, thrownError) {
//        alert(xhr.status);
//        alert(thrownError);
//      }
});
        
    });

jQuery(".button-delete-commentary").click(function()
{
//alert('löschen');
jQuery("#ajaxresponse").html(baseajaxurl);
jQuery("#ajaxresponse").addClass('ajax-loading');
var eventid = this.id.substr(14);   
var token = jQuery("#token").val();       
var url = baseajaxurl + '&task=matches.removeCommentary&tmpl=component';
//var url = baseajaxurl + '&task=ajaxcalls.removeCommentary';
//var url = baseajaxurl + '&task=removeCommentary&tmpl=component&view=matches';
var querystring = '&event_id=' + eventid;

jQuery("#ajaxresponse").html(url + querystring);

//alert(token);

//alert(eventid);
	
jQuery.ajax({
 type: 'POST', // type of request either Get or Post
 url: url + querystring, // Url of the page where to post data and receive response 
 data: {
            'token': '1' // <-- THIS IS IMPORTANT
            
        }, // data to be post
 //data: jQuery("#component-form").serialize(),
 dataType:"json",
 success: commentarydeleted,   //function to be called on successful reply from server
 error: function (xhr, ajaxOptions, thrownError) 
 {
       jQuery("#ajaxresponse").html(xhr);
       //alert(xhr);
       alert(xhr.status);
       alert(thrownError);
     }
});           

// 	var myXhr = new Request.JSON( {
// 			url: url + querystring,
// 			method : 'post',
// 			onRequest : reqsent,
// 			onFailure : reqfailed,
// 			onSuccess : commentarydeleted,
// 			rowid : eventid
// 		});
// 		myXhr.post();

// var request = new Request.JSON({
//       url: '/echo/json/',
//       onRequest: function(){
//         gallery.set('text', 'Loading...');
//       },
//       onComplete: function(jsonObj) {
//         gallery.empty();
//         addImages(jsonObj.previews);
//       },
//       data: { // our demo runner and jsfiddle will return this exact data as a JSON string
//         json: JSON.encode(data)
//       }
//     }).send();



//var req = new Request.JSON({
//        method: 'post',
//        url: url,
//        onSuccess: function(r)
//        {
//                if (!r.success && r.message)
//                {
//                        // Success flag is set to 'false' and main response message given
//                        // So you can alert it or insert it into some HTML element
//                        alert(r.message);
//                }
 //
//                if (r.messages)
//                {
//                        // All the enqueued messages of the $app object can simple be
//                        // rendered by the respective helper function of Joomla!
//                        // They will automatically be displayed at the messages section of the template
//                        Joomla.renderMessages(r.messages);
//                }
 //
//                if (r.data)
//                {
//                        // Here you can access all the data of your response
//                        alert(r.data.myfirstcustomparam);
//                        alert(r.data.mysecondcustomparam);
//                }
//        }.bind(this),
//        onFailure: function(xhr)
//        {
//                // Reaching this point means that the Ajax request itself was not successful
//                // So JResponseJson was never called
//                alert('Ajax error');
//        }.bind(this),
//        onError: function(text, error)
//        {
//                // Reaching this point means that the Ajax request was answered by the server, but
//                // the response was no valid JSON (this happens sometimes if there were PHP errors,
//                // warnings or notices during the development process of a new Ajax request).
//                alert(error + "\n\n" + text);
//        }.bind(this)
//});
//req.post();

        
    });
     
});

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
		
    jQuery("#ajaxresponse").addClass("ajaxsuccess");
		jQuery("#ajaxresponse").text(resp[1]);
	}
   else 
   {
  jQuery("#ajaxresponse").addClass("ajaxerror");
	jQuery("#ajaxresponse").text(resp[1]);
	}
}



function commentsaved(response) 
{
	jQuery("#ajaxresponse").removeClass('ajax-loading');
	// first line contains the status, second line contains the new row.
	var resp = response.split('&');
	
//	alert(resp[0]);
//	alert(resp[1]);
	
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

function commentarydeleted(response) 
{
	var resp = response.split("&");
  var eventid = resp[2]; 
  
//    alert(resp[0]);
//    alert(resp[1]);
//    alert(eventid);

	if (resp[0] != '0') 
  {
//		var currentrow = jQuery('rowcomment-' + this.options.rowid);
//		currentrow.dispose();
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

function updatePlayerSelect() 
{
//	if(jQuery('cell-player'))
//	jQuery('cell-player').empty().appendChild(
//			getPlayerSelect(jQuery("#team_id").val())
//      );
getPlayerSelect(jQuery("#team_id").val());
      
}

/**
 * return players select for specified team
 *
 * @param int )
 *            for home, 1 for away
 * @return dom element
 */
function getPlayerSelect(index) 
{
	// homeroster and awayroster must be defined globally (in the view calling
	// the script)

	//alert(index);

	//alert(rosters + " rosters Zahlen sind definiert");
	//alert(Mitarbeiter.length + " Mitarbeiter Zahlen sind definiert");
	//alert(rosters);
	//var roster = rosters + index;
	//alert(roster);
	//alert(roster.length);
	
  // build select
	var select = new Element('select', {
		id : "teamplayer_id"
	});
	
//	jQuery("#ajaxresponse").text(roster);
	
//	for (var i = 0; i < 1; i++) 
//  {
//  var roster_value = roster[i];
//  alert( roster[i] );
//  }

//	for ( var i = 0, n = roster.length; i < n; i++) 
//  {
//		new Element('option', {
//			value : roster[i].value
//		}).set('text',roster[i].text).injectInside(select);
//	}
	
	return select;
}
  
