<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      mod_sportsmanagement_rquotes.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_rquotes
 */
 
 
 //no direct access
defined('_JEXEC') or die('Restricted access'); 
if(!defined('DS'))
{
define('DS',DIRECTORY_SEPARATOR);
error_reporting(0);
}

if ( !defined('JSM_PATH') )
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

//include helper file	
require_once(dirname(__FILE__).DS.'helper.php'); 
// prüft vor Benutzung ob die gewünschte Klasse definiert ist
if ( !class_exists('sportsmanagementHelper') ) 
{
//add the classes for handling
$classpath = JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php';
JLoader::register('sportsmanagementHelper', $classpath);
JModelLegacy::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

$source = $params->get('source');
$cfg_which_database = $params->get('cfg_which_database');
//text file params
$filename=$params->get('filename','rquotes.txt');
$randomtext=$params->get('randomtext');
//database params
$style = $params->get('style', 'default'); 
$category = $params->get('category','');
$rotate = $params->get('rotate');
$num_of_random = $params->get('num_of_random');

switch ($source) 
{
case 'db':
if($rotate=='single_random')
{
$list = modRquotesHelper::getRandomRquote($category,$num_of_random,$params);
}
elseif($rotate=='multiple_random')
{
$list = modRquotesHelper::getMultyRandomRquote($category,$num_of_random,$params);
}
elseif($rotate=='sequential') 
{
$list = modRquotesHelper::getSequentialRquote($category,$params);
}
elseif($rotate=='daily')
{
$list = modRquotesHelper::getDailyRquote($category,$params);
}
elseif($rotate=='weekly')
{
$list = modRquotesHelper::getWeeklyRquote($category,$params);
}
elseif($rotate=='monthly')
{
$list = modRquotesHelper::getMonthlyRquote($category,$params);
}
elseif($rotate=='yearly')
{
$list = modRquotesHelper::getYearlyRquote($category,$params);
}
//start
elseif($rotate=='today')
{
$list = modRquotesHelper::getTodayRquote($category,$params);
}

//end
?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require(JModuleHelper::getLayoutPath($module->module, $style,'default'));
?>
</div>
<?PHP
break;

case 'text':
if (!$randomtext)
{
$list = modRquotesHelper::getTextFile($params,$filename,$module);
}
else
{
$list = modRquotesHelper::getTextFile2($params,$filename,$module);
}
break;
default:
echo JText::_('MOD_SPORTSMANAGEMENT_RQUOTES_SAVE_DISPLAY_INFORMATION');


}
?> 
