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

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );
$app = JFactory::getApplication();

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
if ( !class_exists('JSMCountries') ) 
{
//add the classes for handling
$classpath = JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'countries.php';
JLoader::register('JSMCountries', $classpath);
}

if (! defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE'))
{    
DEFINE( 'COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE',JComponentHelper::getParams('com_sportsmanagement')->get( 'cfg_which_database' ) );
}

/**
 * besonderheit für das inlinehockey update, wenn sich das 
 * modul in einem artikel befindet
 * 
 */
if ($params->get('ishd_update'))
{
$app = JFactory::getApplication();    
$projectid = $params->get('p');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'extensions'.DS.'jsminlinehockey'.DS.'admin'.DS.'models'.DS.'jsminlinehockey.php');
$actionsModel = JModelLegacy::getInstance('jsminlinehockey', 'sportsmanagementModel'); 
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'projectid<br><pre>'.print_r($projectid,true).'</pre>'),'Notice');   
//$actionsModel->getmatches($projectid);
}


if (!defined('_JSMMATCHLISTMODPATH')) 
{ 
    define('_JSMMATCHLISTMODPATH', dirname( __FILE__ ));
}
if (!defined('_JSMMATCHLISTMODURL')) 
{ 
    define('_JSMMATCHLISTMODURL', JURI::base().'modules/'.$module->module.'/');
}

require_once (_JSMMATCHLISTMODPATH.DS.'helper.php');
require_once (_JSMMATCHLISTMODPATH.DS.'connectors'.DS.'sportsmanagement.php');

$ajax= JRequest::getVar('ajaxMListMod',0,'default','POST');
$match_id = JRequest::getVar('match_id',0,'default','POST');
$nr = JRequest::getVar('nr',-1,'default','POST');
$ajaxmod= JRequest::getVar('ajaxmodid',0,'default','POST');
$template = $params->get('template','default');

if(version_compare(JVERSION,'3.0.0','ge')) 
{
} 
else
{
JHTML::_('behavior.mootools');
}

$doc = JFactory::getDocument();

if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
$doc->addScript( JURI::root().'/media/system/js/mootools-core.js');
$doc->addScript( _JSMMATCHLISTMODURL.'assets/js/'.$module->module.'_joomla_3.js' );
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
$doc->addScript( _JSMMATCHLISTMODURL.'assets/js/'.$module->module.'_joomla_2.js' );
} 


$doc->addStyleSheet(_JSMMATCHLISTMODURL.'tmpl/'.$template.DS.$module->module.'.css');
$cssimgurl = ($params->get('use_icons') != '-1') ? _JSMMATCHLISTMODURL.'assets/images/'.$params->get('use_icons').'/'
: _JSMMATCHLISTMODURL.'assets/images/';
$doc->addStyleDeclaration('
div.tool-tip div.tool-title a.sticky_close{
	display:block;
	position:absolute;
	background:url('.$cssimgurl.'cancel.png) !important;
	width:16px;
	height:16px;
}
');
JHTML::_('behavior.tooltip');
$doc->addScriptDeclaration('
  window.addEvent(\'domready\', function() {
    if ($$(\'#modJLML'.$module->id.'holder .jlmlTeamname\')) addJLMLtips(\'#modJLML'.$module->id.'holder .jlmlTeamname\', \'over\');
  }
  );
  ');
$mod = new MatchesSportsmanagementConnector($params, $module->id, $match_id);
$lastheading = '';
$oldprojectid = 0;
$oldround_id  = 0;
if($ajax == 0) { echo '<div id="modJLML'.$module->id.'holder" class="modJLMLholder">';}
$matches = $mod->getMatches();

$cnt=($nr >= 0) ? $nr : 0;
if (count($matches) > 0){
	//$user = JFactory::getUser();
	foreach ($matches AS $key => $match) {
		if(!isset($match['project_id'])) continue; 
		$styleclass =($cnt%2 == 1) ? $params->get('sectiontableentry1') : $params->get('sectiontableentry2');
		$show_pheading = false;
		$pheading = '';
		if (isset($match['type'])) {
			$heading = $params->get($match['type'].'_notice');
		}
		else { $heading = ''; }
		if ($match['project_id'] != $oldprojectid OR $match['round_id'] != $oldround_id) {
			if (!empty($match['heading'])) $show_pheading = true;
			$pheading .= $match['heading'];
		}
		include(JModuleHelper::getLayoutPath($module->module, $template.DS.'match'));
		$lastheading = $heading;
		$oldprojectid = $match['project_id'];
		$oldround_id = $match['round_id'];
		$cnt++;
	}
}
elseif ( $params->get('show_no_matches_notice') ) {
	echo '<br />'.$params->get('no_matches_notice').'<br />';
}
if($ajax == 0) { echo '</div>';}
?>
