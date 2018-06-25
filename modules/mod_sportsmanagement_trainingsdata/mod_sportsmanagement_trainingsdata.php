<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      mod_sportsmanagement_trainingsdata.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_trainingsdata
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

/**
 * prüft vor Benutzung ob die gewünschte Klasse definiert ist
 */
if ( !class_exists('sportsmanagementHelper') ) 
{
/**
 * add the classes for handling
 */
$classpath = JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php';
JLoader::register('sportsmanagementHelper', $classpath);
JModelLegacy::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

/**
 * get helper
 */
require_once (dirname(__FILE__).DS.'helper.php');

$trainingsdata = modJSMTrainingsData::getData($params);

/**
 * wenn die komponente im frontend nicht geladen oder aufgerufen wurde,
 * dann muss die sprachdatei aus dem backend geladen werden.
 * ansonsten wird der übersetzte text nicht angezeigt.
 */
if ( !defined('COM_SPORTSMANAGEMENT_GLOBAL_MONDAY') )
{
$langtag = JFactory::getLanguage();
$extension = 'com_sportsmanagement';
$base_dir = JPATH_SITE;
$language_tag = $langtag->getTag();
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);
}

$daysOfWeek = array(
				1 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MONDAY'),
				2 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_TUESDAY'),
				3 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_WEDNESDAY'),
				4 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_THURSDAY'),
				5 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_FRIDAY'),
				6 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SATURDAY'),
				7 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SUNDAY')
			);
            
$document = JFactory::getDocument();

/**
 * add css file
 */
$document->addStyleSheet(JUri::base().'modules'.DS.$module->module.DS.'css'.DS.$module->module.'.css');

?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require(JModuleHelper::getLayoutPath($module->module));
?>
</div>