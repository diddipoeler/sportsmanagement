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

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if ( !defined('JSM_PATH') )
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

// prüft vor Benutzung ob die gewünschte Klasse definiert ist
if ( !class_exists('sportsmanagementHelper') ) 
{
//add the classes for handling
$classpath = JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php';
JLoader::register('sportsmanagementHelper', $classpath);
JModelLegacy::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

require_once(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'helpers'.DS.'route.php');

// Reference global application object
$app = JFactory::getApplication();
// JInput object
$jinput = $app->input;

$mainframe = JFactory::getApplication();
// sprachdatei aus dem backend laden
$langtag = JFactory::getLanguage();
//echo 'Current language is: ' . $langtag->getTag();

$lang = JFactory::getLanguage();
$extension = 'com_sportsmanagement';
$base_dir = JPATH_ADMINISTRATOR;
$language_tag = $langtag->getTag();
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);

// welche tabelle soll genutzt werden
$paramscomponent = JComponentHelper::getParams( 'com_sportsmanagement' );
$database_table	= $paramscomponent->get( 'cfg_which_database_table' );
$show_debug_info = $paramscomponent->get( 'show_debug_info' );  
$show_query_debug_info = $paramscomponent->get( 'show_query_debug_info' ); 

//if ( !defined('COM_SPORTSMANAGEMENT_TABLE') )
//{
//DEFINE( 'COM_SPORTSMANAGEMENT_TABLE',$database_table );
//}
if ( !defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO') )
{
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO',$show_debug_info );
}
if ( !defined('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO') )
{
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO',$show_query_debug_info );
}

if ( !defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE') )
{
DEFINE( 'COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE',$paramscomponent->get( 'cfg_which_database' ) );
}

if ( COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE )
{
if ( !defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER') )
{    
DEFINE( 'COM_SPORTSMANAGEMENT_PICTURE_SERVER',$cfg_which_database_server );
}    
}
else
{
if ( !defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER') )
{        
DEFINE( 'COM_SPORTSMANAGEMENT_PICTURE_SERVER',JURI::root() );
}    
}


// get helper
require_once (dirname(__FILE__).DS.'helper.php');

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


$tab_points[] = 'NON';


$ajax = $jinput->post->get('ajaxCalMod', 0, 'INT');
$ajaxmod = $jinput->post->get('ajaxmodid', 0, 'INT');
//$ajax = JRequest::getVar('ajaxCalMod',0,'default','POST');
//$ajaxmod = JRequest::getVar('ajaxmodid',0,'default','POST');

$document = JFactory::getDocument();

$queryvalues = $helper->getQueryValues();

//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' queryvalues<br><pre>'.print_r($queryvalues,true).'</pre>'),'');

$assoc_id = 0;
$subassoc_id = 0;  
$subsubassoc_id = 0;  
$user_name = $helper->getUserName();
$project_id = 0;  
$league_id = 0;
$division_id = 0;
$team_id = 0;  
$country_id  = ''; 
$lightbox = '';

//if ( $queryvalues && ( isset($_POST['reload_View']) || !$_POST )  )
if ( $queryvalues && ( !$_POST )  )
{
    
$ende_if = false;
$league_assoc_id = 0;
$sub_assoc_parent_id = 0;
$sub_sub_assoc_parent_id = 0;

if ( isset($queryvalues['p']) )
{
$project_id  = intval($queryvalues['p']);
}
if ( isset($queryvalues['tid']) )
{
$team_id  = intval($queryvalues['tid']);
}
if ( isset($queryvalues['division']) )
{
$division_id  = intval($queryvalues['division']);
}

$helper->setProject( $project_id, $team_id, $division_id );
$league_id  = $helper->getLeagueId();
$country_id  = $helper->getProjectCountry($project_id);

$league_assoc_id  = $helper->getLeagueAssocId();
$sub_assoc_parent_id  = $helper->getAssocParentId($league_assoc_id);
$sub_sub_assoc_parent_id  = $helper->getAssocParentId($sub_assoc_parent_id);

//if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
//{

//echo 'league_id  => <pre>'.print_r($league_id  , true).'</pre><br>';
//echo 'country_id  => <pre>'.print_r($country_id  , true).'</pre><br>';
//echo 'league_assoc_id => <pre>'.print_r($league_assoc_id, true).'</pre><br>';
//echo 'sub_assoc_parent_id => <pre>'.print_r($sub_assoc_parent_id, true).'</pre><br>';
//echo 'sub_sub_assoc_parent_id => <pre>'.print_r($sub_sub_assoc_parent_id, true).'</pre><br>';

//}


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

//if ( $_POST && !isset($_POST['reload_View']) )
if ( $_POST  )
{

//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' queryvalues<br><pre>'.print_r($queryvalues,true).'</pre>'),'');
    
$league_id = 0;   
$project_id = 0;
$team_id = 0;
$division_id = 0;
    
//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _POST<br><pre>'.print_r($_POST,true).'</pre>'),'');

$project_id = $jinput->post->get('jlamtopproject', 0, 'INT');
//$project_id  = JRequest::getVar('jlamtopproject',0,'default','POST');

if ( empty($project_id) )
{
//$project_id = JRequest::getInt( "p", 0 );
$project_id = $jinput->request->get('p', 0, 'INT');    
}    

//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id<br><pre>'.print_r($project_id,true).'</pre>'),'');
  

$assoc_id = $jinput->post->get('jlamtopassocid', 0, 'INT');
$subassoc_id = $jinput->post->get('jlamtopsubassocid', 0, 'INT');
$subsubassoc_id = $jinput->post->get('jlamtopsubsubassocid', 0, 'INT');
$country_id = $jinput->post->get('jlamtopcountry', '', 'STR');
$season_id = $jinput->post->get('jlamtopseason', 0, 'INT');
$league_id = $jinput->post->get('jlamtopleague', 0, 'INT');
$team_id = $jinput->post->get('jlamtopteam', 0, 'INT');

//$assoc_id  = JRequest::getVar('jlamtopassocid',0,'default','POST');
//$subassoc_id  = JRequest::getVar('jlamtopsubassocid',0,'default','POST');
//$subsubassoc_id  = JRequest::getVar('jlamtopsubsubassocid',0,'default','POST');
//$country_id  = JRequest::getVar('jlamtopcountry',0,'default','POST');
//$season_id  = JRequest::getVar('jlamtopseason',0,'default','POST');
//$league_id  = JRequest::getVar('jlamtopleague',0,'default','POST');
//$team_id  = JRequest::getVar('jlamtopteam',0,'default','POST');

$helper->setProject( $project_id, $team_id, $division_id  );
}

// welche joomla version ?
if(version_compare(JVERSION,'3.0.0','ge')) 
{
JHTML::_('behavior.framework', true);
}
else
{
JHTML::_('behavior.mootools');
}

JHTML::_('behavior.modal');

if ( $params->get('show_favteams_nav_links') )
{
$favteams  = $helper->getFavTeams($project_id);
}

foreach( $points as $row )
{
$federationselect[$row->name] = $helper->getFederationSelect($row->name,$row->id);
$countryassocselect[$row->name] = '';
$leagueselect[$row->name] = '';
}

$federationselect['NON'] = $helper->getFederationSelect('NON',0);
$countryassocselect['NON'] = '';
$leagueselect['NON'] = '';

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
      
if (!defined('JLTOPAM_MODULESCRIPTLOADED')) 
{
if(version_compare(JVERSION,'3.0.0','ge')) 
{    
	$document->addScript( JURI::base().'modules/'.$module->module.'/js/'.$module->module.'.js' );
}    
else
{
    $document->addScript( JURI::base().'modules/'.$module->module.'/js/'.$module->module.'_2.js' );
}	
    //	$document->addScriptDeclaration(';
//    var ajaxmenu_baseurl=\''. JURI::base() . '\';
//      ');
	$document->addStyleSheet(JURI::base().'modules/'.$module->module.'/css/'.$module->module.'.css');
	$document->addStyleSheet(JURI::base().'modules/'.$module->module.'/css/mod_sportsmanagement_ajax_top_navigation_tabs_sliders.css');
	define('JLTOPAM_MODULESCRIPTLOADED', 1);
}

if(version_compare(JVERSION,'3.0.0','ge')) 
{    
$layout = 'default';
}    
else
{
$layout = 'default_2';
}	

?>           
<div id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require(JModuleHelper::getLayoutPath($module->module,$layout));
?>
</div>