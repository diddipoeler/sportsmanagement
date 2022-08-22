<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_rquotes
 * @file       mod_sportsmanagement_rquotes.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

/**
 *
 * Include the functions only once
 */
JLoader::register('modRquotesHelper', __DIR__ . '/helper.php');

// Prüft vor Benutzung ob die gewünschte Klasse definiert ist
if (!class_exists('sportsmanagementHelper'))
{
	// Add the classes for handling
	$classpath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
	JLoader::register('sportsmanagementHelper', $classpath);
	BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

$source             = $params->get('source');
$cfg_which_database = $params->get('cfg_which_database');

// Text file params
$filename   = $params->get('filename', 'rquotes.txt');
$randomtext = $params->get('randomtext');

// Database params
$style         = $params->get('template', 'default');
$category      = $params->get('category', '');
$rotate        = $params->get('rotate');
$num_of_random = $params->get('num_of_random');

switch ($source)
{
	case 'db':
		if ($rotate == 'single_random')
		{
			$list = modRquotesHelper::getRandomRquote($category, $num_of_random, $params);
		}
        elseif ($rotate == 'multiple_random')
		{
			$list = modRquotesHelper::getMultyRandomRquote($category, $num_of_random, $params);
		}
        elseif ($rotate == 'sequential')
		{
			$list = modRquotesHelper::getSequentialRquote($category, $params);
		}
        elseif ($rotate == 'daily')
		{
			$list = modRquotesHelper::getDailyRquote($category, $params);
		}
        elseif ($rotate == 'weekly')
		{
			$list = modRquotesHelper::getWeeklyRquote($category, $params);
		}
        elseif ($rotate == 'monthly')
		{
			$list = modRquotesHelper::getMonthlyRquote($category, $params);
		}
        elseif ($rotate == 'yearly')
		{
			$list = modRquotesHelper::getYearlyRquote($category, $params);
		}
		// Start
        elseif ($rotate == 'today')
		{
			$list = modRquotesHelper::getTodayRquote($category, $params);
		}

		// End
		?>
        <div class="<?php echo $params->get('moduleclass_sfx'); ?>"
             id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
			<?PHP
			include ModuleHelper::getLayoutPath($module->module, $style, 'default');
			?>
        </div>
		<?PHP
		break;

	case 'text':
		if (!$randomtext)
		{
			$list = modRquotesHelper::getTextFile($params, $filename, $module);
		}
		else
		{
			$list = modRquotesHelper::getTextFile2($params, $filename, $module);
		}
		break;
	default:
		echo Text::_('MOD_SPORTSMANAGEMENT_RQUOTES_SAVE_DISPLAY_INFORMATION');
}
