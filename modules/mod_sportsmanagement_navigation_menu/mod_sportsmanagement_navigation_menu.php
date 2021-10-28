<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_navigation_menu
 * @file       mod_sportsmanagement_navigation_menu.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

// Get helper
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helper.php';

// Welche joomla version ?
if (version_compare(JVERSION, '4.0.0', 'ge'))
{
	//HTMLHelper::_('behavior.framework', true);
}
else if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	HTMLHelper::_('behavior.framework', true);
}

$document = Factory::getDocument();

// Add css file
$document->addStyleSheet(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . $module->module . '.css');
$document->addScript(Uri::base() . 'modules' . DIRECTORY_SEPARATOR . $module->module . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . $module->module . '.js');

$helper = new modsportsmanagementNavigationMenuHelper($params);

$seasonselect   = $helper->getSeasonSelect();
$leagueselect   = $helper->getLeagueSelect();
$projectselect  = $helper->getProjectSelect();
$divisionselect = $helper->getDivisionSelect();
$teamselect     = $helper->getTeamSelect();

$defaultview   = $params->get('project_start');
$defaultitemid = $params->get('custom_item_id');

?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>"
     id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
	<?PHP
	require ModuleHelper::getLayoutPath($module->module);
	?>
</div>
