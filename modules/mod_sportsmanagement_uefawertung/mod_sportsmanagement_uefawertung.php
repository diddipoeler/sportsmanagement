<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_uefawertung
 * @file       mod_sportsmanagement_uefawertung.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

/** prüft vor Benutzung ob die gewünschte Klasse definiert ist */
if (!class_exists('JSMModelLegacy'))
{
	JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_SITE);
}


if (!class_exists('JSMCountries'))
{
	JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);
}


if (!class_exists('sportsmanagementHelper'))
{
	/** add the classes for handling */
	$classpath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
	JLoader::register('sportsmanagementHelper', $classpath);
	BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

/** Include the functions only once */
JLoader::register('modJSMUefawertung', __DIR__ . '/helper.php');

$uefapoints = modJSMUefawertung::getData($params);

/**
 * wenn die komponente im frontend nicht geladen oder aufgerufen wurde,
 * dann muss die sprachdatei aus dem backend geladen werden.
 * ansonsten wird der übersetzte text nicht angezeigt.
 */
if (!defined('COM_SPORTSMANAGEMENT_GLOBAL_MONDAY'))
{
	$langtag      = Factory::getLanguage();
	$extension    = 'com_sportsmanagement';
	$base_dir     = JPATH_SITE;
	$language_tag = $langtag->getTag();
	$reload       = true;
	$lang->load($extension, $base_dir, $language_tag, $reload);
}

//$daysOfWeek = array(
//	1 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MONDAY'),
//	2 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TUESDAY'),
//	3 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_WEDNESDAY'),
//	4 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_THURSDAY'),
//	5 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_FRIDAY'),
//	6 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SATURDAY'),
//	7 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SUNDAY')
//);

$document = Factory::getDocument();

/** add css file */
$document->addStyleSheet(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . $module->module . '.css');

?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>"
     id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
	<?PHP
	require ModuleHelper::getLayoutPath($module->module);
	?>
</div>
