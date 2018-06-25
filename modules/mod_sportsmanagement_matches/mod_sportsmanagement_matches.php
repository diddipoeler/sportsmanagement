<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      mod_sportsmanagement_matches.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_matches
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
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'projectid<br><pre>'.print_r($projectid,true).'</pre>'),'Notice');  
for($a=0; $a < sizeof($projectid); $a++ )
{
$project_id = (int)$projectid[$a];
$count_games = MatchesSportsmanagementConnector::getCountGames($project_id,(int)$params->get('ishd_update_hour',4));
if ( $count_games )
{
$actionsModel->getmatches($project_id);    
}

}    

}

$ajax = $app->input->post->get('ajaxMListMod', 0);
$match_id = $app->input->post->get('match_id', 0);
$nr = $app->input->post->get('nr', -1);
$ajaxmod = $app->input->post->get('ajaxmodid', 0);

/*
$ajax= JRequest::getVar('ajaxMListMod',0,'default','POST');
$match_id = JRequest::getVar('match_id',0,'default','POST');
$nr = JRequest::getVar('nr',-1,'default','POST');
$ajaxmod= JRequest::getVar('ajaxmodid',0,'default','POST');
*/

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

//echo 'matches <pre>'.print_r($matches,true).'</pre>';

$cnt = ($nr >= 0) ? $nr : 0;
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
