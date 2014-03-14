 jQuery(document).ready(function(){
 	
 	jQuery("#turtushout-warning").remove();
 	jQuery.get("?action=turtushout_token",function(txt){
		jQuery("#turtushout-form").append('<input type="hidden" name="ts" value="'+txt+'" />');
	});
 	jQuery("#turtushout-form").show();
 	jQuery("#turtushout-status").show();
	jQuery("#turtushout-form").submit( function () {
		jQuery("#turtushout-status").html("Sending...");
		jQuery.get(turtushout_server_url +"?action=turtushout_shout&" + jQuery("#turtushout-form").serialize(),function(txt){
			if (txt == "Shouted!") {
				TurtushoutUpdate();
			} else {
				jQuery("#turtushout-status").html(txt);
			}
		});
		return false;
	});
	setTimeout(TurtushoutUpdate, turtushout_update_timeout);
 });

function TurtushoutUpdate() 
{
	jQuery("#turtushout-status").html("Aktualisierung l&auml;uft...");
	jQuery("#turtushout-shout").load(turtushout_server_url + "?action=turtushout_shouts", function() {
		jQuery("#turtushout-status").html("Spiele sind Aktualisiert");
	});

    setTimeout(TurtushoutUpdate, turtushout_update_timeout);
}
function TurtushoutDelete(id) {
	jQuery("#turtushout-status").html("Deleteing..");
	jQuery("#turtushout-status").load(turtushout_server_url + "?action=turtushout_del&sid=" + id, TurtushoutUpdate);
}
