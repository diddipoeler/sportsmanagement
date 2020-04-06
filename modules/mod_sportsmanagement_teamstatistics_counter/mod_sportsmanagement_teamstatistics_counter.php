<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       mod_sportsmanagement_teamstatistics_counter.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_teamstatistics_counter
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JSM_PATH')) {
    DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

// prüft vor Benutzung ob die gewünschte Klasse definiert ist
if (!class_exists('JSMModelLegacy')) {
    JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_SITE);
}

JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.models.project', JPATH_SITE);
JLoader::import('components.com_sportsmanagement.models.teamstats', JPATH_SITE);

/**
*
 * Include the functions only once
*/
JLoader::register('modJSMTeamStatisticsCounter', __DIR__ . '/helper.php');

$data     = modJSMTeamStatisticsCounter::getData($params);
$document = Factory::getDocument();
/**
 * add css file
 */
$document->addStyleSheet(Uri::base().'modules'.DIRECTORY_SEPARATOR.$module->module.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.$module->module.'.css');

?>

<div class="<?php echo $params->get('moduleclass_sfx'); ?>"
     id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
    <?PHP require ModuleHelper::getLayoutPath($module->module); ?>
</div>
