<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @file       mod_sportsmanagement_teamstatistics_counter.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    This file is part of SportsManagement.
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_teamstatistics_counter
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

// prüft vor Benutzung ob die gewünschte Klasse definiert ist
if (!class_exists('JSMModelLegacy'))
{
	JLoader::import('components.com_sportsmanagement.libraries.sportsmanagement.model', JPATH_SITE);
}

require_once(JPATH_SITE . DS . JSM_PATH . DS . 'helpers' . DS . 'route.php');
require_once(JPATH_SITE . DS . JSM_PATH . DS . 'models' . DS . 'project.php');
require_once(JPATH_SITE . DS . JSM_PATH . DS . 'models' . DS . 'teamstats.php');

// get helper
require_once(dirname(__FILE__) . DS . 'helper.php');

$data     = modJSMTeamStatisticsCounter::getData($params);
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'modules/mod_sportsmanagement_teamstatistics_counter/css/mod_sportsmanagement_teamstatistics_counter.css');
?>

<div class="<?php echo $params->get('moduleclass_sfx'); ?>"
     id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
	<?PHP require(JModuleHelper::getLayoutPath($module->module)); ?>
</div>
