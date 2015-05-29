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

if (! defined('JSM_PATH'))
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

if (! defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO',JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0) );
}

if (!class_exists('sportsmanagementHelper')) 
{
require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php');  
}
if (!class_exists('sportsmanagementHelperRoute')) 
{
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'route.php' );
}

// Reference global application object
$app = JFactory::getApplication();
// JInput object
$jinput = $app->input;
        
// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');
//require_once(JPATH_SITE.DS.'components'.DS.'com_joomleague'.DS.'joomleague.core.php');

JHtml::_('behavior.tooltip');
$ajax= $jinput->getVar('ajaxCalMod',0,'default','POST');
$ajaxmod= $jinput->getVar('ajaxmodid',0,'default','POST');
if(!$params->get('cal_start_date'))
{
	$year = $jinput->getVar('year',date('Y'));    /*if there is no date requested, use the current month*/
	$month  = $jinput->getVar('month',date('m'));
	$day  = $jinput->getVar('day',0);
}
else
{

$startDate= new JDate($params->get('cal_start_date'));
    
if(version_compare(JVERSION,'3.0.0','ge')) 
{
$config = JFactory::getConfig();
$offset = $config->get('offset');    
$year = $jinput->getVar('year', $startDate->toFormat('Y'));
$month  = $jinput->getVar('month', $startDate->toFormat('m'));
$day  = $ajax? '' : $jinput->getVar('day', $startDate->toFormat('d'));      
}
else
{    
$config = JFactory::getConfig();
$offset = $config->get('offset'); 
$year = $jinput->getVar('year', $startDate->toFormat('%Y'));
$month  = $jinput->getVar('month', $startDate->toFormat('%m'));
$day  = $ajax? '' : $jinput->getVar('day', $startDate->toFormat('%d'));
}
    
}

$helper = new modJSMCalendarHelper;
$doc = JFactory::getDocument();
$lightbox = $params->get('lightbox', 1);

JHtml::_('behavior.framework');
JHtml::_('behavior.modal');
if ($lightbox ==1 && (!isset($_GET['format']) OR ($_GET['format'] != 'pdf'))) {
	$doc->addScriptDeclaration(";
      window.addEvent('domready', function() {
          $$('a.jlcmodal".$module->id."').each(function(el) {
            el.addEvent('click', function(e) {
              new Event(e).stop();
              SqueezeBox.fromElement(el);
            });
          });
      });
      ");
}
$inject_container = ($params->get('inject', 0)==1)?$params->get('inject_container', 'joomleague'):'';
$doc->addScriptDeclaration(';
    jlcinjectcontainer['.$module->id.'] = \''.$inject_container.'\';
    jlcmodal['.$module->id.'] = \''.$lightbox.'\';
      ');

if (!defined('JLC_MODULESCRIPTLOADED')) {
	$doc->addScript( JUri::base().'modules'.DS.$module->module.DS.'assets/js'.DS.$module->module.'.js' );
	$doc->addScriptDeclaration(';
    var calendar_baseurl=\''. JUri::base() . '\';
      ');
	$doc->addStyleSheet(JUri::base().'modules'.DS.$module->module.DS.'assets/css'.DS.$module->module.'.css');
	define('JLC_MODULESCRIPTLOADED', 1);
}
$calendar = $helper->showCal($params,$year,$month,$ajax,$module->id);

require(JModuleHelper::getLayoutPath($module->module));

?>