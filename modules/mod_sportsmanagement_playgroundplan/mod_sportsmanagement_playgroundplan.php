<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_playgroundplan
 * @file       mod_sportsmanagement_playgroundplan.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

/**
 * prüft vor Benutzung ob die gewünschte Klasse definiert ist
 */
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
	/**
 * add the classes for handling
 */
	$classpath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
	JLoader::register('sportsmanagementHelper', $classpath);
	BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);

/**
*
 * Include the functions only once
*/
JLoader::register('modSportsmanagementPlaygroundplanHelper', __DIR__ . '/helper.php');

$list = modSportsmanagementPlaygroundplanHelper::getData($params);

$document = Factory::getDocument();

/**
 * add css file
 */
$document->addStyleSheet(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . $module->module . '.css');

$mode = $params->def("mode");

switch ($mode)
{
	case 0:
		$document->addScript(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'js/qscroller.js');
		include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'ticker.js';
	break;
	case 1:
	break;
}

?>
<div class="<?php echo $params->get('divclasscontainer'); ?> table-responsive" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require ModuleHelper::getLayoutPath($module->module);
?>
</div>
