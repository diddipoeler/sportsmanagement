<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// no direct access
defined('_JEXEC') or die('Restricted access'); 

require_once(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'sportsmanagement.php');
// get helper
require_once (dirname(__FILE__).DS.'helper.php');

//require_once(JPATH_SITE.DS.'components'.DS.'com_joomleague'.DS.'joomleague.core.php');

//$lang = JFactory::getLanguage();
//		$extension = "com_joomleague_countries";
//		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
//		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
//		||	$lang->load($extension, $source, null, false, false)
//		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
//		||	$lang->load($extension, $source, $lang->getDefault(), false, false);

JHTML::_('behavior.tooltip');

$helper = new modSportsmanagementAjaxTopNavigationMenuHelper($params);

$points = $helper->getFederations();
$tab_points = array();
$countryassocselect = array();

$countrysubassocselect = array();
$countrysubsubassocselect = array();
$countrysubsubsubassocselect = array();
$leagueselect  = array();
$projectselect  = array();

foreach( $points as $row )
{
$tab_points[] = $row->name;
    
}

//$tab_points[] = 'afc';
//$tab_points[] = 'caf';
//$tab_points[] = 'conmebol';
//$tab_points[] = 'concacaf';
//$tab_points[] = 'ofc';
//$tab_points[] = 'uefa';
$tab_points[] = 'NON';
//$tab_points[] = 'turniere';

$ajax= JRequest::getVar('ajaxCalMod',0,'default','POST');
$ajaxmod= JRequest::getVar('ajaxmodid',0,'default','POST');

$document = JFactory::getDocument();
//$document->addScript(JURI::root(true).'/administrator/components/com_joomleague/assets/js/jl2.noconflict.js');

// $jquery_version =  JComponentHelper::getParams('com_joomleague')->get('jqueryversionfrontend',0);
// $jquery_sub_version = JComponentHelper::getParams('com_joomleague')->get('jquerysubversionfrontend',0);
// $jquery_ui_version = JComponentHelper::getParams('com_joomleague')->get('jqueryuiversionfrontend',0);
// $jquery_ui_sub_version = JComponentHelper::getParams('com_joomleague')->get('jqueryuisubversionfrontend',0);

//$document->addScript('https://ajax.googleapis.com/ajax/libs/jqueryui/'.$jquery_ui_version.'.'.$jquery_ui_sub_version.'/jquery-ui.min.js');
// $document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/'.$jquery_version.'/jquery.min.js');



//$show_debug_info = JComponentHelper::getParams('com_joomleague')->get('show_debug_info',0) ;

$queryvalues = $helper->getQueryValues();
$assoc_id = 0;
$subassoc_id = 0;  
$subsubassoc_id = 0;  
$user_name = $helper->getUserName();


if ( $queryvalues && !$_POST )
{
$ende_if = false;
$league_assoc_id = 0;
$sub_assoc_parent_id = 0;
$sub_sub_assoc_parent_id = 0;

$project_id  = intval($queryvalues['p']);
$team_id  = intval($queryvalues['tid']);
$helper->setProject( intval($queryvalues['p']), intval($queryvalues['tid']),intval($queryvalues['division'])  );
$league_id  = $helper->getLeagueId();
$country_id  = $helper->getProjectCountry($project_id);

$league_assoc_id  = $helper->getLeagueAssocId();
$sub_assoc_parent_id  = $helper->getAssocParentId($league_assoc_id);
$sub_sub_assoc_parent_id  = $helper->getAssocParentId($sub_assoc_parent_id);

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo 'league_assoc_id => <pre>'.print_r($league_assoc_id, true).'</pre><br>';
echo 'sub_assoc_parent_id => <pre>'.print_r($sub_assoc_parent_id, true).'</pre><br>';
echo 'sub_sub_assoc_parent_id => <pre>'.print_r($sub_sub_assoc_parent_id, true).'</pre><br>';
}


if ( !empty($sub_sub_assoc_parent_id) && !$ende_if )
{
$assoc_id  = $sub_sub_assoc_parent_id;
$subassoc_id = $sub_assoc_parent_id;
$subsubassoc_id = $league_assoc_id;
$ende_if = true;
}

if ( !empty($sub_assoc_parent_id) && !$ende_if )
{
$assoc_id = $sub_assoc_parent_id;
$subassoc_id = $league_assoc_id;
$ende_if = true;
}    

if ( !empty($league_assoc_id)  && !$ende_if )
{
$assoc_id = $league_assoc_id;
$ende_if = true;
}
 
}

if ( $_POST 

// JRequest::getVar('jlamtopcountry',0,'default','POST')
// || JRequest::getVar('jlamtopassocid',0,'default','POST')
// || JRequest::getVar('jlamtopsubassocid',0,'default','POST')
// || JRequest::getVar('jlamtopleague',0,'default','POST')
// || JRequest::getVar('jlamtopproject',0,'default','POST')

)
{
$project_id = 0;  
$league_id = 0;   

$team_id = 0;
$division_id = 0;

$assoc_id  = JRequest::getVar('jlamtopassocid',0,'default','POST');
$subassoc_id  = JRequest::getVar('jlamtopsubassocid',0,'default','POST');
$subsubassoc_id  = JRequest::getVar('jlamtopsubsubassocid',0,'default','POST');
$country_id  = JRequest::getVar('jlamtopcountry',0,'default','POST');
$season_id  = JRequest::getVar('jlamtopseason',0,'default','POST');
$league_id  = JRequest::getVar('jlamtopleague',0,'default','POST');
$project_id  = JRequest::getVar('jlamtopproject',0,'default','POST');
$team_id  = JRequest::getVar('jlamtopteam',0,'default','POST');
$helper->setProject( $project_id, $team_id, $division_id  );
}






JHTML::_('behavior.mootools');
JHTML::_('behavior.modal');

if ( $params->get('show_favteams_nav_links') )
{
$favteams  = $helper->getFavTeams($project_id);

}

foreach( $points as $row )
{
$federationselect[$row->name]	= $helper->getFederationSelect($row->name,$row->id);
}

$federationselect['NON']	= $helper->getFederationSelect('NON',0);

//$federationselect['afc']	= $helper->getFederationSelect(1);
//$federationselect['caf']	= $helper->getFederationSelect(2);
//$federationselect['conmebol']	= $helper->getFederationSelect(3);
//$federationselect['concacaf']	= $helper->getFederationSelect(4);
//$federationselect['ofc']	= $helper->getFederationSelect(5);
//$federationselect['uefa']	= $helper->getFederationSelect(6);      
//$federationselect['non']	= $helper->getFederationSelect(7);
//$federationselect['turniere']	= $helper->getFederationSelect(8);  

$country_federation	= $helper->getCountryFederation($country_id);

$startoffset = 0;
foreach ( $tab_points as $key => $value )
{
if ( strtolower($country_federation) == $value )
{
$startoffset = $key;
break;
}
}


      



if ( $country_id )
{
$countryassocselect[$country_federation]['assocs']	= $helper->getCountryAssocSelect($country_id);
$leagueselect[$country_federation]['leagues']	= $helper->getAssocLeagueSelect($country_id,$assoc_id);
}

if ( $assoc_id )
{
$countrysubassocselect[$country_federation]['assocs']	= $helper->getCountrySubAssocSelect($assoc_id);
$leagueselect[$country_federation]['leagues']	= $helper->getAssocLeagueSelect($country_id,$assoc_id);
}

if ( $subassoc_id )
{
$countrysubsubassocselect[$country_federation]['subassocs']	= $helper->getCountrySubSubAssocSelect($subassoc_id);
$leagueselect[$country_federation]['leagues']	= $helper->getAssocLeagueSelect($country_id,$subassoc_id);
}

if ( $subsubassoc_id )
{
$countrysubsubsubassocselect[$country_federation]['subsubassocs']	= $helper->getCountrySubSubAssocSelect($subsubassoc_id);
$leagueselect[$country_federation]['leagues']	= $helper->getAssocLeagueSelect($country_id,$subsubassoc_id);
}

if ( $league_id )
{
$projectselect[$country_federation]['projects']	= $helper->getProjectSelect($league_id);
}

if ( $project_id )
{
$helper->setProject($project_id,$team_id,$division_id);
$divisionsselect[$country_federation]['divisions']	= $helper->getDivisionSelect($project_id);
$projectselect[$country_federation]['teams']	= $helper->getTeamSelect($project_id);
}



		
$inject_container = ($params->get('inject', 0)==1)?$params->get('inject_container', 'joomleague'):'';
$document->addScriptDeclaration(';
    jlcinjectcontainer['.$module->id.'] = \''.$inject_container.'\'; 
    jlcmodal['.$module->id.'] = \''.$lightbox.'\';
      ');
      
if (!defined('JLTOPAM_MODULESCRIPTLOADED')) {
	$document->addScript( JURI::base().'modules/mod_sportsmanagement_ajax_top_navigation_menu/js/mod_sportsmanagement_ajax_top_navigation_menu.js' );
	$document->addScriptDeclaration(';
    var ajaxmenu_baseurl=\''. JURI::base() . '\';
      ');
	$document->addStyleSheet(JURI::base().'modules/mod_sportsmanagement_ajax_top_navigation_menu/css/mod_sportsmanagement_ajax_top_navigation_menu.css');
	$document->addStyleSheet(JURI::base().'modules/mod_sportsmanagement_ajax_top_navigation_menu/css/mod_sportsmanagement_ajax_top_navigation_tabs_sliders.css');
	define('JLTOPAM_MODULESCRIPTLOADED', 1);
}


	

// $defaultview   = $params->get('project_start');
// $defaultitemid = $params->get('custom_item_id');

require(JModuleHelper::getLayoutPath('mod_sportsmanagement_ajax_top_navigation_menu'));