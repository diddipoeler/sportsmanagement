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

defined('_JEXEC') or die('Restricted access');

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if ( !defined('JSM_PATH') )
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'route.php' );
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'databasetool.php');
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php');   
require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'project.php' );
require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'ranking.php' );
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'ranking.php' );

// welche tabelle soll genutzt werden
$paramscomponent = JComponentHelper::getParams( 'com_sportsmanagement' );
$database_table	= $paramscomponent->get( 'cfg_which_database_table' );
$show_debug_info = $paramscomponent->get( 'show_debug_info' );  
$show_query_debug_info = $paramscomponent->get( 'show_query_debug_info' ); 
if ( !defined('COM_SPORTSMANAGEMENT_TABLE') )
{
DEFINE( 'COM_SPORTSMANAGEMENT_TABLE',$database_table );
}
if ( !defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO') )
{
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO',$show_debug_info );
}
if ( !defined('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO') )
{
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO',$show_query_debug_info );
}


require_once (dirname(__FILE__).DS.'helper.php');

$data = new modJSMClubiconsHelper ($params);

$cnt = count($data->teams);
$cnt = ($cnt < $params->get('iconsperrow', 20)) ? $cnt : $params->get('iconsperrow', 20);

JHTML::_('behavior.mootools');
$doc = JFactory::getDocument();
$doc->addStyleSheet(JURI::base() . 'modules/mod_sportsmanagement_clubicons/css/style.css');
$css = 'img.smstarticon { width:25px;}';
if ($params->get('max_width', 800) > 0 AND $cnt <= 20) $css .= 'table.modjlclubicons { max-width: '.$params->get('max_width', 800).'px;}
div.modjlclubicons { max-width: '.$params->get('max_width', 800).'px;}';
$doc->addStyleDeclaration($css);

if ( $cnt )
{
$tdw = intval(100/$cnt);
$doc->addStyleDeclaration('td.modjlclubicons { width:'.$tdw.'%;}
span.modjlclubicons { width:'.$tdw.'%;}
span.modjlclubicons > img{ width:70%;}');
$tpl = $params->get( 'template','default' );
$rowsep = 'tr';
$initjs = "
    var jcclubiconsglobalmaxwidth = ".$params->get('jcclubiconsglobalmaxwidth', 0).";
    window.addEvent('domready', function(){
      $('clubicons".$module->id."').setStyle('opacity', 0);
      new Asset.images($('clubicons".$module->id."').getElements('img.smstarticon'), {
      onComplete: function(){
          observeClubIcons(".$module->id.", 'img.smstarticon', ".$cnt.", ".$params->get( 'logo_widthdif',12 ).", '".$rowsep."');
          $('clubicons".$module->id."').fade('in');
      }
    });
});
window.addEvent('resize', function(){
    observeClubIcons(".$module->id.", 'img.smstarticon', ".$cnt.", ".$params->get( 'logo_widthdif',12 ).", '".$rowsep."');

});

";
$mv = JFactory::getApplication()->get('MooToolsVersion');
$script =  'script';
$doc->addScript( JURI::base() . 'modules/mod_sportsmanagement_clubicons/js/'.$script.'.js');
$doc->addScriptDeclaration($initjs);
require(JModuleHelper::getLayoutPath('mod_sportsmanagement_clubicons', $tpl));
}

/* NOTE: this is not implemented yet:
if ($tpl == 'tableless') {
  ;
  $initjs = "
    window.addEvent('domready', function(){
      var modjlicons".$module->id." = new observeClubIcons($('clubicons".$module->id."'), {'imgclass': 'img.smstarticon', 'itemcnt':".$cnt.", 'wdiff':".$params->get( 'logo_widthdif',12 ).", 'position':'".$params->get( 'iconpos','middle' )."'});
    });
";
  $script .= '.class';
  if ($my->id == 62) $script.='.uncompressed';
}
*/
//echo '<strong>'.$mv.'</strong><br />';
?>