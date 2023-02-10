var jlcinjectcontainer = new Array();
var jlcmodal = new Array();

window.addEvent('domready', function() {
	SqueezeBox.initialize({});
});

function jlcnewtopAjax() {
	/* THIS CREATES THE AJAX OBJECT */
	var xmlhttp = false;
	try {
		// ajax object for non IE navigators
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
		try {
			// ajax object for IE
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		} catch (E) {
			xmlhttp = false;
		}
	}
	if (!xmlhttp && typeof XMLHttpRequest != "undefined") {
		xmlhttp = new XMLHttpRequest();
	}

	return xmlhttp;
}

function jlamtopnewsubsubsubassoc(modid,federation)
{
var countryid = '';
var associd = 0;
var subassocid = 0;
var subsubassocid = 0;
countryid = document.getElementById("jlamtopfederation" + federation + modid).options[document.getElementById("jlamtopfederation" + federation + modid).selectedIndex].value;
associd = document.getElementById("jlamtopassoc" + federation + modid).options[document.getElementById("jlamtopassoc" + federation + modid).selectedIndex].value;
subassocid = document.getElementById("jlamtopsubassoc" + federation + modid).options[document.getElementById("jlamtopsubassoc" + federation + modid).selectedIndex].value;
subsubassocid = document.getElementById("jlamtopsubsubassoc" + federation + modid).options[document.getElementById("jlamtopsubsubassoc" + federation + modid).selectedIndex].value;

//alert('jlamtopnewsubassoc countryid =' + countryid );
//alert('jlamtopnewsubassoc associd =' + associd );

loadHtml = "<p id='loadingDiv-"
			+ modid
			+ "' style='margin-left: 10px; margin-top: -10px; margin-bottom: 10px;'>";
	loadHtml += "<img src='" + ajaxmenu_baseurl + 
				"modules/mod_sportsmanagement_ajax_top_navigation_menu/img/ajax-loader.gif'>";
	loadHtml += "</p>";
	$('jlajaxtopmenu-' + federation + modid).innerHTML += loadHtml;
	
var ajax = jlcnewtopAjax();
ajax.open("POST", location.href, true);
ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
ajax.send('jlamtopcountry=' + countryid + '&ajaxmodid=' + modid + '&jlamtopassocid=' + associd + '&jlamtopsubassocid=' + subassocid + '&jlamtopsubsubassocid=' + subsubassocid );
ajax.onreadystatechange = function() {

if (ajax.readyState == 4) {

var response = ajax.responseText;
			var start = response.indexOf('<!--jlajaxtopmenu' + federation + '-' + modid
					+ ' start-->');
			var finish = response.indexOf('<!--jlajaxtopmenu' + federation + '-' + modid
					+ ' end-->');

			justTheCalendar = response.substring(start, finish);
      $('jlajaxtopmenu-' + federation + modid).innerHTML = justTheCalendar;

}

}	
	
}

function jlamtopnewsubsubassoc(modid,federation)
{
var countryid = '';
var associd = 0;
var subassocid = 0;
countryid = document.getElementById("jlamtopfederation" + federation + modid).options[document.getElementById("jlamtopfederation" + federation + modid).selectedIndex].value;
associd = document.getElementById("jlamtopassoc" + federation + modid).options[document.getElementById("jlamtopassoc" + federation + modid).selectedIndex].value;
subassocid = document.getElementById("jlamtopsubassoc" + federation + modid).options[document.getElementById("jlamtopsubassoc" + federation + modid).selectedIndex].value;

//alert('jlamtopnewsubassoc countryid =' + countryid );
//alert('jlamtopnewsubassoc associd =' + associd );

loadHtml = "<p id='loadingDiv-"
			+ modid
			+ "' style='margin-left: 10px; margin-top: -10px; margin-bottom: 10px;'>";
	loadHtml += "<img src='" + ajaxmenu_baseurl + 
				"modules/mod_sportsmanagement_ajax_top_navigation_menu/img/ajax-loader.gif'>";
	loadHtml += "</p>";
	$('jlajaxtopmenu-' + federation + modid).innerHTML += loadHtml;
	
var ajax = jlcnewtopAjax();
ajax.open("POST", location.href, true);
ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
ajax.send('jlamtopcountry=' + countryid + '&ajaxmodid=' + modid + '&jlamtopassocid=' + associd + '&jlamtopsubassocid=' + subassocid );
ajax.onreadystatechange = function() {

if (ajax.readyState == 4) {

var response = ajax.responseText;
			var start = response.indexOf('<!--jlajaxtopmenu' + federation + '-' + modid
					+ ' start-->');
			var finish = response.indexOf('<!--jlajaxtopmenu' + federation + '-' + modid
					+ ' end-->');

			justTheCalendar = response.substring(start, finish);
      $('jlajaxtopmenu-' + federation + modid).innerHTML = justTheCalendar;

}

}	
	
}

function jlamtopnewsubassoc(modid,federation)
{
var countryid = '';
var associd = 0;
countryid = document.getElementById("jlamtopfederation" + federation + modid).options[document.getElementById("jlamtopfederation" + federation + modid).selectedIndex].value;
associd = document.getElementById("jlamtopassoc" + federation + modid).options[document.getElementById("jlamtopassoc" + federation + modid).selectedIndex].value;

//alert('jlamtopnewsubassoc countryid =' + countryid );
//alert('jlamtopnewsubassoc associd =' + associd );

loadHtml = "<p id='loadingDiv-"
			+ modid
			+ "' style='margin-left: 10px; margin-top: -10px; margin-bottom: 10px;'>";
	loadHtml += "<img src='" + ajaxmenu_baseurl + 
				"modules/mod_sportsmanagement_ajax_top_navigation_menu/img/ajax-loader.gif'>";
	loadHtml += "</p>";
	$('jlajaxtopmenu-' + federation + modid).innerHTML += loadHtml;
	
var ajax = jlcnewtopAjax();
ajax.open("POST", location.href, true);
ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
ajax.send('jlamtopcountry=' + countryid + '&ajaxmodid=' + modid + '&jlamtopassocid=' + associd );
ajax.onreadystatechange = function() {

if (ajax.readyState == 4) {

var response = ajax.responseText;
			var start = response.indexOf('<!--jlajaxtopmenu' + federation + '-' + modid
					+ ' start-->');
			var finish = response.indexOf('<!--jlajaxtopmenu' + federation + '-' + modid
					+ ' end-->');

			justTheCalendar = response.substring(start, finish);
      $('jlajaxtopmenu-' + federation + modid).innerHTML = justTheCalendar;

}

}	
	
}

function jlamtopnewcountries(modid,federation)
{
//alert('jlamtopnewcountries modid=' + modid);
//alert('jlamtopnewcountries federation=' + federation);

var countryid = '';
countryid = document.getElementById("jlamtopfederation" + federation + modid).options[document.getElementById("jlamtopfederation" + federation + modid).selectedIndex].value;

//alert('jlamtopnewcountries country=' + countryid);

loadHtml = "<p id='loadingDiv-"
			+ modid
			+ "' style='margin-left: 10px; margin-top: -10px; margin-bottom: 10px;'>";
	loadHtml += "<img src='" + ajaxmenu_baseurl + 
				"modules/mod_sportsmanagement_ajax_top_navigation_menu/img/ajax-loader.gif'>";
	loadHtml += "</p>";
	$('jlajaxtopmenu-' + federation + modid).innerHTML += loadHtml;
    
var ajax = jlcnewtopAjax();
ajax.open("POST", location.href, true);
ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
ajax.send('jlamtopcountry=' + countryid + '&ajaxmodid=' + modid);
ajax.onreadystatechange = function() {

if (ajax.readyState == 4) {

var response = ajax.responseText;
			var start = response.indexOf('<!--jlajaxtopmenu' + federation + '-' + modid
					+ ' start-->');
			var finish = response.indexOf('<!--jlajaxtopmenu' + federation + '-' + modid
					+ ' end-->');

			justTheCalendar = response.substring(start, finish);
      $('jlajaxtopmenu-' + federation + modid).innerHTML = justTheCalendar;

}

}

}

function jlamtopnewteams(modid,federation)
{
//alert(modid);
//var seasonid = 0;
var leagueid = 0;
var countryid = '';
var associd = 0;
var projectid = 0;
var subassocid = 0;
var subsubassocid = 0;
var teamid = 0;

var element = document.getElementById("jlamtopsubassoc" + federation + modid);
if (element != null ) 
{
subassocid = document.getElementById("jlamtopsubassoc" + federation + modid).options[document.getElementById("jlamtopsubassoc" + federation + modid).selectedIndex].value;
}

var element = document.getElementById("jlamtopsubsubassoc" + federation + modid);
if (element != null ) 
{
subsubassocid = document.getElementById("jlamtopsubsubassoc" + federation + modid).options[document.getElementById("jlamtopsubsubassoc" + federation + modid).selectedIndex].value;
}
countryid = document.getElementById("jlamtopfederation" + federation + modid).options[document.getElementById("jlamtopfederation" + federation + modid).selectedIndex].value;

var element = document.getElementById("jlamtopassoc" + federation + modid);
if (element != null ) 
{
associd = document.getElementById("jlamtopassoc" + federation + modid).options[document.getElementById("jlamtopassoc" + federation + modid).selectedIndex].value;
}

var element = document.getElementById("jlamtopleagues" + federation + modid);
if (element != null ) 
{
leagueid = document.getElementById("jlamtopleagues" + federation + modid).options[document.getElementById("jlamtopleagues" + federation + modid).selectedIndex].value;
}

var element = document.getElementById("jlamtopprojects" + federation + modid);
if (element != null ) 
{
projectid = document.getElementById("jlamtopprojects" + federation + modid).options[document.getElementById("jlamtopprojects" + federation + modid).selectedIndex].value;
}

teamid = document.getElementById("jlamtopteams" + federation + modid).options[document.getElementById("jlamtopteams" + federation + modid).selectedIndex].value;

loadHtml = "<p id='loadingDiv-"
			+ modid
			+ "' style='margin-left: 10px; margin-top: -10px; margin-bottom: 10px;'>";
	loadHtml += "<img src='" + ajaxmenu_baseurl + 
				"modules/mod_sportsmanagement_ajax_top_navigation_menu/img/ajax-loader.gif'>";
	loadHtml += "</p>";
	$('jlajaxtopmenu-' + federation + modid).innerHTML += loadHtml;
    
var ajax = jlcnewtopAjax();
ajax.open("POST", location.href, true);
ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
ajax.send('jlamtopcountry=' + countryid + '&ajaxmodid=' + modid + '&jlamtopassocid=' + associd + '&jlamtopleague=' + leagueid + '&jlamtopsubassocid=' + subassocid + '&jlamtopsubsubassocid=' + subsubassocid + '&jlamtopproject=' + projectid + '&jlamtopteam=' + teamid);
ajax.onreadystatechange = function() {

if (ajax.readyState == 4) {

var response = ajax.responseText;
			var start = response.indexOf('<!--jlajaxtopmenu' + federation + '-' + modid
					+ ' start-->');
			var finish = response.indexOf('<!--jlajaxtopmenu' + federation + '-' + modid
					+ ' end-->');

			justTheCalendar = response.substring(start, finish);
      $('jlajaxtopmenu-' + federation + modid).innerHTML = justTheCalendar;

}

}
    
}

function jlamtopdivision(modid)
{
//alert('jlamdivision_modid=' + modid);

var seasonid = 0;
var leagueid = 0;
var projectid = 0;
var divisionid = 0;
//var teamid = 0;


seasonid = document.getElementById("jlamseason" + modid).options[document.getElementById("jlamseason" + modid).selectedIndex].value;
//alert('jlamdivision seasonid=' + seasonid);

leagueid = document.getElementById("jlamleague" + modid).options[document.getElementById("jlamleague" + modid).selectedIndex].value;
//alert('jlamdivision leagueid=' + leagueid);

projectid = document.getElementById("jlamproject" + modid).options[document.getElementById("jlamproject" + modid).selectedIndex].value;
//alert('jlamdivision projectid=' + projectid);

divisionid = document.getElementById("jlamdivisions" + modid).options[document.getElementById("jlamdivisions" + modid).selectedIndex].value;
//alert('jlamdivision divisionid=' + divisionid);

teamid = document.getElementById("jlamteams" + modid).options[document.getElementById("jlamteams" + modid).selectedIndex].value;
//alert('jlamdivision teamid=' + teamid);


loadHtml = "<p id='loadingDiv-"
			+ modid
			+ "' style='margin-left: 10px; margin-top: -10px; margin-bottom: 10px;'>";
	//loadHtml += "<img src='" + ajaxmenu_baseurl + "modules/mod_sportsmanagement_ajax_top_navigation_menu/img/ajax-loader.gif'>";
	loadHtml += "Bitte warten";
	loadHtml += "</p>";
	$('jlajaxmenu-' + modid).innerHTML += loadHtml;
    
var ajax = top();
ajax.open("POST", location.href, true);
ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
ajax.send('jlamseason=' + seasonid + '&ajaxmodid=' + modid + '&jlamtopleague=' + leagueid + '&jlamtopproject=' + projectid + '&jlamtopdivisionid=' + divisionid + '&jlamtopteamid=' + teamid  );
ajax.onreadystatechange = function() {

if (ajax.readyState == 4) {

var response = ajax.responseText;
			var start = response.indexOf('<!--jlajaxmenu-' + modid
					+ ' start-->');
			var finish = response.indexOf('<!--jlajaxmenu-' + modid
					+ ' end-->');

			justTheCalendar = response.substring(start, finish);
      $('jlajaxmenu-' + modid).innerHTML = justTheCalendar;

}

}    
    
}


function jlamtopnewdivisions(modid,federation)
{
//alert(modid);
//var seasonid = 0;
var leagueid = 0;
var projectid = 0;
var countryid = '';
var associd = 0;
var subassocid = 0;
var subsubassocid = 0;

var element = document.getElementById("jlamtopsubassoc" + federation + modid);
if (element != null ) 
{
subassocid = document.getElementById("jlamtopsubassoc" + federation + modid).options[document.getElementById("jlamtopsubassoc" + federation + modid).selectedIndex].value;
}

var element = document.getElementById("jlamtopsubsubassoc" + federation + modid);
if (element != null ) 
{
subsubassocid = document.getElementById("jlamtopsubsubassoc" + federation + modid).options[document.getElementById("jlamtopsubsubassoc" + federation + modid).selectedIndex].value;
}

//seasonid = document.getElementById("jlamseason" + modid).options[document.getElementById("jlamseason" + modid).selectedIndex].value;
leagueid = document.getElementById("jlamtopleagues" + federation + modid).options[document.getElementById("jlamtopleagues" + federation + modid).selectedIndex].value;
projectid = document.getElementById("jlamtopprojects" + federation + modid).options[document.getElementById("jlamtopprojects" + federation + modid).selectedIndex].value;
countryid = document.getElementById("jlamtopfederation" + federation + modid).options[document.getElementById("jlamtopfederation" + federation + modid).selectedIndex].value;

var element = document.getElementById("jlamtopassoc" + federation + modid);
if (element != null ) 
{
associd = document.getElementById("jlamtopassoc" + federation + modid).options[document.getElementById("jlamtopassoc" + federation + modid).selectedIndex].value;
}

//alert(seasonid);

loadHtml = "<p id='loadingDiv-"
			+ modid
			+ "' style='margin-left: 10px; margin-top: -10px; margin-bottom: 10px;'>";
	loadHtml += "<img src='" + ajaxmenu_baseurl + 
				"modules/mod_sportsmanagement_ajax_top_navigation_menu/img/ajax-loader.gif'>";
	loadHtml += "</p>";
	$('jlajaxtopmenu-' + federation + modid).innerHTML += loadHtml;
    
var ajax = jlcnewtopAjax();
ajax.open("POST", location.href, true);
ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
ajax.send('&ajaxmodid=' + modid + '&jlamtopleague=' + leagueid + '&jlamtopproject=' + projectid + '&jlamtopcountry=' + countryid + '&jlamtopassocid=' + associd + '&jlamtopsubassocid=' + subassocid + '&jlamtopsubsubassocid=' + subsubassocid );
ajax.onreadystatechange = function() {

if (ajax.readyState == 4) {

var response = ajax.responseText;
			var start = response.indexOf('<!--jlajaxtopmenu' + federation + '-' + modid
					+ ' start-->');
			var finish = response.indexOf('<!--jlajaxtopmenu' + federation + '-' + modid
					+ ' end-->');

			justTheCalendar = response.substring(start, finish);
      $('jlajaxtopmenu-' + federation + modid).innerHTML = justTheCalendar;

}

}    
    
}

    
function jlamtopnewprojects(modid,federation)
{
//alert(modid);
//var seasonid = 0;
var leagueid = 0;
var countryid = '';
var associd = 0;
var projectid = 0;
var subassocid = 0;
var subsubassocid = 0;

var element = document.getElementById("jlamtopsubassoc" + federation + modid);
if (element != null ) 
{
subassocid = document.getElementById("jlamtopsubassoc" + federation + modid).options[document.getElementById("jlamtopsubassoc" + federation + modid).selectedIndex].value;
}

var element = document.getElementById("jlamtopsubsubassoc" + federation + modid);
if (element != null ) 
{
subsubassocid = document.getElementById("jlamtopsubsubassoc" + federation + modid).options[document.getElementById("jlamtopsubsubassoc" + federation + modid).selectedIndex].value;
}
countryid = document.getElementById("jlamtopfederation" + federation + modid).options[document.getElementById("jlamtopfederation" + federation + modid).selectedIndex].value;

var element = document.getElementById("jlamtopassoc" + federation + modid);
if (element != null ) 
{
associd = document.getElementById("jlamtopassoc" + federation + modid).options[document.getElementById("jlamtopassoc" + federation + modid).selectedIndex].value;
}
//seasonid = document.getElementById("jlamseason" + modid).options[document.getElementById("jlamseason" + modid).selectedIndex].value;
leagueid = document.getElementById("jlamtopleagues" + federation + modid).options[document.getElementById("jlamtopleagues" + federation + modid).selectedIndex].value;
//projectid = document.getElementById("jlamtopprojects" + federation + modid).options[document.getElementById("jlamtopprojects" + federation + modid).selectedIndex].value;
//alert(seasonid);

loadHtml = "<p id='loadingDiv-"
			+ modid
			+ "' style='margin-left: 10px; margin-top: -10px; margin-bottom: 10px;'>";
	loadHtml += "<img src='" + ajaxmenu_baseurl + 
				"modules/mod_sportsmanagement_ajax_top_navigation_menu/img/ajax-loader.gif'>";
	loadHtml += "</p>";
	$('jlajaxtopmenu-' + federation + modid).innerHTML += loadHtml;
    
var ajax = jlcnewtopAjax();
ajax.open("POST", location.href, true);
ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
ajax.send('jlamtopcountry=' + countryid + '&ajaxmodid=' + modid + '&jlamtopassocid=' + associd + '&jlamtopleague=' + leagueid + '&jlamtopsubassocid=' + subassocid + '&jlamtopsubsubassocid=' + subsubassocid );
ajax.onreadystatechange = function() {

if (ajax.readyState == 4) {

var response = ajax.responseText;
			var start = response.indexOf('<!--jlajaxtopmenu' + federation + '-' + modid
					+ ' start-->');
			var finish = response.indexOf('<!--jlajaxtopmenu' + federation + '-' + modid
					+ ' end-->');

			justTheCalendar = response.substring(start, finish);
      $('jlajaxtopmenu-' + federation + modid).innerHTML = justTheCalendar;

}

}    
    
}


function jlamtopnewleagues(modid)
{
//alert('jlamnewleagues_modid=' + modid);

var seasonid = 0;
seasonid = document.getElementById("jlamseason" + modid).options[document.getElementById("jlamseason" + modid).selectedIndex].value;

//alert('jlamnewleagues_season_id=' + seasonid);

loadHtml = "<p id='loadingDiv-"
			+ modid
			+ "' style='margin-left: 10px; margin-top: -10px; margin-bottom: 10px;'>";
	loadHtml += "<img src='" + ajaxmenu_baseurl + 
				"modules/mod_sportsmanagement_ajax_top_navigation_menu/img/ajax-loader.gif'>";
	loadHtml += "</p>";
	$('jlajaxmenu-' + modid).innerHTML += loadHtml;
    
var ajax = jlcnewtopAjax();
ajax.open("POST", location.href, true);
ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
ajax.send('jlamseason=' + seasonid + '&ajaxmodid=' + modid);
ajax.onreadystatechange = function() {

if (ajax.readyState == 4) {

var response = ajax.responseText;
			var start = response.indexOf('<!--jlajaxmenu-' + modid
					+ ' start-->');
			var finish = response.indexOf('<!--jlajaxmenu-' + modid
					+ ' end-->');

			justTheCalendar = response.substring(start, finish);
      $('jlajaxmenu-' + modid).innerHTML = justTheCalendar;

}

}


}
