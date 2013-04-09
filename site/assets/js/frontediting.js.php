<?php defined( '_JEXEC' ) or die( 'Restricted access' );

/* JoomLeague League Management and Prediction Game for Joomla!
 * Copyright (C) 2007  Robert Moss
*
* Homepage: http://www.JoomLeague.net
* Support: htt://www.JoomLeague.net/forum/
*
* This file is part of JoomLeague.
*
* JoomLeague is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* Please note that the GPL states that any headers in files and
* Copyright notices as well as credits in headers, source files
* and output (screens, prints, etc.) can not be removed.
* You can extend them with your own credits, though...
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*
* The "GNU General Public License" (GPL) is available at
* http://www.gnu.org/copyleft/gpl.html.
*/

?>
<script type="text/javascript">
// <![CDATA[
<!--
function showeditor(id,linkObj) {
	linkObj=document.getElementById(linkObj);
    if (tinyMCE.getInstanceById(id) == null) {
        linkObj.innerHTML = "hide editor";
        tinyMCE.execCommand('mceAddControl', false, id);
    }
    else {
        linkObj.innerHTML = "show editor";
        tinyMCE.execCommand('mceRemoveControl', false, id);
    }
}
function loadmatchdetails(matchid, number) {
	var summary='summary'+matchid;
	ajaxpage(matchid, number);
}
function ReadOnlyCheckBox()
{
return false;
}
function FormSwitch(matchid, action) {
    	if (action == 0) var what=true;
    	if (action == 1) var what=false;
    	var field1='team1_result_decision'+matchid;
    	var field2='team2_result_decision'+matchid;
    	var field3='decision_info'+matchid;
		document.getElementById(field1).disabled=what;
		document.getElementById(field2).disabled=what;
		document.getElementById(field3).disabled=what;
    }
/***********************************************
* Dynamic Ajax Content- � Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
***********************************************/
var http_request = false;
var bustcachevar=0 //bust potential caching of external pages after initial request? (1=yes, 0=no)
var loadedobjects=""
var rootdomain="http://"+window.location.hostname
var bustcacheparameter=""

function ajaxpage(matchid, cidnummer){

var getpage=false;
var containerid = "details" + matchid;
var cidid = "cb"+cidnummer;
if (document.getElementById(containerid).style.display=='none') {
	document.getElementById(containerid).style.display='block';
	var url="<?php echo 'index2.php?option=com_joomleague&task=editmatch.load&p='.$joomleague->id.'&mid=';?>"+matchid;
	document.getElementById(cidid).checked=true;
	document.getElementById('checkmycontainers').value = parseInt(document.getElementById('checkmycontainers').value)+1;
	isChecked(document.getElementById(cidid));
	getpage=true;
}
else {
	
	var url="<?php echo 'index2.php?option=com_joomleague&task=editmatch.cancel&p='.$joomleague->id."&mid=";?>"+matchid;
	var answer = confirm ("<?php echo JText::_('COM_JOOMLEAGUE_EDIT_FORM_RESULTS_CONFIRM');?>")
	if (answer) {
		document.getElementById(cidid).checked=false;
		isChecked(document.getElementById(cidid));
		document.getElementById('checkmycontainers').value = parseInt(document.getElementById('checkmycontainers').value)-1;
		document.getElementById(containerid).innerHTML='';
		document.getElementById(containerid).style.display='none';
		}
		else {
			document.getElementById(cidid).checked=true;
			return;
		}
}

var page_request = false
if (window.XMLHttpRequest) // if Mozilla, Safari etc
page_request = new XMLHttpRequest()
else if (window.ActiveXObject){ // if IE
try {
page_request = new ActiveXObject("Msxml2.XMLHTTP")
} 
catch (e){
try{
page_request = new ActiveXObject("Microsoft.XMLHTTP")
}
catch (e){}
}
}
else
return false
page_request.onreadystatechange=function(){
loadpage(page_request, containerid)
}
if (bustcachevar) //if bust caching of external page
bustcacheparameter=(url.indexOf("?")!=-1)? "&"+new Date().getTime() : "?"+new Date().getTime()
page_request.open('GET', url+bustcacheparameter, true)
page_request.send(null)
}
	
function loadpage(page_request, containerid){
	if (page_request.readyState == 4 && (page_request.status==200 || window.location.href.indexOf("http")==-1))
	document.getElementById(containerid).innerHTML=page_request.responseText
}
//-->
// ]]>
</script>
