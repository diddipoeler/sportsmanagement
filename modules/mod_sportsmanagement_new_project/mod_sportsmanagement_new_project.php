<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_new_project
 * @file       mod_sportsmanagement_new_project.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

/**
 * Prüft vor Benutzung ob die gewünschte Klasse definiert ist
 */
if (!class_exists('sportsmanagementHelper'))
{
	$classpath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
	JLoader::register('sportsmanagementHelper', $classpath);
	BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

if (!defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE'))
{
	DEFINE('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database'));
}

/**
 * Include the functions only once
 */
JLoader::register('modJSMNewProjectHelper', __DIR__ . '/helper.php');

$document = Factory::getDocument();

// Rendering is read-only. Article generation must be triggered explicitly via
// modJSMNewProjectHelper::createArticles().
$list = modJSMNewProjectHelper::getData();

// Add css file
// $document->addStyleSheet(Uri::base().'modules/mod_sportsmanagement_new_project/css/mod_sportsmanagement_new_project.css');
?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>"
     id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
	<?PHP
	require ModuleHelper::getLayoutPath($module->module);
	?>
</div>
