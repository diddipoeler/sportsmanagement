<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      mod_sportsmanagement_navigation_menu.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_navigation_menu
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if ( !defined('JSM_PATH') )
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

// get helper
require_once (dirname(__FILE__).DS.'helper.php');

if ( !defined('COM_SPORTSMANAGEMENT_TABLE') )
{
DEFINE( 'COM_SPORTSMANAGEMENT_TABLE',ComponentHelper::getParams( 'com_sportsmanagement' )->get( 'cfg_which_database_table' ) );
}

HTMLHelper::_('behavior.framework');
$document = Factory::getDocument();
//add css file
$document->addStyleSheet(Uri::base().'modules'.DS.$module->module.DS.'css'.DS.$module->module.'.css');
$document->addScript(Uri::base().'modules'.DS.$module->module.DS.'js'.DS.$module->module.'.js');

$helper = new modsportsmanagementNavigationMenuHelper($params);

$seasonselect	= $helper->getSeasonSelect();
$leagueselect	= $helper->getLeagueSelect();
$projectselect	= $helper->getProjectSelect();
$divisionselect = $helper->getDivisionSelect();
$teamselect		= $helper->getTeamSelect();

$defaultview   = $params->get('project_start');
$defaultitemid = $params->get('custom_item_id');

?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require(ModuleHelper::getLayoutPath($module->module));
?>
</div>