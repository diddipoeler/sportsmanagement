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
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
//require_once(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'sportsmanagement.php');

require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php');  

if (!defined('_JLMATCHLISTMODPATH')) { define('_JLMATCHLISTMODPATH', dirname( __FILE__ ));}
if (!defined('_JLMATCHLISTMODURL')) { define('_JLMATCHLISTMODURL', JURI::base().'modules/mod_sportsmanagement_matches/');}
require_once (_JLMATCHLISTMODPATH.DS.'helper.php');
require_once (_JLMATCHLISTMODPATH.DS.'connectors'.DS.'sportsmanagement.php');

$ajax= JRequest::getVar('ajaxMListMod',0,'default','POST');
$match_id = JRequest::getVar('match_id',0,'default','POST');
$nr = JRequest::getVar('nr',-1,'default','POST');
$ajaxmod= JRequest::getVar('ajaxmodid',0,'default','POST');
$template = $params->get('template','default');
JHTML::_('behavior.mootools');
$doc = JFactory::getDocument();
$doc->addScript( _JLMATCHLISTMODURL.'assets/js/mod_sportsmanagement_matches.js' );
$doc->addStyleSheet(_JLMATCHLISTMODURL.'tmpl/'.$template.'/mod_sportsmanagement_matches.css');
$cssimgurl = ($params->get('use_icons') != '-1') ? _JLMATCHLISTMODURL.'assets/images/'.$params->get('use_icons').'/'
: _JLMATCHLISTMODURL.'assets/images/';
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
		$styleclass=($cnt%2 == 1) ? $params->get('sectiontableentry1') : $params->get('sectiontableentry2');
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
		include(JModuleHelper::getLayoutPath('mod_sportsmanagement_matches', $template.DS.'match'));
		$lastheading = $heading;
		$oldprojectid = $match['project_id'];
		$oldround_id = $match['round_id'];
		$cnt++;
	}
}
elseif ($params->get('show_no_matches_notice') == 1) {
	echo '<br />'.$params->get('no_matches_notice').'<br />';
}
if($ajax == 0) { echo '</div>';}
?>
