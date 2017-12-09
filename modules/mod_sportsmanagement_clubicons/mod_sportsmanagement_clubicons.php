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
* 
* https://stanhub.com/how-to-create-zoom-effect-on-image-hover-with-css-and-jquery/
* 
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

if (!class_exists('sportsmanagementModeldatabasetool')) 
{
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'libraries'.DS.'sportsmanagement'.DS.'model.php');	
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'models'.DS.'databasetool.php');
}

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

if (! defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE',JComponentHelper::getParams('com_sportsmanagement')->get( 'cfg_which_database' ) );
}

require_once (dirname(__FILE__).DS.'helper.php');

$data = new modJSMClubiconsHelper ($params,$module);

$cnt = count($data->teams);
$cnt = ($cnt < $params->get('iconsperrow', 20)) ? $cnt : $params->get('iconsperrow', 20);

// welche joomla version ?
if(version_compare(JVERSION,'3.0.0','ge')) 
{
JHTML::_('behavior.framework', true);
}
else
{
JHTML::_('behavior.mootools');
}

$doc = JFactory::getDocument();
$doc->addStyleSheet(JURI::base() . 'modules'.DS.$module->module.DS.'css/style.css');

if ( $cnt )
{
$script =  'script';
$doc->addScript( JURI::base() . 'modules'.DS.$module->module.DS.'js/'.$script.'.js');
?>           
<div id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require(JModuleHelper::getLayoutPath($module->module, $tpl));
?>
</div>
<?PHP
}


?>