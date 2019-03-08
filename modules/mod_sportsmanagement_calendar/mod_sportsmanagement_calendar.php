<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      mod_sportsmanagement_calendar.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_calendar
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

$app = Factory::getApplication();

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
DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO',ComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0) );
}

if (!class_exists('sportsmanagementHelper')) 
{
JLoader::import('components.com_sportsmanagement.helpers.sportsmanagement', JPATH_ADMINISTRATOR);
}
if (!class_exists('sportsmanagementHelperRoute')) 
{
JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);
}

if (! defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE',ComponentHelper::getParams('com_sportsmanagement')->get( 'cfg_which_database' ) );
}


// Reference global application object
$app = Factory::getApplication();
// JInput object
$jinput = $app->input;
        
// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

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
//$doc->addScript( Uri::root().'/media/system/js/mootools-core.js');    
$config = Factory::getConfig();
$offset = $config->get('offset');    
$year = $jinput->getVar('year', $startDate->toFormat('Y'));
$month  = $jinput->getVar('month', $startDate->toFormat('m'));
$day  = $ajax? '' : $jinput->getVar('day', $startDate->toFormat('d'));      
}
else
{    
$config = Factory::getConfig();
$offset = $config->get('offset'); 
$year = $jinput->getVar('year', $startDate->toFormat('%Y'));
$month  = $jinput->getVar('month', $startDate->toFormat('%m'));
$day  = $ajax? '' : $jinput->getVar('day', $startDate->toFormat('%d'));
}
    
}

$helper = new modJSMCalendarHelper;
$doc = Factory::getDocument();
$lightbox = $params->get('lightbox', 1);


//if ($lightbox ==1 && (!isset($_GET['format']) OR ($_GET['format'] != 'pdf'))) 
//{
//	$doc->addScriptDeclaration(";
//      window.addEvent('domready', function() {
//          $$('a.jlcmodal".$module->id."').each(function(el) {
//            el.addEvent('click', function(e) {
//              new Event(e).stop();
//              SqueezeBox.fromElement(el);
//            });
//          });
//      });
//      ");
//}

$inject_container = ($params->get('inject', 0)==1)?$params->get('inject_container', 'sportsmanagement'):'';
//$doc->addScriptDeclaration(';
//    jlcinjectcontainer['.$module->id.'] = \''.$inject_container.'\';
//    jlcmodal['.$module->id.'] = \''.$lightbox.'\';
//      ');

if (!defined('JLC_MODULESCRIPTLOADED')) 
{
if(version_compare(JVERSION,'3.0.0','ge')) 
{    
    //$doc->addScript( Uri::root().'/media/system/js/mootools-core.js');
    $doc->addScript( Uri::root().'/media/system/js/mootools-core-uncompressed.js');
    $doc->addScript( Uri::root().'/media/system/js/mootools-more-uncompressed.js');
    $doc->addScript( Uri::root().'/media/system/js/modal-uncompressed.js');
	$doc->addScript( Uri::base().'modules'.DS.$module->module.DS.'assets/js'.DS.$module->module.'.js' );
}
else
{
    $doc->addScript( Uri::base().'modules'.DS.$module->module.DS.'assets/js'.DS.$module->module.'_2.js' );
}    

//	$doc->addScriptDeclaration(';
//    var calendar_baseurl=\''. Uri::base() . '\';
//      ');
	$doc->addStyleSheet(Uri::base().'modules'.DS.$module->module.DS.'assets/css'.DS.$module->module.'.css');
	define('JLC_MODULESCRIPTLOADED', 1);
}
$calendar = $helper->showCal($params,$year,$month,$ajax,$module->id);
?>           
<div class="<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require(ModuleHelper::getLayoutPath($module->module));
?>
</div>